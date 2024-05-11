.. include:: ../../Includes.txt


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


Setting responsible for that is:

.. code-block:: php
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializer']['exclusionForExceptionsInAccessorStrategyGetValue']


Example value:

.. code-block:: php
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializer']['exclusionForExceptionsInAccessorStrategyGetValue'] = [
            \SourceBroker\T3apinews\Domain\Model\FileReference::class => [
                \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException::class,
            ],
        ];

Asterix as "all exceptions" is also supported:
Example:

.. code-block:: php
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializer']['exclusionForExceptionsInAccessorStrategyGetValue'] = [
            \SourceBroker\T3apinews\Domain\Model\FileReference::class => ['*'],
        ];
