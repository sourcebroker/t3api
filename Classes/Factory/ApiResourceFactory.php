<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use SourceBroker\T3api\Annotation\ApiFilter as ApiFilterAnnotation;
use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\ApiResource;

class ApiResourceFactory
{
    protected AnnotationReader $annotationReader;

    public function __construct()
    {
        $this->annotationReader = new AnnotationReader();
    }

    public function createApiResourceFromFqcn(string $fqcn): ?ApiResource
    {
        /** @var ApiResourceAnnotation $apiResourceAnnotation */
        $apiResourceAnnotation = $this->annotationReader->getClassAnnotation(
            new ReflectionClass($fqcn),
            ApiResourceAnnotation::class
        );

        if (!$apiResourceAnnotation) {
            return null;
        }

        $apiResource = new ApiResource($fqcn, $apiResourceAnnotation);

        $this->addFiltersToApiResource($apiResource);

        return $apiResource;
    }

    protected function addFiltersToApiResource(ApiResource $apiResource): void
    {
        $filterAnnotations = array_filter(
            $this->annotationReader->getClassAnnotations(new ReflectionClass($apiResource->getEntity())),
            static function ($annotation): bool {
                return $annotation instanceof ApiFilterAnnotation;
            }
        );

        foreach ($filterAnnotations as $filterAnnotation) {
            foreach (ApiFilter::createFromAnnotations($filterAnnotation) as $apiFilter) {
                $apiResource->addFilter($apiFilter);
            }
        }
    }
}
