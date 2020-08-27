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

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\OperationInterface``
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

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\OperationInterface``

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

- ``operation`` - immutable instance of ``\SourceBroker\T3api\Domain\Model\OperationInterface``
- ``request`` - immutable instance of ``Symfony\Component\HttpFoundation\Request``
- ``attributes`` - array with serialization context attributes which can be modified

.. code-block:: php

   \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
      \SourceBroker\T3api\Serializer\ContextBuilder\ContextBuilderInterface::class,
      \SourceBroker\T3api\Serializer\ContextBuilder\ContextBuilderInterface::SIGNAL_CUSTOMIZE_SERIALIZER_CONTEXT_ATTRIBUTES,
      \Vendor\Extension\Slot\T3apiSlot::class,
      'customizeSerializerContextAttributes'
   );

Before grant access
=========================================

``\SourceBroker\T3api\Security\FilterAccessChecker`` and ``\SourceBroker\T3api\Security\OperationAccessChecker`` are services used to decide if filter or operation are allowed for current request (check :ref:`security documentation <security>` for more information).

There are 3 signals emitted before evaluation of security expressions. Although recommended way to register customer variables and methods is to use `build-in TYPO3 mechanism to extend expression language <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SymfonyExpressionLanguage/Index.html>`__ there may be cases when slots are useful. For example: In TYPO3 < 9.4 there was no Symfony Expression Language used in TYPO3 core thus customization is possible only using these signal slots.

.. note::

   Before using these slots consider if `build-in TYPO3 mechanism to extend expression language <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SymfonyExpressionLanguage/Index.html>`__ won't be a better solution. Keep in mind that registering variables within these slots will make it available only in t3api context while using TYPO3 mechanism makes it possible to be used also in other places (e.g. TypoScript conditions).

**Signal emitted before grant operation access**. Emits two arguments:

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\OperationInterface``
- ``expressionLanguageVariables`` - array of variables passed to expression language (empty array)

.. code-block:: php

   \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
      \SourceBroker\T3api\Security\OperationAccessChecker::class,
      \SourceBroker\T3api\Security\OperationAccessChecker::SIGNAL_BEFORE_IS_GRANTED,
      \Vendor\Extension\Slot\T3apiSlot::class,
      'beforeIsGranted'
   );

**Signal emitted before grant post denormalize operation access**. Emits two arguments:

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\OperationInterface``
- ``expressionLanguageVariables`` - array of variables passed to expression language (contains denormalized ``object``)

.. code-block:: php

   \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
      \SourceBroker\T3api\Security\OperationAccessChecker::class,
      \SourceBroker\T3api\Security\OperationAccessChecker::SIGNAL_BEFORE_IS_GRANTED_POST_DENORMALIZE,
      \Vendor\Extension\Slot\T3apiSlot::class,
      'beforeIsGrantedPostDenormalize'
   );

**Signal emitted before grant filter access**. Emits two arguments:

- ``filter`` - instance of ``\SourceBroker\T3api\Domain\Model\ApiFilter``
- ``expressionLanguageVariables`` - array of variables passed to expression language (empty array)

.. code-block:: php

   \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
      \SourceBroker\T3api\Security\FilterAccessChecker::class,
      \SourceBroker\T3api\Security\FilterAccessChecker::SIGNAL_BEFORE_IS_GRANTED,
      \Vendor\Extension\Slot\T3apiSlot::class,
      'beforeIsGranted'
   );
