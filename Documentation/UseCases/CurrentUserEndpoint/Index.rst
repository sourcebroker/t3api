.. include:: ../../Includes.txt

.. _use-cases_current-user-endpoint:

Current user endpoint
======================

Getting current user is a common issue in JSON API frameworks. It is not possible to use any build-in *CRUD* endpoint to fetch current user data because we need to make a request to get single item operation which requires identifier in the URL. But we do not know the identifier of current user before request is done.

`T3api` resolves that issue using custom operation handlers.
Firstly let's register an endpoint as we do for any *CRUD* operation.

.. code-block:: php
   :caption: typo3conf/ext/users/Classes/Domain/Model/User.php

   declare(strict_types=1);

   namespace Vendor\Users\Domain\Model;

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource (
    *     itemOperations={
    *          "get_current"={
    *              "path"="/users/current",
    *          },
    *     }
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

Now let's register our operation handler inside `ext_localconf.php` and create a handler class. :ref:`Here you can read more about implementing your own operation handlers <operations_customizing-operation-handler>`.

.. code-block:: php
   :caption: typo3conf/ext/users/ext_localconf.php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['operationHandlers'][\Vendor\Users\OperationHandler\GetCurrentUserOperationHandler::class] = 500;

.. code-block:: php
   :caption: typo3conf/ext/users/Classes/OperationHandler/GetCurrentUserOperationHandler.php

   declare(strict_types=1);

   namespace Vendor\Users\OperationHandler;

   use Vendor\Users\Domain\Model\User;
   use Psr\Http\Message\ResponseInterface;
   use SourceBroker\T3api\Domain\Model\OperationInterface;
   use SourceBroker\T3api\OperationHandler\ItemGetOperationHandler;
   use Symfony\Component\HttpFoundation\Request;
   use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

   class GetCurrentUserOperationHandler extends ItemGetOperationHandler
   {
       public static function supports(OperationInterface $operation, Request $request): bool
       {
           return $operation->getApiResource()->getEntity() === User::class && $operation->getKey() === 'get_current';
       }

       public function handle(
           OperationInterface $operation,
           Request $request,
           array $route,
           ?ResponseInterface &$response
       ): AbstractDomainObject {
           if (empty($GLOBALS['TSFE']->fe_user->user['uid'])) {
               throw new \RuntimeException('Unknown current user ID. Are you logged in?', 1592570206680);
           }

           $route['id'] = $GLOBALS['TSFE']->fe_user->user['uid'];

           return parent::handle($operation, $request, $route, $response);
       }
   }

And that's it! Request to URL `/users/current` will return current user data depending on properties, getters and serialization configuration for ``Vendor\Users\Domain\Model\User``. Current user will be authorized in exactly the same way as it is authorized on normal request to TYPO3 page (using cookie by default). If request is done by not logged in user exception `Unknown current user ID. Are you logged in?` will be thrown.
