.. _handling_file_upload:

=======================
Handling File Upload
=======================

Creating file resource
=======================

To create uploadable resource it is needed to create ``POST`` endpoint for resource which class extends
``\TYPO3\CMS\Extbase\Domain\Model\File``.

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\Users\Domain\Model;

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "post"={
    *              "path"="/files",
    *              "method"="POST",
    *          },
    *     }
    * )
    */
   class File extends \TYPO3\CMS\Extbase\Domain\Model\File
   {
   }

There is plenty configuration options which allows you to customize upload endpoint for your needs.

- ``folder`` - destination folder (default: ``1:/user_upload/`` which means files will be uploaded into ``user_upload`` directory of file storage ID ``1``).

- ``allowedFileExtensions`` - Array of allowed file extensions (default: ``$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']``). Files with PHP extensions are always rejected.

- ``conflictMode`` - What to do if file with the same name already exists: ``rename`` (default - new file name gets a suffix, e.g. ``image_01.jpg``), ``replace`` or ``cancel`` (values of ``\TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior``).

- ``filenameMask`` - Allows to change the name of the uploaded file (default: ``[filename][extensionWithDot]``; see :ref:`how to customize name of uploaded file <handling_file_upload_customize_uploaded_file_name>`).

- ``filenameHashAlgorithm`` - (default: ``md5``; see :ref:`how to customize name of uploaded file <handling_file_upload_customize_uploaded_file_name>`).

- ``contentHashAlgorithm`` - (default: ``md5``; see :ref:`how to customize name of uploaded file <handling_file_upload_customize_uploaded_file_name>`).

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\Users\Domain\Model;

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "post"={
    *              "path"="/files",
    *              "method"="POST",
    *          },
    *     },
    *     attributes={
    *          "upload"={
    *              "folder"="1:/user_upload/",
    *              "allowedFileExtensions"={"jpg", "jpeg", "png"},
    *              "conflictMode"="rename",
    *          }
    *     }
    * )
    */
   class File extends \TYPO3\CMS\Extbase\Domain\Model\File
   {
   }

Configuring TCA
================

Field which keeps file references has to be configured as TCA field of ``type`` ``file``. TYPO3 fills
``fieldname`` and ``tablenames`` columns of ``sys_file_reference`` automatically for such fields.

.. code-block:: php

   'photo' => [
       'exclude' => true,
       'label' => 'LLL:EXT:users/Resources/Private/Language/locallang_db.xlf:tx_users_domain_model_user.photo',
       'config' => [
           'type' => 'file',
           'allowed' => 'common-image-types',
           'maxitems' => 1,
       ],
   ],

File upload request
====================

File is sent as ``multipart/form-data`` ``POST`` request to the upload endpoint. Name of the form field has to be ``originalResource``. Only one file can be uploaded in a single request - to attach multiple files upload them one by one and then :ref:`save references <handling_file_upload_save_reference>` to all of them in a single request.

.. code-block:: bash

   curl -X POST -F "originalResource=@/path/to/photo.jpg" https://example.com/_api/files

File upload response
=====================

On success the response has status ``201`` and contains created ``sys_file`` record. The most important property is ``uid`` - it is needed to create a file reference in the next request.

.. code-block:: json

   {
      "publicUrl": "fileadmin/user_upload/media-export-excluded/test1_01.jpg",
      "absolutePublicUrl": "https://14.t3api.ddev.site/fileadmin/user_upload/media-export-excluded/test1_01.jpg",
      "properties": {
         "size": 42520,
         "mime_type": "image/jpeg",
         "name": "test1_01.jpg",
         "extension": "jpg",
         "width": 720,
         "height": 449,
         "uid": 3
      },
      "name": "test1_01.jpg",
      "uid": 3,
      "identifier": "/user_upload/media-export-excluded/test1_01.jpg"
   }

(``properties`` shortened.)

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   Testing instance has upload endpoint ``/_api/news/files`` (``EXT:t3apinews`` ``File`` resource) which accepts ``jpg``, ``jpeg`` and ``png`` files and stores them in ``1:/user_upload/media-export-excluded/``:

   .. code-block:: bash

      curl -k -X POST -F "originalResource=@.test/14/public/fileadmin/user_upload/test1.jpg" https://14.t3api.ddev.site/_api/news/files

.. _handling_file_upload_save_reference:

Save reference to new file
===========================

.. important::
    It is not (yet) possible to update existing file reference within T3api request - it is possible only to create
    new reference.

.. code-block:: json

   {
      "photo": {
         "uidLocal": 15
      }
   }

``uidLocal`` is the ``uid`` of the file returned by upload endpoint. If you would like to save any other data inside file reference (e.g. ``showinpreview`` in ``EXT:news``) it is needed to use a class which extends ``TYPO3\CMS\Extbase\Domain\Model\FileReference`` and contains such properties.

.. code-block:: json

   {
      "falMedia": [
         {
            "uidLocal": 15,
            "showinpreview": 1
         },
         {
            "uidLocal": 16,
            "showinpreview": 0
         }
      ]
   }

