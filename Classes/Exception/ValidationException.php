<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use Symfony\Component\HttpFoundation\Response;
use TYPO3\CMS\Extbase\Error\Result;

class ValidationException extends AbstractException
{
    /**
     * @var int
     * @see https://stackoverflow.com/a/3290198/1588346
     */
    protected static $statusCode = Response::HTTP_BAD_REQUEST;

    /**
     * @var Result
     */
    protected $validationResult;

    public function __construct(Result $validationResult)
    {
        $this->validationResult = $validationResult;
        parent::__construct('An error occurred during object validation', 1580940330389);
    }

    /**
     * @VirtualProperty("violations")
     */
    public function getViolations(): array
    {
        return [];
    }
}
