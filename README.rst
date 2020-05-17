TYPO3 Extension t3api
=====================

    .. image:: https://styleci.io/repos/205416349/shield?branch=master
       :target: https://styleci.io/repos/205416349

    .. image:: https://scrutinizer-ci.com/g/sourcebroker/t3api/badges/quality-score.png?b=master
       :target: https://scrutinizer-ci.com/g/sourcebroker/t3api/?branch=master

    .. image:: https://travis-ci.org/sourcebroker/t3api.svg?branch=master
       :target: https://travis-ci.org/sourcebroker/t3api

    .. image:: https://poser.pugx.org/sourcebroker/t3api/license
       :target: https://packagist.org/packages/sourcebroker/t3api

.. contents:: :local:

Features
--------

- Support for Extbase models with GET, POST, PATCH, DELETE operations.
- Configuration with classes, properties and methods annotations.
- Build-in filters: boolean, numeric, order, range and text (partial, match against and exact strategies).
- Build-in pagination.
- Support for TypoLinks and image processing.
- Support for file uploads (FAL).
- Configurable routing.
- Responses in `Hydra <https://www.hydra-cg.com/>`_ /`JSON-LD <https://json-ld.org/>`_ format.
- Serialization contexts - customizable output depending on routing.
- Easy customizable serialization handlers and subscribers.
- Support for all features of `JMSSerializer <https://jmsyst.com/libs/serializer>`_.
- Backend module with Swagger for documentation and real testing.

Usage
-----

Installation
++++++++++++

Installation by composer is recommended.
In your Composer based TYPO3 project root, just do ``composer require sourcebroker/t3api``.


Minimal setup
+++++++++++++

1. If you use TYPO3 8.7 then open main Template record and add "T3api" in tab "Includes" -> field "Include static (from extensions)".
   Skip this step for TYPO3 9.5.

2. Import route enhancer by adding following line on top of your site ``config.yaml`` .

::

   imports:
     - { resource: "EXT:t3api/Configuration/Routing/config.yaml" }

3. Configure routes for your Extbase model using PHP annotations:

::

  /**
   * @SourceBroker\T3api\Annotation\ApiResource(
   *     collectionOperations={
   *          "get"={
   *              "path"="/articles",
   *          },
   *     },
   *     itemOperations={
   *          "get"={
   *              "path"="/articles/{id}",
   *          }
   *     },
   * )
   */
  class Article extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
  {
  }

4. Done! Try your API endpoints at https://example.com/_api/articles and https://example.com/_api/articles/1


To check more configuration options see `t3apinews <https://github.com/sourcebroker/t3apinews>`_
- an example integration of t3api for well known `news <https://github.com/georgringer/news>`_ extension.


Release Management
------------------

T3api uses **semantic versioning** which basically means for you, that:

- **bugfix updates** (e.g. 1.0.0 => 1.0.1) just includes small bugfixes or security relevant stuff without breaking changes.
- **minor updates** (e.g. 1.0.0 => 1.1.0) includes new features and smaller tasks without breaking changes.
- **major updates** (e.g. 1.0.0 => 2.0.0) breaking changes wich can be refactorings, features or bugfixes.

