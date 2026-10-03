<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Serializer\Handler\TypolinkHandler;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TypolinkHandlerTest extends UnitTestCase
{
    public static function emptyTypolinkParameterDataProvider(): array
    {
        return [
            'empty string' => [''],
            'null' => [null],
            'zero' => [0],
        ];
    }

    #[Test]
    #[DataProvider('emptyTypolinkParameterDataProvider')]
    public function returnsEmptyStringForEmptyTypolinkParameter(mixed $typolinkParameter): void
    {
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $contentObjectRenderer->expects(self::never())->method('typoLink_URL');

        self::assertSame('', $this->serialize($contentObjectRenderer, $typolinkParameter));
    }

    #[Test]
    public function returnsEmptyStringWhenLinkCannotBeResolved(): void
    {
        $contentObjectRenderer = self::createStub(ContentObjectRenderer::class);
        $contentObjectRenderer->method('typoLink_URL')->willReturn('');

        self::assertSame('', $this->serialize($contentObjectRenderer, 't3://page?uid=999'));
    }

    #[Test]
    public function returnsAbsoluteUrlForRelativeLink(): void
    {
        $contentObjectRenderer = self::createStub(ContentObjectRenderer::class);
        $contentObjectRenderer->method('typoLink_URL')->willReturn('/some-page');

        self::assertSame('https://example.com/some-page', $this->serialize($contentObjectRenderer, 't3://page?uid=1'));
    }

    private function serialize(ContentObjectRenderer $contentObjectRenderer, mixed $typolinkParameter): string
    {
        $context = SerializationContext::create();
        $context->setAttribute('TYPO3_SITE_URL', 'https://example.com/');

        return (new TypolinkHandler($contentObjectRenderer))->serialize(
            self::createStub(SerializationVisitorInterface::class),
            $typolinkParameter,
            [],
            $context
        );
    }
}
