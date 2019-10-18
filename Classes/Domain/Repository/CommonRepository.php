<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Repository;

use RuntimeException;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Filter\AbstractFilter;
use SourceBroker\T3api\Service\StorageService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
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
     * @var ApiResource
     */
    protected $apiResource;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

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
     * @param ApiResource $apiResource
     *
     * @return CommonRepository
     */
    public static function getInstanceForResource(ApiResource $apiResource): self
    {
        $repository = self::getInstanceForEntity($apiResource->getEntity());
        $repository->apiResource = $apiResource;

        if (!empty($apiResource->getPersistenceSettings()->getStoragePids())) {
            $repository->defaultQuerySettings->setRespectStoragePage(true);
            $repository->defaultQuerySettings->setStoragePageIds(
                StorageService::getRecursiveStoragePids(
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
     * @param string $entity
     *
     * @return static
     */
    public static function getInstanceForEntity(string $entity): self
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $repository = $objectManager->get(self::class);
        $repository->setObjectType($entity);

        return $repository;
    }

    /**
     * @param PersistenceManagerInterface $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * CommonRepository constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->initializeObject();
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
    public function findFiltered(array $apiFilters, Request $request)
    {
        parse_str($request->getQueryString() ?? '', $queryParams);

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
        if (is_null($object->getPid()) && $this->apiResource->getPersistenceSettings()->getMainStoragePid()) {
            $object->setPid($this->apiResource->getPersistenceSettings()->getMainStoragePid());
        } elseif (
            $object->getPid()
            && $this->defaultQuerySettings->getRespectStoragePage()
            && !in_array($object->getPid(), $this->defaultQuerySettings->getStoragePageIds())
        ) {
            throw new RuntimeException(
                sprintf(
                    '`%d` is not allowed storage pid for %s API resource',
                    $object->getPid(),
                    $this->apiResource->getEntity()
                ),
                1568467681848
            );
        }

        $this->persistenceManager->add($object);
    }

    /**
     * @param object $object The object to remove
     */
    public function remove($object)
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
