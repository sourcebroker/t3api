.. _response-cache:

==============
Response cache
==============

T3api can cache whole GET responses (collections and items) in the TYPO3
Caching Framework. Entries are tagged with every record they contain -
including nested relations - and are invalidated automatically when a
record is changed or deleted via the TYPO3 backend (DataHandler) or via
Extbase persistence. See :ref:`response-cache-hit-semantics` for exactly
what runs, and does not run, on a cache hit.

Enabling
========

Caching is opt-in per resource model via the ``cache`` key of the
``@ApiResource`` ``attributes`` array - the same array used for pagination,
persistence and upload settings:

.. code-block:: php

    use SourceBroker\T3api\Annotation\ApiResource;

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={
     *             "lifetime"=3600,
     *             "readCondition"="request.query.get('refresh') == null",
     *             "identifierExpressions"={
     *                 "context.getPropertyFromAspect('frontend.user', 'groupIds', '')"
     *             }
     *         }
     *     }
     * )
     */
    class Book extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
    }

There is no separate caching annotation - all cache configuration lives
inside this ``attributes={"cache"={...}}`` block, at resource level and,
as described next, per operation. Write-triggered cache invalidation is
configured separately, via the sibling
``attributes={"cacheInvalidation"={...}}`` block, see
:ref:`response-cache-invalidation`.

.. warning::
   If your resource output depends on the current user (or any other
   request context beyond URL and declared filters) you **must** declare a
   suitable ``identifierExpressions`` entry (typically reading a TYPO3
   Context aspect via ``context``, see "Cache key" below) - otherwise the
   first user's response is served to everyone.

Configuration reference
========================

The ``cache`` block accepts the following keys:

- ``enabled`` - whether caching is active, see the presence rule below
  (default: ``false``).
- ``lifetime`` - entry lifetime in seconds (default: ``86400``).
- ``readCondition`` / ``writeCondition`` - Symfony expressions gating cache
  read and write, see :ref:`response-cache-conditions`.
- ``tags`` - extra tags added to a stored entry, on top of the automatic
  content tags and the resource table seed (default: none). See
  :ref:`response-cache-declarative-tags`.
- ``tagExpressions`` - Symfony expressions whose non-empty string results are
  each added as an extra tag to a stored entry, alongside ``tags`` (default:
  none). See :ref:`response-cache-tag-expressions`.
- ``memberTagExpressions`` - like ``tagExpressions``, but evaluated once per
  top-level result entity instead of once per response (default: none). See
  :ref:`response-cache-tag-expressions`.
- ``identifierExpressions`` - Symfony expressions whose string results are
  each appended to the cache entry identifier (default: none), see "Cache
  key" below.

Write-triggered invalidation - flushing tags once a write operation
completes - is *not* a ``cache`` key: it is a separate, sibling
``attributes`` block, ``cacheInvalidation``, see
:ref:`response-cache-invalidation` below. A ``GET`` operation may only
declare ``cache``, and a non-``GET`` operation may only declare
``cacheInvalidation`` - declaring the wrong one explicitly on a specific
operation is rejected as soon as routes are built for the resource, see
:ref:`response-cache-invalidation-fail-fast`.

The ``identifierExpressions`` key varies the cache entry identifier by
something the request path and declared pagination/filter parameters do not
otherwise capture - a request header, or a TYPO3 Context aspect via the
``context`` variable (see :ref:`response-cache-conditions`):

.. code-block:: php

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={
     *             "identifierExpressions"={
     *                 "request.headers.get('X-App-Version')",
     *                 "context.getPropertyFromAspect('frontend.user', 'groupIds', '')"
     *             }
     *         }
     *     }
     * )
     */

Two requests differing in any one of the declared expressions' results
produce distinct cache entries; two requests agreeing on every expression's
result share the same entry. Each entry is evaluated by the same expression
engine and variable set as ``readCondition``/``writeCondition`` (see
:ref:`response-cache-conditions`) - a failure of *any* one of them is
treated the same way as a failed condition: the request is not cached, and
the error is logged, regardless of whether the other expressions would have
evaluated fine. Leaving the array empty (the default) leaves the identifier
exactly as it would be without this setting.

.. note::
   A non-empty ``cache`` block enables caching automatically - there is no
   need to add ``"enabled"=true`` yourself. An empty (or absent) ``cache``
   block leaves caching disabled. An explicit ``"enabled"`` key always wins
   over this rule, in either direction, so ``"cache"={"enabled"=false}``
   still disables caching even when other keys are also given. This applies
   both at resource level and, as described next, per operation.

Per-operation override
=======================

The resource-level ``cache`` block is the base setting. Every operation
(item and collection) inherits it, but can override individual keys via its
own ``attributes={"cache"={...}}`` - only the given keys are overridden, the
rest cascade down from the resource. This allows disabling caching (or
changing its behaviour) for a single operation while leaving the others
cached:

.. code-block:: php

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={"lifetime"=3600}
     *     },
     *     collectionOperations={
     *          "get"={
     *              "method"="GET",
     *              "path"="/books",
     *          },
     *     },
     *     itemOperations={
     *          "get"={
     *              "method"="GET",
     *              "path"="/books/{id}",
     *              "attributes"={
     *                  "cache"={
     *                      "enabled"=false,
     *                  },
     *              },
     *          },
     *     },
     * )
     */
    class Book extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
    }

Here the collection ``GET`` stays cached with the resource-level 3600s
lifetime, while the item ``GET`` is never cached. The overridable keys are
``enabled``, ``lifetime``, ``readCondition``, ``writeCondition``,
``identifierExpressions``, ``tags``, ``tagExpressions`` and
``memberTagExpressions`` - same names and semantics as the resource-level
``cache`` block above. The sibling ``cacheInvalidation`` block cascades the
same way, per its own keys (``tags`` and ``tagExpressions``), see
:ref:`response-cache-invalidation` below.

