.. _serialization:

Serialization
==============

T3api uses `JMS serializer <https://jmsyst.com/libs/serializer>`__ to turn Extbase entities into JSON (serialization) and to turn JSON payload of ``POST``, ``PUT`` and ``PATCH`` requests into entities (deserialization).

Serialization of a class can be configured in two ways:

- **Annotations** in the model class - the best option for models you own. T3api delivers its own set of annotations in namespace ``\SourceBroker\T3api\Annotation\Serializer`` (``Groups``, ``SerializedName``, ``VirtualProperty``, ``Exclude``, ``ReadOnlyProperty``, ``MaxDepth`` and types in ``Type\*``).
- **YAML metadata** - for classes you can not change (e.g. models of 3rd party extensions or TYPO3 core). See :ref:`serialization_yaml-metadata`.

Both sources are merged, so you can mix them freely.

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource(
    *     collectionOperations={
    *          "get"={
    *              "path"="/items",
    *              "normalizationContext"={
    *                  "groups"={"api_get_collection_item"}
    *              },
    *          },
    *     },
    * )
    */
   class Item extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
   {
       /**
        * @T3api\Serializer\Groups({"api_get_collection_item"})
        */
       protected string $title = '';

       /**
        * @T3api\Serializer\VirtualProperty("titleLength")
        * @T3api\Serializer\Groups({"api_get_collection_item"})
        */
       public function getTitleLength(): int
       {
           return mb_strlen($this->title);
       }
   }

Next pages describe:

- :ref:`serialization_context-groups` - how to decide which properties are returned by which operation.
- :ref:`serialization_handlers` - built-in types for images, files, links and records, and how to write your own.
- :ref:`serialization_subscribers` - listeners of serialization events (``@id``, ``uid``, cache tags, exceptions).
- :ref:`serialization_yaml-metadata` - configuration of classes you do not own.
- :ref:`serialization_expression-language` - expression language in serialization.
- :ref:`serialization_exceptions` - graceful handling of broken files.

.. toctree::
   :maxdepth: 3
   :hidden:

   ContextGroups/Index
   Handlers/Index
   Subscribers/Index
   YamlMetadata/Index
   Customization/Index
   Exceptions/Index
