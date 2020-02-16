<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Filter;

use Doctrine\DBAL\FetchMode;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use InvalidArgumentException;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;

/**
 * Class DistanceFilter
 */
class DistanceFilter extends AbstractFilter
{
    protected const PARAMETER_LATITUDE = 'lat';
    protected const PARAMETER_LONGITUDE = 'lng';
    protected const PARAMETER_RADIUS = 'radius';
    protected const MILES_MULTIPLIER = 3959;
    protected const KILOMETERS_MULTIPLIER = 6371;
    protected const DEFAULT_RADIUS = 100;

    /**
     * @param ApiFilter $apiFilter
     *
     * @return Parameter[]
     */
    public static function getDocumentationParameters(ApiFilter $apiFilter): array
    {
        return [
            Parameter::create()
                ->name($apiFilter->getParameterName() . '[' . self::PARAMETER_LATITUDE . ']')
                ->schema(Schema::number()),
            Parameter::create()
                ->name($apiFilter->getParameterName() . '[' . self::PARAMETER_LONGITUDE . ']')
                ->schema(Schema::number()),
            Parameter::create()
                ->name($apiFilter->getParameterName() . '[' . self::PARAMETER_RADIUS . ']')
                ->schema(Schema::number()),
        ];
    }

    /**
     * @inheritDoc
     * @throws InvalidQueryException
     * @throws UnexpectedTypeException
     */
    public function filterProperty(
        $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        [$lat, $lng] = $this->getLatLangParameterValues($values, $apiFilter);
        $radius = $this->getRadiusParameterValue($values, $apiFilter);
        $multiplier = strtolower($apiFilter->getArgument('unit') ?? '') === 'mi'
            ? self::MILES_MULTIPLIER : self::KILOMETERS_MULTIPLIER;

        $latProperty = $apiFilter->getArgument('latProperty');
        $lngProperty = $apiFilter->getArgument('lngProperty');

        $tableName = $this->getTableName($query);
        $rootAlias = 'o';
        $queryBuilder = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);

        if ($this->isPropertyNested($latProperty)) {
            $joinedProperty = $this->addJoinsForNestedProperty($latProperty, $rootAlias, $query, $queryBuilder);
            [$latTableAlias, $latPropertyName] = $joinedProperty;
        } else {
            $latTableAlias = $rootAlias;
            $latPropertyName = $latProperty;
        }

        if ($this->isPropertyNested($lngProperty)) {
            $joinedProperty = $this->addJoinsForNestedProperty($lngProperty, $rootAlias, $query, $queryBuilder);
            [$lngTableAlias, $lngPropertyName] = $joinedProperty;
        } else {
            $lngTableAlias = $rootAlias;
            $lngPropertyName = $lngProperty;
        }

        $dataMapper = $this->getObjectManager()->get(DataMapper::class);
        $latColumn = $dataMapper->convertPropertyNameToColumnName($latPropertyName);
        $lngColumn = $dataMapper->convertPropertyNameToColumnName($lngPropertyName);

        $ids = $queryBuilder
            ->select($rootAlias . '.uid')
            ->addSelectLiteral('(
                :multiplier * ACOS(
                    COS(RADIANS(:lat))
                    * COS(RADIANS(' . $latTableAlias . '.' . $latColumn . '))
                    * COS(RADIANS(' . $lngTableAlias . '.' . $lngColumn . ') - RADIANS(:lng))
                    + SIN(RADIANS(:lat))
                    * SIN(RADIANS(' . $latTableAlias . '.' . $latColumn . '))
                )
            ) AS distance')
            ->from($tableName, $rootAlias)
            ->having($queryBuilder->expr()->lte('distance', ':radius'))
            ->setParameters([
                'multiplier' => $multiplier,
                'radius' => $radius,
                'lat' => $lat,
                'lng' => $lng,
            ])
            ->execute()
            ->fetchAll(FetchMode::COLUMN);

        return $query->in('uid', array_merge($ids, [0]));
    }

    /**
     * @param array $values
     * @param ApiFilter $apiFilter
     *
     * @return array array with two elements - lat and lang
     * @throws InvalidArgumentException
     *
     */
    protected function getLatLangParameterValues(array $values, ApiFilter $apiFilter): array
    {
        if (!isset($values[self::PARAMETER_LATITUDE], $values[self::PARAMETER_LONGITUDE])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Parameters `%s[%s]` and %s[%s] are required to use distance filter',
                    $apiFilter->getParameterName(),
                    self::PARAMETER_LATITUDE,
                    $apiFilter->getParameterName(),
                    self::PARAMETER_LONGITUDE
                )
            );
        }

        return [(float)$values[self::PARAMETER_LATITUDE], (float)$values[self::PARAMETER_LONGITUDE]];
    }

    /**
     * @param array $values
     * @param ApiFilter $apiFilter
     *
     * @return float
     */
    protected function getRadiusParameterValue(array $values, ApiFilter $apiFilter): float
    {
        if (
            isset($values[self::PARAMETER_RADIUS])
            && (bool)$apiFilter->getArgument('allowClientRadius')
        ) {
            return (float)$values[self::PARAMETER_RADIUS];
        }

        if ($apiFilter->getArgument('radius') !== null) {
            return (float)$apiFilter->getArgument('radius');
        }

        return self::DEFAULT_RADIUS;
    }
}
