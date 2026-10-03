.. _getting-started:

================
Getting started
================

What does it do
================

T3api extension provides easy configurable and customizable REST API for your Extbase models.
It allows to configure whole API functionality with annotations for classes, properties and methods.

Most of configuration options is based on `API Platform <https://api-platform.com>`_ to make it easier to use for
developers experienced in this awesome framework.

T3api comes with partial support of `JSON-LD <https://json-ld.org/>`__ and `Hydra <http://www.hydra-cg.com/>`__,
which allows to build smart frontend applications with auto-discoverability capabilities.


Installation
============

T3api supports TYPO3 12.4, 13 and 14 (PHP 8.1 or higher). Run

.. code-block:: bash

    composer require sourcebroker/t3api


Configuration
=============

Route enhancer
++++++++++++++

Import route enhancer by adding following lines at the bottom of your site ``config.yaml``:

.. code-block:: yaml

   imports:
     - { resource: "EXT:t3api/Configuration/Routing/config.yaml" }

.. _route-enhancer:

If you do not want to use import you can also manually add new route enhancer of type ``T3apiResourceEnhancer`` directly
in your site configuration.

.. code-block:: yaml

    routeEnhancers:
      T3api:
        type: T3apiResourceEnhancer

.. _getting-started_base-path:

Default base path to api requests is: ``_api``. To change it, it is needed to extend route enhancer configuration by
``basePath`` property, as in example below:

.. code-block:: yaml

    routeEnhancers:
      T3api:
        type: T3apiResourceEnhancer
        basePath: 'my_custom_api_basepath'


Creating API resource
======================

Next step is to make an API resource from our entity.
To map Extbase model to API resource it is just needed to add ``@SourceBroker\T3api\Annotation\ApiResource`` annotation
to our model class and define at least one :ref:`operation <operations>` (endpoint):

.. code-block:: php

    use SourceBroker\T3api\Annotation\ApiResource;

    /**
     * @ApiResource(
     *     collectionOperations={
     *          "get"={
     *              "path"="/items",
     *          },
     *     },
     *     itemOperations={
     *          "get"={
     *              "path"="/items/{id}",
     *          }
     *     },
     * )
     */
    class Item extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
    }

After flushing caches the items are available under ``https://example.com/_api/items`` and single item under ``https://example.com/_api/items/1``.

.. note::
    By default all properties of the entity are returned. Use :ref:`serialization context groups <serialization_context-groups>`
    to control which properties are exposed.

.. note::
    API resource can be created only from class which:

    - Extends ``\TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject``.
    - Is kept in path ``EXT:{extkey}/Classes/Domain/Model`` (can be changed, see :ref:`customization_api-resource-path`).
    - Exists in enabled extension.

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   Testing instance uses ``EXT:t3apinews`` which exposes ``EXT:news`` records as API resources.

   * | Main endpoint with the list of all resources:
     | `https://14.t3api.ddev.site/_api/ <https://14.t3api.ddev.site/_api/>`__
     |
   * | Collection of news:
     | `https://14.t3api.ddev.site/_api/news/news <https://14.t3api.ddev.site/_api/news/news>`__
     |
   * | Single news:
     | `https://14.t3api.ddev.site/_api/news/news/1 <https://14.t3api.ddev.site/_api/news/news/1>`__
     |
