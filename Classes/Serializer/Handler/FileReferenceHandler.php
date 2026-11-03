<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Exception\BadRequestException;
use SourceBroker\T3api\Exception\ValidationException;
use SourceBroker\T3api\Serializer\Subscriber\FileTokenSubscriber;
use SourceBroker\T3api\Service\FileReferenceService;
use SourceBroker\T3api\Service\FileTokenServiceTrait;
use SourceBroker\T3api\Service\SerializerService;
use SourceBroker\T3api\Service\UrlService;
use TYPO3\CMS\Core\Resource\FileReference as Typo3FileReference;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\File;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Domain\Model\Folder;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FileReferenceHandler extends AbstractHandler implements SerializeHandlerInterface, DeserializeHandlerInterface
{
    use InlineRelationServiceTrait;
    use FileTokenServiceTrait;

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
            $fileToken = $this->getFileTokenService()
                ->getTokenForResponse((int)$originalFile->getUid(), FileTokenSubscriber::isEnabledForAllFiles($context));
            if ($fileToken !== null) {
                $out['file']['fileToken'] = $fileToken;
            }

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

        if (
            !is_subclass_of($type['params']['targetType'], Folder::class)
            && !is_a($type['params']['targetType'], File::class, true)
            && !is_subclass_of($type['params']['targetType'], FileReference::class)
        ) {
            throw new \RuntimeException(
                sprintf(
                    'Has to be an instance of %s to be processed',
                    implode(',', [
                        Folder::class,
                        File::class,
                        FileReference::class,
                    ])
                ),
                1577534838461
            );
        }

        // `0` (also as string) removes the reference of a single file property
        if (is_numeric($data) && (int)$data === 0) {
            $this->removeExistingFileReference($context);

            return null;
        }

        // Also Extbase's `File` itself - it is mapped to `sys_file` as well
        if (is_a($type['params']['targetType'], File::class, true)) {
            return $this->deserializeFile($data, $type['params']['targetType'], $context);
        }

        if (!is_numeric($data) && !is_array($data)) {
            throw $this->createInvalidRelationException($data, $context, 1793700006);
        }

        $isNew = is_array($data) && empty($data['uid']);

        if ($isNew) {
            // Checked here, not in `createSysFileReference()`, so the check is kept when the method is overridden
            if (!empty($data['uidLocal'])) {
                $this->assertFileToken((int)$data['uidLocal'], $data['fileToken'] ?? null);
            }

            return $this->createSysFileReference($data, $type['params']['targetType'], $context);
        }

        $uid = is_numeric($data) ? (int)$data : (is_numeric($data['uid']) ? (int)$data['uid'] : 0);

        if ($uid > 0) {
            $fileReference = $this->persistenceManager->getObjectByIdentifier(
                $uid,
                ExtbaseFileReference::class,
                false
            );
            // @todo `Folder` properties are looked up as file references as well - kept unchanged here
            if (!is_subclass_of($type['params']['targetType'], FileReference::class)) {
                return $fileReference;
            }
            if (!$fileReference instanceof AbstractDomainObject) {
                throw new BadRequestException(
                    'relation_not_found',
                    [(string)$uid, implode('.', $context->getCurrentPath())],
                    1793670002
                );
            }
            $this->getInlineRelationService()->assertAssignable($fileReference, $context, 1793660002);

            return $fileReference;
        }

        throw $this->createInvalidRelationException($data, $context, 1793700007);
    }

    /**
     * A property with a file (`sys_file`, not a reference) keeps its current file(s); another file can be set only
     * with the token returned for it by the API: `{"uid": 15, "fileToken": "..."}`.
     *
     * @throws ValidationException
     * @throws BadRequestException
     */
    protected function deserializeFile(mixed $data, string $targetType, DeserializationContext $context): File
    {
        $fileUid = match (true) {
            is_numeric($data) => (int)$data,
            is_array($data) && is_numeric($data['uid'] ?? null) => (int)$data['uid'],
            default => 0,
        };
        if ($fileUid <= 0) {
            throw $this->createInvalidRelationException($data, $context, 1793700005);
        }

        if (!in_array($fileUid, $this->getCurrentFileUids($context), true)) {
            $this->assertFileToken($fileUid, is_array($data) ? ($data['fileToken'] ?? null) : null);
        }

        $file = $this->persistenceManager->getObjectByIdentifier($fileUid, $targetType, false);
        if (!$file instanceof File) {
            throw new BadRequestException(
                'relation_not_found',
                [(string)$fileUid, implode('.', $context->getCurrentPath())],
                1793700004
            );
        }

        return $file;
    }

    /**
     * UIDs of the files of the deserialized property as loaded from the database.
     *
     * @return int[]
     */
    protected function getCurrentFileUids(DeserializationContext $context): array
    {
        $parent = $this->getInlineRelationService()->getContextParent($context);
        $metadataStack = $context->getMetadataStack();
        $propertyMetadata = $metadataStack->isEmpty() ? null : $metadataStack->top();

        return $parent !== null && $propertyMetadata instanceof PropertyMetadata
            ? $this->getInlineRelationService()->getCleanRelationUids($parent, $propertyMetadata->name)
            : [];
    }

    protected function createInvalidRelationException(mixed $data, DeserializationContext $context, int $code): BadRequestException
    {
        return new BadRequestException(
            'invalid_relation',
            [gettype($data), implode('.', $context->getCurrentPath())],
            $code
        );
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
            $fileReference->setPid($currentObject->getPid() ?? 0);
            $fileReference->_setProperty('_languageUid', $currentObject->_getProperty('_languageUid'));
        }

        return $fileReference;
    }

    /**
     * A new reference requires the token returned for the file by the API (upload response, operation offering files
     * with `fileTokens`).
     *
     * @throws ValidationException
     */
    protected function assertFileToken(int $fileUid, mixed $fileToken): void
    {
        if (is_string($fileToken) && $fileToken !== '' && $this->getFileTokenService()->isValidToken($fileToken, $fileUid)) {
            return;
        }

        $result = new Result();
        $result->forProperty('fileToken')->addError(
            new Error('Property `fileToken` is missing or invalid - use the token returned for the file by the API', 1793700002)
        );

        throw new ValidationException($result, 1793700003);
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
