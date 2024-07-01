<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use SourceBroker\T3api\Exception\ValidationException;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

class ValidationService
{
    public function __construct(protected readonly ValidatorResolver $validatorResolver) {}

    /**
     * @throws ValidationException
     */
    public function validateObject(AbstractDomainObject $obj): Result
    {
        $validator = $this->validatorResolver->getBaseValidatorConjunction(get_class($obj));
        $validationResults = $validator->validate($obj);

        if ($validationResults->hasErrors()) {
            throw new ValidationException($validationResults, 1581461085077);
        }

        return $validationResults;
    }
}
