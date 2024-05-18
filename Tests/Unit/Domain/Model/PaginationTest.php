<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Domain\Model;

use SourceBroker\T3api\Domain\Model\Pagination;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class PaginationTest
 */
class PaginationTest extends UnitTestCase
{
    /**
     * $GLOBALS['TYPO3_CONF_VARS'] is not available in test environment, so we need to copy default pagination settings
     */
    public const DEFAULT_API_RESOURCE_PAGINATION_ATTRIBUTES = [
        'pagination_enabled' => true,
        'pagination_client_enabled' => false,
        'pagination_items_per_page' => 30,
        'maximum_items_per_page' => 9999999,
        'pagination_client_items_per_page' => false,
        'items_per_page_parameter_name' => 'itemsPerPage',
        'enabled_parameter_name' => 'pagination',
        'page_parameter_name' => 'page',
    ];

    /**
     * @return array
     */
    public function isEnabledReturnsCorrectStateDataProvider(): array
    {
        return [
            'Pagination disabled' => [
                [
                    'pagination_enabled' => false,
                    'pagination_client_enabled' => false,
                ],
                '',
                false,
            ],
            'Client pagination allowed but disabled' => [
                [
                    'pagination_enabled' => false,
                    'pagination_client_enabled' => true,
                ],
                'https://example.com/_api/',
                false,
            ],
            'Client pagination allowed and enabled' => [
                [
                    'pagination_enabled' => false,
                    'pagination_client_enabled' => true,
                ],
                'https://example.com/_api/?pagination=1',
                true,
            ],
            'Client pagination not allowed but tried to be enabled' => [
                [
                    'pagination_enabled' => false,
                    'pagination_client_enabled' => false,
                ],
                'https://example.com/_api/?pagination=1',
                false,
            ],
            'Server pagination enabled' => [
                [
                    'pagination_enabled' => true,
                    'pagination_client_enabled' => false,
                ],
                '',
                true,
            ],
            'Server and client pagination enabled' => [
                [
                    'pagination_enabled' => true,
                    'pagination_client_enabled' => true,
                ],
                '',
                true,
            ],
            'Server and client pagination enabled but disabled by client parameter' => [
                [
                    'pagination_enabled' => true,
                    'pagination_client_enabled' => true,
                ],
                'https://example.com/_api/?pagination=0',
                false,
            ],
        ];
    }

    /**
     * @param array $paginationAttributes
     * @param string $requestUri
     * @param bool $expectedResult
     *
     * @dataProvider isEnabledReturnsCorrectStateDataProvider
     * @test
     */
    public function isEnabledReturnsCorrectState(
        array $paginationAttributes,
        string $requestUri,
        bool $expectedResult
    ) {
        $request = !empty($requestUri) ? Request::create($requestUri) : null;

        self::assertSame(
            $expectedResult,
            ($this->getPaginationInstance($paginationAttributes, $request))->isEnabled()
        );
    }

    /**
     * @return array
     */
    public function getNumberOfItemsPerPageReturnsCorrectValueDataProvider(): array
    {
        return [
            'Maximum items per page overwrites items per page request by client' => [
                [
                    'maximum_items_per_page' => 15,
                    'pagination_client_items_per_page' => true,
                ],
                'https://example.com/_api/?itemsPerPage=100',
                15,
            ],
            'Custom client parameter name for items per page works' => [
                [
                    'pagination_client_items_per_page' => true,
                    'items_per_page_parameter_name' => 'numberOfItemsPerPage',
                ],
                'https://example.com/_api/?numberOfItemsPerPage=8',
                8,
            ],
            'Client can change items per page if maximum is not exceeded' => [
                [
                    'maximum_items_per_page' => 50,
                    'pagination_client_items_per_page' => true,
                ],
                'https://example.com/_api/?itemsPerPage=35',
                35,
            ],
            'Default number of items per page works' => [
                [
                    'pagination_items_per_page' => 5,
                ],
                '',
                5,
            ],
            'Items per page client parameter is ignored if not enabled' => [
                [
                    'pagination_items_per_page' => 30,
                    'pagination_client_items_per_page' => false,
                ],
                'https://example.com/_api/?itemsPerPage=45',
                30,
            ],
        ];
    }

    /**
     * @param array $paginationAttributes
     * @param string $requestUri
     * @param int $expectedResult
     *
     * @dataProvider getNumberOfItemsPerPageReturnsCorrectValueDataProvider
     * @test
     */
    public function getNumberOfItemsPerPageReturnsCorrectValue(
        array $paginationAttributes,
        string $requestUri,
        int $expectedResult
    ) {
        $request = !empty($requestUri) ? Request::create($requestUri) : null;

        self::assertSame(
            $expectedResult,
            ($this->getPaginationInstance($paginationAttributes, $request))->getNumberOfItemsPerPage()
        );
    }

    /**
     * @param array $attributes
     * @param Request $request
     *
     * @return Pagination
     */
    protected function getPaginationInstance($attributes = [], Request $request = null)
    {
        $pagination = Pagination::create(
            array_merge(self::DEFAULT_API_RESOURCE_PAGINATION_ATTRIBUTES, $attributes)
        );

        if ($request !== null) {
            $pagination->setParametersFromRequest($request);
        }

        return $pagination;
    }
}
