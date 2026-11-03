<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use JMS\Serializer\SerializationContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use SourceBroker\T3api\Exception\ExceptionInterface;
use SourceBroker\T3api\Service\FileTokenService;
use SourceBroker\T3api\Service\SerializerService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\RequestContext;
use T3apiTests\FunctionalTest\Domain\Model\Recipe;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * A file reference can be created by `uidLocal` (and a file property set) only with the token returned for the file by
 * the API: in the upload response, or in the response of an operation with `"fileTokens"=true` (files offered for
 * selection).
 */
class FileTokenDispatcherTest extends AbstractDispatcherTestCase
{
    private const RECIPE_TABLE = 'tx_functionaltest_domain_model_recipe';
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'encryption-key-of-functional-test';
        $this->getConnectionPool()->getConnectionForTable('sys_file_storage')->insert('sys_file_storage', [
            'uid' => 1,
            'pid' => 0,
            'name' => 'fileadmin',
            'driver' => 'Local',
            'configuration' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="basePath"><value index="vDEF">fileadmin/</value></field><field index="pathType"><value index="vDEF">relative</value></field><field index="caseSensitive"><value index="vDEF">1</value></field></language></sheet></data></T3FlexForms>',
            'is_browsable' => 1,
            'is_public' => 1,
            'is_writable' => 1,
            'is_online' => 1,
        ]);
        GeneralUtility::mkdir_deep(Environment::getPublicPath() . '/fileadmin/avatars');
        foreach ([50 => 'one.png', 51 => 'two.png'] as $uid => $name) {
            file_put_contents(Environment::getPublicPath() . '/fileadmin/avatars/' . $name, base64_decode(self::PNG));
            $this->getConnectionPool()->getConnectionForTable('sys_file')->insert('sys_file', [
                'uid' => $uid,
                'pid' => 0,
                'storage' => 1,
                'type' => 2,
                'identifier' => '/avatars/' . $name,
                'identifier_hash' => sha1('/avatars/' . $name),
                'folder_hash' => sha1('/avatars/'),
                'extension' => 'png',
                'mime_type' => 'image/png',
                'name' => $name,
            ]);
        }
        // The test request is a backend request, so FAL evaluates storage permissions of a backend user.
        $this->getConnectionPool()->getConnectionForTable('be_users')
            ->insert('be_users', ['uid' => 1, 'pid' => 0, 'username' => 'admin', 'admin' => 1]);
        $this->setUpBackendUser(1);
        $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE)
            ->insert(self::RECIPE_TABLE, ['uid' => 1, 'pid' => 0, 'title' => 'Recipe']);
    }

    #[Test]
    public function uploadResponseContainsFileToken(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 14) {
            self::markTestSkipped('Before TYPO3 14 FAL accepts only files uploaded through HTTP (is_uploaded_file())');
        }
        $path = tempnam(sys_get_temp_dir(), 't3api');
        file_put_contents($path, base64_decode(self::PNG));
        $request = SymfonyRequest::create(
            'https://example.com/_api/files',
            'POST',
            [],
            [],
            ['originalResource' => new UploadedFile($path, 'photo.png', 'image/png', null, true)]
        );

        $file = json_decode($this->dispatch($request)['body'], true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($this->get(FileTokenService::class)->isValidToken($file['fileToken'] ?? '', $file['uid']));
    }

    #[Test]
    public function onlyGrantedFileGetsTokenInResponse(): void
    {
        $resourceFactory = $this->get(ResourceFactory::class);
        $this->get(FileTokenService::class)->grant(50);

        $serialize = fn(int $uid): array => json_decode($this->get(SerializerService::class)->serialize(
            $resourceFactory->getFileObject($uid),
            SerializationContext::create()->setAttribute('TYPO3_SITE_URL', 'https://example.com/')
        ), true, 512, JSON_THROW_ON_ERROR);
        $granted = $serialize(50);
        $notGranted = $serialize(51);

        self::assertTrue($this->get(FileTokenService::class)->isValidToken($granted['fileToken'] ?? '', 50));
        self::assertArrayNotHasKey('fileToken', $notGranted);
    }

    #[Test]
    public function operationWithFileTokensAddsTokenToEveryFile(): void
    {
        $response = $this->dispatch(SymfonyRequest::create('https://example.com/_api/files', 'GET'));
        $members = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR)['hydra:member'] ?? [];

        self::assertSame(200, $response['status'], $response['body']);
        self::assertCount(2, $members);
        foreach ($members as $member) {
            self::assertTrue($this->get(FileTokenService::class)->isValidToken($member['fileToken'] ?? '', $member['uid']));
            // Virtual properties documented for files offered for selection
            self::assertStringEndsWith('.png', $member['url'] ?? '');
            self::assertStringEndsWith('.png', $member['name'] ?? '');
            // Not repeated in the original resource (serialized since TYPO3 13)
            self::assertArrayNotHasKey('fileToken', $member['originalResource'] ?? []);
        }
    }

    #[Test]
    public function referenceIsCreatedWithFileToken(): void
    {
        $fileToken = $this->get(FileTokenService::class)->createToken(50);

        $response = $this->patchImages([['uidLocal' => 50, 'fileToken' => $fileToken]]);

        self::assertSame(200, $response['status'], $response['body']);
        self::assertSame(1, $this->countReferences(50));
    }

    #[Test]
    public function referenceIsNotCreatedWithoutValidFileToken(): void
    {
        foreach ([
            'missing token' => ['uidLocal' => 50],
            'invalid token' => ['uidLocal' => 50, 'fileToken' => '1.' . str_repeat('a', 64)],
            'token of other file' => ['uidLocal' => 50, 'fileToken' => $this->get(FileTokenService::class)->createToken(51)],
            'token of wrong type' => ['uidLocal' => 50, 'fileToken' => ['x']],
        ] as $case => $reference) {
            $response = $this->patchImages([$reference]);
            $json = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

            self::assertSame(400, $response['status'], $case . ': ' . $response['body']);
            self::assertSame('fileToken', $json['violations'][0]['propertyPath'] ?? null, $case);
        }
        self::assertSame(0, $this->countReferences(50));
    }

    #[Test]
    public function fileOfReferenceGetsTokenInResponseOfOperationWithFileTokens(): void
    {
        $this->getConnectionPool()->getConnectionForTable('sys_file_reference')->insert('sys_file_reference', [
            'uid' => 1,
            'pid' => 0,
            'uid_local' => 50,
            'uid_foreign' => 1,
            'tablenames' => self::RECIPE_TABLE,
            'fieldname' => 'image',
        ]);
        $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE)
            ->update(self::RECIPE_TABLE, ['image' => 1], ['uid' => 1]);
        $recipe = $this->get(PersistenceManager::class)->getObjectByIdentifier(1, Recipe::class);

        $serialize = fn(bool $fileTokens): array => json_decode($this->get(SerializerService::class)->serialize(
            $recipe,
            SerializationContext::create()
                ->setAttribute('TYPO3_SITE_URL', 'https://example.com/')
                ->setAttribute('fileTokens', $fileTokens)
        ), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($this->get(FileTokenService::class)->isValidToken($serialize(true)['image']['file']['fileToken'] ?? '', 50));
        self::assertArrayNotHasKey('fileToken', $serialize(false)['image']['file'] ?? []);
    }

    #[Test]
    public function fileIsSetWithFileToken(): void
    {
        $fileToken = $this->get(FileTokenService::class)->createToken(50);

        $response = $this->patchRecipe(['logo' => ['uid' => 50, 'fileToken' => $fileToken]]);

        self::assertSame(200, $response['status'], $response['body']);
        self::assertSame(50, $this->getLogo());
    }

    #[Test]
    public function fileIsNotSetWithoutValidFileToken(): void
    {
        foreach ([
            'uid' => 50,
            'uid without token' => ['uid' => 50],
            'token of other file' => ['uid' => 50, 'fileToken' => $this->get(FileTokenService::class)->createToken(51)],
        ] as $case => $logo) {
            $response = $this->patchRecipe(['logo' => $logo]);
            $json = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

            self::assertSame(400, $response['status'], $case . ': ' . $response['body']);
            self::assertSame('fileToken', $json['violations'][0]['propertyPath'] ?? null, $case);
        }
        self::assertSame(0, $this->getLogo());
    }

    #[Test]
    public function currentFileIsKeptWithoutFileToken(): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE)
            ->update(self::RECIPE_TABLE, ['logo' => 50], ['uid' => 1]);

        $patch = $this->patchRecipe(['title' => 'Patched', 'logo' => 50]);
        $put = $this->dispatch($this->createJsonRequest('PUT', ['title' => 'Put', 'logo' => ['uid' => 50]]));
        $other = $this->patchRecipe(['logo' => 51]);

        self::assertSame(200, $patch['status'], $patch['body']);
        self::assertSame(200, $put['status'], $put['body']);
        self::assertSame(400, $other['status'], $other['body']);
        self::assertSame(50, $this->getLogo());
    }

    #[Test]
    public function currentFilesOfCollectionAreKeptWithoutFileToken(): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE)
            ->update(self::RECIPE_TABLE, ['logos' => 1], ['uid' => 1]);
        $this->getConnectionPool()->getConnectionForTable('tx_functionaltest_recipe_logo_mm')
            ->insert('tx_functionaltest_recipe_logo_mm', ['uid_local' => 1, 'uid_foreign' => 50, 'sorting' => 1]);
        $fileToken = $this->get(FileTokenService::class)->createToken(51);

        $withoutToken = $this->patchRecipe(['logos' => [50, 51]]);
        $withToken = $this->patchRecipe(['logos' => [50, ['uid' => 51, 'fileToken' => $fileToken]]]);

        self::assertSame(400, $withoutToken['status'], $withoutToken['body']);
        self::assertSame(200, $withToken['status'], $withToken['body']);
        self::assertSame([50, 51], array_map('intval', $this->getConnectionPool()
            ->getConnectionForTable('tx_functionaltest_recipe_logo_mm')
            ->select(['uid_foreign'], 'tx_functionaltest_recipe_logo_mm', ['uid_local' => 1], [], ['sorting' => 'ASC'])
            ->fetchFirstColumn()));
    }

    #[Test]
    #[DataProvider('invalidFileValueProvider')]
    public function invalidFileValueIsRejected(array $data): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE)
            ->update(self::RECIPE_TABLE, ['logo' => 50], ['uid' => 1]);

        $response = $this->patchRecipe($data);
        $json = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, $response['status'], $response['body']);
        self::assertStringContainsString('has to be an identifier or an object', $json['hydra:description'] ?? '');
        self::assertSame(50, $this->getLogo());
    }

    public static function invalidFileValueProvider(): array
    {
        return [
            'file reference format for file' => [['logo' => ['uidLocal' => 51, 'fileToken' => 'x']]],
            'text for file' => [['logo' => 'abc']],
            'boolean for file' => [['logo' => true]],
            'non-numeric uid of file' => [['logo' => ['uid' => 'abc']]],
            'negative uid of file' => [['logo' => -1]],
            'text for file reference' => [['images' => ['abc']]],
            'boolean for file reference' => [['images' => [true]]],
            'non-numeric uid of file reference' => [['images' => [['uid' => 'abc']]]],
        ];
    }

    #[Test]
    public function grantsAndTokensBelongToOneRequest(): void
    {
        $fileTokenService = $this->get(FileTokenService::class);
        $fileTokenService->grant(50);
        $fileTokenService->createToken(51);

        $response = $this->get(Bootstrap::class)->process(
            (new ServerRequest('https://example.com/_api/files/50', 'GET'))
                ->withAttribute('site', $GLOBALS['TYPO3_REQUEST']->getAttribute('site'))
                ->withAttribute('language', $GLOBALS['TYPO3_REQUEST']->getAttribute('language'))
        );
        $file = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(50, $file['uid'] ?? null);
        self::assertArrayNotHasKey('fileToken', $file);
        self::assertFalse($fileTokenService->hasGrantedFiles());
        self::assertFalse($fileTokenService->hasIssuedTokens());
    }

    #[Test]
    public function fileIsClearedWithZero(): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE)
            ->update(self::RECIPE_TABLE, ['logo' => 50], ['uid' => 1]);

        $response = $this->patchRecipe(['logo' => 0]);

        self::assertSame(200, $response['status'], $response['body']);
        self::assertSame(0, $this->getLogo());
    }

    #[Test]
    public function notExistingFileIsNotSet(): void
    {
        $fileToken = $this->get(FileTokenService::class)->createToken(999);

        $response = $this->patchRecipe(['logo' => ['uid' => 999, 'fileToken' => $fileToken]]);

        self::assertSame(400, $response['status'], $response['body']);
        self::assertSame(1793700004, json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR)['hydra:code'] ?? null, $response['body']);
        self::assertSame(0, $this->getLogo());
    }

    /**
     * @return array{status: int, body: string}
     */
    private function patchImages(array $images): array
    {
        return $this->patchRecipe(['images' => $images]);
    }

    /**
     * @return array{status: int, body: string}
     */
    private function patchRecipe(array $data): array
    {
        return $this->dispatch($this->createJsonRequest('PATCH', $data));
    }

    private function createJsonRequest(string $method, array $data): SymfonyRequest
    {
        return SymfonyRequest::create(
            'https://example.com/_api/recipes/1',
            $method,
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    private function getLogo(): int
    {
        return (int)$this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE)
            ->select(['logo'], self::RECIPE_TABLE, ['uid' => 1])->fetchOne();
    }

    /**
     * @return array{status: int, body: string}
     */
    private function dispatch(SymfonyRequest $request): array
    {
        $response = new Response();
        try {
            $body = $this->get(Bootstrap::class)
                ->processOperationByRequest((new RequestContext())->fromRequest($request), $request, $response);
        } catch (ExceptionInterface $exception) {
            $response = $response->withStatus($exception->getStatusCode());
            $body = $this->get(SerializerService::class)->serialize($exception);
        }

        return ['status' => $response->getStatusCode(), 'body' => $body];
    }

    private function countReferences(int $fileUid): int
    {
        return $this->getConnectionPool()->getConnectionForTable('sys_file_reference')
            ->count('*', 'sys_file_reference', ['uid_local' => $fileUid, 'uid_foreign' => 1, 'fieldname' => 'images']);
    }
}
