<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Validation\Exception as ValidationException;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

/**
 * Class ValidationService
 */
class ValidationService
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param AbstractDomainObject $obj
     *
     * @throws ValidationException
     * @return Result
     */
    public function validateObject($obj): Result
    {
        /* @var $validator ConjunctionValidator */
        $validator = $this->objectManager
            ->get(ValidatorResolver::class)
            ->getBaseValidatorConjunction(get_class($obj));
        $validationResults = $validator->validate($obj);

        if ($validationResults->hasErrors()) {
            // @todo #593 change to custom error and pass `$validationResults` there to make it possible to include
            //    validation details in response
            throw new ValidationException('Invalid object', 1568472104363);
        }

        return $validationResults;
    }
}
