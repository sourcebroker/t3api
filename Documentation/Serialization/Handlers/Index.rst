.. _serialization_handlers:

=========
Handlers
=========

Serializer handlers take over (de)serialization of a value of given type. T3api delivers handlers for things which are common in TYPO3 but which plain JSON serialization can not handle well - files, processed images, links to records and typolinks.

Some handlers are used automatically (based on the class of the value), others are activated by setting the ``type`` of a property or virtual property - with annotation from ``\SourceBroker\T3api\Annotation\Serializer\Type`` namespace or with ``type`` key in :ref:`YAML metadata <serialization_yaml-metadata>`.

.. list-table::
   :header-rows: 1
   :widths: 25 35 40

   * - Handler
     - Activated by
     - Output
   * - ``FileReferenceHandler``
     - automatically for classes extending Extbase's ``FileReference``, ``File`` or ``Folder`` nested in an entity
     - object with ``url``, ``uid`` and ``file`` data
   * - ``ImageHandler``
     - ``@T3api\Serializer\Type\Image``, type ``Image<...>``
     - absolute URL of processed image
   * - ``RecordUriHandler``
     - ``@T3api\Serializer\Type\RecordUri``, type ``RecordUri<...>``
     - absolute URL of record (e.g. news detail page)
   * - ``TypolinkHandler``
     - ``@T3api\Serializer\Type\Typolink``, type ``Typolink``
     - absolute URL built from typolink value
   * - ``RteHandler``
     - ``@T3api\Serializer\Type\Rte``, type ``Rte``
     - HTML processed by ``lib.parseFunc_RTE``
   * - ``PasswordHashHandler``
     - ``@T3api\Serializer\Type\PasswordHash``, type ``PasswordHash``
     - on deserialization: hashed password
   * - ``CurrentFeUserHandler``
     - ``@T3api\Serializer\Type\CurrentFeUser``
     - see :ref:`use-cases_current-user-assignment`

.. _serialization_handlers_file-reference:

FileReference
===============

Objects of classes extending Extbase's ``\TYPO3\CMS\Extbase\Domain\Model\FileReference``, ``File`` or ``Folder`` (e.g. ``\GeorgRinger\News\Domain\Model\FileReference`` or your own file reference class) nested in an entity are serialized automatically, there is nothing to configure. Example of ``falMedia`` property of news:

.. code-block:: json

   "falMedia": [
       {
           "url": "https://14.t3api.ddev.site/fileadmin/user_upload/test1.jpg",
           "uid": 4,
           "file": {
               "uid": 1,
               "name": "test1.jpg",
               "mimeType": "image/jpeg",
               "size": 42520
           }
       }
   ]

- ``url`` - absolute URL of the original file.
- ``uid`` - uid of the file reference (``sys_file_reference``).
- ``file`` - data of the original file (``sys_file``).
- ``urlEmbed`` - only for videos (e.g. YouTube, Vimeo) - URL which can be used in ``<iframe>``.

The structure of the output is fixed - other properties of the file reference (``title``, ``alternative``, ``link``...) are **not** added to it, even if they are configured in serialization metadata. If you need them in the response, expose them with a virtual property of the parent entity or with a :ref:`custom handler <serialization_handlers_custom>`.

The same handler is used for deserialization - see :ref:`handling_file_upload` how to attach files in ``POST`` / ``PATCH`` payload.

.. _serialization_handlers_image:

Image
===============

Returns absolute URL of an image processed by TYPO3 (resized / cropped) - so frontend does not need to load original, possibly huge, files. It can be used on a property or a method returning ``FileReference``, ``uid`` of ``sys_file_reference``, or a list (e.g. ``ObjectStorage``) of them - then a list of URLs is returned.

Parameters (all optional; values are the same as in ``f:image`` ViewHelper, so ``c`` and ``m`` suffixes work):

- ``width``
- ``height``
- ``maxWidth``
- ``maxHeight``
- ``cropVariant`` - name of the crop variant (default ``default``).

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;

   class News extends \GeorgRinger\News\Domain\Model\News
   {
       /**
        * @T3api\Serializer\VirtualProperty("imageThumbnail")
        * @T3api\Serializer\Type\Image(width="380", height="250c")
        */
       public function getImageThumbnail(): ?FileReference
       {
           return $this->getFalMedia()->count() ? $this->getFalMedia()->toArray()[0] : null;
       }
   }

The same in YAML metadata (parameters are positional: ``width``, ``height``, ``maxWidth``, ``maxHeight``, ``cropVariant``). This is how ``imageThumbnail`` and ``imageLarge`` are configured in ``EXT:t3apinews``:

.. code-block:: yaml

   SourceBroker\T3apinews\Domain\Model\News:
     virtual_properties:
       getImageThumbnail:
         serialized_name: imageThumbnail
         groups: [api_get_collection_t3apinews_news, api_get_item_t3apinews_news]
         type: "Image<'380','250c','','',''>"
       getImageLarge:
         serialized_name: imageLarge
         groups: [api_get_collection_t3apinews_news, api_get_item_t3apinews_news]
         type: "Image<'1280','768','','',''>"

.. _serialization_handlers_record-uri:

