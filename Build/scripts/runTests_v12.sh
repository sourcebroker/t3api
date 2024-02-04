#!/bin/bash

# Exit on error.
set -e

function echo_magenta() {
    echo -e "\033[35m$1\033[0m"
}

PHP_VERSIONS=("8.1" "8.2" "8.3")
COMPOSER_INSTALLS=("composerInstallLowest" "composerInstallHighest")
TYPO3="12"

for PHP in "${PHP_VERSIONS[@]}"; do
    for COMPOSER in "${COMPOSER_INSTALLS[@]}"; do
        echo_magenta "Performing cleanup..."
        Build/scripts/runTests.sh -s clean

        echo_magenta "Validating composer.json and composer.lock for TYPO3 $TYPO3, PHP $PHP with $COMPOSER"
        Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -s composer -e 'validate'

        echo_magenta "Installing testing system for TYPO3 $TYPO3, PHP $PHP with $COMPOSER"
        Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -s "$COMPOSER"

        echo_magenta "Linting PHP for TYPO3 $TYPO3, PHP $PHP with $COMPOSER"
        Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -s lint

        echo_magenta "Validating code against CGL for TYPO3 $TYPO3, PHP $PHP with $COMPOSER"
        PHP_CS_FIXER_IGNORE_ENV=1 Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -s cgl -n

        echo_magenta "Running unit tests for TYPO3 $TYPO3, PHP $PHP with $COMPOSER"
        Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -s unit

        echo_magenta "Running functional tests for TYPO3 $TYPO3, PHP $PHP with $COMPOSER on mariadb with mysqli"
        Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -d mariadb -a mysqli -s functional

        echo_magenta "Running functional tests for TYPO3 $TYPO3, PHP $PHP with $COMPOSER on mariadb with pdo_mysql"
        Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -d mariadb -a pdo_mysql -s functional

        echo_magenta "Running functional tests for TYPO3 $TYPO3, PHP $PHP with $COMPOSER on mysql with mysqli"
        Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -d mysql -a mysqli -s functional

        echo_magenta "Running functional tests for TYPO3 $TYPO3, PHP $PHP with $COMPOSER on mysql with pdo_mysql"
        Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -d mysql -a pdo_mysql -s functional

        echo_magenta "Running functional tests for TYPO3 $TYPO3, PHP $PHP with $COMPOSER on postgres"
        Build/scripts/runTests.sh -t "$TYPO3" -p "$PHP" -d postgres -s functional
    done
done
