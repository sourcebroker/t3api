.. _cors:

=====================================
Cross-Origin Resource Sharing (CORS)
=====================================

If you are facing issues while requesting API from the browser and errors in the console looks similar to the one below, you need to setup CORS policy for your API.

.. warning::

   XMLHttpRequest at 'https://example.com' from origin 'https://example.org' has been blocked by CORS policy

We are not going here to explain what the CORS is. There is plenty of websites explaining it and official specification which you should definitely check before configuring CORS for your website. In documentation below you will find only explanation how to configure CORS in t3api.

CORS configuration in t3api is based and (almost) fully compatible with well known Symfony bundle `nelmio/cors-bundle <https://github.com/nelmio/NelmioCorsBundle>`__.

In code below there is a list of all supported configuration options and their default values. If you would like to change these values to custom ones, you should use ``ext_localconf.php`` file of your extension (``typo3conf/ext/my_custom_ext/ext_localconf.php``).

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowCredentials'] = false;
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowOrigin'] = [];
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowHeaders'] = [];
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowMethods'] = [];
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['exposeHeaders'] = [];
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['maxAge'] = 0;
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['originRegex'] = false;

- ``allowCredentials`` (``boolean``) - when set to ``true`` adds response header ``Access-Control-Allow-Credentials`` with value ``true``.

- ``allowOrigin`` (``array`` or ``string``) - can be set to ``*`` to accept any value. Can be an array with string values of allowed origins (e.g. ``['http://www.example.com', 'https://www.example.com']``) or regular expressions when ``originRegex`` is set to ``true`` (e.g. ``['http://.*\.example\.com', 'https://.*\.example\.com']``).

- ``allowHeaders`` (``array`` or ``string``) - can be set to ``*`` to accept any value or an array of strings with allowed headers (e.g. ``['Content-Type']``).

- ``allowMethods`` (``array``) - array of strings with HTTP methods (e.g. ``['GET', 'POST', 'PUT']``).

- ``exposeHeaders`` (``array``) - controls the value of ``Access-Control-Expose-Headers``.

- ``maxAge`` (``int``) - controls the value of ``Access-Control-Max-Age``.

- ``originRegex`` (``boolean``) - indicates if values from ``allowOrigin`` should be treated as regular expression.
