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
This command will run :bash:`typo3 cache:flush` for all active TYPO3
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
  * :bash:`ci:php:rector`
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
    ddev ci 14 8.4 lowest
    ddev ci all


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

All TYPO3 testing instances are using the same exported files. This means that
there is no much difference if you make :bash:`ddev data export 14` or
:bash:`ddev data export 13`. Important is only that you do export from the
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
    this command will test if docs are able to render correctly.
    Used in :ref:`development_commands_list_ddev_ci` command.



.. _development_commands_list_ddev_fix:
:bash:`ddev fix`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This command will run all possible automated fixes: Rector, PHP CS Fixer and composer normalization.


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
    ddev install 14
    ddev install all


.. _development_commands_list_ddev_next:
:bash:`ddev next [major|minor|patch]`
++++++++++++++++++++++++++++++++++++++++++++++++++++++++
This command helps to release the next version of the extension. The default branch can be protected (changes only
through pull requests), so the version files are changed in a pull request and the command is run twice.

It does several checks first:

1. Checks if you are on default branch.
2. Checks if you are up to date with remote default branch.
3. Checks if you do not have any not pushed changes to remote.
4. Checks validity of last tag.
5. Checks if the version in :file:`/ext_emconf.php` is the last tag or the next version - otherwise you passed another
   version type than in the first run.

The next version is computed from the last tag, so both runs compute the same version.

**First run** updates the version in:

* :file:`/ext_emconf.php`
* :file:`/composer.json`
* :file:`/Documentation/guides.xml` (``release`` - full version, ``version`` - major and minor)

and outputs a command which pushes them to a new branch:

.. code-block:: bash

    zsh -e -c "git switch -c 'version-5.1.0'; git add Documentation/guides.xml ext_emconf.php composer.json; git commit -m 'Tag version 5.1.0'; git push -u origin 'version-5.1.0'"

Open a pull request from this branch to the default branch and merge it.

**Second run** - on the updated default branch (:bash:`git pull`), with the same version type. The version files are up
to date, so it outputs a command which pushes the tag only:

.. code-block:: bash

    zsh -e -c "git tag -a '5.1.0' -m 'Version 5.1.0' -s; git push origin '5.1.0'"

Pushing the tag starts the release workflow (GitHub release and publishing to TER).

When the version files are already updated in the branch merged to the default branch (e.g. a release branch), the first
run outputs the tag command right away.