The presence rule from the note above also applies per operation, and works
the other way round too: an operation can enable caching on its own even
when the resource declares no ``cache`` block at all (so the resource-level
default stays disabled):

.. code-block:: php

    /**
     * @ApiResource(
     *     itemOperations={
     *          "get"={
     *              "path"="/books/{id}",
     *              "attributes"={
     *                  "cache"={"lifetime"=60},
     *              },
     *          },
     *     },
     * )
     */
    class Book extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
    }

Only the item ``GET`` above is cached; any other operation on the same
resource is not.

Cache key
=========

The cache entry identifier is built from the site identifier, language id,
request path, declared pagination/filter parameters (see :ref:`pagination`
and :ref:`filtering`) and, when declared, every ``identifierExpressions``
result, in the declared order (see "Configuration reference" above).
Unknown query parameters do not create separate entries.

.. note::
   Declared parameters are kept as given, including falsy values: a request
   with ``?active=0`` produces a different cache entry than the same request
   without ``active`` at all. Treating ``"0"`` or an empty string as if the
   parameter were absent would incorrectly merge distinct requests into one
   cache entry.

.. _response-cache-conditions:

Conditions
==========

``readCondition`` is evaluated before the cache lookup. An empty condition
(the default) always allows reading. When the expression evaluates to a
falsy value the response is not served from cache for this request - the
operation is processed normally and the fresh result may still be stored.
This enables a refresh mechanism:

.. code-block:: php

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={"readCondition"="request.query.get('refresh') == null"}
     *     }
     * )
     */

A request carrying ``?refresh=1`` skips the cache lookup, produces a fresh
response and (with an empty or satisfied ``writeCondition``) overwrites the
cached entry, so subsequent requests are served the refreshed data.

``writeCondition`` is evaluated before storing a response. An empty
condition always allows storing; a falsy result skips the store.

Both conditions, every ``identifierExpressions`` entry (see "Cache key"
above) and every ``tagExpressions`` entry (see
:ref:`response-cache-tag-expressions`) are evaluated by the same t3api
expression engine used for the ``security`` expressions and see the same
variables - ``backend`` and ``frontend`` (current user information),
``t3apiOperation`` (the matched operation), ``route`` (the matched route
parameters, as a plain array - e.g. ``route['id']`` for ``/books/{id}``, or
``route['recipeId']`` for ``/recipes/{recipeId}/favorite``; the router's
internal ``_route`` name is never included), ``context`` (the TYPO3 ``Context``
object) - plus the Symfony ``request`` object and TYPO3's default expression
variables (``applicationContext``, ``typo3``, ``date``, ``features``). Any
expression-language provider registered for the ``t3api`` namespace via
``Configuration/ExpressionLanguage.php`` (TYPO3's standard
expression-provider mechanism) contributes its variables to
``readCondition``/``writeCondition``/``identifierExpressions``/``tagExpressions``
automatically, too - the same variables already available to ``security``
expressions.

.. note::
   None of the expressions on this page ever see ``object`` - the loaded
   entity variable ``security`` expressions may reference (see "Access
   control" below). Two *other* response cache expressions do get it, under
   different names/semantics: ``cache.memberTagExpressions`` and
   ``cacheInvalidation.tagExpressions``, see
   :ref:`response-cache-tag-expressions` for the full availability matrix.

.. note::
   ``context`` mirrors the variable of the same name TYPO3 core itself
   exposes to TypoScript conditions - the same object, supporting the same
   ``context.getPropertyFromAspect('name', 'property', default)`` call. It
   is unrelated to the ``context`` variable seen inside JMS serializer
   metadata expressions (``exclude_if``, virtual properties), which JMS
   injects per-call as its own ``SerializationContext``/
   ``DeserializationContext`` - the two expression surfaces have disjoint
   variable sets, and provider variables such as this one do not leak into
   serializer expressions.

.. note::
   Conditions fail closed: an expression that cannot be evaluated counts as
   falsy (no cache read, no cache write) and the error is logged.

Examples
========

**Cache only anonymous traffic** - some responses serve user-specific
fields (e.g. a personalised greeting or favourite flag) that must stay
fresh for a logged-in visitor, while everyone else can safely share one
cached response:

.. code-block:: php

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={
     *             "readCondition"="!user.isLoggedIn()",
     *             "writeCondition"="!user.isLoggedIn()"
     *         }
     *     }
     * )
     */

``user`` here is not a built-in t3api variable - it is a project-provided
expression variable, registered via a TYPO3 expression-language provider for
the ``t3api`` namespace (see :ref:`customization_expression-language`), the
same one used in ``security`` expressions. With both conditions in place, a
logged-in visitor's request is neither served from, nor stored in, the
cache - it always runs the operation fresh - while anonymous visitors share
the cached entry.

**Refresh/warm-up** - reusing the ``readCondition`` from the mechanism above,
a scheduler-driven job can keep an entry warm ahead of real traffic by
periodically requesting it with ``?refresh=1``: the request bypasses the
cache lookup but its result is still stored (``writeCondition`` is left
empty), so the *next* real visitor - without ``?refresh=1`` - gets an
already-fresh cache hit instead of triggering the rebuild themselves:

.. code-block:: php

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={"readCondition"="request.query.get('refresh') == null"}
     *     }
     * )
     */

**Debug bypass** - a request investigated with ``?debug=1`` should reflect
the current state exactly, and must not pollute the cache with a
debug-flavoured response for later, real requests:

