<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Exception\AbstractException;
use SourceBroker\T3api\Exception\BadRequestException;
use SourceBroker\T3api\Exception\InvalidPaginationParameterException;
use SourceBroker\T3api\Exception\OpenApiSupportingExceptionInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Exception\OriginNotAllowedException;
use SourceBroker\T3api\Exception\ResourceNotFoundException;
use SourceBroker\T3api\Exception\UnsupportedMediaTypeException;
use SourceBroker\T3api\Exception\ValidationException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class OpenApiSupportingExceptionTest extends UnitTestCase
{
    // translation of the response description creates `Locales` singleton
    protected bool $resetSingletonInstances = true;

    /**
     * @return array<string, array{class-string<AbstractException&OpenApiSupportingExceptionInterface>}>
     */
    public static function exceptionClassDataProvider(): array
    {
        return [
            'BadRequestException' => [BadRequestException::class],
            'InvalidPaginationParameterException' => [InvalidPaginationParameterException::class],
            'OperationNotAllowedException' => [OperationNotAllowedException::class],
            'OriginNotAllowedException' => [OriginNotAllowedException::class],
            'ResourceNotFoundException' => [ResourceNotFoundException::class],
            'UnsupportedMediaTypeException' => [UnsupportedMediaTypeException::class],
            'ValidationException' => [ValidationException::class],
        ];
    }

    /**
     * @param class-string<AbstractException&OpenApiSupportingExceptionInterface> $exceptionClass
     */
    #[Test]
    #[DataProvider('exceptionClassDataProvider')]
    public function openApiResponseHasStatusCodeOfException(string $exceptionClass): void
    {
        // `getStatusCode()` does not depend on constructor arguments
        $exception = (new \ReflectionClass($exceptionClass))->newInstanceWithoutConstructor();

        self::assertSame($exception->getStatusCode(), $exceptionClass::getOpenApiResponse()->statusCode);
    }
}
