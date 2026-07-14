<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Utility\ParameterUtility;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Http\ServerRequest as Typo3Request;

class Pagination extends AbstractOperationResourceSettings
{
    protected bool $serverEnabled;

    protected bool $clientEnabled;

    protected ?int $itemsPerPage;

    protected int $maximumItemsPerPage;

    protected bool $clientItemsPerPage;

    protected string $itemsPerPageParameterName;

    protected string $enabledParameterName;

    protected string $pageParameterName;

    protected array $parameters = [];

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

    public function setParametersFromRequest(Request|Typo3Request $request): self
    {
        if ($request instanceof Request) {
            parse_str($request->getQueryString() ?? '', $this->parameters);
        } else {
            $this->parameters = $request->getQueryParams();
        }

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->clientEnabled && isset($this->parameters[$this->enabledParameterName])
            ? (bool)$this->parameters[$this->enabledParameterName]
            : $this->isServerEnabled();
    }

    public function getNumberOfItemsPerPage(): int
    {
        return min(array_filter(
            [$this->maximumItemsPerPage, $this->getClientNumberOfItemsPerPage() ?? $this->itemsPerPage],
            static function (?int $itemsPerPage): bool {
                return $itemsPerPage !== null && $itemsPerPage !== 0;
            }
        ));
    }

    public function getPage(): int
    {
        return (isset($this->parameters[$this->pageParameterName])) ? (int)$this->parameters[$this->pageParameterName] : 1;
    }

    public function getOffset(): int
    {
        return ($this->getPage() - 1) * $this->getNumberOfItemsPerPage();
    }

    public function getPageParameterName(): string
    {
        return $this->pageParameterName;
    }

    public function isClientItemsPerPage(): bool
    {
        return $this->clientItemsPerPage;
    }

    public function getItemsPerPageParameterName(): string
    {
        return $this->itemsPerPageParameterName;
    }

    public function getMaximumItemsPerPage(): int
    {
        return $this->maximumItemsPerPage;
    }

    public function getEnabledParameterName(): string
    {
        return $this->enabledParameterName;
    }

    public function isServerEnabled(): bool
    {
        return $this->serverEnabled;
    }

    public function isClientEnabled(): bool
    {
        return $this->clientEnabled;
    }

    protected function getClientNumberOfItemsPerPage(): ?int
    {
        if ($this->clientItemsPerPage && isset($this->parameters[$this->itemsPerPageParameterName])) {
            return (int)$this->parameters[$this->itemsPerPageParameterName];
        }

        return null;
    }
}
