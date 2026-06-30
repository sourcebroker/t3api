<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ApiFilterTest extends UnitTestCase
{
    protected ApiFilter $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new ApiFilter('filterClass', 'property', 'strategy', ['argument' => 'argumentValue']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    #[Test]
    public function getFilterClassReturnsInitialValueForString()
    {
        self::assertSame(
            'filterClass',
            $this->subject->getFilterClass()
        );
    }

    /**
     * @test
     */
    #[Test]
    public function getStrategyReturnsInitialValueForApiFilterStrategy()
    {
        self::assertSame(
            'strategy',
            $this->subject->getStrategy()->getName()
        );
    }

    /**
     * @test
     */
    #[Test]
    public function getPropertyReturnsInitialValueForString()
    {
        self::assertSame(
            'property',
            $this->subject->getProperty()
        );
    }

    /**
     * @test
     */
    #[Test]
    public function getArgumentsReturnsInitialValueForArray()
    {
        self::assertSame(
            ['argument' => 'argumentValue'],
            $this->subject->getArguments()
        );
    }

    /**
     * @test
     */
    #[Test]
    public function getArgumentReturnsInitialValueForAccessingSingleItem()
    {
        self::assertSame(
            'argumentValue',
            $this->subject->getArgument('argument')
        );
    }
}