.. code-block:: php

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={
     *             "readCondition"="request.query.get('debug') == null",
     *             "writeCondition"="request.query.get('debug') == null"
     *         }
     *     }
     * )
     */

A request carrying ``?debug=1`` neither reads from, nor writes to, the
cache - it is entirely bypassed for the duration of that request.

Access control
==============

Operations that declare a ``security`` expression **are** cached. ``security``
is evaluated on every request before a response may be served from - or written
to - cache, by the same ``OperationAccessChecker`` the (uncached) operation
handler itself uses. A denied check throws exactly the exception the handler
would have thrown, regardless of whether the request would otherwise have
been a cache hit or a miss: a cache hit never bypasses ``security``.

For item operations whose ``security`` expression references the loaded
entity via the ``object`` variable, evaluating the expression without an
entity fails; in that case the response cache path loads the entity - the
same way the item operation handler does - and evaluates the check again
with ``object`` in scope. This is logged as a warning, since it means the
entity is now loaded on *every* request to that operation, cached or not -
consider avoiding ``object`` access in ``security`` for cached item
operations, or disabling caching for it, if this cost is unacceptable. If
the entity cannot be found, the request falls through to the normal
(uncached) handler path, which produces its usual not-found response.

``security_post_denormalize`` is never evaluated on the cache path: it only
applies to write operations (POST/PUT/PATCH), which the response cache does
not handle in the first place (only ``GET`` requests are cacheable at all).

A request bypasses the cache entirely whenever it targets a filter (see
:ref:`filtering`) whose strategy carries a non-empty ``condition`` (evaluated
by ``FilterAccessChecker``) - e.g. a strategy declared as
``{"name": "partial", "condition": "is_granted('ROLE_ADMIN')"}`` - since that
check is not re-evaluated on the cache path.

Tags and invalidation
======================

Invalidation is automatic on record create/update/delete through the TYPO3
backend (DataHandler) and through Extbase persistence. "Flush frontend
caches" and "Flush all caches" clear the response cache too (see "Storage
backend" below for the cache group this relies on).

Tags come from three sources: the resource's own ``<table>`` tag; a scope
tag - ``<table>--collection`` on a collection response, ``<table>--single``
on an item response; and per-record ``<table>_<uid>`` tags. The ``<table>``
and scope tags are both seeded before serialization runs and are therefore
present on every response regardless of what it actually contains
(including an empty collection); the per-record tags are added for the
Extbase entities actually serialized into the response, including nested
relations. A resource whose ``entity`` is not an ``AbstractDomainObject``
subclass (e.g. a future DTO-based resource) has nothing to seed the
resource-level ``<table>``/scope tags with, so such a response carries only
the per-record tags of whatever entities it actually happens to contain (if
any, via nested relations); with no entities in the response at all it
carries no tags and only expires by ``lifetime``.

.. note::
   The double-hyphen in the scope tags is deliberate: a real TYPO3/Extbase
   table name never contains a hyphen, so ``collection``/``single`` as a
   table-name suffix could in principle collide with a genuine, unrelated
   table (e.g. ``tx_shop_products_collection``) if the ``_`` separator used
   by ``<table>_<uid>`` were reused here instead.

.. note::
   The bare ``<table>`` tag is deliberately never the *automatic* flush
   target of the per-record invalidation below - only the more precise
   ``<table>--collection``/``<table>--single`` and ``<table>_<uid>`` tags
   are, so that invalidating one record's collection membership never
   evicts an unrelated, still-correct cached item response on the same
   table. The ``<table>`` tag remains available as a manual/administrative
   "flush everything cached for this table, items and collections alike"
   lever (``$cache->flushByTags([$table])``), and is exactly what
   ``t3api:cache:invalidate-expired`` needs and keeps using unmodified (see
   "Scheduler command" below) - that command can only ever detect that *a
   table* crossed a ``starttime``/``endtime`` threshold, never which
   specific uids did, so it has no more precise tag to flush by.

.. warning::
   Automatic tags only see entities that are *serialized into* the response -
   a virtual property or custom serializer handler that returns the related
   entity itself (typed so JMS serializes it as a nested object) is tagged
   the same as any other relation. Only a virtual property or handler that
   returns a **computed value derived from** a related record - e.g.
   ``authorName`` built from a related author record, rather than the author
   itself - keeps that record outside the serialized object graph entirely:
   an invisible dependency, since editing that related record will NOT
   invalidate the cached response. Declare such dependencies explicitly on
   the operation, via ``tags`` (e.g. the related table name), ``tagExpressions``
   (e.g. a tag that does not depend on which entity ended up in the response)
   or ``memberTagExpressions`` (e.g. a per-record tag built from the
   serialized entity's relation, such as the ``author_<uid>`` example
   below), see :ref:`response-cache-declarative-tags`.

Invalidation is granular and driven by what could actually be stale:

- A plain **update** to an already-persisted record flushes only that
  record's own ``<table>_<uid>`` tag. Every cached collection that already
  contains the record was tagged with that same uid when it was serialized
  (see "Cache key" above), so it still refreshes correctly - a
  ``<table>--collection`` flush would otherwise also evict unrelated
  collections that never contained the record at all.
- An **update** that changes visibility or placement - any of the table's
  TCA ``enablecolumns`` fields (e.g. ``disabled``, ``starttime``,
  ``endtime``, ``fe_group``), its ``pid``, or its manual sorting field - is
  treated like create/move/undelete below and flushes both the
  ``<table>--collection`` tag and the record's own ``<table>_<uid>`` tag
  instead, since such a change (e.g. un-hiding a record) can move it into a
  collection that did not contain it before, and can also make the
  record's own previously-cached responses (e.g. its item response) stale.
