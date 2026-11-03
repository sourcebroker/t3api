<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Domain\Model\UploadSettings;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class UploadSettingsTest extends UnitTestCase
{
    private mixed $imageFileExtensionsBackup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imageFileExtensionsBackup = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] ?? null;
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = 'gif,JPG,png,svg';
    }

    protected function tearDown(): void
    {
        if ($this->imageFileExtensionsBackup === null) {
            unset($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
        } else {
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = $this->imageFileExtensionsBackup;
        }
        parent::tearDown();
    }

    #[Test]
    public function createWithoutAttributesOrBaseAllowsImageFileExtensions(): void
    {
        self::assertSame(['gif', 'jpg', 'png', 'svg'], UploadSettings::create()->getAllowedFileExtensions());
    }

    #[Test]
    public function createAppliesGivenAllowedFileExtensions(): void
    {
        $settings = UploadSettings::create(['allowedFileExtensions' => ['pdf']]);

        self::assertSame(['pdf'], $settings->getAllowedFileExtensions());
    }

    /**
     * Mirrors an operation without its own `upload` attributes - the extensions set on the
     * resource apply to the operation.
     */
    #[Test]
    public function createInheritsAllowedFileExtensionsFromBase(): void
    {
        $base = UploadSettings::create(['allowedFileExtensions' => ['pdf']]);

        $settings = UploadSettings::create([], $base);

        self::assertSame(['pdf'], $settings->getAllowedFileExtensions());
    }

    /**
     * Mirrors an operation changing another upload setting only - the extensions set on the
     * resource still apply to the operation.
     */
    #[Test]
    public function createInheritsAllowedFileExtensionsFromBaseWhenOtherAttributesAreGiven(): void
    {
        $base = UploadSettings::create(['allowedFileExtensions' => ['pdf']]);

        $settings = UploadSettings::create(['folder' => '1:/documents/'], $base);

        self::assertSame(['pdf'], $settings->getAllowedFileExtensions());
        self::assertSame('1:/documents/', $settings->getFolder());
    }

    #[Test]
    public function createInheritsEmptyAllowedFileExtensionsFromBase(): void
    {
        $base = UploadSettings::create(['allowedFileExtensions' => []]);

        $settings = UploadSettings::create([], $base);

        self::assertSame([], $settings->getAllowedFileExtensions());
    }

    #[Test]
    public function createLowercasesAllowedFileExtensions(): void
    {
        $base = UploadSettings::create(['allowedFileExtensions' => ['JPG', 'Png']]);

        self::assertSame(['jpg', 'png'], $base->getAllowedFileExtensions());
        self::assertSame(['pdf'], UploadSettings::create(['allowedFileExtensions' => ['PDF']], $base)->getAllowedFileExtensions());
    }

    #[Test]
    public function createOverridesAllowedFileExtensionsOfBaseWhenGiven(): void
    {
        $base = UploadSettings::create(['allowedFileExtensions' => ['pdf']]);

        $settings = UploadSettings::create(['allowedFileExtensions' => ['jpg']], $base);

        self::assertSame(['jpg'], $settings->getAllowedFileExtensions());
    }
}
