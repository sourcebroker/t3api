<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use SourceBroker\T3api\Domain\Model\ApiFilter;

/**
 * Test case.
 */
class ApiFilterTest extends UnitTestCase
{
    /**
     * @var \SourceBroker\T3api\Domain\Model\ApiFilter
     */
    protected $subject;

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
    public function getArgumentReturnsInitialValueForAccessingSingleItem()
    {
        self::assertSame(
            'argumentValue',
            $this->subject->getArgument('argument')
        );
    }
}