- **Create**, **move** and **undelete** flush the same
  ``<table>--collection`` tag and the record's own ``<table>_<uid>`` tag,
  since any of them can change which collections the record belongs to - a
  cached collection that never contained the record carries no tag that a
  uid-only flush could match. The record's own uid tag was never attached
  to any cached entry before a create, so including it here is harmless,
  not incorrect.
- **Delete** also flushes ``<table>--collection`` and the record's own
  ``<table>_<uid>`` tag: the uid tag is implied dead along with the record,
  and ``<table>--collection`` already covers every collection that contained
  it.

This is driven by the TYPO3 DataHandler hooks and by three granular Extbase
persistence events - entity added, updated and removed - each of which only
fires after a real database write, so walking the persistence graph without
any actual change does not trigger a needless flush.

Both paths dispatch the public PSR-14 events
``SourceBroker\T3api\Event\RecordUpdatedEvent`` and
``SourceBroker\T3api\Event\RecordDeletedEvent``, which double as the API for
invalidating the cache manually - e.g. after a write that bypasses
DataHandler and Extbase persistence (see "Limitations" below). Dispatch
``RecordUpdatedEvent`` with ``changesCollectionMembership`` set to ``true``
when the change may alter which collections the record belongs to (mirrors
create/move/undelete above); leave it ``false`` (the default) for a plain
field update to an already-persisted record.

.. _response-cache-declarative-tags:

Declarative extra tags
========================

The automatic invalidation above covers records read or written through
DataHandler or Extbase persistence. The ``cache`` block's ``tags`` key
covers a response whose staleness is driven by *another* table. It cascades
per operation exactly like every other ``cache`` key (see "Per-operation
override" above).

``tags`` - extra literal tags added to a *stored* entry, on top of the
automatic content tags and the resource table seed. Plain strings only - a
tag whose value depends on the matched route parameters, the current user,
or anything else that is not the same for every request belongs in
``tagExpressions`` instead, see :ref:`response-cache-tag-expressions` below.
Typical use for a literal ``tags`` entry: a DTO endpoint whose data is
actually sourced from other tables - reusing those tables' tags lets the
existing DataHandler/Extbase invalidation for them invalidate this endpoint
too, at zero extra invalidation code:

.. code-block:: php

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={
     *             "tags"={"pages", "tt_content"}
     *         }
     *     }
     * )
     */
    class StaticPage
    {
    }

A ``StaticPage`` GET response built from ``pages``/``tt_content`` data has no
table of its own for the automatic resource-table seed (see "Tags and
invalidation" above) to tag it with - declaring ``tags`` here makes the
existing ``pages``/``tt_content`` DataHandler flush reach it anyway.

.. _response-cache-tag-expressions:

Tag expressions
------------------

``tags`` above covers a *fixed* relationship: a literal tag name, the same
regardless of who is asking or which route parameters matched. Three
expression-based sources cover everything dynamic instead: a tag whose value
depends on the matched route parameters (via ``route``), the current user, a
request header, a TYPO3 Context aspect, the top-level result entity/entities
(via ``object``), or anything else visible to a Symfony expression:

- ``cache.tagExpressions`` - evaluated once per stored response, before the
  operation handler's result is known - so it never has ``object``, and is
  evaluated even for an empty collection. The right place for a tag that does
  not depend on which entities ended up in the response, e.g. the current
  user (see the ``fe_user_<uid>`` example below).
- ``cache.memberTagExpressions`` - evaluated once per *top-level result
  entity* instead of once per response: once for an item GET's single result
  entity, once per collection GET member (the resulting tags unioned and
  deduplicated across all members), zero times for an empty collection. The
  only one of the three with ``object``, bound to that one entity. The right
  place for a tag that *does* depend on which entities ended up in the
  response, e.g. each member's related author (see the ``author_<uid>``
  example below) - something a single per-response ``tagExpressions``
  evaluation cannot express for a collection.
- ``cacheInvalidation.tagExpressions`` - evaluated once after a successful
  write, with ``object`` bound to the written entity (``null`` on a
  ``DELETE``, or when a custom handler returns nothing, see "DELETE and other
  object-less writes" below).

All three are evaluated by the same expression engine and variable set as
``readCondition``/``writeCondition``/``identifierExpressions`` (see
:ref:`response-cache-conditions`), plus ``object`` where noted above - see
"Availability of ``object``" below for the full picture across every response
cache expression on this page.

The canonical route-driven case is a write handler that bypasses
DataHandler/Extbase persistence and must flush a tag built from a matched
route parameter (see :ref:`response-cache-invalidation` below for the full
worked example):

.. code-block:: php

    /**
     * @ApiResource(
     *     itemOperations={
     *         "toggleFavorite"={
     *             "method"="POST",
     *             "path"="/recipes/{recipeId}/favorite",
     *             "attributes"={
     *                 "cacheInvalidation"={
     *                     "tagExpressions"={"'tx_myext_domain_model_recipe_' ~ route['recipeId']"}
     *                 }
     *             }
     *         }
     *     }
     * )
     */

``route['recipeId']`` reads the matched ``{recipeId}`` route parameter
directly - the same ``route`` variable available to every other cache
expression (see :ref:`response-cache-conditions`).

The pair below tags every cached ``GET`` response with the current user, and
flushes exactly that user's entries once they write something - each cached
response stays scoped to the visitor who produced it, and a write only ever
invalidates its own author's cache, never anyone else's:

.. code-block:: php

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={
     *             "tagExpressions"={
     *                 "user.isLoggedIn() ? 'fe_user_' ~ user.getUid() : ''"
     *             }
     *         },
     *         "cacheInvalidation"={
     *             "tagExpressions"={"'fe_user_' ~ user.getUid()"}
     *         }
     *     }
     * )
     */

