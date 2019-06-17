<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
}
