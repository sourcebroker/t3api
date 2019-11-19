<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use SourceBroker\T3api\Utility\ParameterUtility;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Http\ServerRequest as Typo3Request;

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
     * ClientSidePagination constructor.
     *
     * @param ApiResourceAnnotation $apiResource
     */
    public function __construct(ApiResourceAnnotation $apiResource)
    {
        $attributes = $apiResource->getAttributes();
        $this->serverEnabled = isset($attributes['pagination_enabled'])
            ? ParameterUtility::toBoolean($attributes['pagination_enabled'])
            : $this->serverEnabled;
        $this->clientEnabled = isset($attributes['pagination_client_enabled'])
            ? ParameterUtility::toBoolean($attributes['pagination_client_enabled'])
            : $this->clientEnabled;
        $this->itemsPerPage = (int)$attributes['pagination_items_per_page'] ?? $this->itemsPerPage;
        $this->maximumItemsPerPage = (int)$attributes['maximum_items_per_page'] ?? $this->maximumItemsPerPage;
        $this->clientItemsPerPage = isset($attributes['pagination_client_items_per_page'])
            ? ParameterUtility::toBoolean($attributes['pagination_client_items_per_page'])
            : $this->clientItemsPerPage;
        $this->enabledParameterName = $attributes['enabled_parameter_name'] ?? $this->enabledParameterName;
        $this->itemsPerPageParameterName = $attributes['items_per_page_parameter_name'] ?? $this->itemsPerPageParameterName;
        $this->pageParameterName = $attributes['page_parameter_name'] ?? $this->pageParameterName;
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