``user`` here is the same project-provided expression variable used in
"Examples" above (see :ref:`response-cache-conditions`), not a built-in one.
An anonymous visitor's ``GET`` response evaluates the ``cache`` expression to
an empty string - the documented idiom for "no tag from this expression":
skipped silently rather than added as a literal empty tag - so anonymous
traffic carries only the usual automatic content tags. A logged-in visitor's
response additionally carries ``fe_user_<uid>``; once that same user performs
a write (any non-``GET`` operation on the resource), the
``cacheInvalidation`` expression flushes exactly that tag - every cached
response the user has ever produced, and nothing belonging to anyone else.

``memberTagExpressions`` combines with ``tagExpressions`` on the same
``cache`` block, each covering what the other cannot. Extending the example
above, a ``Book`` collection also tags each stored entry with every embedded
book's author - something ``tagExpressions``, evaluated once for the whole
response, has no way to express for more than one book at a time:

.. code-block:: php

    /**
     * @ApiResource(
     *     attributes={
     *         "cache"={
     *             "tagExpressions"={"'fe_user_' ~ user.getUid()"},
     *             "memberTagExpressions"={"'author_' ~ object.getAuthor().getUid()"}
     *         }
     *     }
     * )
     */

``tagExpressions`` still evaluates once per response, exactly as above -
``user`` does not change depending on which book is being looked at.
``memberTagExpressions`` evaluates once per book in the collection, each time
with ``object`` bound to that one ``Book``: a two-book response ends up
tagged with (among others) both ``author_1`` and ``author_2``. Editing either
embedded author now invalidates exactly the collections that embedded them -
the same granularity the automatic nested-relation tagging already gives
editing the *book* record itself (see "Tags and invalidation" above), now
available for a *derived* per-member tag too.

.. note::
   An empty collection has no member to evaluate ``memberTagExpressions``
   against - zero evaluations, zero tags from this source, same as if the
   setting were not declared at all. A tag that does not depend on which
   entities the response contains - including a user/request-scoped one like
   the ``fe_user_<uid>`` example above, which must still apply to an empty
   response - belongs in ``tagExpressions`` instead, which is always
   evaluated exactly once regardless of how many entities (zero or more) end
   up in the response.

The two evaluated-once-per-response sides fail differently, deliberately.
``cache.tagExpressions`` is evaluated at store time and is fail-closed: an
expression that cannot be evaluated, or that produces a value that is not a
valid cache tag, means the would-be entry's invalidation contract cannot be
guaranteed, so the entry is not stored at all (logged as an error) - the
response is still returned to the caller, only the cache write is skipped.
``cache.memberTagExpressions`` follows the exact same fail-closed policy, but
per member: a single member's expression failure is enough to withhold the
*entire* entry, on the same reasoning - a partial per-member tag set could
not guarantee the whole entry's invalidation contract either.
``cacheInvalidation.tagExpressions`` is evaluated after a successful write
and is fail-soft: the write already succeeded and must not break because of
a caching side effect, so a failing expression there merely skips that one
tag (logged as a warning) while every other declared tag - static or
expression-derived - still flushes.

DELETE and other object-less writes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``object`` variable ``cacheInvalidation.tagExpressions`` sees is ``null``
whenever the write operation has no entity to offer - a ``DELETE`` (the
object was just removed), or a custom operation handler that returns
nothing. Referencing a property or method on a ``null`` ``object`` throws,
which - being on the fail-soft side - only skips that one tag rather than
the whole flush, but a tag that is supposed to fire on every write
(create/update *and* delete) needs the null-safe idiom instead:

.. code-block:: php

    /**
     * @ApiResource(
     *     itemOperations={
     *         "delete"={
     *             "attributes"={
     *                 "cacheInvalidation"={
     *                     "tagExpressions"={
     *                         "object != null ? 'tx_myext_domain_model_recipe_' ~ object.getUid() : ''"
     *                     }
     *                 }
     *             }
     *         }
     *     }
     * )
     */

A ``PUT``/``PATCH``/``POST`` on this same operation set (sharing the
resource-level ``cacheInvalidation`` block, or declaring the same expression
on their own) evaluates the ``object != null`` branch and flushes the tag as
usual; the ``DELETE`` above evaluates the ``: ''`` branch - the documented
"no tag from this expression" idiom - rather than erroring. For a tag that
does not need the entity at all (e.g. the ``route['recipeId']`` example
above), this is not a concern - it works identically for every HTTP method.

.. _response-cache-object-availability:

Availability of ``object``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

``object`` is deliberately not part of the shared variable set every
response cache expression sees (see :ref:`response-cache-conditions`) - most
of the surface below runs *before* the operation handler produces (or, on a
cache hit, never produces) any entity at all. The two sources that do
introduce it bind it to different things, at different points in the
request lifecycle:

.. list-table::
   :header-rows: 1
   :widths: 30 70

   * - Evaluation
     - ``object``
   * - ``readCondition``, ``identifierExpressions``
     - never (pre-execution; a cache HIT never has an object at all)
   * - ``writeCondition``
     - never (per-response; kept object-free by design)
   * - ``cache.tagExpressions``
     - never (per-response; evaluated once, including for an empty
       collection)
   * - ``cache.memberTagExpressions``
     - the top-level result entity - item GET: the single result entity (one
       evaluation); collection GET: each collection member (one evaluation
       per member, results unioned and deduplicated); empty collection: zero
       evaluations
   * - ``cacheInvalidation.tagExpressions``
     - the persisted entity of the write operation; ``null`` on a ``DELETE``
       (or when a custom handler returns nothing), see "DELETE and other
       object-less writes" above

