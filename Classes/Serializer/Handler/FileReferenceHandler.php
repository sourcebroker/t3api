<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use RuntimeException;
use SourceBroker\T3api\Exception\ValidationException;
use SourceBroker\T3api\Service\SerializerService;
use TYPO3\CMS\Core\Resource\FileReference as Typo3FileReference;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class FileReferenceHandler
 */
class FileReferenceHandler extends AbstractHandler implements SerializeHandlerInterface, DeserializeHandlerInterface
{
    public const TYPE = 'FileReferenceTransport';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var SerializerService
     */
    protected $serializerService;

    /**
     * @param ResourceFactory $resourceFactory
     */
    public function injectResourceFactory(ResourceFactory $resourceFactory): void
    {
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * @param ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param SerializerService $serializerService
     */
    public function injectSerializerService(SerializerService $serializerService): void
    {
        $this->serializerService = $serializerService;
    }

    /**
     * @param SerializationVisitorInterface $visitor
     * @param ExtbaseFileReference|Typo3FileReference $fileReference
     * @param array $type
     * @param SerializationContext $context
     *
     * @return array
     *
     * @todo Try to implement it with default JMS serialization functionality instead of using this handler
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $fileReference,
        array $type,
        SerializationContext $context
    ): array {
        /** @var Typo3FileReference $originalResource */
        $originalResource = $fileReference instanceof ExtbaseFileReference
            ? $fileReference->getOriginalResource()
            : $fileReference->getPublicUrl();
        $originalFile = $originalResource->getOriginalFile();

        $url = $originalResource->getPublicUrl();
        if (parse_url($url, PHP_URL_SCHEME) === null) {
            $url = $context->getAttribute('TYPO3_SITE_URL') . $url;
        }

        $out = [
            'uid' => $fileReference->getUid(),
            'url' => $url,
            'file' => [
                'uid' => $originalFile->getUid(),
                'name' => $originalFile->getName(),
                'mimeType' => $originalFile->getMimeType(),
                'size' => $originalFile->getSize(),
            ],
        ];

        // TODO: move to some signal/slot
        if (preg_match('#video/.*#', $originalFile->getMimeType())) {
            $fileRenderer = RendererRegistry::getInstance()->getRenderer($originalFile);
            if ($fileRenderer !== null && preg_match(
                    '/src="([^"]+)"/',
                    $fileRenderer->render($originalFile, 1, 1),
                    $match
                )) {
                $urlEmbed = $match[1];
                if (parse_url($urlEmbed, PHP_URL_SCHEME) === null) {
                    $urlEmbed = $context->getAttribute('TYPO3_SITE_URL') . $urlEmbed;
                }
                $out['urlEmbed'] = $urlEmbed;
            }
        }
        return $out;
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param mixed $data
     * @param array $type
     * @param DeserializationContext $context
     * @throws ValidationException
     * @return mixed|void
     */
    public function deserialize(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        DeserializationContext $context
    ) {
        if ($type['name'] !== self::TYPE) {
            throw new RuntimeException(sprintf('`%s` is unknown type.', $type['name']), 1577534783745);
        }

        if (empty($type['params']['targetType'])) {
            throw new RuntimeException('`targetType` is required parameter.', 1577534803669);
        }

        if (!is_subclass_of($type['params']['targetType'], AbstractFileFolder::class)) {
            throw new RuntimeException(
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
            /** @var ExtbaseFileReference $fileReference */
            return $this->persistenceManager->getObjectByIdentifier(
                $uid,
                ExtbaseFileReference::class,
                false
            );
        }
    }

    /**
     * @param array $data
     * @param string $type
     * @param DeserializationContext $context
     * @throws ValidationException
     * @return ExtbaseFileReference
     */
    protected function createSysFileReference(
        array $data,
        string $type,
        DeserializationContext $context
    ): ExtbaseFileReference {
        if (empty($data['uidLocal'])) {
            $result = new Result();
            $result->forProperty('uidLocal')->addError(
                new Error('Property `uidLocal` is required to create sys file reference', 1577083636258)
            );

            throw new ValidationException($result, 1581461062805);
        }

        $this->removeExistingFileReference($context);

        // @todo change it to `$context->getNavigator()->accept()` instead of using separate serializer to keep the context and improve performance
        $deserializationContext = DeserializationContext::create();
        if ($context->getAttribute('groups')) {
            $deserializationContext->setGroups($context->getAttribute('groups'));
        }
        $fileReference = $this->serializerService->deserialize(json_encode($data, JSON_THROW_ON_ERROR), $type, $deserializationContext);

        $fileReference->setOriginalResource(
            $this->resourceFactory->createFileReferenceObject(
                [
                    'uid_local' => $data['uidLocal'],
                    'uid' => uniqid('NEW_', true),
                ]
            )
        );

        return $fileReference;
    }

    /**
     * Removes already existing file reference if property is not a collection but relation to single file
     *
     * @param DeserializationContext $context
     */
    protected function removeExistingFileReference(DeserializationContext $context): void
    {
        $propertyName = $context->getCurrentPath()[count($context->getCurrentPath()) - 1];
        $propertyValue = ObjectAccess::getProperty($context->getVisitor()->getCurrentObject(), $propertyName);
        if ($propertyValue instanceof ExtbaseFileReference || $propertyValue instanceof Typo3FileReference) {
            $this->persistenceManager->remove($propertyValue);
        }
    }
}
