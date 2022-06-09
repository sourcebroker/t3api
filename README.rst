TYPO3 Extension t3api
=====================

.. image:: https://poser.pugx.org/sourcebroker/t3api/v/stable
   :target: https://extensions.typo3.org/extension/t3api/

.. image:: https://img.shields.io/badge/TYPO3-11-orange.svg
   :target: https://get.typo3.org/version/11

.. image:: https://img.shields.io/badge/TYPO3-10-orange.svg
   :target: https://get.typo3.org/version/10

.. image:: https://scrutinizer-ci.com/g/sourcebroker/t3api/badges/quality-score.png?b=master
   :target: https://scrutinizer-ci.com/g/sourcebroker/t3api/?branch=master

.. image:: https://travis-ci.org/sourcebroker/t3api.svg?branch=master
   :target: https://travis-ci.org/sourcebroker/t3api

.. image:: https://poser.pugx.org/sourcebroker/t3api/license
   :target: https://packagist.org/packages/sourcebroker/t3api

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

Documentation
-------------

Read the docs at https://docs.typo3.org/p/sourcebroker/t3api/master/en-us/

Example integration
-------------------

To check some real code see `t3apinews <https://github.com/sourcebroker/t3apinews>`_ - an example integration of t3api for well known `news <https://github.com/georgringer/news>`_ extension.

Demo
----

If you use `ddev <https://www.ddev.com/>`_ then in less than 5min you can have working demo of ``ext:t3api`` on you local computer.
Try https://github.com/sourcebroker/t3api-demo
