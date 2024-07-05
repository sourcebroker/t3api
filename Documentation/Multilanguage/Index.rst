.. _multilanguage:

==============
Multilanguage
==============

Yes, **t3api supports multilanguage applications**! If you would like to do request in language other than default you just need to add request header ``X-Locale`` with value set to identifier of expected language. Identifier means ``languageId`` from your site's ``config.yaml``.

Here is an example configuration of multilanguage page.

.. code-block:: php

   languages:
     -
       title: English
       enabled: true
       languageId: '0'
       base: /
       typo3Language: default
       locale: en_US.UTF-8
       iso-639-1: en
       navigationTitle: ''
       hreflang: en-US
       direction: ltr
       flag: en-us-gb
     -
       title: German
       enabled: true
       languageId: '1'
       base: /de/
       typo3Language: de
       locale: de_DE.UTF-8
       iso-639-1: de
       navigationTitle: ''
       hreflang: de-DE
       direction: ltr
       fallbackType: strict
       flag: de

According to example above: Sending ``X-Locale: 0`` or not sending ``X-Locale`` header at all means that it will be processed in English. If request would include header ``X-Locale: 1`` then it will be processed in German.

.. important::
   t3api **respects** `fallbackType <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SiteHandling/AddLanguages.html#fallbacktype>`_ set in site's configuration.

It is possible to customize name of the language header inside ``ext_localconf.php``:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader'] = 'My-Header-Name';
