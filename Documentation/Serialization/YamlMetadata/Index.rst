.. _serialization_yaml-metadata:

=============
Yaml metadata
=============

Serialization of your own models is usually configured with t3api annotations (``@T3api\Serializer\Groups`` etc.) directly in the model. It is not possible for classes you do not own - e.g. models of 3rd party extensions (``EXT:news``) or TYPO3 core. For such classes serialization metadata can be defined in YAML files.

YAML files are read from directories registered in ``$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs']``. Register your directory in ``ext_localconf.php``:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] = array_merge(
       $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] ?? [],
       [
           'GeorgRinger\News' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('my_ext')
               . 'Resources/Private/Serializer/GeorgRinger.News',
       ]
   );

.. important::

   Use ``array_merge`` - do not overwrite the whole array. t3api registers its own directory there (``EXT:t3api/Resources/Private/Serializer/Metadata``) with metadata for core classes like ``FileReference`` or ``ObjectStorage``.

All ``*.yml`` files from registered directories are loaded (subdirectories are not scanned) and merged. Name of the file does not matter, but the convention is to use class name with dots, e.g. ``Domain.Model.News.yml``. Top level key of the file is the fully qualified class name. Syntax is the `JMS serializer YAML reference <https://jmsyst.com/libs/serializer/master/reference/yml_reference>`__.

Example from testing instance (``EXT:t3apinews``) which exposes properties of ``\GeorgRinger\News\Domain\Model\News`` - class extended by API resource ``\SourceBroker\T3apinews\Domain\Model\News``:

.. code-block:: yaml

   GeorgRinger\News\Domain\Model\News:
     properties:
       title:
         groups: [api_get_collection_t3apinews_news, api_get_item_t3apinews_news]
       teaser:
         groups: [api_get_collection_t3apinews_news, api_get_item_t3apinews_news]
       bodytext:
         groups: [api_get_item_t3apinews_news]
       datetime:
         groups: [api_get_collection_t3apinews_news, api_get_item_t3apinews_news]
       categories:
         groups: [api_get_collection_t3apinews_news, api_get_item_t3apinews_news]
         type: 'TYPO3\CMS\Extbase\Persistence\ObjectStorage<SourceBroker\T3apinews\Domain\Model\Category>'

With such configuration:

- ``bodytext`` is returned only for item operation (``/news/news/{id}``), not in collection.
- ``categories`` are serialized as ``\SourceBroker\T3apinews\Domain\Model\Category`` (the API resource class), because ``type`` points to the extended class instead of ``\GeorgRinger\News\Domain\Model\Category``.

Metadata merging
=================

YAML metadata are merged with annotations of the class - values from YAML win. If the same key is set in more than one YAML file, files from directories registered later win (``array_replace_recursive``). It means that you can change metadata delivered by t3api or by other extensions - e.g. remove a property from serialization:

.. code-block:: yaml

   GeorgRinger\News\Domain\Model\News:
     properties:
       teaser:
         exclude: true

.. note::

   Merged metadata is generated once and cached in ``var/`` folder. After changing YAML files flush system caches ("Flush all caches" in backend). In ``Development`` application context metadata is regenerated on every request.

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | List of news - ``bodytext`` is not included (not in ``api_get_collection_t3apinews_news`` group):
     | `https://14.t3api.ddev.site/_api/news/news <https://14.t3api.ddev.site/_api/news/news>`__
     |
   * | Single news - ``bodytext`` is included (``api_get_item_t3apinews_news`` group):
     | `https://14.t3api.ddev.site/_api/news/news/1 <https://14.t3api.ddev.site/_api/news/news/1>`__
     |
