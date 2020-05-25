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

- Support for Extbase models with GET, POST, PATCH, PUT, DELETE operations.
- Configuration with classes, properties and methods annotations.
- Build-in filters: boolean, numeric, order, range and text (partial, match against and exact strategies).
- Build-in pagination.
- Support for typolinks.
- Support for image processing.
- Support for file uploads (FAL).
- Configurable routing.
- Responses in `Hydra <https://www.hydra-cg.com/>`_ /`JSON-LD <https://json-ld.org/>`_ format.
- Serialization contexts - customizable output depending on routing.
- Easy customizable serialization handlers and subscribers.
- Backend module with Swagger for documentation and real testing.

Real code
---------

To check some real code see `t3apinews <https://github.com/sourcebroker/t3apinews>`_ - an example integration of t3api for well known `news <https://github.com/georgringer/news>`_ extension.

DEMO
----

If you use `ddev <https://www.ddev.com/>`_ then in less than 5min you can have working demo of ``ext:t3api`` on you local computer.
Try https://github.com/sourcebroker/t3api-demo

Release Management
------------------

T3api uses **semantic versioning** which basically means for you, that:

- **bugfix updates** (e.g. 1.0.0 => 1.0.1) just includes small bugfixes or security relevant stuff without breaking changes.
- **minor updates** (e.g. 1.0.0 => 1.1.0) includes new features and smaller tasks without breaking changes.
- **major updates** (e.g. 1.0.0 => 2.0.0) breaking changes wich can be refactorings, features or bugfixes.