Referencing ``object`` anywhere it is "never" available is an ordinary
expression evaluation error, handled by that side's usual failure policy -
fail-closed for ``readCondition``/``writeCondition``/``identifierExpressions``/
``cache.tagExpressions`` (see :ref:`response-cache-conditions` and above),
fail-soft for ``cacheInvalidation.tagExpressions`` (see above).

.. _response-cache-invalidation:

Write-triggered invalidation
==============================

A write that bypasses DataHandler/Extbase persistence entirely invalidates
nothing automatically (see "Limitations" below). The ``cacheInvalidation``
attributes block - a sibling of ``cache``, not one of its keys - covers this
case: it flushes declared tags once a non-``GET`` operation completes
successfully.

.. code-block:: php

    use SourceBroker\T3api\Annotation\ApiResource;

    /**
     * @ApiResource(
     *     itemOperations={
     *         "toggleFavorite"={
     *             "method"="POST",
     *             "path"="/recipes/{recipeId}/favorite",
     *             "attributes"={
     *                 "cacheInvalidation"={
     *                     "tagExpressions"={"'tx_myext_domain_model_recipe_' ~ route['recipeId']"}
     *                 }
     *             }
     *         }
     *     }
     * )
     */
    class Recipe extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
    }

The favorites-toggle handler writes with raw SQL (or any other means
bypassing DataHandler/Extbase persistence); once it returns successfully,
``cacheInvalidation`` flushes exactly the affected recipe's tag, built from
``route['recipeId']`` - the matched ``{recipeId}`` route parameter (see
:ref:`response-cache-tag-expressions` above).

``cacheInvalidation`` accepts two keys:

- ``tags`` - literal tags flushed after the operation completes successfully
  (default: none). Plain strings only, see :ref:`response-cache-declarative-tags`
  above.
- ``tagExpressions`` - Symfony expressions whose non-empty string results are
  each flushed alongside ``tags`` (default: none). See
  :ref:`response-cache-tag-expressions`.

Like ``cache``, ``cacheInvalidation`` cascades from resource level to every
operation (see "Per-operation override" above): a resource-level
``cacheInvalidation`` block flushes its ``tags``/``tagExpressions`` after
*any* write operation on the resource succeeds, unless a specific operation
overrides them with its own block.

It fires only after a **successfully completed** non-``GET`` operation - a
null response (e.g. a DELETE) or a 2xx status both count as success; an
exception raised by the operation propagates without flushing anything,
since a failed write must not invalidate a cache entry that never went
stale. A ``GET`` operation never flushes, even when a resource-level
``cacheInvalidation`` block cascades onto it - such a block is meant for the
resource's write operations, and reading never invalidates anything.

.. _response-cache-invalidation-fail-fast:

Fail-fast validation
----------------------

``cache`` and ``cacheInvalidation`` are mutually exclusive per operation, by
HTTP method. Declaring ``cache`` explicitly on a non-``GET`` operation raises
an ``InvalidArgumentException`` (naming the operation and entity) as soon as
routes are built for the resource, pointing to ``cacheInvalidation`` for
write-triggered invalidation instead. Declaring ``cacheInvalidation``
explicitly on a ``GET`` operation raises the mirror exception, pointing to
``cache`` for read caching instead. Both checks only apply to an *explicit*
per-operation ``attributes`` block - a block inherited from the resource
level (e.g. a resource-level ``cacheInvalidation`` reaching a ``GET``
operation) is a legitimate cascade and never throws.

Tag validation
===============

Every ``tags`` entry (both ``cache`` and ``cacheInvalidation``) and every
non-empty ``tagExpressions``/``memberTagExpressions`` result is validated
against the TYPO3 Caching Framework's allowed tag charset
(``FrontendInterface::PATTERN_TAG`` - letters, digits, ``_``, ``%``, ``-``,
``&``, 1 to 250 characters) before it ever reaches the cache backend. A
literal ``tags`` entry that fails this check is skipped with a logged warning
instead of failing the request - see :ref:`response-cache-tag-expressions`
for how a failing ``tagExpressions``/``memberTagExpressions`` result is
handled (fail-closed on the ``cache`` side, fail-soft on
``cacheInvalidation``).

Scheduler command
==================

For records that appear or disappear over time via ``starttime``/``endtime``,
schedule the CLI command:

.. code-block:: bash

    vendor/bin/typo3 t3api:cache:invalidate-expired

On each run it checks every TCA table that declares a ``starttime`` or
``endtime`` enable-field - not just tables backing a cacheable resource,
since a cached response can embed related records from other tables (for
example an author nested in a book response) that need the same check -
for whether a start or end time passed since the command's last run, and
flushes that table's ``<table>`` tag if so. The per-table last-run
timestamp is only persisted to the TYPO3 Registry once that table's check
(and the flush it may trigger) has completed without error, so a failure
does not silently lose an invalidation window - the next run simply
re-checks it. A table whose TCA declares neither the ``starttime`` nor the
``endtime`` enable-field is skipped silently: never flushed, no output, no
error.

TYPO3 workspaces are not supported by the response cache invalidation,
neither by this command nor by the record-change event listeners.

.. _response-cache-hit-semantics:

Cache hit semantics
====================

A cache hit is served without running the operation handler, database
queries, serialization, or any PSR-14 operation events (e.g.
``AfterProcessOperationEvent``). The ``security`` check and the
``readCondition`` expression are the exception: both run on every request,
hit or miss, before the cached entry is looked up - see "Access control" and
"Conditions" above.

