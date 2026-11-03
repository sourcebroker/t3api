.. _getting-started:

================
Getting started
================

What does it do
================

T3api provides a configurable and customizable REST API for Extbase models. API resources, their operations, filters
and access rules are configured with annotations on the model class (or on a class extending a model of another
extension, see :ref:`integration`), output of the models with annotations or YAML serializer metadata.

Most configuration options follow `API Platform <https://api-platform.com>`__, so they are familiar to developers who
know this framework.

Responses use `JSON-LD <https://json-ld.org/>`__ and `Hydra <https://www.hydra-cg.com/>`__ (partial support), which
allows frontend applications to discover resources, filters and pagination from the responses.


Installation
============

Supported versions:

.. list-table::
   :header-rows: 1

   * - TYPO3
     - PHP
   * - 12.4
     - 8.1 - 8.4
   * - 13.4
     - 8.2 - 8.5
   * - 14
     - 8.2 - 8.5

Run

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

.. _getting-started_backend-module:

Backend module
==============

Backend module **Web API** shows the API of a site in `Swagger UI <https://swagger.io/tools/swagger-ui/>`__ - all
resources and operations with their parameters, filters and response schemas, generated as OpenAPI specification from
the configuration of the API resources. Select the site in the module header. The site needs the
:ref:`route enhancer <route-enhancer>`, otherwise the module shows an error message.

Backend users need access to the module (admins have it by default).

.. important::

   "Try it out" in Swagger UI sends real requests to the API of the selected site - ``POST``, ``PATCH``, ``PUT`` and
   ``DELETE`` change and delete records. The browser sends the requests with its cookies of the API domain, so
   :ref:`security <security>` expressions see the frontend or backend user logged in on that domain.

The backend Content Security Policy of the module allows requests to the domains of all sites with the t3api route
enhancer. Swagger UI does not use the online validator of swagger.io, so the URL of the specification is not sent
outside of the TYPO3 instance.
