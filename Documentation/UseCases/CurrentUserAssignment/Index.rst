.. _use-cases_current-user-assignment:

Current user assignment
========================

If your API allows users to interact by making some push requests and persist data it is very likely you want to assign some records to current frontend user.

Let's say that our frontend users can vote in the poll and we want to persist user whom voted to know the answer and protect against double voting. It means we want to assign voting user to ``Vote.user`` property. **We assume that voting user is the one who is doing the request to API**.

To achieve the same effect you could just send current user UID inside payload of the request (like ``{"user": 50}``) but there is one huge difference in these approaches - if value comes from request payload you need to ensure it is not faked.

It is much easier just to add ``@T3api\Serializer\Type\CurrentFeUser`` annotation to property where current user should be assigned to. **It is done already on API side so you do not need to protect it in any special way**.

Annotation ``@T3api\Serializer\Type\CurrentFeUser`` accepts one parameter which is the name of the class which represents frontend user (in our example it is: ``Vendor\Poll\Domain\Model\User``).

.. code-block:: php
   :caption: typo3conf/ext/poll/Classes/Domain/Model/Vote.php

   declare(strict_types=1);

   namespace Vendor\Poll\Domain\Model;

   use SourceBroker\T3api\Annotation as T3api;
   use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "post"={
    *              "method"="POST",
    *              "path"="/votes",
    *              "security"="frontend.user.isLoggedIn",
    *          }
    *     }
    * )
    */
   class Vote extends AbstractEntity
   {
      // ...

      /**
       * @var User
       * @T3api\Serializer\Type\CurrentFeUser(User::class)
       */
      protected $user;

      // ...
   }

.. important::
   If you are using serialization groups they also has to be added to ``Vote::$user`` property annotations as you would usually do when ``user`` property would be sent in request payload.

.. note::
   This is valid example for TYPO3 version >= 10.0. For lower versions you would need to pass class name with namespace in annotations instead of short class name.

.. note::
   Example is based on common approach where custom class is used to represent frontend user - ``Vendor\Poll\Domain\Model\User``. But you may also work on default Extbase's class for that - ``\TYPO3\CMS\Extbase\Domain\Model\FrontendUser``. The only requirement is that your class use table `fe_users` as a data provider (using extbase persistence configuration).
