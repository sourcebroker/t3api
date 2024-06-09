.. _development_commands_list:

=================
Commands list
=================

Below is a list of commands that you can use in the development process of ext:t3api.

..  contents::
    :local:
    :depth: 2


.. _development_commands_list_ddev_cache_flush:
:bash:`ddev cache-flush`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This command will run :bash:`typo3 flush:cache` for all active TYPO3
integration testing instances.


.. _development_commands_list_ddev_ci:
:bash:`ddev ci [T3_VERSION|all] [PHP_VERSION] [lowest]`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++

If called without any arguments, this command will run multiple commands
for testing and linting.

* :bash:`ddev composer ci` which will run:

  * :bash:`ci:composer:normalize`
  * :bash:`ci:yaml:lint`
  * :bash:`ci:json:lint`
  * :bash:`ci:php:lint`
  * :bash:`ci:php:cs-fixer`
  * :bash:`ci:php:stan`
  * :bash:`ci:tests:unit`
  * :bash:`ci:tests:functional`
  * :bash:`ci:tests:postman`

* :bash:`ddev docs ci` which will check if docs can be rendered without problems.

If called with arguments like :bash:`ddev ci [T3_VERSION] [PHP_VERSION] [lowest]`
this command will restart ddev, set required PHP, install required version of TYPO3
with optional composer option :bash:`--prefer-lowest` and then run :bash:`ddev ci` on it.

When the argument is only :bash:`all`, then this command will run matrix tests for
all supported TYPO3, PHP, COMPOSER. The same command is run for each TYPO3, PHP, COMPOSER
combination in matrix tests on github actions.

Examples:

.. code-block:: bash
    ddev ci
    ddev ci 12 8.3 lowest
    ddev ci all


.. tip::
   You can use this command to fast switch with development to whatever TYPO3/PHP
   version you like because after ci tests it does not return to previous TYPO3/PHP.


.. _development_commands_list_ddev_data:
:bash:`ddev data [export|import] [T3_VERSION]`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This command will export or import database/files of specific testing instance into
folder :folder:`.ddev/test/impexp/`. It uses the TYPO3 core extension impexp.
These exported files are later used by command :bash:`ddev install [T3_VERSION]`.
When you do new features or write postman tests this is very likely that you will
need to do changes to database/files and commit this state to git.
This command is just for that reason.

.. note::
    All TYPO3 testing instances are using the same exported files. This means that
    there is no much difference if you make :bash:`ddev data export 13` or
    :bash:`ddev data export 12`. Important is only that you do export from the
    testing instance you actually modified.


.. _development_commands_list_ddev_docs:
:bash:`ddev docs [watch|build|ci]`
++++++++++++++++++++++++++++++++++++
build
    will build docs into the folder :folder:`Documentation-GENERATED-temp`
    You can browse it for example on PHPStorm "open in browser" option.

watch
    this command will run hot reload for documentation.

ci
    this command will test if docs are able to  render correctly.
    Used in :ref:`_development_commands_list_ddev_ci` command.



.. _development_commands_list_ddev_fix:
:bash:`ddev fix`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This command will run all possible automate fixes. For now it makes
PHP CS Fixer changes and composer normalisation.


.. _development_commands_list_ddev_install:
:bash:`ddev install [T3_VERSION|all]`
++++++++++++++++++++++++++++++++++++
This command will install specific (or all) integration testing instances
of TYPO3 in folder :folder:`/.test/`. List of supported TYPO3 versions is defined
in file :file:`.ddev/docker-compose.web.yaml` in variable :text:`TYPO3_VERSIONS`.
Integration testing instances are available under url :uri:`https://[T3_VERSION].t3api.ddev.site`.
You can also open https://t3api.ddev.site to see list of all supported testing
instances for given t3api version.

Example:

.. code-block:: bash
    ddev install
    ddev install 12
    ddev install all


.. _development_commands_list_ddev_next:
:bash:`ddev next [major|minor|patch]`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This command will prepare t3api for next release.

For now, the following files are changed with info about next version:

* :file:`/ext_emconf.php`
* :file:`/Documentation/guides.xml`

Additionally it outputs a command you need to run to push changes and tag to git.

Example output:

.. code-block:: bash
    git add Documentation/guides.xml ext_emconf.php && git commit -m 'Tag new version' && git tag -a '2.0.4' -m '2.0.4' && git push origin master --tags
