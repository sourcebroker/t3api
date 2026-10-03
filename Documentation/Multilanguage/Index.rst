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

- :uri:`https://14.t3api.ddev.site/_api/news/news` will return news in default language.
- :uri:`https://14.t3api.ddev.site/de/_api/news/news` will return news in ``de`` language.


Language header
++++++++++++++++

Second way is to use always the same default language URL and add request header ``X-Locale``
with value set to identifier of expected language. Identifier means ``languageId`` value
from your site's ``config.yaml``.

- :uri:`https://14.t3api.ddev.site/_api/news/news` with header :header:`X-Locale: 0` or no header at all will return news in default language.
- :uri:`https://14.t3api.ddev.site/_api/news/news` with header :header:`X-Locale: 1` will return news in ``de`` language.

.. code-block:: bash

   curl -H "X-Locale: 1" https://14.t3api.ddev.site/_api/news/news

Here is a part of ``config.yaml`` used for the examples (site configuration of testing instance):

.. code-block:: yaml

   languages:
     -
       title: English
       enabled: true
       languageId: 0
       base: /
       typo3Language: default
       locale: en_US.UTF-8
       flag: us
     -
       title: German
       enabled: true
       languageId: 1
       base: /de/
       typo3Language: de
       locale: de_DE.UTF-8
       fallbackType: strict
       fallbacks: ''
       flag: de
     -
       title: Polish
       enabled: true
       languageId: 2
       base: /pl/
       typo3Language: pl
       locale: pl_PL.UTF-8
       fallbackType: fallback
       fallbacks: '0'
       flag: pl

.. important::
   t3api **respects** `fallbackType <https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/SiteHandling/AddLanguages.html#fallbacktype>`_
   set in site's configuration.

It is possible to customize name of the language header inside ``ext_localconf.php``:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader'] = 'My-Header-Name';

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | News in default language (6 records):
     | `https://14.t3api.ddev.site/_api/news/news <https://14.t3api.ddev.site/_api/news/news>`__
     |
   * | News in German - ``fallbackType: strict`` so only 2 translated records are returned:
     | `https://14.t3api.ddev.site/de/_api/news/news <https://14.t3api.ddev.site/de/_api/news/news>`__
     |
   * | News in Polish - ``fallbackType: fallback`` so 2 translated records and default language records for the rest are returned:
     | `https://14.t3api.ddev.site/pl/_api/news/news <https://14.t3api.ddev.site/pl/_api/news/news>`__
     |