RecordUri
===========

Returns absolute URL to the record, built with TYPO3 record link handler (``t3://record?identifier=...&uid=...``). The only parameter is the ``identifier`` of the link handler configured in ``config.recordLinks`` TypoScript. The value of the property itself is not used - ``uid`` of the serialized entity is taken, so it is usually used on a virtual property.

.. code-block:: typoscript

   config.recordLinks.tx_news {
       typolink {
           parameter = {$plugin.tx_t3apinews.pageUid.newsDetails}
           additionalParams.data = field:uid
           additionalParams.wrap = &tx_news_pi1[controller]=News&tx_news_pi1[action]=detail&tx_news_pi1[news]=|
       }
   }

.. code-block:: php

   /**
    * @T3api\Serializer\VirtualProperty("singleUri")
    * @T3api\Serializer\Type\RecordUri("tx_news")
    */
   public function getSingleUri(): string
   {
       return '';
   }

.. code-block:: yaml

   SourceBroker\T3apinews\Domain\Model\News:
     virtual_properties:
       getSingleUri:
         serialized_name: singleUri
         groups: [api_get_collection_t3apinews_news, api_get_item_t3apinews_news]
         type: "RecordUri<'tx_news'>"

If the URL can not be resolved an empty string is returned.

.. note::

   JMS serializer skips virtual properties returning ``null`` before the handler is called, so the method used for ``RecordUri`` has to return a non-null value (e.g. empty string).

.. _serialization_handlers_typolink:

Typolink
=========

Converts value of a typolink field (e.g. ``t3://page?uid=5``, ``https://example.com``, ``t3://file?uid=1``, or legacy ``5 _blank``) into an absolute URL. If the value is empty or the link can not be resolved (e.g. target page is hidden or deleted), an empty string is returned.

.. code-block:: php

   /**
    * @T3api\Serializer\Type\Typolink
    */
   protected string $link = '';

.. code-block:: yaml

   GeorgRinger\News\Domain\Model\FileReference:
     properties:
       link:
         type: Typolink

.. _serialization_handlers_rte:

Rte
=========

Processes the value with ``lib.parseFunc_RTE`` (as ``f:format.html`` does), so ``t3://`` links inside RTE content are converted into real URLs.

.. code-block:: php

   /**
    * @T3api\Serializer\Type\Rte
    */
   protected string $bodytext = '';

.. _serialization_handlers_password-hash:

PasswordHash
=============

Used in deserialization only - plain password sent in the payload is hashed with default frontend hashing method before it is set on the entity. Useful for registration endpoints.

.. code-block:: php

   /**
    * @T3api\Serializer\Type\PasswordHash
    */
   protected $password = '';

.. warning::

   Serialization returns the value as is (the hash). Never add the password property to groups used in ``normalizationContext``.

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | Single news - see ``falMedia`` (FileReference), ``imageThumbnail`` / ``imageLarge`` (Image) and ``singleUri`` (RecordUri):
     | `https://14.t3api.ddev.site/_api/news/news/1 <https://14.t3api.ddev.site/_api/news/news/1>`__
     |

.. _serialization_handlers_custom:

Custom handlers
================

Custom handler is a class implementing JMS ``\JMS\Serializer\Handler\SubscribingHandlerInterface``. The easiest way is to extend ``\SourceBroker\T3api\Serializer\Handler\AbstractHandler`` and implement ``SerializeHandlerInterface`` and/or ``DeserializeHandlerInterface`` - then only the list of supported types is needed.

.. code-block:: php

   <?php

   namespace Vendor\Extension\Serializer\Handler;

   use JMS\Serializer\SerializationContext;
   use JMS\Serializer\Visitor\SerializationVisitorInterface;
   use SourceBroker\T3api\Serializer\Handler\AbstractHandler;
   use SourceBroker\T3api\Serializer\Handler\SerializeHandlerInterface;

   class PriceHandler extends AbstractHandler implements SerializeHandlerInterface
   {
       public const TYPE = 'Price';

       protected static $supportedTypes = [self::TYPE];

       public function serialize(
           SerializationVisitorInterface $visitor,
           $value,
           array $type,
           SerializationContext $context
       ): string {
           $currency = $type['params'][0] ?? 'EUR';

           return number_format((int)$value / 100, 2, '.', '') . ' ' . $currency;
       }
   }

Register the handler in ``ext_localconf.php``. Handlers are created with ``GeneralUtility::makeInstance()``, so constructor dependency injection works if the class is public in ``Services.yaml``.

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerHandlers'][]
       = \Vendor\Extension\Serializer\Handler\PriceHandler::class;

And use the type in YAML metadata:

.. code-block:: yaml

   Vendor\Extension\Domain\Model\Product:
     properties:
       priceInCents:
         serialized_name: price
         type: "Price<'USD'>"

To use the type as annotation, create a class implementing ``\SourceBroker\T3api\Annotation\Serializer\Type\TypeInterface`` (see ``\SourceBroker\T3api\Annotation\Serializer\Type\Image`` as an example) - ``getName()`` returns the type name and ``getParams()`` the list of parameters.

.. note::

   Flush system caches after registering a new handler or changing types - serializer metadata is cached.
