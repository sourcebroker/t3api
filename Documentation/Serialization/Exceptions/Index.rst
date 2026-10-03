.. _serialization_exceptions:

==========
Exceptions
==========

TYPO3 may encounter issues with FileReference or File objects, such as when a file
is missing, inaccessible, or if the relation is broken. These issues can interrupt
the serialization process and a jsonified error will be returned.
To address this, we've introduced a configuration option that allows for the
graceful handling of specific exceptions during the serialization process.
This configuration is defined on a per-class basis, meaning that different
classes can have different sets of exceptions that are handled gracefully.

Once configured, these exceptions will not interrupt the serialization process
for the respective class. Instead, they will be handled appropriately,
allowing the serialization process to continue uninterrupted.
This ensures that a problem with a single object does not prevent the successful
serialization of other objects.


Setting responsible for that is
:php:`$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializer']['exclusionForExceptionsInAccessorStrategyGetValue']`.
Keys are class names (subclasses are matched too), values are lists of exception classes which should be
ignored when a value of a property of that class is read. If such exception is thrown, the property is
returned as ``null`` (and a warning is triggered) instead of breaking the whole response.

By default t3api handles missing files for ``FileReference`` objects:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializer']['exclusionForExceptionsInAccessorStrategyGetValue'] = [
       \TYPO3\CMS\Core\Resource\FileReference::class => [
           \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException::class,
           \UnexpectedValueException::class,
       ],
       \TYPO3\CMS\Extbase\Domain\Model\FileReference::class => [
           \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException::class,
           \UnexpectedValueException::class,
       ],
   ];

To add your own class, extend the array in :file:`ext_localconf.php` of your extension (do not overwrite it):

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializer']['exclusionForExceptionsInAccessorStrategyGetValue'][\SourceBroker\T3apinews\Domain\Model\FileReference::class] = [
       \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException::class,
   ];

Asterisk (``*``) as "all exceptions" is also supported:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializer']['exclusionForExceptionsInAccessorStrategyGetValue'][\SourceBroker\T3apinews\Domain\Model\FileReference::class] = ['*'];
