.. include:: ../Includes.txt

.. _development_introduction:

=============
Introduction
=============

During development of t3api, we are dealing with two different TYPO3 installations,
which serve different purposes.

1. **Unit and functional testing installation in the `.Build/public` directory**:
   This installation is created based on the configuration in the :file:`composer.json` file,
   in the `extra` section. It is a minimal TYPO3 installation, which is mainly used for
   running unit and functional tests. It does not have a full database or full TYPO3
   configuration, which makes it fast and lightweight. It is an ideal environment
   for quickly running tests and checking if the basic functions and classes
   are working correctly.

   .. code-block:: json
        "extra": {
         "typo3/cms": {
          "extension-key": "t3api",
          "web-dir": ".Build/public"
         }
        }


2. **Integration and manual testing installation in the `/.test/[TYPO3_VERSION]` directory**:

   This installation is a full, standard TYPO3 installation, which is
   created using ddev infrastructure. It is accessible under :uri:`https://[TYPO3_VERSION].t3api.ddev.site`
   It is an environment that most closely resembles a real production environment.
   It is used for testing the backend module and REST API endpoints, both manually
   and using Postman tests.

In summary, both installations serve different purposes and are necessary
at different stages of the development process. The unit and functional testing
installation in :file:`.Build/public` is used for quick unit and functional tests,
while the integration and manual testing installation in :file:`/.test/[TYPO3_VERSION]`
is used for more detailed tests and simulation of a real production environment.
