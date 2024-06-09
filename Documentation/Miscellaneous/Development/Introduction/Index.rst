.. _development_introduction:

=============
Introduction
=============

During development of t3api, you will be dealing with two different TYPO3 installations,
which serve different purposes.

Unit and functional testing installation
++++++++++++++++++++++++++++++++++++++++

* It is a minimal TYPO3 installation, which is used for running unit and functional tests.
* Files are under directory :directory:`.Build`
* You can install it manually with :bash:`ddev composer i`
* It is installed automatically while using command :ref:`_development_commands_list_ddev_ci`
* There can be only one TYPO3 version installed at one time.

Integration and manual testing installation
+++++++++++++++++++++++++++++++++++++++++++

* This installation is a full TYPO3 accessible under :uri:`https://[TYPO3_VERSION].t3api.ddev.site`
  and it is used for testing REST API endpoints using Postman tests. It is also used for manual testing.
* Files are under directory :directory:`/.test/[TYPO3_VERSION]`
* You can install it manually using command :ref:`_development_commands_list_ddev_install`
* It is installed automatically while using command :ref:`_development_commands_list_ddev_ci`
* There can be multiple TYPO3 versions integrations installations at one time each under different url.
