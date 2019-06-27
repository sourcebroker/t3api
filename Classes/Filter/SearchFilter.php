<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Filter;

use http\Exception\RuntimeException;
use SourceBroker\Restify\Domain\Model\ApiFilter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class SearchFilter
 */
class SearchFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     * @throws InvalidQueryException
     */
    public function filterProperty($property, $values, QueryInterface $query, ApiFilter $apiFilter): ?ConstraintInterface
    {
        $values = (array)$values;

        switch ($apiFilter->getStrategy()) {
            case 'partial':
                return $query->logicalOr(
                    array_map(
                        function ($value) use ($query, $property) {
                            return $query->like($property, '%' . $value . '%');
                        },
                        $values
                    )
                );
                break;
            case 'matchAgainst':
                $withQueryExpansion = (bool) $apiFilter->getArgument('withQueryExpansion');

                $ids = $this->matchAgainstFindIds($property, $values, $query, $withQueryExpansion);
                if (!$ids) {
                    return $query->equals('uid', 0);
                }

                return $query->in('uid', $ids);
                break;
            case 'exact':
            default:
                return $query->in($property, $values);
        }
    }

    public function matchAgainstFindIds(string $property, array $values, QueryInterface $query, bool $queryExpansion = false): array
    {
        $source = $query->getSource();
        if (!($source instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface)) {
            throw new RuntimeException('Query source does not implement SelectorInterface.', 1561557242370);
        }

        $tableName = $source->getSelectorName();

        $condtions = [];
        $binds = [];

        foreach ($values as $i => $value) {
            $key = ':text_ma_' . ((int)$i);
            $condtions[] = "MATCH(`$property`) AGAINST ($key IN NATURAL LANGUAGE MODE" . ($queryExpansion ? ' WITH QUERY EXPANSION' : '') .")";
            $binds[$key] = $value;
        }

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $connection = $objectManager->get(ConnectionPool::class);
        $queryBuilder = $connection->getQueryBuilderForTable($tableName);

        $ids = $queryBuilder->select('uid')
            ->from($tableName)
            ->andWhere(...$condtions)
            ->setParameters($binds)
            ->execute()
            ->fetchAll(\Doctrine\DBAL\FetchMode::COLUMN);

        return $ids;
    }
}
