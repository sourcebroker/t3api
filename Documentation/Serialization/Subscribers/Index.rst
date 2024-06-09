.. _serialization_subscribers:

===========
Subscribers
===========

A subscriber is a class that listens to one or more events during the serialization
or deserialization process. These events can include pre-serialization, post-serialization,
pre-deserialization, and post-deserialization.  Subscribers are used to customize
the serialization/deserialization process. For example, you might use a subscriber
to change the serialized representation of certain types of objects, or to perform
some custom logic before an object is serialized.

Here is a list of build in T3API subscribers.


.. _serialization_subscribers_abstract_entity_subscriber:

AbstractEntitySubscriber
========================

This subscriber listens to the POST_SERIALIZE and PRE_DESERIALIZE events. In the case
of the POST_SERIALIZE event, it adds additional properties (defined in :php:`$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['forceEntityProperties']`)
and IRI to the serialized object if the object is an instance of AbstractDomainObject.
In the case of the PRE_DESERIALIZE event, it changes the type to a custom one to enable
data handling with a serializer handler.


.. _serialization_subscribers_current_fe_user_subscriber:

CurrentFeUserSubscriber
========================

This subscriber listens to the PRE_SERIALIZE and PRE_DESERIALIZE events.
In the case of the PRE_SERIALIZE event, it changes the type to a custom one if the type is
equal to CurrentFeUserHandler::TYPE. In the case of the PRE_DESERIALIZE event, it adds the
FE user identifier to the data. This identifier is later used by CurrentFeUserHandler to
get FeUser object. It allows to securely attach info about FeUser to incoming data.
Look for more info at at :ref:`current user endpoint use case <use-cases_current-user-endpoint>`.


.. _serialization_subscribers_file_reference_subscriber:

FileReferenceSubscriber
========================

This subscriber listens to the PRE_SERIALIZE and PRE_DESERIALIZE events.
In both cases, it changes the type to a custom one to enable data handling with
a serializer handler if the type is a subclass of AbstractFileFolder.


.. _serialization_subscribers_generate_metadata_subscriber:

GenerateMetadataSubscriber
========================

This subscriber listens to the PRE_SERIALIZE and PRE_DESERIALIZE events.
In both cases, it generates yml serializer metadata and cache it in :file:`var/cache/code/t3api`.
Data from :file:`var/cache/code/t3api` is later used to serialize/deserialize by JSM serializer.


.. _serialization_subscribers_resource_type_subscriber:

ResourceTypeSubscriber
========================

This subscriber listens to the POST_SERIALIZE event. It adds the resource type (:json:`@type`)
to the serialized object if the object is an instance of AbstractDomainObject.
Examples of types: :php:`SourceBroker\\T3apinews\\Tag`, :php:`SourceBroker\\T3apinews\\News`. Adding
``@type`` is not activated by default as it can take a lot of space in the response.
To activate it add in your local extension :file:`ext_localconf.php.

.. code-block:: php

  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers'][] = \SourceBroker\T3api\Serializer\Subscriber\ResourceTypeSubscriber::class;


.. _serialization_subscribers_throwable_subscriber:

ThrowableSubscriber
===================

This subscriber listens to the POST_SERIALIZE event. It adds a description and debug
to the serialized object if the object is an instance of Throwable.
