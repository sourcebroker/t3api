.. _serialization_expression-language:

====================
Expression language
====================

JMS serializer, which is used by t3api, supports Symfony expression language in serialization metadata. Expressions can be used to:

- compute a virtual property (``exp`` key of ``virtual_properties`` in :ref:`YAML metadata <serialization_yaml-metadata>`),
- exclude or expose a property conditionally (``exclude_if`` / ``expose_if`` in YAML, or t3api annotation ``@T3api\Serializer\Exclude(if="...")``),
- exclude a whole class conditionally (``exclude_if`` on class level in YAML).

.. important::

   T3api builds serializer metadata only from its own annotations (``\SourceBroker\T3api\Annotation\Serializer\*``) and YAML metadata. JMS annotations (``@JMS\Serializer\Annotation\...``) in model classes are **ignored**, so use YAML for everything which t3api annotations do not cover (e.g. ``exp``, ``expose_if``).

Variables
==========

Variables are passed by JMS serializer, so they are **different** from the variables available in :ref:`security expressions <security>`:

- ``object`` - the object being serialized.
- ``context`` - JMS serialization context (``\JMS\Serializer\SerializationContext``). Note that it is **not** the TYPO3 ``Context`` object which is available as ``context`` in security expressions.
- ``property_metadata`` - metadata of the property being serialized (in ``exp`` and property ``exclude_if`` / ``expose_if``).
- ``class_metadata`` - metadata of the class (in class level ``exclude_if`` only).

``frontend.user``, ``backend.user`` and other security variables are **not** available in serialization expressions.

Context attributes
-------------------

``context.getAttribute('name')`` gives access to attributes of the serialization context. Following attributes are set by t3api for every operation:

- ``groups`` - serialization groups of the operation (from ``normalizationContext``).
- any other key set in ``normalizationContext`` of the operation.
- ``TYPO3_SITE_URL``, ``TYPO3_REQUEST_HOST``, ``TYPO3_REQUEST_URL``, ``TYPO3_HOST_ONLY``, ``TYPO3_PORT``, ``TYPO3_SSL``, ``TYPO3_PROXY``, ``TYPO3_SITE_PATH``, ``TYPO3_SITE_SCRIPT``, ``TYPO3_REQUEST_SCRIPT``, ``TYPO3_REQUEST_DIR``, ``TYPO3_DOCUMENT_ROOT`` - values of ``GeneralUtility::getIndpEnv()`` for the current request.

Use ``context.hasAttribute('name')`` before reading an attribute which may not exist - ``context.getAttribute()`` of a missing attribute triggers an error.

Functions
==========

- ``force_absolute_url(url, fallbackHost)`` - returns ``url`` unchanged if it is already absolute (has a scheme or starts with ``//``), otherwise prefixes it with ``fallbackHost``.

T3api uses it to expose absolute URL of files (``EXT:t3api/Resources/Private/Serializer/Metadata/TYPO3.CMS.Core.Resource.AbstractFile.yml``):

.. code-block:: yaml

   TYPO3\CMS\Core\Resource\AbstractFile:
     virtual_properties:
       absolutePublicUrl:
         type: string
         exp: force_absolute_url(object.getPublicUrl(), context.getAttribute('TYPO3_SITE_URL'))

Functions of TYPO3 core default provider (``like()``, ``compatVersion()``, ``date()``, ``feature()``, ``traverse()``) and functions from your own providers registered for ``t3api`` context are available as well.

Examples
=========

Virtual property computed from the object (YAML metadata):

.. code-block:: yaml

   Vendor\Users\Domain\Model\User:
     virtual_properties:
       fullName:
         exp: "object.getFirstName() ~ ' ' ~ object.getLastName()"
         type: string
         groups: [api_get_item_user]

Property returned only if a custom attribute is set in ``normalizationContext`` of the operation:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource(
    *     itemOperations={
    *          "get"={
    *              "path"="/users/{id}",
    *          },
    *          "get_full"={
    *              "path"="/users/{id}/full",
    *              "normalizationContext"={
    *                  "includeEmail"=true
    *              },
    *          },
    *     },
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
       /**
        * @T3api\Serializer\Exclude(if="!context.hasAttribute('includeEmail')")
        */
       protected $email = '';
   }

The same in YAML metadata (see :ref:`serialization_yaml-metadata`):

.. code-block:: yaml

   Vendor\Users\Domain\Model\User:
     properties:
       email:
         exclude_if: "!context.hasAttribute('includeEmail')"

To make a property depend on the current user (e.g. return ``email`` only to backend admins), set a context attribute in ``AfterCreateContextForOperationEvent`` (see :ref:`events`) and check it in the expression:

.. code-block:: php

   use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;
   use TYPO3\CMS\Core\Context\Context;

   class AddIsAdminContextAttributeEventListener
   {
       public function __construct(private readonly Context $context) {}

       public function __invoke(AfterCreateContextForOperationEvent $event): void
       {
           $event->getContext()->setAttribute(
               'isAdmin',
               (bool)$this->context->getPropertyFromAspect('backend.user', 'isAdmin', false)
           );
       }
   }

.. code-block:: yaml

   Vendor\Users\Domain\Model\User:
     properties:
       email:
         exclude_if: "!context.hasAttribute('isAdmin') || !context.getAttribute('isAdmin')"

.. note::

   If serialized data depends on the current user and the endpoint uses :ref:`response cache <response-cache>`, make sure such responses are not shared between users (see :ref:`response-cache-conditions`).

:ref:`Here you can find out how to customize and extend expression language for serialization <customization_expression-language>`
