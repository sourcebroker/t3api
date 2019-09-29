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

- Support for Extbase models.
- Configuration with classes, properties and methods annotations.
- Build-in filters: boolean, numeric, order, range and text (partial, match against and exact strategies).
- Build-in pagination.
- Support for TypoLinks and image processing.
- Configurable routing.
- Responses in Hydra/`JSON-LD <https://json-ld.org/>`_ format.
- Serialization contexts - customizable output depending on routing.
- Easy customizable serialization handlers and subscribers.
- Support for all features of `JMSSerializer <https://jmsyst.com/libs/serializer>`_.

Usage
-----

Installation
++++++++++++

Installation by composer is recommended.
In your Composer based TYPO3 project root, just do ``composer require sourcebroker/t3api``.


Minimal setup
+++++++++++++

1. Open main Template record and add "T3api" in tab "Includes" -> field "Include static (from extensions)"

2. Add route enhancer to your site ``config.yaml`` file. ``basePath`` is the prefix for all api endpoints.

::

 routeEnhancers:
    T3api:
      type: T3apiResourceEnhancer
      basePath: '_api'


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


To check more configuration options see `t3apinews <https://github.com/sourcebroker/t3apinews>`_ - an example integration of t3api for well known `news <https://github.com/georgringer/news>`_ extension.

Administration corner
---------------------

Versions and support
++++++++++++++++++++

+-------------+------------+-----------+-----------------------------------------+
| T3api       | TYPO3      | PHP       | Support/Development                     |
+=============+============+===========+=========================================+
| 0.1.x       | 9.x        | 7.2 - 7.3 | Features, Bugfixes, Security Updates    |
+-------------+------------+-----------+-----------------------------------------+

Release Management
++++++++++++++++++

T3api uses **semantic versioning** which basically means for you, that:

- **bugfix updates** (e.g. 1.0.0 => 1.0.1) just includes small bugfixes or security relevant stuff without breaking changes.
- **minor updates** (e.g. 1.0.0 => 1.1.0) includes new features and smaller tasks without breaking changes.
- **major updates** (e.g. 1.0.0 => 2.0.0) breaking changes wich can be refactorings, features or bugfixes.

