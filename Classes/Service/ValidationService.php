<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Service;

use SourceBroker\T3api\Exception\ValidationException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

/**
 * Class ValidationService
 */
class ValidationService
{
    /**
     * @param AbstractDomainObject $obj
     *
     * @throws ValidationException
     * @return Result
     */
    public function validateObject($obj): Result
    {
        /* @var $validator ConjunctionValidator */
        $validator = GeneralUtility::makeInstance(ValidatorResolver::class)
            ->getBaseValidatorConjunction(get_class($obj));
        $validationResults = $validator->validate($obj);

        if ($validationResults->hasErrors()) {
            throw new ValidationException($validationResults, 1581461085077);
        }

        return $validationResults;
    }
}
