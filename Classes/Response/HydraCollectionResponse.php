<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Response;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

/**
 * Class CollectionResponse
 */
class HydraCollectionResponse extends AbstractCollectionResponse
{
    /**
     * @param string $membersReference
     *
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return Schema
     */
    public static function getOpenApiSchema(string $membersReference): Schema
    {
        return Schema::object()
            ->properties(
                Schema::array('hydra:member')->items(Schema::ref($membersReference)),
                Schema::integer('hydra:totalItems')->minimum(0),
                Schema::object('hydra:view')->properties(
                    Schema::string('hydra:first')->description('URI to first page'),
                    Schema::string('hydra:last')->description('URI to first page'),
                    Schema::string('hydra:prev')->description('URI to previous page'),
                    Schema::string('hydra:next')->description('URI to next page'),
                    Schema::array('hydra:pages')->items(Schema::string())->description('URIs to all pages'),
                    Schema::number('hydra:page')->description('Number of current page')
                ),
                Schema::object('hydra:search')->properties(
                    Schema::string('hydra:template'),
                    Schema::array('hydra:mapping')->items(
                        Schema::object()->properties(
                            Schema::string('variable'),
                            Schema::string('property')
                        )
                    )
                )
            );
    }

    /**
     * @return array
     */
    public function getMembers(): array
    {
        return parent::getMembers();
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return parent::getTotalItems();
    }

    /**
     * @return array
     *
     * @todo move $viewData to separate class
     */
    public function getView(): array
    {
        $viewData = [];

        if ($this->operation->getPagination()->isEnabled()) {
            $pagination = $this->operation->getPagination();
            $lastPage = (int)ceil($this->getTotalItems() / $pagination->getNumberOfItemsPerPage());
            $viewData['hydra:first'] = $this->operation->getRoute()->getPath() . '?' .
                $this->getCurrentQueryStringWithOverrideParams([
                    $pagination->getPageParameterName() => 1,
                ]);
            $viewData['hydra:last'] = $this->operation->getRoute()->getPath() . '?' .
                $this->getCurrentQueryStringWithOverrideParams([
                    $pagination->getPageParameterName() => $lastPage,
                ]);

            if ($pagination->getPage() > 1) {
                $viewData['hydra:prev'] = $this->operation->getRoute()->getPath() . '?' .
                    $this->getCurrentQueryStringWithOverrideParams([
                        $pagination->getPageParameterName() => $pagination->getPage() - 1,
                    ]);
            }

            if ($pagination->getPage() < $lastPage) {
                $viewData['hydra:next'] = $this->operation->getRoute()->getPath() . '?' .
                    $this->getCurrentQueryStringWithOverrideParams([
                        $pagination->getPageParameterName() => $pagination->getPage() + 1,
                    ]);
            }

            $viewData['hydra:pages'] = [];
            foreach (range(1, $lastPage) as $pageNumber) {
                $viewData['hydra:pages'][] = $this->operation->getRoute()->getPath() . '?' .
                    $this->getCurrentQueryStringWithOverrideParams([
                        $pagination->getPageParameterName() => $pageNumber,
                    ]);
            }

            $viewData['hydra:page'] = $pagination->getPage();
        }

        return $viewData;
    }

    /**
     * @return array
     */
    public function getSearch(): array
    {
        $searchData = [];
        $searchData['hydra:template'] = $this->operation->getRoute()->getPath();
        $searchData['hydra:mapping'] = [];
        $variables = [];
        foreach ($this->operation->getFilters() as $filter) {
            if ($filter->isOrderFilter()) {
                $variable = sprintf($filter->getParameterName() . '[%s]', $filter->getProperty());
            } else {
                $variable = $filter->getParameterName();
            }
            if (!in_array($variable, $variables)) {
                $searchData['hydra:mapping'][] = [
                    'variable' => $variable,
                    'property' => $filter->getProperty(),
                ];
                $variables[] = $variable;
            }
        }
        $searchData['hydra:template'] .= sprintf('{?%s}', implode(',', $variables));

        return $searchData;
    }

    /**
     * @param array $overrideParams
     *
     * @return string
     */
    protected function getCurrentQueryStringWithOverrideParams(array $overrideParams): string
    {
        return http_build_query(array_merge($this->request->query->all(), $overrideParams));
    }
}
