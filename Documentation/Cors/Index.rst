.. _cors:

=====================================
Cross-Origin Resource Sharing (CORS)
=====================================

If you are facing issues while requesting API from the browser and errors in the console looks similar to the one below, you need to setup CORS policy for your API.

.. warning::

   XMLHttpRequest at 'https://example.com' from origin 'https://example.org' has been blocked by CORS policy

We are not going here to explain what the CORS is. There is plenty of websites explaining it and official specification which you should definitely check before configuring CORS for your website. In documentation below you will find only explanation how to configure CORS in t3api.

CORS configuration in t3api is based and (almost) fully compatible with well known Symfony bundle `nelmio/cors-bundle <https://github.com/nelmio/NelmioCorsBundle>`__.

In code below there is a list of all supported configuration options and their default values. If you would like to change these values to custom ones, you should use ``ext_localconf.php`` file of your extension (``EXT:my_custom_ext/ext_localconf.php``).

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowCredentials'] = false;
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowOrigin'] = [];
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowHeaders'] = [];
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowMethods'] = [];
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['exposeHeaders'] = [];
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['maxAge'] = 0;
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['originRegex'] = false;

- ``allowCredentials`` (``boolean``) - when set to ``true`` adds response header ``Access-Control-Allow-Credentials`` with value ``true``.

- ``allowOrigin`` (``array`` or ``string``) - can be set to ``*`` to accept any value. Can be an array with string values of allowed origins (e.g. ``['http://www.example.com', 'https://www.example.com']``) or regular expressions when ``originRegex`` is set to ``true`` (e.g. ``['https://[a-z0-9-]+\.example\.com']``). Patterns must match the complete origin, including its scheme and any non-default port: t3api wraps each pattern in ``\A(?:...)\z``. Existing patterns with ``^`` and ``$`` remain supported.

- ``allowHeaders`` (``array`` or ``string``) - can be set to ``*`` to accept any value or an array of strings with allowed headers (e.g. ``['Content-Type']``).

- ``allowMethods`` (``array``) - array of strings with HTTP methods (e.g. ``['GET', 'POST', 'PUT']``).

- ``exposeHeaders`` (``array``) - controls the value of ``Access-Control-Expose-Headers``.

- ``maxAge`` (``int`` or ``null``) - controls the value of ``Access-Control-Max-Age``. The default ``0`` disables preflight caching. Set a positive number of seconds to allow caching (e.g. ``600``), or ``null`` to omit the header and let the browser apply its default.

- ``originRegex`` (``boolean``) - indicates if values from ``allowOrigin`` should be treated as regular expression.

Request and response handling
=============================

An allowed cross-origin request receives ``Access-Control-Allow-Origin`` containing its request origin.
This also applies when ``allowOrigin`` is ``*``; t3api reflects the concrete origin so that
``allowCredentials`` can work with the same configuration. Combining ``*`` with ``allowCredentials=true``
allows every origin to read credentialed responses. Use an explicit list of trusted origins for private APIs.
An origin outside the configured policy receives no CORS permission headers.

A preflight is an ``OPTIONS`` request containing both ``Origin`` and ``Access-Control-Request-Method``.
It succeeds only when the origin, requested method and requested headers are allowed.
A disallowed origin returns ``403``; a disallowed method or header returns ``405``. Rejected preflights have
an empty body and no CORS permission headers. Ordinary ``OPTIONS`` without preflight headers returns an empty
successful response for an existing endpoint. Cross-origin ``OPTIONS`` without ``Access-Control-Request-Method``
uses the policy for an actual request.

Header names are compared without regard to case, and surrounding whitespace in header lists is ignored.
With ``allowHeaders`` set to ``*``, t3api returns the concrete requested header names, including
``Authorization``, instead of a literal wildcard. The configured ``simpleHeaders`` list is merged into
``allowHeaders``; by default it includes ``Accept``, ``Accept-Language``, ``Content-Language``, ``Origin``
and the configured language header. Add ``Content-Type`` to ``allowHeaders`` for JSON requests.
Method, allowed-header and exposed-header lists are trimmed and deduplicated; null and empty entries are ignored.
Methods are normalized to uppercase and header names to lowercase.

Responses include ``Vary: Origin``, including requests without an origin and requests from disallowed origins.
Preflight responses also vary by ``Access-Control-Request-Method`` and ``Access-Control-Request-Headers``.
Existing ``Vary`` values are preserved. These headers tell HTTP caches which request headers affect the response,
as described in the `Fetch specification <https://fetch.spec.whatwg.org/#cors-protocol-and-http-caches>`__.
t3api's response cache stores the serialized content; CORS headers are evaluated for each request.

CORS controls whether a browser can read a response. It does not authorize an operation or provide CSRF
protection for writes authenticated with cookies. Configure operation access rules and CSRF protection separately.

Customizing CORS handling
=========================

``CorsService`` provides the shared policy for ``CorsProcessor`` and ``OptionsOperationHandler``.
The protected ``CorsProcessor::isCorsRequest()`` and ``CorsProcessor::isPreflightRequest()`` hooks control only
whether that processor applies headers. They do not control the subsequent operation handler:
``OptionsOperationHandler`` independently uses ``CorsService::isPreflightRequest()``.

To customize preflight detection consistently, supply the same custom ``CorsService`` implementation to both
components through dependency injection, and leave the processor hook delegating to that service.

For release changes, see :ref:`changelog`.
