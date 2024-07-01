.. _events:

======
Events
======


After deserialization
=====================

An event that is dispatched after request payload is deserialized to objects.
May be useful when it is needed e.g. to change datetime property to current or
assign current TYPO3 user as an author. This event gives access to following data:

- ``operation`` - instance of ``\SourceBroker\T3api\HydraCollectionResponseomain\Model\OperationInterface``
- ``object`` -  object deserialized from payload

Example event registration in :file:`Services.yaml`:

.. code-block:: yaml

  V\Site\EventListener\AfterDeserializeOperationEventListener:
    tags:
      - name: 'event.listener'


Example implementation:

.. code-block:: php

    <?php

    namespace V\Site\EventListener;

    use SourceBroker\T3api\Event\AfterDeserializeOperationEvent;

    class AfterDeserializeOperationEventListener
    {
        public function __invoke(AfterDeserializeOperationEvent $event): void
        {
            $operation = $event->getOperation();
            $object => $event->getObject();
            ...
            $event->setObject($object);
        }
    }



After processing operation
==========================

An event that is dispatched after an operation is processed and before objects
are passed to the serializer. This event gives access to following data:

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\OperationInterface``
- ``result`` - instance of resource class (entity) or, in collection GET request,
  instance of ``\SourceBroker\T3api\Response\AbstractCollectionResponse``
  (``\SourceBroker\T3api\Response\HydraCollectionResponse`` by default if not customized)


Example event registration in :file:`Services.yaml`:

.. code-block:: yaml

  V\Site\EventListener\AfterProcessOperationEventListener:
    tags:
      - name: 'event.listener'


Example implementation:

.. code-block:: php

    <?php

    namespace V\Site\EventListener;

    use SourceBroker\T3api\Event\AfterProcessOperationEvent;

    class AfterProcessOperationEventListener
    {
        public function __invoke(AfterProcessOperationEvent $event): void
        {
            $operation = $event->getOperation();
            $result = $event->getResult();
        }
    }


After create context for operation
==================================

Event emitted during building serialization context. Useful to modify serialization
context attributes (e.g. add groups which can conditionally include some
properties in API response). This event gives access to following data:

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\OperationInterface``
- ``request`` - instance of ``Symfony\Component\HttpFoundation\Request``
- ``context`` - instance of ``JMS\Serializer\SerializationContext``

Example event registration in :file:`Services.yaml`:

.. code-block:: yaml

  V\Site\EventListener\AfterCreateContextForOperationEventListener:
    tags:
      - name: 'event.listener'


Example implementation:

.. code-block:: php

    <?php

    namespace V\Site\EventListener;

    use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;

    class AfterCreateContextForOperationEventListener
    {
        public function __invoke(AfterCreateContextForOperationEvent $event): void
        {
            $operation = $event->getOperation();
            $request = $event->getRequest();
            $context = $event->getContext();
        }
    }


Before grant access
===================

``\SourceBroker\T3api\Security\FilterAccessChecker`` and ``\SourceBroker\T3api\Security\OperationAccessChecker``
are services used to decide if filter or operation are allowed for current request
(check :ref:`security documentation <security>` for more information).

There are 3 events dispatched before evaluation of security expressions.

.. note::

   Before using these events consider if `build-in TYPO3 mechanism to extend expression language <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SymfonyExpressionLanguage/Index.html>`__
   won't be a better solution. Keep in mind that registering variables within these
   events will make it available only in t3api context while using TYPO3 mechanism makes
   it possible to be used also in other places (e.g. TypoScript conditions).

Before grant operation access
+++++++++++++++++++++++++++++

Event dispatched before grant operation access. This event gives access to following data:

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\OperationInterface``
- ``expressionLanguageVariables`` - array of variables passed to expression language (empty array)

Example event registration in :file:`Services.yaml`:

.. code-block:: yaml

  V\Site\EventListener\BeforeOperationAccessGrantedEventListener:
    tags:
      - name: 'event.listener'

Example implementation:

.. code-block:: php

        <?php

        namespace V\Site\EventListener;

        use SourceBroker\T3api\Event\BeforeOperationAccessGrantedEvent;

        class BeforeOperationAccessGrantedEventListener
        {
            public function __invoke(BeforeOperationAccessGrantedEvent $event): void
            {
                $operation = $event->getOperation();
                $expressionLanguageVariables = $event->getExpressionLanguageVariables();
                ...
            }
        }


Before grant post denormalize operation access
++++++++++++++++++++++++++++++++++++++++++++++

Event dispatched before grant post denormalize operation access. This event gives access to following data:

- ``operation`` - instance of ``\SourceBroker\T3api\Domain\Model\OperationInterface``
- ``expressionLanguageVariables`` - array of variables passed to expression language (contains denormalized ``object``)

Example event registration in :file:`Services.yaml`:

.. code-block:: yaml

  V\Site\EventListener\BeforeOperationAccessGrantedPostDenormalizeEventListener:
    tags:
      - name: 'event.listener'

Example implementation:

.. code-block:: php

            <?php

            namespace V\Site\EventListener;

            use SourceBroker\T3api\Event\BeforeOperationAccessGrantedPostDenormalizeEvent;

            class BeforeOperationAccessGrantedPostDenormalizeEventListener
            {
                public function __invoke(BeforeOperationAccessGrantedPostDenormalizeEvent $event): void
                {
                    $operation = $event->getOperation();
                    $expressionLanguageVariables = $event->getExpressionLanguageVariables();
                    ...
                }
            }


Before grant filter access
++++++++++++++++++++++++++

Event dispatched before grant filter access. This event gives access to following data:

- ``filter`` - instance of ``\SourceBroker\T3api\Domain\Model\ApiFilter``
- ``expressionLanguageVariables`` - array of variables passed to expression language (empty array)

Example event registration in :file:`Services.yaml`:

.. code-block:: yaml

  V\Site\EventListener\BeforeFilterAccessGrantedEventListener:
    tags:
      - name: 'event.listener'

Example implementation:

.. code-block:: php

    <?php

    namespace V\Site\EventListener;

    use SourceBroker\T3api\Event\BeforeFilterAccessGrantedEvent;

    class BeforeFilterAccessGrantedEventListener
    {
        public function __invoke(BeforeFilterAccessGrantedEvent $event): void
        {
            $filter = $event->getFilter();
            $expressionLanguageVariables = $event->getExpressionLanguageVariables();
            ...
        }
    }
