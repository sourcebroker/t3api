<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use SourceBroker\T3api\Annotation\ApiFilter as ApiFilterAnnotation;
use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Service\ApiResourceConfigurationValidator;

class ApiResourceFactory
{
    protected AnnotationReader $annotationReader;

    public function __construct(
        protected readonly ApiResourceConfigurationValidator $apiResourceConfigurationValidator
    ) {
        $this->annotationReader = new AnnotationReader();
    }

    public function createApiResourceFromFqcn(string $fqcn): ?ApiResource
    {
        $apiResourceAnnotation = $this->getApiResourceAnnotation($fqcn);

        if ($apiResourceAnnotation === null) {
            return null;
        }

        $apiResource = new ApiResource($fqcn, $apiResourceAnnotation);

        $this->addFiltersToApiResource($apiResource);

        $this->apiResourceConfigurationValidator->validate($apiResource);

        return $apiResource;
    }

    protected function getApiResourceAnnotation(string $fqcn): ?ApiResourceAnnotation
    {
        $apiResourceAnnotation = $this->annotationReader->getClassAnnotation(
            new \ReflectionClass($fqcn),
            ApiResourceAnnotation::class
        );

        return $apiResourceAnnotation instanceof ApiResourceAnnotation ? $apiResourceAnnotation : null;
    }

    protected function addFiltersToApiResource(ApiResource $apiResource): void
    {
        $filterAnnotations = array_filter(
            $this->annotationReader->getClassAnnotations(new \ReflectionClass($apiResource->getEntity())),
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