What a hit actually saves scales with what serving the request would have
cost otherwise: skipping the database queries and serialization of a small,
simple resource buys little, while a large or deeply nested one benefits far
more. Either way, the request still pays the full TYPO3 bootstrap (site and
language resolution, the DI container, TCA) - the response cache is reached
from inside frontend request handling, not in front of it, so a hit cannot
shortcut that fixed baseline.

.. _response-cache-verifying:

Verifying and debugging
=========================

**Debug headers** - in a development context (``TYPO3_CONTEXT=Development``
or any of its subcontexts, e.g. ``Development/Local`` - see
``Environment::getContext()->isDevelopment()``), every t3api response
carries ``X-T3api-Cache*`` headers describing what this exact request did:

- ``X-T3api-Cache: hit|miss|bypass`` - ``hit`` when the response was served
  from a stored entry, ``miss`` when the lookup ran but found nothing,
  ``bypass`` when ``readCondition`` evaluated falsy and the lookup never
  ran. Present only on a cacheable request (a ``GET`` whose cache entry
  identifier could be built) - a request with nothing to cache in the first
  place (a non-``GET`` operation, a disabled ``cache`` block, a
  secured-filter bypass) carries none of it.
- ``X-T3api-Cache-Identifier`` - the cache entry identifier: present on a
  ``hit``, and on a ``miss``/``bypass`` whenever the response was actually
  stored.
- ``X-T3api-Cache-Tags`` - the full stored tag set (content tags, the
  resource table seed, declarative ``tags``, ``tagExpressions`` and
  ``memberTagExpressions``, comma-separated), present only when the
  response was actually stored.
- ``X-T3api-Cache-Flushed-Tags`` - the tags a ``cacheInvalidation`` flush
  actually flushed, present whenever that flush ran with at least one tag.

These headers are hard off outside a development context - they disclose
cache internals (entry identifiers, tag names) that must never reach a
production response. A ``bypass`` still allows storing: the refresh/warm-up
pattern (see "Examples" above) deliberately skips the lookup while still
writing a fresh entry, so seeing ``X-T3api-Cache: bypass`` alongside a
present ``X-T3api-Cache-Tags`` header is exactly that happening, not a bug.

