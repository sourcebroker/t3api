.. _integration:

============
Integration
============

Integration with other extensions
====================================

Models of other extensions (e.g. ``EXT:news``) can be exposed in the API without changing their code:

#. Create a model in your extension which extends the model of the other extension and add ``@T3api\ApiResource`` annotation to it.
#. Map your model to the table of the original model with Extbase persistence configuration (``Configuration/Extbase/Persistence/Classes.php``). If your class should also be returned by the original repositories and relations, register it as ``subclasses`` / ``recordType`` as described in TYPO3 documentation.
#. Configure serialization of properties of the original class with :ref:`YAML metadata <serialization_yaml-metadata>` - annotations can not be added to classes you do not own.

News extension - Example integration
======================================

Testing instance of t3api contains complete integration with ``EXT:news`` - extension ``t3apinews`` (see ``.ddev/test/files/src/t3apinews`` in t3api repository). It is a good starting point for your own integration:

- ``Classes/Domain/Model/News.php``, ``Category.php``, ``Tag.php`` - API resources extending ``EXT:news`` models, with operations and :ref:`filters <filtering>`.
- ``Classes/Domain/Model/File.php`` - resource used for :ref:`file upload <handling_file_upload>`.
- ``Configuration/Extbase/Persistence/Classes.php`` - mapping of the models to ``EXT:news`` tables.
- ``Resources/Private/Serializer/GeorgRinger.News/*.yml`` - :ref:`YAML metadata <serialization_yaml-metadata>` with serialization groups for ``EXT:news`` models, registered in ``ext_localconf.php``.

.. code-block:: php
   :caption: EXT:t3apinews/Configuration/Extbase/Persistence/Classes.php

   use SourceBroker\T3apinews\Domain\Model\Category;
   use SourceBroker\T3apinews\Domain\Model\News;
   use SourceBroker\T3apinews\Domain\Model\Tag;

   return [
       News::class => [
           'tableName' => 'tx_news_domain_model_news',
       ],
       Tag::class => [
           'tableName' => 'tx_news_domain_model_tag',
       ],
       Category::class => [
           'tableName' => 'sys_category',
       ],
   ];

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | News, categories and tags of ``EXT:news`` exposed by ``t3apinews``:
     | `https://14.t3api.ddev.site/_api/news/news <https://14.t3api.ddev.site/_api/news/news>`__
     | `https://14.t3api.ddev.site/_api/news/categories <https://14.t3api.ddev.site/_api/news/categories>`__
     | `https://14.t3api.ddev.site/_api/news/tags <https://14.t3api.ddev.site/_api/news/tags>`__
     |

Inline output
======================================

If you would like to include API response directly in TYPO3 HTML output (e.g. to avoid waiting for initial request
to the API in your frontend application) you can use ``t3api:inline`` ViewHelper. It processes the request internally,
without HTTP request, and returns the same JSON which would be returned by the endpoint.

Arguments:

- ``route`` (``string``, required) - path of the endpoint, without API base path, e.g. ``news/news`` or ``news/news/1``.
- ``params`` (``array``) - query params, e.g. filters: ``{search: 'minima', order: {title: 'asc'}}``.
- ``itemsPerPage`` (``int``) - number of items per page (sent as ``itemsPerPage`` param).
- ``page`` (``int``) - page number (sent as ``page`` param).

.. code-block:: html

    <html xmlns:t3api="http://typo3.org/ns/SourceBroker/T3api/ViewHelpers"
          data-namespace-typo3-fluid="true">

        <script type="application/json" id="initial-news">
            <f:format.raw><t3api:inline route="news/news" params="{istopnews: 'true'}" itemsPerPage="10" /></f:format.raw>
        </script>

    </html>

.. important::

   Output of the ViewHelper is HTML-escaped by Fluid, so wrap it in ``<f:format.raw>`` when it is placed inside ``<script>``.
   ``itemsPerPage`` and ``page`` arguments use global names of the params (``itemsPerPage`` and ``page``). If you changed
   the names for a resource, pass them in ``params`` instead. ``itemsPerPage`` is respected only if
   ``pagination_client_items_per_page`` is enabled (see :ref:`pagination`).
