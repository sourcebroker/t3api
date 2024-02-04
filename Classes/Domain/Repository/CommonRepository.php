<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Repository;

use RuntimeException;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Filter\FilterInterface;
use SourceBroker\T3api\Security\FilterAccessChecker;
use SourceBroker\T3api\Service\StorageService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class CommonRepository
 *
 * We do not extend \TYPO3\CMS\Extbase\Persistence\Repository because we don't want to use singleton interface
 */
class CommonRepository
{
    /**
     * @var OperationInterface
     */
    protected $operation;

    /**
     * @var QuerySettingsInterface
     */
    protected $defaultQuerySettings;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var FilterAccessChecker
     */
    protected $filterAccessChecker;

    /**
     * @param OperationInterface $operation
     *
     * @return CommonRepository
     */
    public static function getInstanceForOperation(OperationInterface $operation): self
    {
        $repository = self::getInstanceForEntity($operation->getApiResource()->getEntity());
        $repository->operation = $operation;

        if (!empty($operation->getPersistenceSettings()->getStoragePids())) {
            $repository->defaultQuerySettings->setRespectStoragePage(true);
            $repository->defaultQuerySettings->setStoragePageIds(
                StorageService::getRecursiveStoragePids(
                    $operation->getPersistenceSettings()->getStoragePids(),
                    $operation->getPersistenceSettings()->getRecursionLevel()
                )
            );
        } else {
            $repository->defaultQuerySettings->setRespectStoragePage(false);
        }

        // @todo add signal for customization of repository (e.g. change of the default query settings)

        return $repository;
    }

    /**
     * @param string $entity
     *
     * @return static
     */
    public static function getInstanceForEntity(string $entity): self
    {
        /** @var self $repository */
        $repository = GeneralUtility::makeInstance(self::class);
        $repository->setObjectType($entity);

        return $repository;
    }

    public function __construct(
        PersistenceManagerInterface $persistenceManager,
        FilterAccessChecker $filterAccessChecker,
        Typo3QuerySettings $defaultQuerySettings
    ) {
        $this->persistenceManager = $persistenceManager;
        $this->filterAccessChecker = $filterAccessChecker;
        $this->defaultQuerySettings = $defaultQuerySettings;
    }

        /**
     * @param string $objectType
     *
     * @return self
     */
    public function setObjectType(string $objectType): self
    {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * @param int $uid The identifier of the object to find
     *
     * @return object The matching object if found, otherwise NULL
     */
    public function findByUid($uid)
    {
        return $this->persistenceManager->getObjectByIdentifier($uid, $this->objectType);
    }

    /**
     * @param ApiFilter[] $apiFilters
     * @param Request $request
     *
     * @return QueryInterface
     */
    public function findFiltered(array $apiFilters, Request $request): QueryInterface
    {
        parse_str($request->getQueryString() ?? '', $queryParams);

        $query = $this->createQuery();
        $constraintGroups = [];

        $apiFilters = $this->filterGrantedFilters($apiFilters);
        $apiFilters = $this->filterAndSortApiFiltersByQueryParams($apiFilters, $queryParams);

        foreach ($apiFilters as $apiFilter) {
            $parameterName = $apiFilter->getParameterName();

            /** @var FilterInterface $filter */
            $filter = GeneralUtility::makeInstance($apiFilter->getFilterClass());
            $constraint = $filter->filterProperty(
                $apiFilter->getProperty(),
                $queryParams[$parameterName],
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

    /**
     * @param ApiFilter[] $apiFilters
     * @return ApiFilter[]
     */
    protected function filterGrantedFilters(array $apiFilters): array
    {
        return array_filter(
            $apiFilters,
            function (ApiFilter $apiFilter) {
                return $this->filterAccessChecker->isGranted($apiFilter);
            }
        );
    }

    /**
     * It may be important for some type of filters (e.g. OrderFilter) to apply in specific order.
     * This method ensures that filters are applied in the order which they was requested in $queryParams.
     *
     * @param ApiFilter[] $apiFilters
     * @param array $queryParams
     *
     * @return ApiFilter[]
     */
    protected function filterAndSortApiFiltersByQueryParams(array $apiFilters, array $queryParams): array
    {
        $apiFilters = array_filter(
            $apiFilters,
            static function (ApiFilter $apiFilter) use ($queryParams) {
                return isset($queryParams[$apiFilter->getParameterName()]);
            }
        );

        usort(
            $apiFilters,
            static function (ApiFilter $apiFilterA, ApiFilter $apiFilterB) use ($queryParams) {
                if (
                    is_array($queryParams[$apiFilterA->getParameterName()])
                    && $apiFilterA->getParameterName() === $apiFilterB->getParameterName()
                    && $apiFilterA->getProperty() !== $apiFilterB->getProperty()
                ) {
                    return array_search(
                        $apiFilterA->getProperty(),
                        array_keys($queryParams[$apiFilterA->getParameterName()]),
                        true
                    ) - array_search(
                        $apiFilterB->getProperty(),
                        array_keys($queryParams[$apiFilterA->getParameterName()]),
                        true
                    );
                }

                return array_search($apiFilterA->getParameterName(), array_keys($queryParams), true)
                    - array_search($apiFilterB->getParameterName(), array_keys($queryParams), true);
            }
        );

        return $apiFilters;
    }

    /**
     * @return QueryInterface
     */
    protected function createQuery()
    {
        $query = $this->persistenceManager->createQueryForType($this->objectType);
        if ($this->defaultQuerySettings !== null) {
            $query->setQuerySettings(clone $this->defaultQuerySettings);
        }

        return $query;
    }

    /**
     * @param AbstractDomainObject $object
     *
     * @throws @todo 591
     */
    public function add($object)
    {
        if ($object->getPid() === null && $this->operation->getPersistenceSettings()->getMainStoragePid()) {
            $object->setPid($this->operation->getPersistenceSettings()->getMainStoragePid());
        } elseif (
            (bool)$object->getPid()
            && $this->defaultQuerySettings->getRespectStoragePage()
            && !in_array($object->getPid(), $this->defaultQuerySettings->getStoragePageIds(), true)
        ) {
            throw new RuntimeException(
                sprintf(
                    '`%d` is not allowed storage pid for %s API resource',
                    $object->getPid(),
                    $this->operation->getApiResource()->getEntity()
                ),
                1568467681848
            );
        }

        $this->persistenceManager->add($object);
    }

    /**
     * @param object $object The object to remove
     */
    public function remove($object): void
    {
        $this->persistenceManager->remove($object);
    }

    /**
     * @param object $modifiedObject
     *
     * @throws UnknownObjectException
     */
    public function update($modifiedObject)
    {
        $this->persistenceManager->update($modifiedObject);
    }
}
