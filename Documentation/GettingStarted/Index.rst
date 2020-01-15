.. include:: ../Includes.txt


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

@todo short description for IRI

.. _route-enhancer:

Adding route enhancer
======================

Minimal configuration requires definition of new route enhancer of type `T3apiResourceEnhancer`.

.. code-block:: yaml

    routeEnhancers:
      T3api:
        type: T3apiResourceEnhancer

.. _getting-started_base-path:

Changing base path to API endpoints
====================================

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
to our model class.

.. code-block:: php

    use SourceBroker\T3api\Annotation\ApiResource;

    /**
     * @ApiResource()
     */
    class Item extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
    }

.. note::
    API resource can be created only from class which:

    - Extends ``\TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject``.
    - Is kept in path ``EXT:{extkey}/Classes/Domain/Model``.
    - Exists in enabled extension.
