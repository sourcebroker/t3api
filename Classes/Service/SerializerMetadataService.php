<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Service;

use DateTime;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;
use SourceBroker\T3api\Annotation\Serializer\Groups;
use SourceBroker\T3api\Annotation\Serializer\Type\TypeInterface;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Reflection\DocCommentParser;

/**
 * Class SerializerMetadataService
 */
class SerializerMetadataService
{
    /**
     * @param string $entity
     *
     * @throws ReflectionException
     * @throws AnnotationException
     */
    public static function generateAutoloadForEntity(string $entity): void
    {
        foreach (self::getClassHierarchy($entity) as $class) {
            $generatedMetadataFile = SerializerService::getAutogeneratedMetadataDirectory() . '/'
                . str_replace('\\', '.', $class->getName()) . '.yml';

            $classMergedMetadata = array_replace_recursive(
                self::getMetadataFromMetadataDirs($class->getName()),
                self::getForClass($class)
            );

            file_put_contents(
                $generatedMetadataFile,
                Yaml::dump([$class->getName() => $classMergedMetadata], 99)
            );
        }
    }

    /**
     * @param string $class
     * @return array
     */
    protected static function getMetadataFromMetadataDirs(string $class): array
    {
        static $parsedMetadata;

        if ($parsedMetadata === null) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] as $serializerMetadataDir) {
                $files = glob(rtrim($serializerMetadataDir, '/') . '/*.yml');

                if (!empty($files)) {
                    foreach ($files as $file) {
                        $parsedMetadata = array_replace_recursive($parsedMetadata ?? [], Yaml::parseFile($file));
                    }
                }
            }
        }

        return $parsedMetadata[$class] ?? [];
    }

    /**
     * @param ReflectionClass $reflectionClass
     *
     * @throws AnnotationException
     * @return array
     */
    protected static function getForClass(ReflectionClass $reflectionClass): array
    {
        $annotationReader = new AnnotationReader();

        return [
            'properties' => self::getProperties($reflectionClass, $annotationReader),
            'virtual_properties' => self::getVirtualProperties($reflectionClass, $annotationReader),
        ];
    }

    /**
     * @param string $class
     * @throws ReflectionException
     * @return ReflectionClass[]
     */
    protected static function getClassHierarchy(string $class): array
    {
        $classes = [];
        $reflectionClass = new ReflectionClass($class);

        do {
            $classes[] = $reflectionClass;
            $reflectionClass = $reflectionClass->getParentClass();
        } while (false !== $reflectionClass);

        return array_reverse($classes, false);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param AnnotationReader $annotationReader
     *
     * @return array
     */
    protected static function getProperties(ReflectionClass $reflectionClass, AnnotationReader $annotationReader): array
    {
        $docCommentParser = new DocCommentParser(true);
        $properties = [];

        /** @var ReflectionProperty $property */
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $docCommentParser->parseDocComment($reflectionProperty->getDocComment());
            $type = $docCommentParser->getTagValues('var')[0];

            if (empty($type)) {
                throw new RuntimeException(
                    sprintf(
                        '`@var` annotation missing in property %s',
                        $reflectionClass->getName() . '::' . $reflectionProperty->getName()
                    ),
                    1570723476311
                );
            }

            $properties[$reflectionProperty->getName()] = self::getPropertyMetadataFromAnnotations(
                $annotationReader->getPropertyAnnotations($reflectionProperty)
            );
            if (empty($properties[$reflectionProperty->getName()]['type'])) {
                $properties[$reflectionProperty->getName()]['type'] = self::parsePropertyType($type);
            }
        }

        return $properties;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param AnnotationReader $annotationReader
     *
     * @return array
     */
    protected static function getVirtualProperties(
        ReflectionClass $reflectionClass,
        AnnotationReader $annotationReader
    ): array {
        $virtualProperties = [];

        /** @var ReflectionMethod $property */
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            /** @var VirtualProperty $virtualProperty */
            $virtualProperty = $annotationReader->getMethodAnnotation($reflectionMethod, VirtualProperty::class);

            if (!$virtualProperty) {
                continue;
            }

            if ($virtualProperty->name) {
                $propertyName = $virtualProperty->name;
            } elseif (strpos($reflectionMethod->getName(), 'is') === 0) {
                $propertyName = lcfirst(substr($reflectionMethod->getName(), 2));
            } elseif (strpos($reflectionMethod->getName(), 'get') === 0) {
                $propertyName = lcfirst(substr($reflectionMethod->getName(), 3));
            } elseif (strpos($reflectionMethod->getName(), 'has') === 0) {
                $propertyName = lcfirst(substr($reflectionMethod->getName(), 3));
            } else {
                $propertyName = $reflectionMethod->getName();
            }

            $virtualProperties[$reflectionMethod->getName()] = self::getPropertyMetadataFromAnnotations(
                $annotationReader->getMethodAnnotations($reflectionMethod)
            );
            $virtualProperties[$reflectionMethod->getName()]['name'] = $propertyName;
            $virtualProperties[$reflectionMethod->getName()]['serialized_name'] = $propertyName;
        }

        return $virtualProperties;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected static function parsePropertyType(string $type): string
    {
        $type = self::getValuablePropertyType($type);

        if (StringUtility::endsWith($type, '[]')) {
            $subType = self::parsePropertyType(rtrim($type, '[]'));

            if (!empty($subType)) {
                return sprintf('array<%s>', $subType);
            }

            return 'array';
        }

        if (is_a($type, DateTime::class, true)) {
            return sprintf('DateTime<"%s">', PHP_VERSION_ID >= 70300 ? DateTime::RFC3339_EXTENDED : 'Y-m-d\TH:i:s.uP');
        }

        if (class_exists($type)) {
            return ltrim($type, '\\');
        }

        if (in_array($type, ['string', 'int', 'integer', 'boolean', 'bool', 'double', 'float'])) {
            return $type;
        }

        if (strpos($type, '<') !== false) {
            $collectionType = self::parsePropertyType(trim(explode('<', $type)[0]));
            $itemsType = self::parsePropertyType(trim(explode('<', $type)[1], '> '));

            return sprintf('%s<%s>', $collectionType, $itemsType);
        }

        return $type;
    }

    /**
     * Returns first valuable property type if multiple types are defined.
     *
     * @example Ensures that `\DateTime` is returned when type is wrote like `null|\DateTime`
     *
     * @param string $inputType
     *
     * @return string
     */
    protected static function getValuablePropertyType(string $inputType): string
    {
        $multipleTypes = GeneralUtility::trimExplode('|', $inputType);

        $type = $multipleTypes[0];

        if (count($multipleTypes) > 1) {
            foreach ($multipleTypes as $type) {
                if (strtolower($type) !== 'null') {
                    break;
                }
            }
        }

        return explode(' ', $type)[0];
    }

    /**
     * @param object[] $annotations
     *
     * @return array
     */
    protected static function getPropertyMetadataFromAnnotations(array $annotations): array
    {
        $metadata = [];

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Groups) {
                $metadata['groups'] = $annotation->groups;
            } elseif ($annotation instanceof TypeInterface) {
                $metadata['type'] = $annotation->getName();

                if (!empty($annotation->getParams())) {
                    $metadata['type'] .= '<"' . implode('","', $annotation->getParams()) . '">';
                }
            }

            // @todo 591 add support to rest of t3api annotations
        }

        return $metadata;
    }
}
