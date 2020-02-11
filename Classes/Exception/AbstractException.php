<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use Exception;
use SourceBroker\T3api\Annotation\Serializer\Exclude;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use Symfony\Component\HttpFoundation\Response;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

abstract class AbstractException extends Exception implements ExceptionInterface
{
    /**
     * @var string
     * @Exclude()
     */
    protected $title;

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

    protected function translate(string $key, array $arguments = null): ?string
    {
        return LocalizationUtility::translate($key, 't3api', $arguments);
    }
}
