<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use Doctrine\DBAL\FetchMode;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class ContainFilter
 */
class ContainFilter extends AbstractFilter
{
    /**
     * @param ApiFilter $apiFilter
     *
     * @return Parameter[]
     */
    public static function getDocumentationParameters(ApiFilter $apiFilter): array
    {
        return [
            Parameter::create()
                ->name($apiFilter->getParameterName())
                ->schema(Schema::string()),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterProperty(
        $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $ids = $this->findContainingIds(
            $property,
            $values,
            $query
        );

        return $query->in('uid', $ids + [0]);
    }

    /**
     * @param string $property
     * @param array $values
     * @param QueryInterface $query
     * @throws UnexpectedTypeException
     * @return int[]
     */
    protected function findContainingIds(
        string $property,
        array $values,
        QueryInterface $query
    ): array {
        $tableName = $this->getTableName($query);
        $conditions = [];
        $rootAlias = 'o';
        $queryBuilder = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);

        if ($this->isPropertyNested($property)) {
            $joinedProperty = $this->addJoinsForNestedProperty($property, $rootAlias, $query, $queryBuilder);
            [$tableAlias, $propertyName] = $joinedProperty;
        } else {
            $tableAlias = $rootAlias;
            $propertyName = $property;
        }

        foreach ($values as $i => $value) {
            $conditions[] = sprintf(
                'FIND_IN_SET(%s, %s) > 0',
                $queryBuilder->createNamedParameter($value),
                $queryBuilder->quoteIdentifier(
                    $tableAlias . '.' . $this->getObjectManager()->get(DataMapper::class)
                        ->convertPropertyNameToColumnName($propertyName)
                )
            );
        }

        return $queryBuilder
            ->select($rootAlias . '.uid')
            ->from($tableName, $rootAlias)
            ->andWhere($queryBuilder->expr()->orX(...$conditions))
            ->execute()
            ->fetchAll(FetchMode::COLUMN);
    }
}
