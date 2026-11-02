<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Serializer\ContextBuilder\SerializationContextBuilder;
use SourceBroker\T3api\Service\OperationResponseCache;
use SourceBroker\T3api\Service\SerializerService;
use T3apiTests\FunctionalTest\Processor\FailingProcessor;
use T3apiTests\FunctionalTest\Processor\FailingWithErrorProcessor;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * Unexpected exceptions are logged with an event ID. Outside of `Development` context the client
 * gets a generic error with that event ID instead of the exception message.
 */
class InternalErrorDispatcherTest extends AbstractDispatcherTestCase
{
    private ApplicationContext $originalContext;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalContext = Environment::getContext();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowOrigin'] = ['https://client.example'];
    }

    protected function tearDown(): void
    {
        $this->setApplicationContext($this->originalContext);
        parent::tearDown();
    }

    #[Test]
    public function unexpectedExceptionMessageIsReplacedOutsideOfDevelopmentContext(): void
    {
        $this->registerFailingProcessor();
        $logged = [];
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->willReturnCallback(static function ($message, array $context) use (&$logged): void {
                $logged = $context;
            });

        $response = $this->dispatch($logger);
        $body = (string)$response->getBody();
        $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertFalse(SerializerService::isDebugMode());
        self::assertSame(500, $response->getStatusCode(), $body);
        self::assertStringNotContainsString('db_user', $body);
        self::assertStringNotContainsString('settings.php', $body);
        self::assertSame('Internal Server Error', $json['hydra:title']);
        self::assertSame('An internal error occurred. Event ID: ' . $logged['eventId'], $json['hydra:description']);
        self::assertArrayNotHasKey('hydra:debug', $json);
        self::assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $logged['eventId']);
        self::assertInstanceOf(\RuntimeException::class, $logged['exception']);
        self::assertSame(FailingProcessor::MESSAGE, $logged['exception']->getMessage());
        // https://github.com/sourcebroker/t3api/issues/38
        self::assertSame('https://client.example', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    #[Test]
    public function unexpectedExceptionMessageIsReturnedInDevelopmentContext(): void
    {
        $this->registerFailingProcessor();
        $this->setApplicationContext(new ApplicationContext('Development'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $response = $this->dispatch($logger);
        $body = (string)$response->getBody();
        $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(500, $response->getStatusCode(), $body);
        self::assertSame(FailingProcessor::MESSAGE, $json['hydra:description']);
        self::assertArrayHasKey('hydra:debug', $json);
    }

    /**
     * https://github.com/sourcebroker/t3api/issues/38 - an `Error` could not be serialized, so it was rethrown
     * and rendered by TYPO3 without CORS headers.
     */
    #[Test]
    public function errorIsReturnedAsApiResponseWithCorsHeadersInDevelopmentContext(): void
    {
        $this->registerFailingProcessor(FailingWithErrorProcessor::class);
        $this->setApplicationContext(new ApplicationContext('Development'));

        $response = $this->dispatch(self::createStub(LoggerInterface::class));
        $body = (string)$response->getBody();
        $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(500, $response->getStatusCode(), $body);
        self::assertSame(FailingWithErrorProcessor::MESSAGE, $json['hydra:description']);
        self::assertSame('https://client.example', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    #[Test]
    public function controlledExceptionMessageIsReturnedOutsideOfDevelopmentContext(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $response = $this->dispatch($logger, '/_api/articles/999');
        $body = (string)$response->getBody();
        $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(404, $response->getStatusCode(), $body);
        self::assertStringContainsString('999', $json['hydra:description']);
    }

    #[Test]
    public function loggerIsInjectedIntoBootstrapService(): void
    {
        $logger = (new \ReflectionProperty(Bootstrap::class, 'logger'))->getValue($this->get(Bootstrap::class));

        self::assertInstanceOf(LoggerInterface::class, $logger);
    }

    private function dispatch(LoggerInterface $logger, string $path = '/_api/articles/1'): ResponseInterface
    {
        $dispatcher = new Bootstrap(
            $this->get(SerializerService::class),
            $this->get(ApiResourceRepository::class),
            $this->get(SerializationContextBuilder::class),
            $this->get(DeserializationContextBuilder::class),
            $this->get(EventDispatcherInterface::class),
            $this->get(OperationResponseCache::class)
        );
        $dispatcher->setLogger($logger);

        return $dispatcher->process(
            (new ServerRequest('https://example.com' . $path, 'GET'))->withHeader('Origin', 'https://client.example')
        );
    }

    /**
     * Lower priority than CorsProcessor (100) - the exception is thrown after CORS headers are set,
     * like an exception thrown by an operation handler.
     */
    private function registerFailingProcessor(string $processorClass = FailingProcessor::class): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['processors'][$processorClass] = 50;
    }

    private function setApplicationContext(ApplicationContext $context): void
    {
        (new \ReflectionProperty(Environment::class, 'context'))->setValue(null, $context);
    }
}