Custom file reference class is handled in the same way as the default one - t3api supports every class which extends ``TYPO3\CMS\Extbase\Domain\Model\FileReference``. Make sure that the property type points to your class (in annotation or :ref:`YAML metadata <serialization_yaml-metadata>`), e.g. ``ObjectStorage<SourceBroker\T3apinews\Domain\Model\FileReference>`` for ``falMedia`` in testing instance.

Removing single file reference
===============================

To remove existing file reference it is needed to send value `0`. **Because of extbase and JMS serializer limitations sending `NULL` will not remove existing file reference**. "Extbase limitation" means that existing file references are not removed when persisting empty value instead of file reference object (column for property in entity is cleared but file reference is kept). "JMS serializer limitations" means  that JMS does not allow to apply custom subscribers and handlers when `NULL` is sent.

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\User\Domain\Model;

   use SourceBroker\T3api\Annotation as T3api;
   use TYPO3\CMS\Extbase\Domain\Model\FileReference;

   /**
    * @T3api\ApiResource (
    *     itemOperations={
    *          "patch"={
    *              "path"="/users/{id}",
    *              "method"="PATCH",
    *          }
    *     },
    * )
    */

   class User extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
   {
       /**
        * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
        */
       protected $avatar = null;

       public function getAvatar(): ?FileReference
       {
           return $this->avatar;
       }

       public function setAvatar(?FileReference $avatar): void
       {
           $this->avatar = $avatar;
       }
   }

To remove file from model definition above we need to send a JSON payload as follows to ``PATCH`` ``/users/X`` endpoint to remove image.

.. code-block:: json

   {
      "avatar": 0
   }

Removing collection file reference
====================================

To remove collection file reference it is needed to send array with new elements. If array is empty - all elements will be removed.

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\News\Domain\Model;

   use SourceBroker\T3api\Annotation as T3api;
   use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

   /**
    * @T3api\ApiResource (
    *     itemOperations={
    *          "patch"={
    *              "path"="/news/{id}",
    *              "method"="PATCH",
    *          },
    *     }
    * )
    */
   class News extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
   {
       /**
        * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
        */
       protected $falMedia;

       public function __construct()
       {
           $this->falMedia = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
       }

       /**
        * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
        */
       public function getFalMedia(): ObjectStorage
       {
           return $this->falMedia;
       }

       /**
        * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $falMedia
        */
       public function setFalMedia(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $falMedia): void
       {
           $this->falMedia = $falMedia;
       }

       /***
        * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $falMedia
        */
       public function addFalMedia(\TYPO3\CMS\Extbase\Domain\Model\FileReference $falMedia): void
       {
           $this->falMedia->attach($falMedia);
       }
   }

To remove files from model definition above we need to send a JSON payload as follows to ``PATCH`` ``/news/X`` endpoint to remove image.

.. code-block:: json

   {
      "falMedia": []
   }

.. _handling_file_upload_customize_uploaded_file_name:

Customizing name of uploaded file
===================================

Keeping name of the file uploaded by client sometimes may not be wanted - as developers we need to protect website against some joke or vulgar URLs which does not return 404 errors. In such cases very useful will be processing of the name of uploaded file. It is possible to achieve that using configuration option ``filenameMask``.

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\Users\Domain\Model;

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "post"={
    *              "path"="/files",
    *              "method"="POST",
    *          },
    *     },
    *     attributes={
    *          "upload"={
    *              "folder"="1:/user_upload/",
    *              "allowedFileExtensions"={"jpg", "jpeg", "png"},
    *              "conflictMode"="rename",
    *              "filenameMask"="static-prefix-[filenameHash][extensionWithDot]",
    *          }
    *     }
    * )
    */
   class File extends \TYPO3\CMS\Extbase\Domain\Model\File
   {
   }

``filenameMask`` supports few "magic" strings:

- ``[filename]`` - File name without extension.
- ``[extension]`` - Extension.
- ``[extensionWithDot]`` - Extension prefixed by dot.
- ``[contentHash]`` - Hash generated from file content.
- ``[filenameHash]`` - Hash generated from file name.

It is possible to customize hash algorithm used to generate ``contentHash`` and ``filenameHash`` strings. By default ``md5`` is used, but inside ``contentHashAlgorithm`` and ``filenameHashAlgorithm`` settings you can easily change it to any hash method supported by PHP `hash <https://www.php.net/manual/en/function.hash.php>`_ method.

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\Users\Domain\Model;

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "post"={
    *              "path"="/files",
    *              "method"="POST",
    *          },
    *     },
    *     attributes={
    *          "upload"={
    *              "folder"="1:/user_upload/",
    *              "allowedFileExtensions"={"jpg", "jpeg", "png"},
    *              "conflictMode"="rename",
    *              "filenameMask"="static-prefix-[filenameHash]-[contentHash][extensionWithDot]",
    *              "contentHashAlgorithm"="sha1",
    *              "filenameHashAlgorithm"="sha1",
    *          }
    *     }
    * )
    */
   class File extends \TYPO3\CMS\Extbase\Domain\Model\File
   {
   }
