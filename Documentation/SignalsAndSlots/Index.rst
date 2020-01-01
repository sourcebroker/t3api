.. include:: ../Includes.txt

.. _signals_and_slots:

==================
Signals and slots
==================

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
