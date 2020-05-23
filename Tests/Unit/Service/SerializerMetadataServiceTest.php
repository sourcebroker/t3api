<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use ReflectionClass;
use ReflectionException;
use SourceBroker\T3api\Annotation\Serializer\Groups;
use SourceBroker\T3api\Annotation\Serializer\Type\Image;
use SourceBroker\T3api\Annotation\Serializer\Type\RecordUri;
use SourceBroker\T3api\Service\SerializerMetadataService;
use SourceBroker\T3api\Tests\Unit\Fixtures\Address;
use SourceBroker\T3api\Tests\Unit\Fixtures\Company;
use SourceBroker\T3api\Tests\Unit\Fixtures\Group;
use SourceBroker\T3api\Tests\Unit\Fixtures\Person;
use SourceBroker\T3api\Tests\Unit\Fixtures\Tag;

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
        $dateTimeFormat = PHP_VERSION_ID >= 70300 ? \DateTimeInterface::RFC3339_EXTENDED : 'Y-m-d\TH:i:s.uP';

        return [
            Person::class => [
                Person::class,
                [
                    'id' => [
                        'type' => 'int',
                        'read_only' => true,
                    ],
                    'groups' => [
                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3api\Tests\Unit\Service\Fixtures\Group>',
                    ],
                    'address' => [
                        'type' => 'SourceBroker\T3api\Tests\Unit\Fixtures\Address',
                    ],
                    'firstName' => [
                        'type' => 'string'
                    ],
                    'lastName' => [
                        'type' => 'string'
                    ],
                    'maidenName' => [
                        'type' => 'string',
                        'serialized_name' => 'familyName',
                    ],
                    'dateOfBirth' => [
                        'type' => sprintf('DateTime<"%s">', $dateTimeFormat),
                    ],
                    'created' => [
                        'type' => sprintf('DateTimeImmutable<"%s">', $dateTimeFormat),
                    ],
                    'bankAccountNumber' => [
                        'type' => 'string',
                        'groups' => [
                            'accountancy',
                        ],
                    ],
                    'hidden' => [
                        'type' => 'bool',
                    ],
                ]
            ],
            Company::class => [
                Company::class,
                [
                    'id' => [
                        'type' => 'int',
                        'read_only' => true,
                    ],
                    'name' => [
                        'type' => 'string',
                    ],
                    'groups' => [
                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3api\Tests\Unit\Service\Fixtures\Group>',
                    ],
                    'address' => [
                        'type' => 'SourceBroker\T3api\Tests\Unit\Fixtures\Address',
                    ],
                    'bankAccountNumber' => [
                        'type' => 'string',
                        'groups' => [
                            'accountancy',
                        ],
                    ],
                    'invoiceAddress' => [
                        // @todo should be uncommented after moving to Symfony/PropertyInfo
//                        'type' => 'SourceBroker\T3api\Tests\Unit\Fixtures\Address',
                        'type' => 'Address',
                    ],
                    'hidden' => [
                        'type' => 'bool',
                    ],
                ]
            ],
            Group::class => [
                Group::class,
                [
                    'title' => [
                        'type' => 'string',
                    ],
                ]
            ],
            Tag::class => [
                Tag::class,
                [
                    'title' => [
                        'type' => 'string',
                    ]
                ]
            ],
            Address::class => [
                Address::class,
                [
                    'street' => [
                        'type' => 'string',
                    ],
                    'zip' => [
                        'type' => 'string',
                    ],
                    'city' => [
                        'type' => 'string',
                    ],
                    'created' => [
                        'type' => sprintf('DateTimeImmutable<"%s">', $dateTimeFormat),
                    ],
                    'modified' => [
                        'type' => sprintf('DateTime<"%s">', $dateTimeFormat),
                    ]
                ]
            ]
        ];
    }

    /**
     * @param string $className
     * @param $expectedType
     * @throws ReflectionException
     * @dataProvider getPropertiesReturnsCorrectValueDataProvider
     * @test
     */
    public function getPropertiesReturnsCorrectValue($className, $expectedType): void
    {
        self::assertEquals(
            $expectedType,
            self::callProtectedMethod(
                'getProperties',
                [
                    new ReflectionClass($className),
                    new AnnotationReader(),
                ]
            )
        );
    }

    public function getVirtualPropertiesReturnsCorrectValueDataProvider(): array
    {
        $dateTimeFormat = PHP_VERSION_ID >= 70300 ? \DateTimeInterface::RFC3339_EXTENDED : 'Y-m-d\TH:i:s.uP';

        return [
            Person::class => [
                Person::class,
                [
                    'getFullName' => [
                        'type' => 'string',
                        'serialized_name' => 'fullName',
                        'name' => 'fullName',
                    ],
                    'getTagIds' => [
                        'type' => 'array<int>',
                        'serialized_name' => 'tagIds',
                        'name' => 'tagIds',
                    ],
                    'getIdsOfAssignedGroups' => [
                        'type' => 'array<int>',
                        'name' => 'groupIds',
                        'serialized_name' => 'groupIds',
                    ],
                    'getTags' => [
                        'name' => 'tags',
                        'serialized_name' => 'tags',
                        // @todo should be uncommented after moving to Symfony/PropertyInfo
//                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3api\Tests\Unit\Fixtures\Tag>',
                        'type' => 'ObjectStorage<Tag>',
                    ],
                    'getBankAccountIban' => [
                        'name' => 'bankAccountIban',
                        'serialized_name' => 'bankAccountIban',
                        'type' => 'string',
                    ]
                ]
            ],
            Company::class => [
                Company::class,
                [
                    'getTagIds' => [
                        'type' => 'array<int>',
                        'serialized_name' => 'tagIds',
                        'name' => 'tagIds',
                    ],
                    'getIdsOfAssignedGroups' => [
                        'type' => 'array<int>',
                        'name' => 'groupIds',
                        'serialized_name' => 'groupIds',
                    ],
                    'getTags' => [
                        'name' => 'tags',
                        'serialized_name' => 'tags',
                        // @todo should be uncommented after moving to Symfony/PropertyInfo
//                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3api\Tests\Unit\Fixtures\Tag>',
                        'type' => 'ObjectStorage<Tag>',
                    ],
                    'getBankAccountIban' => [
                        'name' => 'bankAccountIban',
                        'serialized_name' => 'bankAccountIban',
                        'type' => 'string',
                    ]
                ]
            ],
            Group::class => [
                Group::class,
                [
                    'getNumberOfAssignedEntries' => [
                        'name' => 'numberOfAssignedEntries',
                        'serialized_name' => 'numberOfAssignedEntries',
                        'type' => 'int',
                    ],
                ]
            ],
            Tag::class => [
                Tag::class,
                []
            ],
            Address::class => [
                Address::class,
                []
            ]
        ];
    }

    /**
     * @param string $className
     * @param $expectedType
     * @throws ReflectionException
     * @dataProvider getVirtualPropertiesReturnsCorrectValueDataProvider
     * @test
     */
    public function getVirtualPropertiesReturnsCorrectValue(string $className, $expectedType): void
    {
        self::assertEquals(
            $expectedType,
            self::callProtectedMethod(
                'getVirtualProperties',
                [
                    new ReflectionClass($className),
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
