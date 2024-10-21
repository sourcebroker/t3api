.. _multilanguage:

==============
Multilanguage
==============

Yes, **t3api supports multilanguage applications**!

T3api offers two ways you can request multilanguage data:

- standard language prefix
- language header


Standard prefix
+++++++++++++++

First is a standard way, just to prefix your request with language ``base`` as defined
in your site's ``config.yaml``.

- :uri:`https://13.t3api.ddev.site/_api/news/news` will return news in default language.
- :uri:`https://13.t3api.ddev.site/de/_api/news/news` will return news in ``de`` language.


Language header
++++++++++++++++

Second way it to use always the same default lang url and add request header ``X-Locale``
with value set to identifier of expected language. Identifier means ``languageId`` value
from your site's ``config.yaml``.

- :uri:`https://13.t3api.ddev.site/_api/news/news` with header :header:`X-Locale: 0` or no header at all will return news in default language.
- :uri:`https://13.t3api.ddev.site/_api/news/news` with header :header:`X-Locale: 1` will return news in ``de`` language.


Here is an ``config.yaml`` for the above examples:

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


.. important::
   t3api **respects** `fallbackType <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SiteHandling/AddLanguages.html#fallbacktype>`_
   set in site's configuration.

It is possible to customize name of the language header inside ``ext_localconf.php``:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader'] = 'My-Header-Name';
