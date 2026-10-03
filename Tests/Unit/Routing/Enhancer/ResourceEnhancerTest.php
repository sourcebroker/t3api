<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Routing\Enhancer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use TYPO3\CMS\Core\Routing\Route;
use TYPO3\CMS\Core\Routing\RouteCollection;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * `ResourceEnhancer::getBasePath()` resolves the base path via static services (and memoizes it),
 * so it is replaced in an anonymous subclass to either return a base path or throw.
 */
class ResourceEnhancerTest extends UnitTestCase
{
    #[Test]
    public function enhanceForMatchingAddsApiRouteVariant(): void
    {
        $collection = $this->createCollection();

        $this->createEnhancer(basePath: '_api')->enhanceForMatching($collection);

        self::assertCount(2, $collection);
        $paths = array_map(static fn(Route $route): string => $route->getPath(), $collection->all());
        self::assertContains('/_api/{t3apiResource}', $paths);
    }

    public static function skippableExceptionCodes(): array
    {
        return [
            'current site cannot be determined' => [1604259480589],
            'route enhancer is not defined for current site' => [1565853631761],
        ];
    }

    #[Test]
    #[DataProvider('skippableExceptionCodes')]
    public function enhanceForMatchingSkipsEnhancementWhenBasePathCannotBeResolved(int $exceptionCode): void
    {
        $collection = $this->createCollection();

        $this->createEnhancer(exception: new \RuntimeException('', $exceptionCode))->enhanceForMatching($collection);

        self::assertCount(1, $collection);
    }

    #[Test]
    public function enhanceForMatchingRethrowsOtherExceptions(): void
    {
        $this->expectExceptionCode(123);

        $this->createEnhancer(exception: new \RuntimeException('', 123))->enhanceForMatching($this->createCollection());
    }

    private function createCollection(): RouteCollection
    {
        $collection = new RouteCollection();
        $collection->add('default', new Route('/', []));

        return $collection;
    }

    private function createEnhancer(string $basePath = '', ?\Throwable $exception = null): ResourceEnhancer
    {
        return new class ([], $basePath, $exception) extends ResourceEnhancer {
            public function __construct(array $configuration, private readonly string $basePath, private readonly ?\Throwable $exception)
            {
                parent::__construct($configuration);
            }

            protected function getBasePath(): string
            {
                if ($this->exception !== null) {
                    throw $this->exception;
                }

                return $this->basePath;
            }
        };
    }
}
