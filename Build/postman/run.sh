#!/bin/bash

source .ddev/test/utils.sh

set +x
set -e

POSTMAN_BUILD_PATH=/var/www/html/Build/postman/
cd ${POSTMAN_BUILD_PATH} || exit

if [[ ! -e ".nvmrc" ]]; then
    echo_red "No file .nvmrc with node version in folder: ${POSTMAN_BUILD_PATH}" && exit 1
fi

if [[ ! -d "node_modules" ]]; then
    npm ci
fi

TYPO3=""
TEST_FILE=""

while [[ $# -gt 0 ]]; do
    case $1 in
        --file)
            TEST_FILE="$2"
            shift 2
            ;;
        *)
            TYPO3="$1"
            shift
            ;;
    esac
done

if [ -z "$TYPO3" ]; then
    TYPO3=$("../../.Build/bin/typo3" | grep -oP 'TYPO3 CMS \K[0-9]+')
fi

if ! check_typo3_version "$TYPO3"; then
   exit 1
fi

if [[ ! -d "/var/www/html/.test/$TYPO3" ]]; then
    echo_red "Can not test. Install first TYPO3 $TYPO3 with command 'ddev install $TYPO3'"
else
    DOMAINS=("https://$TYPO3.$EXTENSION_KEY.ddev.site")
    for DOMAIN in "${DOMAINS[@]}"; do
        if [[ -n "$TEST_FILE" ]]; then
            ./node_modules/.bin/newman run "../../Tests/Postman/$TEST_FILE" --verbose --bail  --env-var "baseUrl=$DOMAIN"
        else
            for TEST_FILE in ../../Tests/Postman/*.json; do
                ./node_modules/.bin/newman run "$TEST_FILE" --verbose --bail --env-var "baseUrl=$DOMAIN"
            done
        fi
    done
fi
