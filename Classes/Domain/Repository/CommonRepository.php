<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Repository;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Filter\AbstractFilter;
use SourceBroker\T3api\Service\StorageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class CommonRepository
 */
class CommonRepository extends Repository
{

    /**
     * @param ApiResource $apiResource
     *
     * @return CommonRepository
     */
    public static function getInstanceForResource(ApiResource $apiResource): self
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $repository = $objectManager->get(self::class);
        $repository->setObjectType($apiResource->getEntity());

        if (!empty($apiResource->getPersistenceSettings()->getStoragePids())) {
            $repository->defaultQuerySettings->setRespectStoragePage(true);
            $repository->defaultQuerySettings->setStoragePageIds(
                $objectManager->get(StorageService::class)->getRecursiveStoragePids(
                    $apiResource->getPersistenceSettings()->getStoragePids(),
                    $apiResource->getPersistenceSettings()->getRecursionLevel()
                )
            );
        } else {
            $repository->defaultQuerySettings->setRespectStoragePage(false);
        }

        // @todo add signal for customization of repository (e.g. change of the default query settings)

        return $repository;
    }

    /**
     * CommonRepository constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->initializeObject();
    }

    /**
     * @param string $objectType
     */
    public function setObjectType(string $objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @param ApiFilter[] $apiFilters
     *
     * @return QueryInterface
     */
    public function findFiltered(array $apiFilters)
    {
        $queryParams = $GLOBALS['TYPO3_REQUEST']->getQueryParams();

        $query = $this->createQuery();
        $constraintGroups = [];

        /** @var ApiFilter $apiFilter */
        foreach ($apiFilters as $groupKey => $apiFilter) {
            // allow params grouping by namespace (for example "page" constraint -> constraint[page])
            $namespacedQueryParams = !empty($apiFilter->getArgument('parameterNamespace'))
                ? $queryParams[$apiFilter->getArgument('parameterNamespace')]
                : $queryParams;
            $parameterName = $apiFilter->getParameterName();

            if (isset($namespacedQueryParams[$apiFilter->getParameterName()])) {
                /** @var AbstractFilter $filter */
                $filter = $this->objectManager->get($apiFilter->getFilterClass());
                $constraint = $filter->filterProperty(
                    $apiFilter->getProperty(),
                    $namespacedQueryParams[$parameterName],
                    $query,
                    $apiFilter
                );

                if ($constraint instanceof ConstraintInterface) {
                    $constraintGroups[$apiFilter->getParameterName()] = array_merge(
                        $constraintGroups[$apiFilter->getParameterName()] ?? [],
                        [$constraint]
                    );
                }
            }
        }

        $constraints = [];
        foreach ($constraintGroups as $constraintGroup) {
            if (!empty($constraintGroup)) {
                $constraints[] = $query->logicalOr($constraintGroup);
            }
        }

        if (!empty($constraints)) {
            $query->matching($query->logicalAnd($constraints));
        }

        return $query;
    }
}
