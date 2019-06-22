<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Model;

use SourceBroker\Restify\Annotation\ApiResource as ApiResourceAnnotation;

/**
 * Class Pagination
 */
class Pagination
{
    /**
     * @var bool
     */
    protected $serverEnabled;

    /**
     * @var bool
     */
    protected $clientEnabled;

    /**
     * @var integer
     */
    protected $itemsPerPage;

    /**
     * @var integer
     */
    protected $maximumItemsPerPage;

    /**
     * @var bool
     */
    protected $clientItemsPerPage;

    /**
     * @var string
     */
    protected $itemsPerPageParameterName;

    /**
     * @var string
     */
    protected $enabledParameterName;

    /**
     * @var string
     */
    protected $pageParameterName;

    /**
     * ClientSidePagination constructor.
     *
     * @param ApiResourceAnnotation $apiResource
     */
    public function __construct(ApiResourceAnnotation $apiResource)
    {
        $attributes = $apiResource->getAttributes();
        $this->serverEnabled = !!($attributes['pagination_enabled'] ?? $this->serverEnabled);
        $this->clientEnabled = !!($attributes['pagination_client_enabled'] ?? $this->clientEnabled);
        $this->itemsPerPage = (int)$attributes['pagination_items_per_page'] ?? $this->itemsPerPage;
        $this->maximumItemsPerPage = (int)$attributes['maximum_items_per_page'] ?? $this->maximumItemsPerPage;
        $this->clientItemsPerPage = !!($attributes['pagination_client_items_per_page'] ?? $this->clientItemsPerPage);
        $this->enabledParameterName = $attributes['enabled_parameter_name'] ?? $this->enabledParameterName;
        $this->itemsPerPageParameterName = $attributes['items_per_page_parameter_name'] ?? $this->itemsPerPageParameterName;
        $this->pageParameterName = $attributes['page_parameter_name'] ?? $this->pageParameterName;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isServerEnabled() || $this->isClientEnabled();
    }

    /**
     * @return int
     */
    public function getNumberOfItemsPerPage(): int
    {
        return min(array_filter(
            [$this->maximumItemsPerPage, $this->getClientNumberOfItemsPerPage() ?? $this->itemsPerPage],
            function (?int $itemsPerPage) {
                return !empty($itemsPerPage);
            }
        ));
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return (isset($GLOBALS['TYPO3_REQUEST']->getQueryParams()[$this->pageParameterName]))
            ? (int)$GLOBALS['TYPO3_REQUEST']->getQueryParams()[$this->pageParameterName]
            : 1;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->getPage() - 1) * $this->getNumberOfItemsPerPage();
    }

    /**
     * @return string
     */
    public function getPageParameterName(): string
    {
        return $this->pageParameterName;
    }

    /**
     * @return bool
     */
    protected function isServerEnabled(): bool
    {
        return $this->serverEnabled;
    }

    /**
     * @return bool
     */
    protected function isClientEnabled(): bool
    {
        return $this->clientEnabled && !empty($GLOBALS['TYPO3_REQUEST']->getQueryParams()[$this->enabledParameterName]);
    }

    /**
     * @return int|null
     */
    protected function getClientNumberOfItemsPerPage(): ?int
    {
        if (
            $this->clientItemsPerPage
            && isset($GLOBALS['TYPO3_REQUEST']->getQueryParams()[$this->itemsPerPageParameterName])
        ) {
            return (int)$GLOBALS['TYPO3_REQUEST']->getQueryParams()[$this->itemsPerPageParameterName];
        }

        return null;
    }
}
