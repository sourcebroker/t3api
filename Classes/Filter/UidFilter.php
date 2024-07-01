<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class UidFilter extends NumericFilter
{
    public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $languageAspect = new LanguageAspect(
            $query->getQuerySettings()->getLanguageAspect()->getId(),
            $query->getQuerySettings()->getLanguageAspect()->getContentId(),
            LanguageAspect::OVERLAYS_ON
        );
        $query->getQuerySettings()->setLanguageAspect($languageAspect);

        return parent::filterProperty($property, $values, $query, $apiFilter);
    }
}
