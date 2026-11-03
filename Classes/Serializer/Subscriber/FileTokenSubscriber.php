<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use SourceBroker\T3api\Service\FileTokenService;
use TYPO3\CMS\Core\Resource\File as CoreFile;
use TYPO3\CMS\Extbase\Domain\Model\File as ExtbaseFile;

/**
 * Adds `fileToken` (required to reference the file by `uidLocal`) to a file granted in the current request (e.g. an
 * uploaded file) and to every file in the response of an operation with `"fileTokens"=true` in its
 * `normalizationContext` (e.g. files offered for selection). Nested files are serialized by `FileReferenceHandler`,
 * which adds the token itself.
 */
class FileTokenSubscriber implements EventSubscriberInterface
{
    public const CONTEXT_ATTRIBUTE = 'fileTokens';

    public function __construct(private readonly FileTokenService $fileTokenService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        $tokensForAllFiles = self::isEnabledForAllFiles($event->getContext());
        // Called for every serialized object - return early for responses without files to tokenize
        if (!$tokensForAllFiles && !$this->fileTokenService->hasGrantedFiles()) {
            return;
        }

        $file = $event->getObject();
        if ($file instanceof CoreFile && $this->isOriginalResourceOfExtbaseFile($event)) {
            return;
        }

        $fileUid = match (true) {
            $file instanceof CoreFile => $file->getUid(),
            $file instanceof ExtbaseFile => (int)$file->getUid(),
            default => 0,
        };
        $token = $this->fileTokenService->getTokenForResponse($fileUid, $tokensForAllFiles);
        if ($token === null) {
            return;
        }

        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();
        $visitor->visitProperty(new StaticPropertyMetadata(get_class($file), 'fileToken', $token), $token);
    }

    /**
     * The token is added to the Extbase file only, not repeated in its `originalResource`.
     */
    protected function isOriginalResourceOfExtbaseFile(ObjectEvent $event): bool
    {
        $metadataStack = $event->getContext()->getMetadataStack();
        $propertyMetadata = $metadataStack->isEmpty() ? null : $metadataStack->top();

        // Declared in `File` since TYPO3 13, in its parent class `AbstractFileFolder` before
        return $propertyMetadata instanceof PropertyMetadata
            && $propertyMetadata->name === 'originalResource'
            && (
                is_a($propertyMetadata->class, ExtbaseFile::class, true)
                || is_a(ExtbaseFile::class, $propertyMetadata->class, true)
            );
    }

    public static function isEnabledForAllFiles(Context $context): bool
    {
        return $context->hasAttribute(self::CONTEXT_ATTRIBUTE) && (bool)$context->getAttribute(self::CONTEXT_ATTRIBUTE);
    }
}
