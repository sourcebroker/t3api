TYPO3 Extension t3api
=====================

.. image:: https://poser.pugx.org/sourcebroker/t3api/v/stable
   :target: https://extensions.typo3.org/extension/t3api/

.. image:: https://scrutinizer-ci.com/g/sourcebroker/t3api/badges/quality-score.png?b=master
   :target: https://scrutinizer-ci.com/g/sourcebroker/t3api/?branch=master

.. image:: https://github.com/sourcebroker/t3api/actions/workflows/TYPO3_12.yml/badge.svg
   :target: https://github.com/sourcebroker/t3api/actions/workflows/TYPO3_12.yml

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

Take a look and test
--------------------

After cloning repo you can run ``ddev restart && ddev composer install`` and then ``ddev ci 12`` to install local integration test instance.
Local instance is available at https://12.t3api.ddev.site/ (login to backend with ``admin`` / ``Password1!`` credentials).

At frontend part you can at once test REST API responses for ext news:

* https://12.t3api.ddev.site/_api/news/news
* https://12.t3api.ddev.site/_api/news/news/1
* https://12.t3api.ddev.site/_api/news/categories
* etc

You can also run Postman test with ``ddev ci:tests:postman`` command or full test suite with ``ddev composer ci``.
Postman is doing full CRUD test with category and news (with image).

Development
-----------

If you want to help with development take a look at https://docs.typo3.org/p/sourcebroker/t3api/main/en-us/Miscellaneous/Development/Index.html
