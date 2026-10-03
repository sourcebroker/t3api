.. _security:

=========
Security
=========

Access to operations and filters is controlled with `Symfony expression language <https://symfony.com/doc/current/components/expression_language.html>`__ expressions - the same mechanism TYPO3 uses for TypoScript conditions. An expression is evaluated for every request and has to return ``true`` to grant access.

.. _security_operation:

Operation security
===================

Every operation accepts two attributes:

- ``security`` - checked **before** the operation is processed. For item operations (``GET``, ``PATCH``, ``PUT``, ``DELETE``) it is checked after the record is loaded from the database, so the record is available as ``object``.
- ``security_post_denormalize`` - checked **after** request payload is deserialized into the object (only for operations which read the payload: collection ``POST`` and item ``PATCH`` / ``PUT``). ``object`` contains the entity with data from the request already applied, so it is possible to validate *what* the user is trying to save.

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "get"={
    *              "path"="/votes",
    *          },
    *          "post"={
    *              "method"="POST",
    *              "path"="/votes",
    *              "security"="frontend.user.isLoggedIn",
    *          },
    *     },
    *     itemOperations={
    *          "get"={
    *              "path"="/votes/{id}",
    *          },
    *          "patch"={
    *              "method"="PATCH",
    *              "path"="/votes/{id}",
    *              "security"="frontend.user.isLoggedIn && object.getUser().getUid() == frontend.user.userId",
    *              "security_post_denormalize"="object.getUser().getUid() == frontend.user.userId",
    *          },
    *          "delete"={
    *              "method"="DELETE",
    *              "path"="/votes/{id}",
    *              "security"="backend.user.isAdmin",
    *          },
    *     },
    * )
    */
   class Vote extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
   {
   }

In the example above, the ``PATCH`` operation is protected twice: ``security`` makes sure the user edits their own vote and ``security_post_denormalize`` makes sure the user did not change the owner of the vote in the request payload.

If an expression returns ``false``, the operation is not processed and the response has status ``403 Forbidden``. If ``security`` is not set (default), the operation is available for everyone.

.. note::

   ``object`` exists only in item operations and in ``security_post_denormalize``. Collection operations (``GET`` and ``POST`` on a collection) do not have it in ``security`` - for collection ``GET`` use filters or your own repository constraints to limit records, for ``POST`` use ``security_post_denormalize``.

Filter security
================

Built-in and custom filters can be enabled conditionally. Instead of a strategy name, pass an array with ``name`` (strategy) and ``condition`` (expression) for a property:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\SearchFilter;

   /**
    * @T3api\ApiFilter(
    *     SearchFilter::class,
    *     properties={
    *          "lastName": "partial",
    *          "email": {"name": "partial", "condition": "backend.user.isLoggedIn"},
    *     },
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

A filter whose condition returns ``false`` is **silently ignored** - the request does not fail, the filter just does not affect the results. In the example above, anonymous users can search users by ``lastName``, but only a logged-in backend user can search by ``email`` (``?email=...`` is ignored for everyone else).

Available variables and functions
==================================

User variables
---------------

Built from TYPO3 ``Context`` aspects ``frontend.user`` and ``backend.user``, the same as in TypoScript conditions.

.. list-table::
   :header-rows: 1
   :widths: 40 60

   * - Variable
     - Description
   * - ``frontend.user.isLoggedIn``
     - ``true`` if a frontend user is logged in.
   * - ``frontend.user.userId``
     - UID of the logged in frontend user (``0`` if not logged in).
   * - ``frontend.user.userGroupIds``
     - Array of frontend user group UIDs.
   * - ``frontend.user.userGroupList``
     - Comma separated list of frontend user group UIDs.
   * - ``backend.user.isLoggedIn``
     - ``true`` if a backend user is logged in (backend session cookie is sent with the API request).
   * - ``backend.user.isAdmin``
     - ``true`` if the logged in backend user is an admin.
   * - ``backend.user.userId``
     - UID of the logged in backend user.
   * - ``backend.user.userGroupIds``
     - Array of backend user group UIDs.
   * - ``backend.user.userGroupList``
     - Comma separated list of backend user group UIDs.

Examples:

- Only logged in frontend users: ``frontend.user.isLoggedIn``
- Only frontend users from group 3: ``3 in frontend.user.userGroupIds``
- Only frontend users from group 3 or 4: ``3 in frontend.user.userGroupIds || 4 in frontend.user.userGroupIds``
- Only a specific frontend user: ``frontend.user.userId == 12``
- Only logged in backend users: ``backend.user.isLoggedIn``
- Only backend admins: ``backend.user.isAdmin``
- Only backend users from group 2: ``2 in backend.user.userGroupIds``

.. tip::

   Prefer ``in frontend.user.userGroupIds`` over ``like(frontend.user.userGroupList, '*3*')`` - the latter matches also groups ``13``, ``30`` etc.

T3api variables
----------------

- ``object`` - the entity, see :ref:`operation security <security_operation>` above.
- ``t3apiOperation`` - the operation being checked (``\SourceBroker\T3api\Domain\Model\OperationInterface``), e.g. ``t3apiOperation.getPath()``. Available in operation security only.
- ``t3apiFilter`` - the filter being checked (``\SourceBroker\T3api\Domain\Model\ApiFilter``). Available in filter security only.
- ``context`` - TYPO3 ``\TYPO3\CMS\Core\Context\Context`` object, so any aspect can be read, e.g. ``context.getPropertyFromAspect('workspace', 'id') == 0``.

TYPO3 core variables and functions
-----------------------------------

T3api uses TYPO3 core default expression language provider and TypoScript condition functions, so most of `TypoScript conditions <https://docs.typo3.org/m/typo3/reference-typoscript/main/en-us/Conditions/Index.html>`__ work the same way:

- variables: ``applicationContext``, ``typo3.version``, ``typo3.branch``, ``typo3.devIpMask``, ``date``, ``features``,
- functions: ``compatVersion()``, ``like()``, ``date()``, ``feature()``, ``traverse()``.

T3api adds also function ``force_absolute_url()`` (see :ref:`serialization_expression-language`).

Examples:

- Only in development context: ``applicationContext matches "/^Development/"``
- Only on TYPO3 v13 or newer: ``compatVersion("13.0")``
- Only before a deadline: ``date("Y-m-d") < "2027-01-01"``

.. important::

   Variables related to a page rendering are **not** available, as an API request does not render any page: ``page``, ``tree``, ``request``, ``site``, ``siteLanguage`` and functions using them (``ip()``, ``site()``, ``siteLanguage()``, ``session()``, ``locale()``, ``getTSFE()``). ``ip()`` throws an exception on TYPO3 v13 and newer because there is no ``request`` variable (on TYPO3 v12 it still works with a deprecation notice). ``getTSFE()`` exists only on TYPO3 v12. If you need such data, add your own variables with an event (see below).

Extending expression language
==============================

There are two ways to make additional variables or functions available:

- Register an expression language provider for ``t3api`` context - available in security and serialization expressions. See :ref:`customization_expression-language`.
- Listen to ``BeforeOperationAccessGrantedEvent``, ``BeforeOperationAccessGrantedPostDenormalizeEvent`` or ``BeforeFilterAccessGrantedEvent`` and add variables with ``setExpressionLanguageVariable($name, $value)`` - available only in security expressions. See :ref:`events`.
