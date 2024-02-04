<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response as OpenApiResponse;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Annotation\Serializer\Exclude;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use TYPO3\CMS\Extbase\Error\Result;

class ValidationException extends AbstractException implements OpenApiSupportingExceptionInterface
{
    /**
     * @var Result
     * @Exclude
     */
    protected $validationResult;

    public static function getOpenApiResponse(): OpenApiResponse
    {
        return parent::getOpenApiResponse()
            ->statusCode(SymfonyResponse::HTTP_BAD_REQUEST)
            ->description(self::translate('exception.validation.title'));
    }

    protected static function getOpenApiResponseSchemaProperties(): array
    {
        return array_merge(
            parent::getOpenApiResponseSchemaProperties(),
            [
                Schema::array('violations')->items(
                    Schema::object()->properties(
                        Schema::string('propertyPath'),
                        Schema::string('message'),
                        Schema::integer('code')
                    )
                ),
            ]
        );
    }

    public function __construct(Result $validationResult, int $code)
    {
        $this->validationResult = $validationResult;
        $this->title = self::translate('exception.validation.title');
        parent::__construct(self::translate('exception.validation.description'), $code);
    }

    /**
     * @VirtualProperty("violations")
     */
    public function getViolations(): array
    {
        return $this->getViolationsRecursive($this->validationResult);
    }

    /**
     * @see https://stackoverflow.com/a/3290198/1588346
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    protected function getViolationsRecursive(Result $result, array $propertyPath = [], array &$violations = []): array
    {
        foreach ($result->getErrors() as $error) {
            $violations[] = [
                'propertyPath' => implode('.', $propertyPath),
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
            ];
        }

        if (!empty($result->getSubResults())) {
            foreach ($result->getSubResults() as $subPropertyName => $subResult) {
                $this->getViolationsRecursive(
                    $subResult,
                    array_merge($propertyPath, [$subPropertyName]),
                    $violations
                );
            }
        }

        return $violations;
    }
}
