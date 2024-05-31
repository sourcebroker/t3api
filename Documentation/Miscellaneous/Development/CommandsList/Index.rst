.. include:: ../Includes.txt

.. _development_commands_list_commands_list:

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


.. _development_commands_list_composer_ci_stan:
:bash:`ddev composer ci:php:stan`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will run phpstan tests.


.. _development_commands_list_composer_unit:
:bash:`ddev composer ci:tests:unit`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will run unit tests.


.. _development_commands_list_composer_functional:
:bash:`ddev composer ci:tests:functional`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will run functional tests.


.. _development_commands_list_composer_functional:
:bash:`ddev composer ci:tests:postman`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will run postman tests.


.. _development_commands_list_ddev_cache_flush:
:bash:`ddev cache-flush`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will run :bash:`typo3 flush:cache` for all active TYPO3 instances.


.. _development_commands_list_ddev_docs:
:bash:`ddev docs`
++++++++++++++++++++++++++++++++++++
This commands will generate the docs in folder :file:`Documentation-GENERATED-temp`.
You can use it after :bash:`ddev docs` to do some final check for generated docs.
The same command will be run but TYPO3 docs generator.


.. _development_commands_list_ddev_docs_watch:
:bash:`ddev docs-watch`
++++++++++++++++++++++++++++++++++++
This is the main command you will use as it automatically opens browser and
refresh the docs HTML as you write.


:bash:`ddev dump [TYPO3_VERSION]`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will dump database of testing instance into
:file:`.ddev/test/[TYPO3_VERSION]/dump.sql`. This file is later used by
command :bash:`ddev install [TYPO3_VERSION]`. When you do new features
or write postman tests this is very likely that you will need to do changes
to database and commit this state to git. This command is just for that reason.


.. _development_commands_list_ddev_install_typo3_version:
:bash:`ddev install [TYPO3_VERSION]`
++++++++++++++++++++++++++++++++++++

This command will install testing version of TYPO3 in :file:`.test` folder. List
of supported TYPO3 versions is defined in file :file:`.ddev/docker-compose.web.yaml`
in variable :text:`TYPO3_VERSIONS`. Testing instance is available under url
:uri:`https://[TYPO3_VERSION].t3api.ddev.site`. You can also open :uri:`https://t3api.ddev.site`
to see list of all supported testing instances.

Example: :bash:`ddev install 12`


.. _development_commands_list_ddev_install_all:
:bash:`ddev install-all`
++++++++++++++++++++++++
This command will install testing versions of TYPO3 in :file:`.test` folder.
It will install as much TYPO3 instances as is supported by current version of t3api.
List of supported TYPO3 versions is defined in file :file:`.ddev/docker-compose.web.yaml`
in variable :text:`TYPO3_VERSIONS`. Testing instances are available under url
:uri:`https://[TYPO3_VERSION].t3api.ddev.site`.


.. _development_commands_list_ddev_test_typo3_version_php_version_lowest:
:bash:`ddev test [TYPO3_VERSION] [PHP_VERSION] [lowest]`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will install specific version of TYPO3, with specific version of
PHP and with optional composer option `--prefer-lowest` and run tests on it.
Running this command will restart ddev with specific PHP version, run
:bash:`ddev install-[TYPO3_VERSION]` and run the tests with :bash:`ddev composer ci`.
This command is useful when you want to fast switch with development to some specific
TYPO3/PHP becase after test it do not return to previous TYPO3/PHP.
This command is also used in command :bash:`ddev test-all` to run matrix tests for
all supported TYPO3/PHP/COMPOSER.

.. note::
    For regular fast testing during development it is better to run
    just :bash:`ddev composer ci`. Use `ddev test [TYPO3_VERSION]`
    and `ddev test-all` only for final testing. They are slow because they
    are restarting ddev and installing TYPO3 but are more accurate because
    the env is fresh from zero.

Example: :bash:`ddev test 12 8.3 lowest`


.. _development_commands_list_ddev_test_all:
:bash:`ddev test-all`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This commands will install and test whole matrix of supported TYPO3/PHP/COMPOSER.
The same kind of testing is done on github actions so it is good that you run it
before putting pull request.

Example: :bash:`ddev test 12 8.3 lowest`.


.. _development_commands_list_ddev_dump:


