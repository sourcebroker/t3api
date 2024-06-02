#!/bin/bash

source .ddev/test/utils.sh

POSTMAN_BUILD_PATH=/var/www/html/Build/postman/
cd ${POSTMAN_BUILD_PATH} || exit

if [[ ! -e ".nvmrc" ]]; then
    echo_red "No file .nvmrc with node version in folder: ${POSTMAN_BUILD_PATH}" && exit 1
fi

if [[ ! -d "node_modules" ]]; then
    npm ci
fi

TYPO3=${1}
if [ -z "$TYPO3" ]; then
    TYPO3=$("../../.Build/bin/typo3" | grep -oP 'TYPO3 CMS \K[0-9]+')
fi

if ! check_typo3_version "$TYPO3"; then
   exit 1
fi

if [[ ! -d "/var/www/html/.test/$TYPO3" ]]; then
        echo_red "Can not test. Install first TYPO3 $TYPO3 with command 'ddev install-$TYPO3'"
    else
        DOMAINS=("https://$TYPO3.t3api.ddev.site")
        for DOMAIN in "${DOMAINS[@]}"; do
            for TEST_FILE in ../../Tests/Postman/*.json; do
                ./node_modules/.bin/newman run "$TEST_FILE" --env-var "baseUrl=$DOMAIN"
            done
        done
fi
