<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Repository;

use SourceBroker\Restify\Filter\AbstractFilter;
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
     * @param string $entity
     *
     * @return CommonRepository
     */
    public static function getInstanceForEntity(string $entity): self
    {
        $repository = GeneralUtility::makeInstance(ObjectManager::class)->get(self::class);
        $repository->setObjectType($entity);

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
        $this->defaultQuerySettings = new Typo3QuerySettings();
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }

    /**
     * @param string $objectType
     */
    public function setObjectType(string $objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @param array $apiFilters
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
            $parameterNamespace = $apiFilter->getArgument('parameterNamespace') ?? null;
            $parameterName = $apiFilter->getParameterName();
            $namespacedQueryParams = $parameterNamespace
                ? $queryParams[$parameterNamespace]
                : $queryParams;

            if ($parameterName) {
                $groupKey = $parameterName;
            }

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
                    $constraintGroups[$groupKey][] = $constraint;
                }
            }
        }

        $constraints = [];
        foreach ($constraintGroups as $constraintGroup) {
            if (!empty($constraintGroup)) {
                $query->matching($query->logicalOr($constraintGroup));
            }
        }

        if (!empty($constraints)) {
            $query->matching($query->logicalAnd($constraints));
        }

        return $query;
    }
}
