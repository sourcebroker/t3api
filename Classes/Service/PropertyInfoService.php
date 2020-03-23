<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use ReflectionClass;
use RuntimeException;
use SourceBroker\T3api\Annotation\ORM\Cascade;

class PropertyInfoService
{
    /**
     * @param string $className
     * @param string $propertyName
     * @return bool
     * @throws RuntimeException
     */
    public static function allowsCascadePersistence(string $className, string $propertyName): bool
    {
        try {
            $annotationReader = new AnnotationReader();
            $reflectionClass = new ReflectionClass($className);
            $propertyReflection = $reflectionClass->getProperty($propertyName);
            $annotations = $annotationReader->getPropertyAnnotations($propertyReflection);
            $cascadeAnnotations = array_filter(
                $annotations,
                static function ($annotation) {
                    return $annotation instanceof Cascade;
                }
            );

            /** @var Cascade $cascadeAnnotation */
            foreach ($cascadeAnnotations as $cascadeAnnotation) {
                if (in_array('persist', $cascadeAnnotation->values, true)) {
                    return true;
                }
            }
        } catch (Exception $exception) {
            throw new RuntimeException('It was not possible to check if property allows cascade persistence due to exception', 1584949881062, $exception);
        }

        return false;
    }
}
