<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Filter;

use RuntimeException;
use InvalidArgumentException;
use SourceBroker\Restify\Domain\Model\ApiFilter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Doctrine\DBAL\FetchMode;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Selector;

/**
 * Class SearchFilter
 */
class SearchFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     * @throws InvalidQueryException
     */
    public function filterProperty(
        $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
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
            case 'matchAgainst':
                if (strpos($property, '.') !== false) {
                    throw new InvalidArgumentException(
                        '`matchAgainst` strategy does not support searching in nested properties yet',
                        1562140187815
                    );
                }

                $ids = $this->matchAgainstFindIds(
                    $property,
                    $values,
                    $query,
                    (bool)$apiFilter->getArgument('withQueryExpansion')
                );

                return $query->in('uid', $ids + [0]);
            case 'exact':
            default:
                return $query->in($property, $values);
        }
    }

    /**
     * @param string $property
     * @param array $values
     * @param QueryInterface $query
     * @param bool $queryExpansion
     *
     * @return array
     */
    protected function matchAgainstFindIds(
        string $property,
        array $values,
        QueryInterface $query,
        bool $queryExpansion = false
    ): array {
        if (!$query instanceof Query) {
            throw new RuntimeException(
                sprintf('Query needs to be instance of %s to get source', Query::class),
                1562138597664
            );
        }

        /** @var Selector $source */
        $source = $query->getSource();

        if (!$query->getSource() instanceof SelectorInterface) {
            throw new RuntimeException('Query source does not implement SelectorInterface.', 1561557242370);
        }

        $tableName = $source->getSelectorName();
        $conditions = [];
        $binds = [];

        foreach ($values as $i => $value) {
            $key = ':text_ma_' . ((int)$i);
            $conditions[] = sprintf(
                'MATCH(`%s`) AGAINST (%s IN NATURAL LANGUAGE MODE  %s)',
                $property,
                $key,
                $queryExpansion ? ' WITH QUERY EXPANSION ' : ''
            );
            $binds[$key] = $value;
        }

        return GeneralUtility::makeInstance(ObjectManager::class)
            ->get(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName)
            ->select('uid')
            ->from($tableName)
            ->andWhere(...$conditions)
            ->setParameters($binds)
            ->execute()
            ->fetchAll(FetchMode::COLUMN);
    }
}
