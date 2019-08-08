<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Dispatcher;

use SourceBroker\T3api\Domain\Model\AbstractOperation;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Domain\Repository\CommonRepository;
use SourceBroker\T3api\Service\SerializerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Exception;
use InvalidArgumentException;

/**
 * Class Bootstrap
 */
class Bootstrap
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var SerializerService
     */
    protected $serializerService;

    /**
     * @var ApiResourceRepository
     */
    protected $apiResourceRepository;

    /**
     * @var string
     */
    protected $output = '';

    /**
     * Bootstrap constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->serializerService = $this->objectManager->get(SerializerService::class);
        $this->apiResourceRepository = $this->objectManager->get(ApiResourceRepository::class);
    }

    /**
     * @return void
     * @throws RouteNotFoundException
     * @throws Exception
     */
    public function process(): void
    {
        $context = (new RequestContext())->fromRequest(Request::createFromGlobals());
        $matchedRoute = null;

        foreach ($this->apiResourceRepository->getAll() as $apiResource) {
            try {
                $urlMatcher = new UrlMatcher($apiResource->getRoutes(), $context);
                $matchedRoute = $urlMatcher->match($context->getPathInfo());
                $this->processOperation(
                    $apiResource->getOperationByRouteName($matchedRoute['_route']),
                    $matchedRoute
                );
                break;
            } catch (ResourceNotFoundException $resourceNotFoundException) {
            }
        }

        if (!$matchedRoute) {
            throw new RouteNotFoundException('T3api resource not found for current route', 1557217186441);
        }

        $this->output();
    }

    /**
     * @param AbstractOperation $operation
     * @param array $matchedRoute
     *
     * @return void
     *
     * @throws Exception
     */
    protected function processOperation(AbstractOperation $operation, array $matchedRoute): void
    {
        $repository = CommonRepository::getInstanceForEntity($operation->getApiResource()->getEntity());

        if ($operation instanceof ItemOperation) {
            $result = $repository->findByUid((int)$matchedRoute['id']);

            if (!$result) {
                // @todo throw appropriate exception
                throw new InvalidArgumentException('Item not found');
            }
        } elseif ($operation instanceof CollectionOperation) {
            $result = $this->objectManager->get(
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'],
                $operation,
                $repository->findFiltered($operation->getFilters())
            );
        } else {
            // @todo throw appropriate exception
            throw new Exception('Unknown operation', 1557506987081);
        }

        $this->output = $this->serializerService->serialize($operation, $result);
    }

    /**
     * @return void
     * @todo add signal/hook just before the output?
     */
    protected function output(): void
    {
        echo $this->output;
    }
}
