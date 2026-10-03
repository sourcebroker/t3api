.. _serialization_context-groups:

===============
Context groups
===============

By default all properties of an entity (and all getters marked as virtual properties) are serialized. Usually it is not what you want - for performance reasons (collection does not need ``bodytext``) and for security reasons (``fe_users.password`` should never leave the server). Serialization groups decide which properties are included in the response of a given operation.

It works in two steps:

1. Every operation declares which groups it uses - ``normalizationContext`` for the response (serialization) and ``denormalizationContext`` for the request payload (deserialization).
2. Every property declares to which groups it belongs - with ``@T3api\Serializer\Groups`` annotation or ``groups`` key in :ref:`YAML metadata <serialization_yaml-metadata>`.

Only properties which share at least one group with the operation are processed. When operation does not declare any groups, all properties are processed.

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource(
    *     collectionOperations={
    *          "get"={
    *              "path"="/articles",
    *              "normalizationContext"={
    *                  "groups"={"api_get_collection_article"}
    *              },
    *          },
    *          "post"={
    *              "method"="POST",
    *              "path"="/articles",
    *              "normalizationContext"={
    *                  "groups"={"api_get_item_article"}
    *              },
    *              "denormalizationContext"={
    *                  "groups"={"api_post_item_article"}
    *              },
    *          },
    *     },
    *     itemOperations={
    *          "get"={
    *              "path"="/articles/{id}",
    *              "normalizationContext"={
    *                  "groups"={"api_get_item_article"}
    *              },
    *          },
    *     },
    * )
    */
   class Article extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
   {
       /**
        * @T3api\Serializer\Groups({"api_get_collection_article", "api_get_item_article", "api_post_item_article"})
        */
       protected string $title = '';

       /**
        * @T3api\Serializer\Groups({"api_get_item_article", "api_post_item_article"})
        */
       protected string $bodytext = '';

       /**
        * Never serialized and can not be set from request payload.
        */
       protected string $internalNote = '';
   }

With configuration above:

- ``GET /articles`` returns only ``title``.
- ``GET /articles/{id}`` returns ``title`` and ``bodytext``.
- ``POST /articles`` accepts ``title`` and ``bodytext`` from payload - ``internalNote`` sent in payload is ignored.

.. note::

   ``uid`` (and ``@id`` if the entity is an API resource with item operation) is always added to serialized entities, regardless of groups (see :ref:`AbstractEntitySubscriber <serialization_subscribers_abstract_entity_subscriber>`).

.. note::

   If ``denormalizationContext`` is not set, ``normalizationContext`` is used also for deserialization. This is a fallback kept for backward compatibility - it is recommended to always set ``denormalizationContext`` for write operations, so it is explicit which properties can be changed by the client.

Naming convention
==================

Group names are free strings, but it is good to have a convention. Testing instance (``EXT:t3apinews``) uses ``api_<method>_<collection|item>_<extension>_<model>``, e.g. ``api_get_collection_t3apinews_news`` or ``api_patch_item_t3apinews_news``. It makes it easy to see in the model which property is exposed by which endpoint.

Groups of related objects
==========================

Groups are applied to the whole object graph. If ``News`` is serialized with group ``api_get_collection_t3apinews_news``, then also its ``categories``, ``tags`` etc. are serialized with this group - so properties of ``Category`` which should be visible in news list have to contain group ``api_get_collection_t3apinews_news`` as well (see ``Domain.Model.Category.yml`` in ``EXT:t3apinews``).

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | List of news - group ``api_get_collection_t3apinews_news``, no ``bodytext``:
     | `https://14.t3api.ddev.site/_api/news/news <https://14.t3api.ddev.site/_api/news/news>`__
     |
   * | Single news - group ``api_get_item_t3apinews_news``, with ``bodytext`` and ``related``:
     | `https://14.t3api.ddev.site/_api/news/news/1 <https://14.t3api.ddev.site/_api/news/news/1>`__
     |
   * | Single tag - tag operations do not declare groups, so all properties (``crdate``, ``slug``, ``seoTitle``, ...) are returned:
     | `https://14.t3api.ddev.site/_api/news/tags/1 <https://14.t3api.ddev.site/_api/news/tags/1>`__
     |

.. _serialization_context-groups_customization:

Customization
===============

Context created from ``normalizationContext`` / ``denormalizationContext`` can be changed at runtime with PSR-14 event ``\SourceBroker\T3api\Event\AfterCreateContextForOperationEvent`` (see :ref:`events`). It is dispatched for serialization and deserialization context of every operation. The event gives access to the operation (``getOperation()``), the request (``getRequest()``) and the context (``getContext()``).

Typical use case is to conditionally expose additional properties, e.g. to return ``internalNote`` only when current frontend user belongs to group ``editors`` (uid ``3``):

.. code-block:: php

   /**
    * @T3api\Serializer\Groups({"api_editor"})
    */
   protected string $internalNote = '';

.. code-block:: php

   <?php

   namespace Vendor\Extension\EventListener;

   use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;
   use TYPO3\CMS\Core\Context\Context;

   class AddEditorGroupEventListener
   {
       public function __construct(private readonly Context $context) {}

       public function __invoke(AfterCreateContextForOperationEvent $event): void
       {
           $serializerContext = $event->getContext();
           $userGroupIds = $this->context->getPropertyFromAspect('frontend.user', 'groupIds', []);

           if (!in_array(3, $userGroupIds, true) || !$serializerContext->hasAttribute('groups')) {
               return;
           }

           $serializerContext->setGroups(array_merge(
               $serializerContext->getAttribute('groups'),
               ['api_editor']
           ));
       }
   }

.. code-block:: yaml
   :caption: Configuration/Services.yaml

   services:
     Vendor\Extension\EventListener\AddEditorGroupEventListener:
       tags:
         - name: event.listener
           event: SourceBroker\T3api\Event\AfterCreateContextForOperationEvent

.. important::

   The event is dispatched for both directions. Group added as above is used also for deserialization of ``POST`` / ``PUT`` / ``PATCH`` payload, so editors would be able to **write** ``internalNote`` too. If you want to change only the response, check the type of context: ``$serializerContext instanceof \JMS\Serializer\SerializationContext``.

.. important::

   Responses of ``GET`` endpoints can be cached by :ref:`response cache <response-cache>`. If the response depends on the current user, skip the cache for such requests with ``readCondition`` / ``writeCondition`` (see :ref:`response-cache-conditions`).
