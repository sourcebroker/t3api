<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Service\InlineRelationService;
use T3apiTests\FunctionalTest\Domain\Model\Recipe;
use T3apiTests\FunctionalTest\Domain\Model\RecipeStep;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Translated records: Extbase writes the localized UID of a translated parent when attaching a record to a
 * collection (a record stored with another value would be moved) and updates the row of the translation of a
 * translated record.
 */
class InlineRelationServiceTest extends FunctionalTestCase
{
    private const STEP_TABLE = 'tx_functionaltest_domain_model_recipestep';

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/t3api',
        'typo3conf/ext/t3api/Tests/Functional/Fixtures/Extensions/t3api_functional_test',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $steps = $this->getConnectionPool()->getConnectionForTable(self::STEP_TABLE);
        foreach ([10 => 1, 11 => 5, 12 => 5, 13 => 7, 14 => 0, 15 => 7] as $uid => $recipe) {
            $steps->insert(self::STEP_TABLE, ['uid' => $uid, 'pid' => 0, 'title' => 'Step ' . $uid, 'recipe' => $recipe]);
        }
    }

    #[Test]
    #[DataProvider('translatedParentProvider')]
    public function recordOfCollectionOfTranslatedParentIsAssignable(int $recordUid, int $recordLocalizedUid, bool $expected): void
    {
        $recipe = $this->createDomainObject(Recipe::class, 1, 5);
        $step = $this->createDomainObject(RecipeStep::class, $recordUid, $recordLocalizedUid);

        self::assertSame($expected, $this->get(InlineRelationService::class)->isAssignable($recipe, 'steps', $step));
    }

    public static function translatedParentProvider(): array
    {
        return [
            'stored with localized UID of parent' => [12, 12, true],
            'translation stored with localized UID of parent' => [10, 11, true],
            'stored with default language UID of parent, would be moved' => [10, 10, false],
            'stored with other parent' => [13, 13, false],
            'without parent' => [14, 14, false],
            'default language record of parent, translation of other parent' => [10, 15, false],
        ];
    }

    #[Test]
    public function recordIsNotAssignableToNewParent(): void
    {
        $step = $this->createDomainObject(RecipeStep::class, 14, 14);

        self::assertFalse($this->get(InlineRelationService::class)->isAssignable(new Recipe(), 'steps', $step));
    }

    #[Test]
    public function onlyRelationsStoredInRelatedTableAreDetected(): void
    {
        $service = $this->get(InlineRelationService::class);
        $recipe = new Recipe();

        self::assertTrue($service->isStoredInRelatedTable($recipe, 'steps'));
        self::assertTrue($service->isStoredInRelatedTable($recipe, 'images'));
        self::assertFalse($service->isStoredInRelatedTable($recipe, 'title'));
    }

    /**
     * @template T of AbstractDomainObject
     * @param class-string<T> $className
     * @return T
     */
    private function createDomainObject(string $className, int $uid, int $localizedUid): AbstractDomainObject
    {
        $object = new $className();
        $object->_setProperty(AbstractDomainObject::PROPERTY_UID, $uid);
        $object->_setProperty(AbstractDomainObject::PROPERTY_LOCALIZED_UID, $localizedUid);

        return $object;
    }
}
