<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use ReflectionClass;
use ReflectionException;
use SourceBroker\T3api\Annotation\Serializer\Exclude;
use SourceBroker\T3api\Annotation\Serializer\Groups;
use SourceBroker\T3api\Annotation\Serializer\ReadOnly;
use SourceBroker\T3api\Annotation\Serializer\SerializedName;
use SourceBroker\T3api\Annotation\Serializer\Type\Image;
use SourceBroker\T3api\Annotation\Serializer\Type\RecordUri;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use SourceBroker\T3api\Service\SerializerMetadataService;

/**
 * Class SerializerMetadataServiceTest
 */
class SerializerMetadataServiceTest extends UnitTestCase
{
    public function getPropertyMetadataFromAnnotationsReturnsCorrectValueDataProvider(): array
    {
        return [
            'Groups' => [
                static function () {
                    $groups = new Groups();
                    $groups->groups = ['api_example_group_1234', 'api_another_example_group'];

                    return [$groups];
                },
                [
                    'groups' => [
                        'api_example_group_1234',
                        'api_another_example_group',
                    ],
                ],
            ],
            'Type - Image (with width and height)' => [
                static function () {
                    $image = new Image();
                    $image->width = '800c';
                    $image->height = '600';

                    return [$image];
                },
                [
                    'type' => 'Image<"800c","600","","">',
                ],
            ],
            'Type - Image (with maxWidth)' => [
                static function () {
                    $image = new Image();
                    $image->maxWidth = 450;

                    return [$image];
                },
                [
                    'type' => 'Image<"","","450","">',
                ],
            ],
            'Type - RecordUri' => [
                static function () {
                    $recordUri = new RecordUri();
                    $recordUri->identifier = 'tx_example_identifier';

                    return [$recordUri];
                },
                [
                    'type' => 'RecordUri<"tx_example_identifier">',
                ],
            ],
            'RecordUri with groups' => [
                static function () {
                    $recordUri = new RecordUri();
                    $recordUri->identifier = 'tx_another_identifier';
                    $groups = new Groups();
                    $groups->groups = ['api_group_sample', 'api_group_sample_2'];

                    return [$groups, $recordUri];
                },
                [
                    'type' => 'RecordUri<"tx_another_identifier">',
                    'groups' => [
                        'api_group_sample',
                        'api_group_sample_2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param callable $annotations
     * @param array $expectedResult
     *
     * @dataProvider getPropertyMetadataFromAnnotationsReturnsCorrectValueDataProvider
     * @test
     *
     * @throws ReflectionException
     */
    public function getPropertyMetadataFromAnnotationsReturnsCorrectValue(
        callable $annotations,
        array $expectedResult
    ): void {
        self::assertEqualsCanonicalizing(
            $expectedResult,
            self::callProtectedMethod('getPropertyMetadataFromAnnotations', [$annotations()])
        );
    }

    public function parsePropertyTypeReturnsCorrectValueDataProvider(): array
    {
        $dateTimeFormat = PHP_VERSION_ID >= 70300 ? \DateTimeInterface::RFC3339_EXTENDED : 'Y-m-d\TH:i:s.uP';

        return [
            '\DateTime' => [
                '\DateTime',
                sprintf('DateTime<"%s">', $dateTimeFormat),
            ],
            '\DateTime|null' => [
                '\DateTime|null',
                sprintf('DateTime<"%s">', $dateTimeFormat),
            ],
            'null|\DateTime' => [
                'null|\DateTime',
                sprintf('DateTime<"%s">', $dateTimeFormat),
            ],
            'DateTime|null' => [
                '\DateTime|null',
                sprintf('DateTime<"%s">', $dateTimeFormat),
            ],
            'DateTime | null' => [
                'DateTime | null',
                sprintf('DateTime<"%s">', $dateTimeFormat),
            ],
            '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>',
                'TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            'TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                'TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>',
                'TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
                'TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            '\TYPO3\CMS\Extbase\Domain\Model\FileReference' => [
                '\TYPO3\CMS\Extbase\Domain\Model\FileReference',
                'TYPO3\CMS\Extbase\Domain\Model\FileReference',
            ],
            'TYPO3\CMS\Extbase\Domain\Model\FileReference' => [
                'TYPO3\CMS\Extbase\Domain\Model\FileReference',
                'TYPO3\CMS\Extbase\Domain\Model\FileReference',
            ],
            'string' => [
                'string',
                'string',
            ],
            'string And here, in same lane as var annotation, is description for the property' => [
                'string And here, in same lane as var annotation, is description for the property',
                'string',
            ],
            'boolean' => [
                'boolean',
                'boolean',
            ],
            'bool' => [
                'bool',
                'bool',
            ],
            'int' => [
                'int',
                'int',
            ],
            'integer' => [
                'integer',
                'integer',
            ],
            'double' => [
                'double',
                'double',
            ],
            'float' => [
                'float',
                'float',
            ],
            'array<string>' => [
                'array<string>',
                'array<string>',
            ],
            'array<string> And some additional description here' => [
                'array<string> And some additional description here',
                'array<string>',
            ],
            'array<\TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                'array<\TYPO3\CMS\Extbase\Domain\Model\FileReference>',
                'array<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            'string[]' => [
                'string[]',
                'array<string>',
            ],
            '[]' => [
                '[]',
                'array',
            ],
            'array' => [
                'array',
                'array',
            ],
            '\TYPO3\CMS\Extbase\Domain\Model\FileReference[]' => [
                '\TYPO3\CMS\Extbase\Domain\Model\FileReference[]',
                'array<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            'TYPO3\CMS\Extbase\Domain\Model\FileReference[]' => [
                'TYPO3\CMS\Extbase\Domain\Model\FileReference[]',
                'array<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            '\TYPO3\CMS\Extbase\Domain\Model\FileReference[] Additional description goes here' => [
                '\TYPO3\CMS\Extbase\Domain\Model\FileReference[]',
                'array<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
        ];
    }

    /**
     * @param string $varAnnotation
     * @param string $expectedType
     *
     * @dataProvider parsePropertyTypeReturnsCorrectValueDataProvider
     * @test
     *
     * @throws ReflectionException
     */
    public function parsePropertyTypeReturnsCorrectValue(string $varAnnotation, string $expectedType): void
    {
        self::assertEquals(
            $expectedType,
            self::callProtectedMethod('parsePropertyType', [$varAnnotation])
        );
    }

    public function getPropertiesReturnsCorrectValueDataProvider(): array
    {
        // @todo instead of anonymous class maybe move them to some fixtures - may be needed also in other tests
        return [
            'Class with primitive types only' => [
                new class {
                    /**
                     * @var string
                     */
                    protected $stringProperty;

                    /**
                     * @var int
                     */
                    protected $integerProperty;

                    /**
                     * @var int[]
                     */
                    protected $arrayOfIntegers;
                },
                [
                    'stringProperty' => ['type' => 'string'],
                    'integerProperty' => ['type' => 'int'],
                    'arrayOfIntegers' => ['type' => 'array<int>'],
                ],
            ],
            'Class with some t3api specific annotations' => [
                new class {
                    /**
                     * @ReadOnly()
                     * @var string
                     */
                    protected $readOnlyProperty;

                    /**
                     * @var int
                     * @Groups({
                     *      "group_a",
                     *      "group_b",
                     * })
                     */
                    protected $groupedProperty;

                    /**
                     * @var int[]
                     * @Exclude()
                     */
                    protected $excludedProperty;
                },
                [
                    'readOnlyProperty' => [
                        'type' => 'string',
                        'read_only' => true,
                    ],
                    'groupedProperty' => [
                        'type' => 'int',
                        'groups' => [
                            'group_a',
                            'group_b',
                        ],
                    ],
                    'excludedProperty' => [
                        'type' => 'array<int>',
                        'exclude' => true,
                    ],
                ],
            ],
            'Class with virtual property' => [
                new class {
                    /**
                     * @var bool
                     */
                    protected $propertyX;

                    /**
                     * @VirtualProperty()
                     * @return bool
                     */
                    public function isConfirmed(): bool
                    {
                        return false;
                    }
                },
                [
                    'propertyX' => [
                        'type' => 'bool',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $class
     * @param $expectedType
     * @throws ReflectionException
     * @dataProvider getPropertiesReturnsCorrectValueDataProvider
     * @test
     */
    public function getPropertiesReturnsCorrectValue($class, $expectedType): void
    {
        self::assertEquals(
            $expectedType,
            self::callProtectedMethod(
                'getProperties',
                [
                    new ReflectionClass($class),
                    new AnnotationReader(),
                ]
            )
        );
    }

    public function getVirtualPropertiesReturnsCorrectValueDataProvider(): array
    {
        // @todo instead of anonymous class maybe move them to some fixtures - may be needed also in other tests
        return [
            'Class with mixed properties - virtual and default' => [
                new class {
                    /**
                     * @var string
                     */
                    protected $someNonVirtualProperty;

                    /**
                     * @VirtualProperty()
                     * @return string
                     */
                    public function getTitle(): string
                    {
                        return 'zxc';
                    }
                },
                [
                    'getTitle' => [
                        'type' => 'string',
                        'name' => 'title',
                        'serialized_name' => 'title',
                    ],
                ],
            ],
            'Class with virtual property with custom serialized name' => [
                new class {
                    /**
                     * @return bool
                     * @VirtualProperty()
                     * @SerializedName("approved")
                     */
                    public function isConfirmed(): bool
                    {
                        return true;
                    }
                },
                [
                    'isConfirmed' => [
                        'type' => 'bool',
                        'name' => 'confirmed',
                        'serialized_name' => 'approved',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $class
     * @param $expectedType
     * @throws ReflectionException
     * @dataProvider getVirtualPropertiesReturnsCorrectValueDataProvider
     * @test
     */
    public function getVirtualPropertiesReturnsCorrectValue($class, $expectedType): void
    {
        self::assertEquals(
            $expectedType,
            self::callProtectedMethod(
                'getVirtualProperties',
                [
                    new ReflectionClass($class),
                    new AnnotationReader(),
                ]
            )
        );
    }

    /**
     * @param $methodName
     * @param array $arguments
     * @param object|null $object
     *
     * @throws ReflectionException
     * @return mixed
     */
    protected static function callProtectedMethod($methodName, array $arguments = [], object $object = null)
    {
        $serializerMetadataServiceReflection = new ReflectionClass(SerializerMetadataService::class);
        $method = $serializerMetadataServiceReflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object ? $object : null, $arguments);
    }
}
