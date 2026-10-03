.. _serialization_subscribers:

===========
Subscribers
===========

A subscriber is a class that listens to one or more events during the serialization
or deserialization process. These events can include pre-serialization, post-serialization,
pre-deserialization, and post-deserialization. Subscribers are used to customize
the serialization/deserialization process. For example, you might use a subscriber
to change the serialized representation of certain types of objects, or to perform
some custom logic before an object is serialized.

Subscribers are registered in :php:`$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers']`.
Each of them has to implement ``\JMS\Serializer\EventDispatcher\EventSubscriberInterface``.
To add your own subscriber add it in :file:`ext_localconf.php` of your extension:

.. code-block:: php

  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers'][] = \Vendor\Extension\Serializer\Subscriber\MySubscriber::class;

Here is a list of built-in T3api subscribers.

.. _serialization_subscribers_abstract_entity_subscriber:

AbstractEntitySubscriber
========================

This subscriber listens to the ``POST_SERIALIZE`` and ``PRE_DESERIALIZE`` events. In the case
of the ``POST_SERIALIZE`` event, it adds additional properties (defined in :php:`$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['forceEntityProperties']`, by default ``uid``)
and IRI (``@id``) to the serialized object if the object is an instance of ``AbstractDomainObject``.
IRI is added only if the class is an API resource with an item operation.
In the case of the ``PRE_DESERIALIZE`` event, it changes the type of nested entities to a custom one to enable
data handling with a serializer handler (so related records can be passed in payload as ``uid`` or as an object).

.. _serialization_subscribers_cache_tag_subscriber:

CacheTagSubscriber
==================

This subscriber listens to the ``POST_SERIALIZE`` event. For every serialized ``AbstractDomainObject``
it collects cache tags ``<table>`` and ``<table>_<uid>`` (e.g. ``tx_news_domain_model_news_1``).
Tags are used by :ref:`response cache <response-cache>` to invalidate cached responses when a record is changed.
Tags are collected only while response cache is collecting (otherwise the subscriber does nothing).

.. _serialization_subscribers_current_fe_user_subscriber:

CurrentFeUserSubscriber
========================

This subscriber listens to the ``PRE_SERIALIZE`` and ``PRE_DESERIALIZE`` events.
In the case of the ``PRE_SERIALIZE`` event, it changes the type to a custom one if the type is
equal to ``CurrentFeUserHandler::TYPE``. In the case of the ``PRE_DESERIALIZE`` event, it adds the
FE user identifier to the data. This identifier is later used by ``CurrentFeUserHandler`` to
get FeUser object. It allows to securely attach info about FeUser to incoming data.
Look for more info at :ref:`current user assignment use case <use-cases_current-user-assignment>`.

.. _serialization_subscribers_file_reference_subscriber:

FileReferenceSubscriber
========================

This subscriber listens to the ``PRE_SERIALIZE`` and ``PRE_DESERIALIZE`` events.
In both cases, if the type is a subclass of ``FileReference``, ``File`` or ``Folder`` (and it is nested in another object), it changes the type to a custom one
to enable data handling with ``FileReferenceHandler`` (see :ref:`serialization_handlers_file-reference`).

.. _serialization_subscribers_generate_metadata_subscriber:

GenerateMetadataSubscriber
==========================

This subscriber listens to the ``PRE_SERIALIZE`` and ``PRE_DESERIALIZE`` events.
In both cases, it generates YAML serializer metadata (merged from annotations and :ref:`YAML metadata <serialization_yaml-metadata>`)
and caches it in :file:`var/cache/code/t3api/jms-metadir`.
Data from :file:`var/cache/code/t3api/jms-metadir` is later used to serialize/deserialize by JMS serializer.

.. _serialization_subscribers_resource_type_subscriber:

ResourceTypeSubscriber
========================

This subscriber listens to the ``POST_SERIALIZE`` event. It adds the resource type (:json:`@type`)
to the serialized object if the object is an instance of ``AbstractDomainObject``.
Value is the fully qualified class name, e.g. :php:`SourceBroker\\T3apinews\\Domain\\Model\\News`. Adding
``@type`` is not activated by default as it can take a lot of space in the response.
To activate it add in your local extension :file:`ext_localconf.php`:

.. code-block:: php

  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers'][] = \SourceBroker\T3api\Serializer\Subscriber\ResourceTypeSubscriber::class;

.. _serialization_subscribers_throwable_subscriber:

ThrowableSubscriber
===================

This subscriber listens to the ``POST_SERIALIZE`` event. If the object is an instance of ``Throwable``, it adds
``hydra:description`` (exception message) and - in ``Development`` application context only - ``hydra:debug`` (stack trace)
to the serialized object.
