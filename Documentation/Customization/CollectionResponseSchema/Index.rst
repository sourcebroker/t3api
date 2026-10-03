.. _customization_collection-response-schema:

Collection response schema
===========================

By default collection ``GET`` operations return response in `Hydra <https://www.hydra-cg.com/spec/latest/core/>`__ format, built by ``\SourceBroker\T3api\Response\HydraCollectionResponse``:

.. code-block:: json

   {
       "hydra:member": [ ... ],
       "hydra:totalItems": 6,
       "hydra:view": {
           "hydra:first": "/_api/news/news?page=1",
           "hydra:last": "/_api/news/news?page=1",
           "hydra:pages": ["/_api/news/news?page=1"],
           "hydra:page": 1
       },
       "hydra:search": {
           "hydra:template": "/_api/news/news{?order[uid],order[title],order[datetime],istopnews,uid,datetime,pid,search}",
           "hydra:mapping": [
               {"variable": "order[uid]", "property": "uid"},
               ...
           ]
       }
   }

- ``hydra:member`` - items of the current page.
- ``hydra:totalItems`` - number of all items matching the filters (not only on current page).
- ``hydra:view`` - pagination links, see :ref:`pagination`.
- ``hydra:search`` - URI template with all filters available for the endpoint.

Class used to build collection responses is configured globally:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'] = \SourceBroker\T3api\Response\HydraCollectionResponse::class;

Custom response class
======================

To change the schema (e.g. to match what your frontend expects) create a class which extends ``\SourceBroker\T3api\Response\AbstractCollectionResponse`` and register it in ``ext_localconf.php``. Abstract class gives you ``getMembers()`` (already paginated) and ``getTotalItems()``. The only method you have to implement is ``getOpenApiSchema()`` which describes the response in the generated OpenAPI documentation.

.. code-block:: php

   <?php

   namespace Vendor\Extension\Response;

   use SourceBroker\T3api\OpenApi\Objects\Schema;
   use SourceBroker\T3api\Response\AbstractCollectionResponse;

   class SimpleCollectionResponse extends AbstractCollectionResponse
   {
       public static function getOpenApiSchema(string $membersReference): Schema
       {
           return Schema::object()
               ->properties(
                   Schema::array('items')->items(Schema::ref($membersReference)),
                   Schema::integer('total')->minimum(0),
                   Schema::integer('page')->minimum(1)
               );
       }

       public function getPage(): int
       {
           return $this->operation->getPagination()->setParametersFromRequest($this->request)->getPage();
       }
   }

.. code-block:: php

   // ext_localconf.php
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'] = \Vendor\Extension\Response\SimpleCollectionResponse::class;

Response object is serialized with JMS serializer like any other object, so it is needed to tell the serializer which methods should be exposed. Do it with :ref:`YAML metadata <serialization_yaml-metadata>` (file ``Vendor.Extension.Response.SimpleCollectionResponse.yml`` in one of the ``serializerMetadataDirs``). Internal properties of ``AbstractCollectionResponse`` (``operation``, ``query``, ``request`` etc.) are already excluded by t3api.

.. code-block:: yaml

   Vendor\Extension\Response\SimpleCollectionResponse:
     virtual_properties:
       getMembers:
         serialized_name: items
         type: array
         groups: ['__simple_collection_response']
       getTotalItems:
         serialized_name: total
         type: int
         groups: ['__simple_collection_response']
       getPage:
         serialized_name: page
         type: int
         groups: ['__simple_collection_response']

Collection operations are usually serialized with ``normalizationContext`` groups (see :ref:`serialization_context-groups`), so the group used above has to be added to the serialization context. ``HydraCollectionResponse`` does the same with group ``__hydra_collection_response`` (see ``AddHydraCollectionResponseSerializationGroupEventListener``). Use ``AfterCreateContextForOperationEvent`` (see :ref:`events`):

.. code-block:: php

   <?php

   namespace Vendor\Extension\EventListener;

   use SourceBroker\T3api\Domain\Model\CollectionOperation;
   use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;

   class AddSimpleCollectionResponseGroupEventListener
   {
       public function __invoke(AfterCreateContextForOperationEvent $event): void
       {
           $context = $event->getContext();

           if (
               $event->getOperation() instanceof CollectionOperation
               && $event->getOperation()->isMethodGet()
               && $context->hasAttribute('groups')
           ) {
               $context->setGroups(array_merge(
                   $context->getAttribute('groups'),
                   ['__simple_collection_response']
               ));
           }
       }
   }

.. code-block:: yaml

   # Configuration/Services.yaml
   services:
     Vendor\Extension\EventListener\AddSimpleCollectionResponseGroupEventListener:
       tags:
         - name: event.listener
           event: SourceBroker\T3api\Event\AfterCreateContextForOperationEvent

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | Default Hydra collection response with pagination (``hydra:view``) and available filters (``hydra:search``):
     | `https://14.t3api.ddev.site/_api/news/news?itemsPerPage=2 <https://14.t3api.ddev.site/_api/news/news?itemsPerPage=2>`__
     |
