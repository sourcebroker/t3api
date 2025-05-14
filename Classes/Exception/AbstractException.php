<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response as OpenApiResponse;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Annotation\Serializer\Exclude;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use Symfony\Component\HttpFoundation\Response;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

abstract class AbstractException extends \Exception implements ExceptionInterface
{
    /**
     * @Exclude
     */
    protected string $title;

    protected static function translate(string $key, ?array $arguments = null): ?string
    {
        return LocalizationUtility::translate($key, 't3api', $arguments);
    }

    public static function getOpenApiResponse(): OpenApiResponse
    {
        return OpenApiResponse::create()
            ->content(
                MediaType::json()->schema(
                    Schema::object()->properties(...static::getOpenApiResponseSchemaProperties())
                )
            );
    }

    /**
     * @return SchemaContract[]
     */
    protected static function getOpenApiResponseSchemaProperties(): array
    {
        return [
            Schema::string('hydra:title'),
            Schema::string('hydra:description'),
            Schema::integer('hydra:code'),
        ];
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * @VirtualProperty("hydra:title")
     */
    public function getTitle(): string
    {
        return $this->title ?? Response::$statusTexts[$this->getStatusCode()] ?? '';
    }

    /**
     * @VirtualProperty("hydra:code")
     */
    public function getExceptionCode(): int
    {
        return $this->getCode();
    }

    /**
     * @VirtualProperty("hydra:description")
     */
    public function getDescription(): string
    {
        return $this->message ?? '';
    }
}
