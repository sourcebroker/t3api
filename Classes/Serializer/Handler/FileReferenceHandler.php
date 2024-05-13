<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Exception\ValidationException;
use SourceBroker\T3api\Service\FileReferenceService;
use SourceBroker\T3api\Service\SerializerService;
use SourceBroker\T3api\Service\UrlService;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\FileReference as Typo3FileReference;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;

/**
 * Class FileReferenceHandler
 */
class FileReferenceHandler extends AbstractHandler implements SerializeHandlerInterface, DeserializeHandlerInterface
{
    /**
     * @var string
     */
    public const TYPE = 'FileReferenceTransport';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    public function __construct(
        protected readonly ResourceFactory $resourceFactory,
        protected readonly PersistenceManager $persistenceManager,
        protected readonly SerializerService $serializerService,
        protected readonly FileReferenceService $fileReferenceService,
        protected readonly LinkFactory $linkFactory,
        protected readonly LinkService $linkService,
        protected readonly ContentObjectRenderer $contentObjectRenderer
    ) {}

    /**
     * @param ExtbaseFileReference|Typo3FileReference $fileReference
     *
     * @return array{uid: int|null, url: string|null, file: array{uid: int, name: string, mimeType: string, size: int|null}, urlEmbed?: mixed}
     *
     * @todo Try to implement it with default JMS serialization functionality instead of using this handler
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $fileReference,
        array $type,
        SerializationContext $context
    ): ?array {
        $out = null;
        try {
            /** @var Typo3FileReference $originalResource */
            $originalResource = $fileReference instanceof ExtbaseFileReference
                ? $fileReference->getOriginalResource()
                : $fileReference;
            $out['url'] = $this->fileReferenceService->getUrlFromResource($originalResource, $context);
            $out['uid'] = $originalResource->getUid();
            $originalFile = $originalResource->getOriginalFile();
            $out['file']['uid'] = $originalFile->getUid();
            $out['file']['name'] = $originalFile->getName();
            $out['file']['mimeType'] = $originalFile->getMimeType();
            $out['file']['size'] = $originalFile->getSize();

            if (preg_match('#video/.*#', $originalFile->getMimeType())) {
                $fileRenderer = GeneralUtility::makeInstance(RendererRegistry::class)->getRenderer($originalFile);
                if ($fileRenderer !== null
                    && preg_match(
                        '/src="([^"]+)"/',
                        $fileRenderer->render($originalFile, 1, 1),
                        $match
                    )) {
                    if ($match[1] === '') {
                        $out['urlEmbed'] = '';
                    } else {
                        $out['urlEmbed'] = UrlService::forceAbsoluteUrl(
                            $match[1],
                            $context->getAttribute('TYPO3_SITE_URL')
                        );
                    }
                }
            }

        } catch (\Exception $e) {
            trigger_error(
                $e->getMessage(),
                E_USER_WARNING
            );
        }
        return $out;
    }

    /**
     * @param mixed $data
     * @return mixed|void
     * @throws ValidationException
     */
    public function deserialize(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        DeserializationContext $context
    ) {
        if ($type['name'] !== self::TYPE) {
            throw new \RuntimeException(sprintf('`%s` is unknown type.', $type['name']), 1577534783745);
        }

        if (empty($type['params']['targetType'])) {
            throw new \RuntimeException('`targetType` is required parameter.', 1577534803669);
        }

        if (!is_subclass_of($type['params']['targetType'], AbstractFileFolder::class)) {
            throw new \RuntimeException(
                sprintf('Has to be an instance of `%s` to be processed', AbstractFileFolder::class),
                1577534838461
            );
        }

        if ($data === 0) {
            $this->removeExistingFileReference($context);

            return null;
        }

        $isNew = is_array($data) && empty($data['uid']);

        if ($isNew) {
            return $this->createSysFileReference($data, $type['params']['targetType'], $context);
        }

        $uid = (int)(is_numeric($data) ? $data : $data['uid']);

        if ($uid) {
            return $this->persistenceManager->getObjectByIdentifier(
                $uid,
                ExtbaseFileReference::class,
                false
            );
        }
        return null;
    }
    /**
     * @throws ValidationException
     */
    protected function createSysFileReference(
        array $data,
        string $type,
        DeserializationContext $context
    ): ExtbaseFileReference {
        /** @var JsonDeserializationVisitor $visitor */
        $visitor = $context->getVisitor();

        if (empty($data['uidLocal'])) {
            $result = new Result();
            $result->forProperty('uidLocal')->addError(
                new Error('Property `uidLocal` is required to create sys file reference', 1577083636258)
            );

            throw new ValidationException($result, 1581461062805);
        }

        $this->removeExistingFileReference($context);

        /** @var ExtbaseFileReference $fileReference */
        $fileReference = $this->serializerService->deserialize(
            json_encode($data),
            $type,
            $this->cloneDeserializationContext($context, ['target' => null])
        );

        $fileReference->setOriginalResource(
            $this->resourceFactory->createFileReferenceObject(
                [
                    'uid_local' => $data['uidLocal'],
                    'uid' => uniqid('NEW_', true),
                ]
            )
        );

        if ($visitor->getCurrentObject() instanceof AbstractDomainObject) {
            /** @var AbstractDomainObject $currentObject */
            $currentObject = $visitor->getCurrentObject();
            $fileReference->setPid($currentObject->getPid());
            $fileReference->_setProperty('_languageUid', $currentObject->_getProperty('_languageUid'));
        }

        return $fileReference;
    }

    /**
     * Removes already existing file reference if property is not a collection but relation to single file
     */
    protected function removeExistingFileReference(DeserializationContext $context): void
    {
        /** @var JsonDeserializationVisitor $visitor */
        $visitor = $context->getVisitor();
        $propertyName = $context->getCurrentPath()[count($context->getCurrentPath()) - 1];
        $propertyValue = ObjectAccess::getProperty($visitor->getCurrentObject(), $propertyName);
        if ($propertyValue instanceof ExtbaseFileReference || $propertyValue instanceof Typo3FileReference) {
            $this->persistenceManager->remove($propertyValue);
        }
    }
}
