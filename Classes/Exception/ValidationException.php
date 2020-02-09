<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

use SourceBroker\T3api\Annotation\Serializer\Exclude;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use Symfony\Component\HttpFoundation\Response;
use TYPO3\CMS\Extbase\Error\Result;

class ValidationException extends AbstractException
{
    /**
     * @var Result
     * @Exclude()
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
        return $this->getViolationsRecursive($this->validationResult);
    }

    /**
     * @see https://stackoverflow.com/a/3290198/1588346
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    protected function getViolationsRecursive(Result $result, array $propertyPath = [], array &$violations = []): array
    {
        foreach ($result->getErrors() as $error) {
            $violations[] = [
                'propertyPath' => implode('.', $propertyPath),
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
            ];
        }

        if (!empty($result->getSubResults())) {
            foreach ($result->getSubResults() as $subPropertyName => $subResult) {
                $this->getViolationsRecursive(
                    $subResult,
                    array_merge($propertyPath, [$subPropertyName]),
                    $violations
                );
            }
        }

        return $violations;
    }
}
