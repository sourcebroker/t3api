.. include:: ../Includes.txt

.. _development_commands_list:

=================
Commands list
=================

Below is a list of commands that you can use in development process.

..  contents::
    :local:
    :depth: 2


.. _development_commands_list_composer_fix:
:bash:`ddev composer fix`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will clean make cs-fixer changes and composer normalisation.

.. _development_commands_list_composer_ci:
:bash:`ddev composer ci`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will run multiple composer commands for testing and linting:

* ci:composer:normalize
* ci:yaml:lint
* ci:json:lint
* ci:php:lint
* ci:php:cs-fixer
* ci:php:stan
* ci:tests:unit
* ci:tests:functional
* ci:tests:postman

Tests will be run on currently active TYPO3/PHP.
You can change the TYPO3/PHP by running :bash:`ddev test [TYPO3_VERSION] [PHP_VERSION]`.


.. _development_commands_list_ddev_cache_flush:
:bash:`ddev cache-flush`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will run :bash:`typo3 flush:cache` for all active TYPO3 instances.


.. _development_commands_list_ddev_docs:
:bash:`ddev docs [watch|build]`
++++++++++++++++++++++++++++++++++++
This commands will build or watch documentation. Documentation "build" mode is
only as check if there are any difference between build/watch. The same "build"
is in theory run on TYPO3 Documentation side.


.. _development_commands_list_ddev_data:
:bash:`ddev data [export|import] [TYPO3_VERSION]`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will export or import database/files of specific testing instance into
folder :folder:`.ddev/test/impexp/`. It uses the TYPO3 core extension impexp.
This exported files are later used by command :bash:`ddev install [TYPO3_VERSION]`.
When you do new features or write postman tests this is very likely that you will
need to do changes to database/files and commit this state to git.
This command is just for that reason.

.. note::
    All TYPO3 testing instances are using the same exported files. This means that
    there is no much difference if you make :bash:`ddev data export 13` or
    :bash:`ddev data export 12`. Important is only that you do export from the
    testing instance you actually modified.


.. _development_commands_list_ddev_install:
:bash:`ddev install [TYPO3_VERSION|all]`
++++++++++++++++++++++++++++++++++++
This command will install specific (or all) testing version of TYPO3 in :file:`.test` folder. List
of supported TYPO3 versions is defined in file :file:`.ddev/docker-compose.web.yaml`
in variable :text:`TYPO3_VERSIONS`. Testing instance is available under url
:uri:`https://[TYPO3_VERSION].t3api.ddev.site`. You can also open :uri:`https://t3api.ddev.site`
to see list of all supported testing instances.

Example:

* :bash:`ddev install`
* :bash:`ddev install 12`
* :bash:`ddev install all`


.. _development_commands_list_ddev_test:
:bash:`ddev test [TYPO3_VERSION|all] [PHP_VERSION] [lowest]`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will install specific version of TYPO3, with specific version of
PHP and with optional composer option `--prefer-lowest` and run tests on it.
Running this command will restart ddev with specific PHP version, run
:bash:`ddev install [TYPO3_VERSION]` and run the tests with :bash:`ddev composer ci`.
This command is useful when you want to fast switch with development to some specific
TYPO3/PHP because after test it do not return to previous TYPO3/PHP.
When first argument is :bash:`all` then this command will run matrix tests for
all supported TYPO3/PHP/COMPOSER.

.. note::
    For regular fast testing during development it is better to run
    just :bash:`ddev composer ci`. Use :bash:`ddev test [TYPO3_VERSION]`
    and :bash:`ddev test all` only for final testing. They are slow because they
    are restarting ddev and installing TYPO3 but are more accurate because
    the env is fresh from zero.

Example:

* :bash:`ddev test 12 8.3 lowest`
* :bash:`ddev test all`

