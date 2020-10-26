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
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Address;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Category;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Company;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Group;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Person;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Tag;
use Symfony\Component\PropertyInfo\Type;

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
                    'type' => 'Image<\'800c\',\'600\',\'\',\'\'>',
                ],
            ],
            'Type - Image (with maxWidth)' => [
                static function () {
                    $image = new Image();
                    $image->maxWidth = 450;

                    return [$image];
                },
                [
                    'type' => 'Image<\'\',\'\',\'450\',\'\'>',
                ],
            ],
            'Type - RecordUri' => [
                static function () {
                    $recordUri = new RecordUri();
                    $recordUri->identifier = 'tx_example_identifier';

                    return [$recordUri];
                },
                [
                    'type' => 'RecordUri<\'tx_example_identifier\'>',
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
                    'type' => 'RecordUri<\'tx_another_identifier\'>',
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

    public function stringifyPropertyTypeReturnsCorrectValueDataProvider(): array
    {
        $dateTimeFormat = PHP_VERSION_ID >= 70300 ? \DateTimeInterface::RFC3339_EXTENDED : 'Y-m-d\TH:i:s.uP';

        return [
            'DateTime' => [
                new Type('object', false, 'DateTime'),
                sprintf('DateTime<\'%s\'>', $dateTimeFormat),
            ],
            'DateTimeImmutable' => [
                new Type('object', true, 'DateTimeImmutable'),
                sprintf('DateTimeImmutable<\'%s\'>', $dateTimeFormat),
            ],
            'TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                new Type(
                    'object',
                    false,
                    'TYPO3\CMS\Extbase\Persistence\ObjectStorage',
                    true,
                    null,
                    new Type('object', false, 'TYPO3\CMS\Extbase\Domain\Model\FileReference')
                ),
                'TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            'TYPO3\CMS\Extbase\Domain\Model\FileReference' => [
                new Type('object', true, 'TYPO3\CMS\Extbase\Domain\Model\FileReference'),
                'TYPO3\CMS\Extbase\Domain\Model\FileReference',
            ],
            'string' => [
                new Type('string'),
                'string',
            ],
            'bool' => [
                new Type('bool'),
                'bool',
            ],
            'int' => [
                new Type('int'),
                'int',
            ],
            'float' => [
                new Type('float'),
                'float',
            ],
            'array<string>' => [
                new Type('array', false, null, true, null, new Type('string')),
                'array<string>',
            ],
            'array<\TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                new Type(
                    'array',
                    false,
                    null,
                    true,
                    null,
                    new Type('object', false, 'TYPO3\CMS\Extbase\Domain\Model\FileReference')
                ),
                'array<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            'array' => [
                new Type('array'),
                'array',
            ],
        ];
    }

    /**
     * @param Type $type
     * @param string $expectedType
     *
     * @dataProvider stringifyPropertyTypeReturnsCorrectValueDataProvider
     * @test
     *
     * @throws ReflectionException
     */
    public function stringifyPropertyTypeReturnsCorrectValue(Type $type, string $expectedType): void
    {
        self::assertEquals(
            $expectedType,
            self::callProtectedMethod('stringifyPropertyType', [$type])
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
                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Group>',
                    ],
                    'categories' => [
                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Category>',
                    ],
                    'address' => [
                        'type' => 'SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\\Address',
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
                        'type' => sprintf('DateTime<\'%s\'>', $dateTimeFormat),
                    ],
                    'created' => [
                        'type' => sprintf('DateTimeImmutable<\'%s\'>', $dateTimeFormat),
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
                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Group>',
                    ],
                    'categories' => [
                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Category>',
                    ],
                    'address' => [
                        'type' => 'SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\\Address',
                    ],
                    'bankAccountNumber' => [
                        'type' => 'string',
                        'groups' => [
                            'accountancy',
                        ],
                    ],
                    'invoiceAddress' => [
                        'type' => 'SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\\Address',
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
            Category::class => [
                Category::class,
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
                        'type' => sprintf('DateTimeImmutable<\'%s\'>', $dateTimeFormat),
                    ],
                    'modified' => [
                        'type' => sprintf('DateTime<\'%s\'>', $dateTimeFormat),
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
                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Tag>',
                    ],
                    'getBankAccountIban' => [
                        'name' => 'bankAccountIban',
                        'serialized_name' => 'bankAccountIban',
                        'type' => 'string',
                    ],
                    'getPrivateAddress' => [
                        'name' => 'privateAddress',
                        'serialized_name' => 'privateAddress',
                        'type' => 'ExampleTypeWithNestedParams<\'PrivateAddress\',\'{"parameter1":"value1","parameter2":["value2a","value2b"],"parameter3":{"parameter3a":"value3a","parameter3b":3}}\'>',
                    ],
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
                        'type' => 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Tag>',
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
            Category::class => [
                Category::class,
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
