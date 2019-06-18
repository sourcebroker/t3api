<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Repository;

use SourceBroker\Restify\Domain\Model\ApiFilter;
use SourceBroker\Restify\Filter\AbstractFilter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;
use \TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

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
     * @param ApiFilter[] $apiFilters
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findFiltered(array $apiFilters)
    {
        $query = $this->createQuery();
        $constraints = [];

        foreach ($apiFilters as $apiFilter) {
            if (isset($GLOBALS['TYPO3_REQUEST']->getQueryParams()[$apiFilter->getParameterName()])) {
                /** @var AbstractFilter $filter */
                $filter = $this->objectManager->get($apiFilter->getFilterClass());
                $constraint = $filter->filterProperty(
                    $apiFilter->getProperty(),
                    $GLOBALS['TYPO3_REQUEST']->getQueryParams()[$apiFilter->getParameterName()],
                    $query,
                    $apiFilter
                );

                if ($constraint instanceof ConstraintInterface) {
                    $constraints[] = $constraint;
                }
            }
        }

        if (!empty($constraints)) {
            $query->matching($query->logicalAnd($constraints));
        }

        return $query->execute();
    }
}