**Inspecting the cache** - with the default database backend (see "Storage
backend" below), every stored entry is a row in the ``cache_t3api_response``
table: ``identifier`` is the md5 hash described in "Cache key" above,
``content`` the stored response body, and ``expires`` the unix timestamp it
expires at. Its tags live in ``cache_t3api_response_tags``, one row per
``(identifier, tag)`` pair - ``SELECT tag FROM cache_t3api_response_tags
WHERE identifier = '<hash>'`` lists everything that invalidates a given
entry. No row for the identifier you expect means the request was never
stored in the first place (see the checklist below) or the entry already
expired/was flushed; both look identical from a single lookup, so to tell
them apart, request the same URL twice in a row - if neither attempt leaves
a row, the response was never being stored at all.

**Testing from the command line** - a plain ``curl`` request exercises the
same path a real client does, custom authentication header included:

.. code-block:: bash

    curl -i -H "Authorization: Bearer <token>" "https://example.com/_api/books"

Run it twice: the first response is a MISS (assembled fresh), the second -
within ``lifetime``, with the same declared pagination/filter parameters and
the same result for every declared ``identifierExpressions`` entry - is a
HIT served from the stored entry. An undeclared query parameter (e.g.
``?debugMe=1`` when no such filter exists) neither creates a new entry nor
bypasses the existing one - see "Cache key" above.

**Why is my endpoint not cached?** - work through this list:

- No ``cache`` block resolves to enabled for this exact operation - the
  per-operation override wins in either direction (see "Per-operation
  override" above), so check both the resource and the specific operation.
- The request is not ``GET`` - only ``GET`` operations are ever read from,
  or written to, the response cache.
- The response status is not exactly ``200`` - a response with any other
  status (e.g. a redirect or an empty ``204``) is never stored, even for an
  otherwise-cacheable operation.
- ``readCondition``/``writeCondition`` evaluates falsy, or fails to
  evaluate - both fail closed. Look for ``t3api response cache condition
  evaluation failed`` in the logs (see below).
- Any ``identifierExpressions`` entry fails to evaluate - unlike a failed
  condition, this disables caching for the request entirely (no read, no
  write). Look for ``t3api response cache identifier expression evaluation
  failed``.
- Any ``cache.tagExpressions``/``cache.memberTagExpressions`` entry fails to
  evaluate, or produces an invalid tag - fail-closed, same as
  ``identifierExpressions``: the entry is not stored at all, even when only
  one collection member's ``memberTagExpressions`` evaluation was the one
  that failed (see :ref:`response-cache-tag-expressions`). Look for ``t3api
  response cache tag expression evaluation failed`` / ``... produced an
  invalid tag`` (both logged as errors).
- The request targets a filter whose strategy declares a non-empty
  ``condition`` - such a request bypasses the cache entirely, since that
  check is not re-evaluated on the cache path (see "Access control" above).
- On an item operation, the ``security`` expression references ``object``
  and the entity cannot be loaded (e.g. a non-existent id) - the request
  falls through to the normal, uncached handler path (see "Access control"
  above).
- The resource has no ``AbstractDomainObject`` entity (a synthetic/DTO
  resource, typically served by a custom operation handler) - it is cached
  and expires by ``lifetime`` like any other resource, but only invalidates
  automatically for the entities it happens to serialize. Add ``cache``'s
  ``tags`` to tie its ``GET`` response to the tables it actually reads from,
  and ``cacheInvalidation``'s ``tags`` to flush it after a write that
  bypasses DataHandler/Extbase persistence (see "Tags and invalidation",
  :ref:`response-cache-declarative-tags` and
  :ref:`response-cache-invalidation` above).
- ``cache`` was declared explicitly on a non-``GET`` operation, or
  ``cacheInvalidation`` explicitly on a ``GET`` operation - this raises an
  ``InvalidArgumentException`` as soon as routes are built for the resource,
  it is never silently ignored (see
  :ref:`response-cache-invalidation-fail-fast` above).

**Where to look in the logs** - every response-cache failure is logged via
TYPO3's standard PSR-3 logging at warning level or above, which by default
reaches TYPO3's file log writer (``var/log/typo3_<hash>.log`` in a standard
installation) alongside the rest of the site's log records. Search for:

- ``t3api response cache condition evaluation failed`` - a ``readCondition``/
  ``writeCondition`` error.
- ``t3api response cache identifier expression evaluation failed`` - an
  ``identifierExpressions`` entry error.
- ``t3api response cache read failed`` / ``... write failed`` / ``... flush
  failed`` - the cache backend itself errored.
- ``t3api response cache tag is invalid`` - a ``cache``/``cacheInvalidation``
  ``tags`` entry failed the tag charset check and was skipped (see "Tag
  validation" above).
- ``t3api response cache tag expression evaluation failed`` / ``... produced
  an invalid tag`` - a ``tagExpressions``/``memberTagExpressions`` entry
  error, logged as an error (entry not stored) on the ``cache`` side and a
  warning (tag skipped, entry still flushed) on the ``cacheInvalidation``
  side, see :ref:`response-cache-tag-expressions`.
- ``t3api response cache memberTagExpressions skipped a result entry that is
  not an AbstractDomainObject`` - a collection member (or an item GET's
  result) was not an Extbase entity, so ``memberTagExpressions`` was never
  evaluated against it - logged as a warning, does not block the store (see
  :ref:`response-cache-tag-expressions`).
- ``Response cache security check for operation`` - an item operation's
  ``security`` expression needed the loaded entity (see "Access control"
  above).

**Interaction with other caching layers** - t3api requests deliberately
never populate, and are never served from, TYPO3's own page cache: t3api
randomises the page cache identifier for every request it handles, so these
responses stay out of it. The response cache described in this chapter is
therefore the only caching layer t3api adds inside TYPO3.

T3api does not set any HTTP caching headers (``Cache-Control``, ``Expires``,
``ETag``) on its responses - the development-only ``X-T3api-Cache*`` headers
above are diagnostics, not caching directives, and carry no instruction any
proxy, CDN or browser would act on. A reverse proxy or CDN placed in front
of TYPO3 is unaware of this feature entirely: it neither shares its
invalidation (a record change purges only the response cache described
here, never an external cache) nor reacts to anything t3api emits. Bypass
such a layer for the API path, or drive its invalidation independently, if
one sits in front of your installation.

Limitations
===========

Three limitations remain:

- A plain update that makes a record newly match a collection's filters
  (e.g. a field change that satisfies a filter condition it previously
  failed) does not refresh a collection that did not already contain the
  record - the update flush above only reaches the record's own
  ``<table>_<uid>`` tag, which such a collection was never tagged with. It
  simply expires with the entry's ``lifetime``.
- Writes that bypass DataHandler and Extbase persistence (raw SQL, direct
  imports) trigger no invalidation. Flush the ``t3api_response`` cache
  manually, or dispatch ``RecordUpdatedEvent``/``RecordDeletedEvent``
  yourself for the affected records (see "Tags and invalidation" above).
- The Extbase persistence path cannot detect *which* fields changed on an
  update, unlike the DataHandler hook - it always dispatches
  ``RecordUpdatedEvent`` with ``changesCollectionMembership`` ``false``. An
  Extbase update that un-hides a record (or otherwise changes its
  visibility or placement) therefore only refreshes responses that already
  contained the record - a collection that did not contain it before stays
  stale until ``lifetime`` expires. The same change made through the TYPO3
  backend does not have this gap (see "Tags and invalidation" above).

Storage backend
================

The cache is registered as ``t3api_response`` with TYPO3 defaults (database
backend). Swap the backend in ``config/system/settings.php``, e.g. to Redis:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3api_response']['backend']
        = \TYPO3\CMS\Core\Cache\Backend\RedisBackend::class;

By default the cache is placed in both the ``all`` and ``pages`` cache
groups, so the backend's "Flush frontend caches" action (which flushes the
``pages`` group) clears it alongside the page cache, and "Flush all caches"
clears it too (see "Tags and invalidation" above) - on top of the tag-based
invalidation that already keeps entries fresh as records change. T3api only
sets this default and never overrides an existing ``t3api_response``
configuration, so a project that wants API responses decoupled from the
frontend flush (relying on tag-based invalidation and ``lifetime`` alone)
can opt out the same way:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3api_response']['groups']
        = ['all'];

Every distinct combination of declared filter and pagination parameters
produces its own cache entry (see "Cache key" above), so a heavily
filterable/orderable resource has an effectively unbounded key space. Size
the backend - or configure its eviction policy - accordingly, and keep
``lifetime`` tight enough to bound growth.

To disable the response cache outright - typically for local development,
where stale output while iterating on a resource is more of a nuisance than
a saving - swap the backend to TYPO3's ``NullBackend`` the same way:

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3api_response']['backend']
        = \TYPO3\CMS\Core\Cache\Backend\NullBackend::class;

Every store becomes a no-op and every read a guaranteed miss, so the
operation handler always runs.
