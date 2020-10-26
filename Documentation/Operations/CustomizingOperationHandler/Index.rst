.. include:: ../../Includes.txt

.. _operations_customizing-operation-handler:

Customizing operation handler
===============================

Sometimes predefined operations may not be enough to handle all use cases. In fact build-in endpoints supports *CRUD* on Extbase entities. If you would like to create something more complex then you need to utilize custom operation handlers.

As name suggests, "operation handlers" determines how specific request is processed. T3api dispatcher checks if there is handler matching current request and executes it.

To register new operation handler it is needed to add new item to `t3api` configuration array, as on example below:

.. code-block:: php
   :caption: ext_localconf.php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['operationHandlers'][\Vendor\Extension\OperationHandler\MyCustomOperationHandler::class] = 100;

Name of the element of array is the name of the class and the value is the priority. Priority is needed because there may be multiple handlers which supports current request. Higher priority means that handler wins and will be executed. Only one handler will be executed for every request. All build-in handlers have negative priorities so it is suggested to use priority higher than 0 in custom handlers.

Every handler has to implement interface ``\SourceBroker\T3api\OperationHandler\OperationHandlerInterface``. This interface forces handler class to contain two methods:

- ``supports`` (static) - Basing on input arguments method takes a decision if current class can handle the operation and return boolean value (``true`` - supports; ``false`` - does not support). Accepts arguments:
   - `$operation` (``\SourceBroker\T3api\Domain\Model\OperationInterface``).
   - `$request` (``\Symfony\Component\HttpFoundation\Request``).

- ``handle`` - Contains code responsible for handling operation. Executed only when ``supports`` returns ``true``. Accepts arguments:
   - `$operation` (``\SourceBroker\T3api\Domain\Model\OperationInterface``).
   - `$request` (``\Symfony\Component\HttpFoundation\Request``).
   - `$route` (``array``) - Array with route parameters (e.g. ``$route['id']`` inside single items operations which uses ``{id}`` in URL).
   - `&$response` (``\Psr\Http\Message\ResponseInterface``) - *reference* to response.

.. note::

   Please keep in mind that ``$response`` parameter of ``handle`` method is **reference**. It is needed to make it possible to customize response inside operation handler since response object is immutable. It means that new ``ResponseInterface`` object has to be write to ``$response`` variable to really affect the server response. For example, if you would like to change the status code you should use such code inside your handler:

   .. code-block:: php

      $response = $response ? $response->withStatus(201) : $response;

It is useful and advised (but not required) to extend existing abstract classes when creating custom handlers:
   - ``\SourceBroker\T3api\OperationHandler\AbstractCollectionOperationHandler`` when creating custom collection operation handler.
   - ``\SourceBroker\T3api\OperationHandler\AbstractItemOperationHandler`` when creating custom item operation handler.
   - ``\SourceBroker\T3api\OperationHandler\AbstractOperationHandler`` or at least common abstract operation handler which contain bunch of useful things which may be needed in your custom handler (like deserialization process, injecting services or getting appropriate repository for current operation).

Even better to extend any of already existing operations if that is possible:
   - ``\SourceBroker\T3api\OperationHandler\CollectionGetOperationHandler``
   - ``\SourceBroker\T3api\OperationHandler\CollectionPostOperationHandler``
   - ``\SourceBroker\T3api\OperationHandler\FileUploadOperationHandler``
   - ``\SourceBroker\T3api\OperationHandler\ItemDeleteOperationHandler``
   - ``\SourceBroker\T3api\OperationHandler\ItemGetOperationHandler``
   - ``\SourceBroker\T3api\OperationHandler\ItemPatchOperationHandler``
   - ``\SourceBroker\T3api\OperationHandler\ItemPutOperationHandler``

Example
+++++++++

Example of declaration and usage of custom operation handler can be found in :ref:`current user endpoint use case <use-cases_current-user-endpoint>`.
