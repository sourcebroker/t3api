<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Serializer\Handler\RecordUriHandler;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class RecordUriHandlerTest extends UnitTestCase
{
    #[Test]
    public function buildsRecordTypolinkParameterFromHandlerParamAndEntityUid(): void
    {
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $contentObjectRenderer->expects(self::once())
            ->method('typoLink_URL')
            ->with(['parameter' => 't3://record?identifier=tx_news&uid=42'])
            ->willReturn('/news/42');

        $this->serialize($contentObjectRenderer, $this->createEntity(42));
    }

    #[Test]
    public function returnsEmptyStringWhenLinkCannotBeResolved(): void
    {
        $contentObjectRenderer = self::createStub(ContentObjectRenderer::class);
        $contentObjectRenderer->method('typoLink_URL')->willReturn('');

        self::assertSame('', $this->serialize($contentObjectRenderer, $this->createEntity(999)));
    }

    #[Test]
    public function returnsAbsoluteUrlForRelativeLink(): void
    {
        $contentObjectRenderer = self::createStub(ContentObjectRenderer::class);
        $contentObjectRenderer->method('typoLink_URL')->willReturn('/news/42');

        self::assertSame('https://example.com/news/42', $this->serialize($contentObjectRenderer, $this->createEntity(42)));
    }

    #[Test]
    public function throwsExceptionWhenObjectIsNotDomainObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1562229270419);

        $this->serialize(self::createStub(ContentObjectRenderer::class), new \stdClass());
    }

    private function createEntity(int $uid): AbstractEntity
    {
        $entity = new class () extends AbstractEntity {};
        $entity->_setProperty('uid', $uid);

        return $entity;
    }

    private function serialize(ContentObjectRenderer $contentObjectRenderer, object $object): string
    {
        $context = self::createStub(SerializationContext::class);
        $context->method('getObject')->willReturn($object);
        $context->method('getAttribute')->willReturnMap([['TYPO3_SITE_URL', 'https://example.com/']]);

        return (new RecordUriHandler($contentObjectRenderer))->serialize(
            self::createStub(SerializationVisitorInterface::class),
            null,
            ['name' => RecordUriHandler::TYPE, 'params' => ['tx_news']],
            $context
        );
    }
}
