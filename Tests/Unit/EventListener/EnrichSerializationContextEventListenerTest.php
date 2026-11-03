<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\EventListener;

use JMS\Serializer\SerializationContext;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;
use SourceBroker\T3api\EventListener\EnrichSerializationContextEventListener;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class EnrichSerializationContextEventListenerTest extends UnitTestCase
{
    private const SERVER_PARAMS = [
        'HTTP_HOST' => 'example.com:8080',
        'HTTPS' => 'on',
        'SCRIPT_NAME' => '/index.php',
        'REQUEST_URI' => '/_api/news?page=2',
    ];

    private array $serverBackup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        $_SERVER = $this->serverBackup;
        parent::tearDown();
    }

    #[Test]
    public function attributesAreReadFromNormalizedParamsOfCurrentRequest(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com:8080/_api/news?page=2'))
            ->withAttribute('normalizedParams', $this->createNormalizedParams(self::SERVER_PARAMS));

        $context = $this->dispatch();

        self::assertSame('example.com', $context->getAttribute('TYPO3_HOST_ONLY'));
        self::assertSame('8080', $context->getAttribute('TYPO3_PORT'));
        self::assertSame('https://example.com:8080', $context->getAttribute('TYPO3_REQUEST_HOST'));
        self::assertSame('https://example.com:8080/_api/news?page=2', $context->getAttribute('TYPO3_REQUEST_URL'));
        self::assertSame('https://example.com:8080/index.php', $context->getAttribute('TYPO3_REQUEST_SCRIPT'));
        self::assertSame('https://example.com:8080/', $context->getAttribute('TYPO3_REQUEST_DIR'));
        self::assertSame('https://example.com:8080/', $context->getAttribute('TYPO3_SITE_URL'));
        self::assertSame('/', $context->getAttribute('TYPO3_SITE_PATH'));
        self::assertSame('_api/news?page=2', $context->getAttribute('TYPO3_SITE_SCRIPT'));
        self::assertSame('/var/www/html/public', $context->getAttribute('TYPO3_DOCUMENT_ROOT'));
        self::assertTrue($context->getAttribute('TYPO3_SSL'));
        self::assertFalse($context->getAttribute('TYPO3_PROXY'));
    }

    #[Test]
    public function portIsEmptyStringWhenHostHasNoPort(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/_api/news'))
            ->withAttribute(
                'normalizedParams',
                $this->createNormalizedParams(['HTTP_HOST' => 'example.com'] + self::SERVER_PARAMS)
            );

        self::assertSame('', $this->dispatch()->getAttribute('TYPO3_PORT'));
    }

    /**
     * Like `GeneralUtility::getIndpEnv()` - a request created by hand usually has no server params.
     */
    #[Test]
    public function attributesAreReadFromGlobalServerWhenRequestHasNoNormalizedParams(): void
    {
        $_SERVER = self::SERVER_PARAMS + $_SERVER;
        $GLOBALS['TYPO3_REQUEST'] = new ServerRequest('https://other.example.com/_api/news');

        self::assertSame('https://example.com:8080', $this->dispatch()->getAttribute('TYPO3_REQUEST_HOST'));
    }

    #[Test]
    public function attributesAreReadFromGlobalServerWithoutRequest(): void
    {
        $_SERVER = self::SERVER_PARAMS + $_SERVER;

        self::assertSame('https://example.com:8080', $this->dispatch()->getAttribute('TYPO3_REQUEST_HOST'));
    }

    private function dispatch(): SerializationContext
    {
        $context = SerializationContext::create();
        (new EnrichSerializationContextEventListener())(
            new AfterCreateContextForOperationEvent(self::createStub(OperationInterface::class), Request::create('/'), $context)
        );

        return $context;
    }

    private function createNormalizedParams(array $serverParams): NormalizedParams
    {
        return new NormalizedParams($serverParams, [], '/var/www/html/public/index.php', '/var/www/html/public');
    }
}
