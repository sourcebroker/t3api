<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Repository;

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
 * We do not extend \TYPO3\CMS\Extbase\Persistence\Repository because we don't want to use singleton interface
 */
class CommonRepository
{
    protected OperationInterface $operation;

    protected string $objectType;

    public function __construct(
        protected readonly PersistenceManagerInterface $persistenceManager,
        protected readonly FilterAccessChecker $filterAccessChecker,
        protected readonly Typo3QuerySettings $defaultQuerySettings
    ) {}

    public static function getInstanceForOperation(OperationInterface $operation): self
    {
        $repository = self::getInstanceForEntity($operation->getApiResource()->getEntity());
        $repository->operation = $operation;

        if ($operation->getPersistenceSettings()->getStoragePids() !== []) {
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
     * @return static
     */
    public static function getInstanceForEntity(string $entity): self
    {
        /** @var static $repository */
        $repository = GeneralUtility::makeInstance(self::class);
        $repository->setObjectType($entity);

        return $repository;
    }

    public function setObjectType(string $objectType): self
    {
        $this->objectType = $objectType;

        return $this;
    }

    public function findByUid(int $uid): ?object
    {
        return $this->persistenceManager->getObjectByIdentifier($uid, $this->objectType);
    }

    /**
     * @param ApiFilter[] $apiFilters
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
            $constraints[] = $query->logicalOr(...$constraintGroup);
        }

        if ($constraints !== []) {
            $query->matching($query->logicalAnd(...$constraints));
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
            function (ApiFilter $apiFilter): bool {
                return $this->filterAccessChecker->isGranted($apiFilter);
            }
        );
    }

    /**
     * It may be important for some type of filters (e.g. OrderFilter) to apply in specific order.
     * This method ensures that filters are applied in the order which they was requested in $queryParams.
     *
     * @param ApiFilter[] $apiFilters
     *
     * @return ApiFilter[]
     */
    protected function filterAndSortApiFiltersByQueryParams(array $apiFilters, array $queryParams): array
    {
        $apiFilters = array_filter(
            $apiFilters,
            static function (ApiFilter $apiFilter) use ($queryParams): bool {
                return isset($queryParams[$apiFilter->getParameterName()]);
            }
        );

        usort(
            $apiFilters,
            static function (ApiFilter $apiFilterA, ApiFilter $apiFilterB) use ($queryParams): int {
                if (
                    is_array($queryParams[$apiFilterA->getParameterName()])
                    && $apiFilterA->getParameterName() === $apiFilterB->getParameterName()
                    && $apiFilterA->getProperty() !== $apiFilterB->getProperty()
                ) {
                    $a = array_search(
                        $apiFilterA->getProperty(),
                        array_keys($queryParams[$apiFilterA->getParameterName()]),
                        true
                    );
                    $b = array_search(
                        $apiFilterB->getProperty(),
                        array_keys($queryParams[$apiFilterA->getParameterName()]),
                        true
                    );
                    return $a - $b;
                }

                return array_search($apiFilterA->getParameterName(), array_keys($queryParams), true)
                    - array_search($apiFilterB->getParameterName(), array_keys($queryParams), true);
            }
        );

        return $apiFilters;
    }

    protected function createQuery(): QueryInterface
    {
        $query = $this->persistenceManager->createQueryForType($this->objectType);
        if ($this->defaultQuerySettings instanceof QuerySettingsInterface) {
            $query->setQuerySettings(clone $this->defaultQuerySettings);
        }

        return $query;
    }

    public function add(AbstractDomainObject $object): void
    {
        if ($object->getPid() === null && $this->operation->getPersistenceSettings()->getMainStoragePid() > 0) {
            $object->setPid($this->operation->getPersistenceSettings()->getMainStoragePid());
        } elseif (
            $object->getPid() > 0
            && $this->defaultQuerySettings->getRespectStoragePage()
            && !in_array($object->getPid(), $this->defaultQuerySettings->getStoragePageIds(), true)
        ) {
            throw new \RuntimeException(
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

    public function remove(object $object): void
    {
        $this->persistenceManager->remove($object);
    }

    /**
     * @throws UnknownObjectException
     */
    public function update(object $modifiedObject): void
    {
        $this->persistenceManager->update($modifiedObject);
    }
}
