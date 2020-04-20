.. include:: ../Includes.txt

.. _signals_and_slots:

==================
Signals and slots
==================

After deserialization
=======================

Signal emitted after request payload is deserialized to objects.
May be useful when it is needed e.g. to change datetime property to current or assign current TYPO3 user as an author.
This signal emits two arguments:

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\AbstractOperation``
- ``object`` - object deserialized from payload

Example connection of slot to this signal can looks like one below.

.. code-block:: php

   \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
      \SourceBroker\T3api\Dispatcher\AbstractDispatcher::class,
      \SourceBroker\T3api\Dispatcher\AbstractDispatcher::SIGNAL_AFTER_DESERIALIZE_OPERATION,
      \Vendor\Extension\Slot\T3apiSlot::class,
      'afterDeserializeOperation'
   );

After processing operation
============================

Signal emitted after operation is processed and before objects are passed to serializer. This signal emits two
arguments:

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\AbstractOperation``

- ``result`` - instance of resource class (entity) or, in collection GET request, instance of ``\SourceBroker\T3api\Response\AbstractCollectionResponse`` (``\SourceBroker\T3api\Response\HydraCollectionResponse`` by default if not customized)

Example connection of slot to this signal can looks like one below.

.. code-block:: php

   \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
      \SourceBroker\T3api\Dispatcher\AbstractDispatcher::class,
      \SourceBroker\T3api\Dispatcher\AbstractDispatcher::SIGNAL_AFTER_PROCESS_OPERATION,
      \Vendor\Extension\Slot\T3apiSlot::class,
      'afterProcessOperation'
   );

Customize serializer context attributes
=========================================

Signal emitted during building serialization context. Useful to modify serialization context attributes (e.g. add groups which can conditionally include some properties in API response).

- ``operation`` - immutable instance of ``\SourceBroker\T3api\Domain\Model\AbstractOperation``
- ``request`` - immutable instance of ``Symfony\Component\HttpFoundation\Request``
- ``attributes`` - array with serialization context attributes which can be modified

.. code-block:: php

   \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
      \SourceBroker\T3api\Serializer\ContextBuilder\ContextBuilderInterface::class,
      \SourceBroker\T3api\Serializer\ContextBuilder\ContextBuilderInterface::SIGNAL_CUSTOMIZE_SERIALIZER_CONTEXT_ATTRIBUTES,
      \Vendor\Extension\Slot\T3apiSlot::class,
      'customizeSerializerContextAttributes'
   );
