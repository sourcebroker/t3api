.. _start:

=============================================================
TYPO3 REST API
=============================================================

:Version:
   |release|

:Language:
   en

:Authors:
   Inscript Team

:Email:
    office@inscript.dev

:Website:
   https://www.inscript.team/

:License:
   This extension documentation is published under the
   `CC BY-NC-SA 4.0 <https://creativecommons.org/licenses/by-nc-sa/4.0/>`__ (Creative Commons)
   license

:Rendered:
    |today|


The content of this document is related to TYPO3 CMS, a GNU/GPL CMS/Framework available from `typo3.org
<https://typo3.org/>`_ .

This documentation is for the TYPO3 extension `t3api <https://github.com/sourcebroker/t3api/>`_.

If you find an error or something is missing, please: `Report a Problem
<https://github.com/sourcebroker/t3api/issues/new>`__.


T3api provides a REST API for Extbase models. API resources, their operations and filters are configured with
annotations on the model class (or on a class extending a model of another extension, see :ref:`integration`), output
with serializer metadata in annotations or :ref:`YAML <serialization_yaml-metadata>`. Configuration options follow
`API Platform <https://api-platform.com>`__ and responses use `JSON-LD <https://json-ld.org/>`__ /
`Hydra <https://www.hydra-cg.com/>`__ format.

Overview
========

- :ref:`Operations <operations>` - ``GET``, ``POST``, ``PATCH``, ``PUT`` and ``DELETE`` for Extbase models, :ref:`slug fields <operations_slugs>` generated according to TCA.
- :ref:`Filtering <filtering>` and :ref:`pagination <pagination>` of collections, :ref:`ordered UIDs filter <filtering_ordered-uids-filter>` for collections in externally resolved order.
- :ref:`Serialization <serialization>` - output per operation with :ref:`context groups <serialization_context-groups>`, metadata in annotations or :ref:`YAML <serialization_yaml-metadata>`, typolinks, processed images and file references.
- :ref:`Security <security>` - access to operations and filters controlled with expressions.
- :ref:`Response cache <response-cache>` - opt-in cache of ``GET`` responses with tag-based invalidation.
- :ref:`Multilanguage <multilanguage>`, :ref:`file upload <handling_file_upload>`, :ref:`cascade persistence <handling_cascade_persistence>` of relations and :ref:`CORS <cors>`.
- :ref:`Backend module <getting-started_backend-module>` with Swagger UI - OpenAPI documentation of the API and a client to test it.
- :ref:`Customization <customization>` and :ref:`events <events>` to change the behaviour.

**Table of Contents**

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   GettingStarted/Index
   Operations/Index
   Filtering/Index
   Pagination/Index
   ResponseCache/Index
   Security/Index
   Serialization/Index
   Integration/Index
   Customization/Index
   Events/Index
   Multilanguage/Index
   HandlingFileUpload/Index
   HandlingCascadePersistence/Index
   UseCases/Index
   Cors/Index
   Miscellaneous/Index
   Sitemap
