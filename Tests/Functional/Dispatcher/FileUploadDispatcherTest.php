<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Http\Response;

/**
 * Upload settings of the resource apply to its upload operation, see `T3apiTests\FunctionalTest\Domain\Model\File`
 * (resource allows `png` only).
 */
class FileUploadDispatcherTest extends AbstractDispatcherTestCase
{
    #[Test]
    public function fileExtensionNotAllowedByResourceIsRejected(): void
    {
        // `gif` is allowed by default (`$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']`), but not by the resource
        $path = tempnam(sys_get_temp_dir(), 't3api');
        file_put_contents($path, 'GIF89a');
        $request = SymfonyRequest::create(
            'https://example.com/_api/files',
            'POST',
            [],
            [],
            ['originalResource' => new UploadedFile($path, 'photo.gif', 'image/gif', null, true)]
        );

        $response = new Response();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1577000112816);

        try {
            $this->get(Bootstrap::class)
                ->processOperationByRequest((new RequestContext())->fromRequest($request), $request, $response);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
