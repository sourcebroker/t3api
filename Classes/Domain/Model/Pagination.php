<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Utility\ParameterUtility;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Http\ServerRequest as Typo3Request;

/**
 * Class Pagination
 */
class Pagination extends AbstractOperationResourceSettings
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
     * @var int
     */
    protected $itemsPerPage;

    /**
     * @var int
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
     * @var array
     */
    protected $parameters;

    /**
     * @param array $attributes
     * @param Pagination|null $pagination
     * @return Pagination
     */
    public static function create(
        array $attributes = [],
        ?AbstractOperationResourceSettings $pagination = null
    ): AbstractOperationResourceSettings {
        $pagination = parent::create($attributes, $pagination);

        $pagination->serverEnabled = isset($attributes['pagination_enabled'])
            ? ParameterUtility::toBoolean($attributes['pagination_enabled'])
            : $pagination->serverEnabled;
        $pagination->clientEnabled = isset($attributes['pagination_client_enabled'])
            ? ParameterUtility::toBoolean($attributes['pagination_client_enabled'])
            : $pagination->clientEnabled;
        $pagination->itemsPerPage = isset($attributes['pagination_items_per_page'])
            ? (int)$attributes['pagination_items_per_page'] : $pagination->itemsPerPage;
        $pagination->maximumItemsPerPage = isset($attributes['maximum_items_per_page'])
            ? (int)$attributes['maximum_items_per_page'] : $pagination->maximumItemsPerPage;
        $pagination->clientItemsPerPage = isset($attributes['pagination_client_items_per_page'])
            ? ParameterUtility::toBoolean($attributes['pagination_client_items_per_page'])
            : $pagination->clientItemsPerPage;
        $pagination->enabledParameterName = $attributes['enabled_parameter_name'] ?? $pagination->enabledParameterName;
        $pagination->itemsPerPageParameterName = $attributes['items_per_page_parameter_name'] ?? $pagination->itemsPerPageParameterName;
        $pagination->pageParameterName = $attributes['page_parameter_name'] ?? $pagination->pageParameterName;

        return $pagination;
    }

    /**
     * @param Request|Typo3Request $request
     *
     * @return self
     */
    public function setParametersFromRequest($request): self
    {
        if ($request instanceof Request) {
            parse_str($request->getQueryString() ?? '', $this->parameters);
        } else {
            $this->parameters = $request->getQueryParams();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->clientEnabled && isset($this->parameters[$this->enabledParameterName])
            ? (bool)$this->parameters[$this->enabledParameterName]
            : $this->isServerEnabled();
    }

    /**
     * @return int
     */
    public function getNumberOfItemsPerPage(): int
    {
        return min(array_filter(
            [$this->maximumItemsPerPage, $this->getClientNumberOfItemsPerPage() ?? $this->itemsPerPage],
            static function (?int $itemsPerPage) {
                return !empty($itemsPerPage);
            }
        ));
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return (isset($this->parameters[$this->pageParameterName])) ? (int)$this->parameters[$this->pageParameterName] : 1;
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
    public function isClientItemsPerPage(): bool
    {
        return $this->clientItemsPerPage;
    }

    /**
     * @return string
     */
    public function getItemsPerPageParameterName(): string
    {
        return $this->itemsPerPageParameterName;
    }

    /**
     * @return int
     */
    public function getMaximumItemsPerPage(): int
    {
        return $this->maximumItemsPerPage;
    }

    /**
     * @return string
     */
    public function getEnabledParameterName(): string
    {
        return $this->enabledParameterName;
    }

    /**
     * @return bool
     */
    public function isServerEnabled(): bool
    {
        return $this->serverEnabled;
    }

    /**
     * @return bool
     */
    public function isClientEnabled(): bool
    {
        return $this->clientEnabled;
    }

    /**
     * @return int|null
     */
    protected function getClientNumberOfItemsPerPage(): ?int
    {
        if ($this->clientItemsPerPage && isset($this->parameters[$this->itemsPerPageParameterName])) {
            return (int)$this->parameters[$this->itemsPerPageParameterName];
        }

        return null;
    }
}
