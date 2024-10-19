#!/bin/bash

set -e

function install_start() {
    check_ddev_environment
    setup_environment "$1"
    create_symlinks_main_extension
    create_symlinks_additional_extensions
    setup_composer
}

function install_end() {
    setup_typo3
    import_data
    update_typo3
}

function check_ddev_environment() {
    if [ -z "$DDEV_PROJECT" ]; then
        echo "This script must be run inside a DDEV web container."
        exit 1
    fi
}

function setup_environment() {
    local version=$1
    BASE_PATH=".test/$version"
    rm -rf "$BASE_PATH"
    mkdir -p "$BASE_PATH/src/$EXTENSION_KEY"
    export DATABASE="database_$version"
    export BASE_PATH
    export VERSION="$version"
    export TYPO3_BIN="$BASE_PATH/vendor/bin/typo3"
    mysql -uroot -proot -e "DROP DATABASE IF EXISTS $DATABASE"
}

function create_symlinks_main_extension() {
    local exclusions=(".*" "Documentation" "Documentation-GENERATED-temp" "var")
    for item in ./*; do
        local base_name=$(basename "$item")
        for exclusion in "${exclusions[@]}"; do
            if [[ $base_name == "$exclusion" ]]; then
                continue 2
            fi
        done
        ln -sr "$item" "$BASE_PATH/src/$EXTENSION_KEY/$base_name"
    done
}

function create_symlinks_additional_extensions() {
    for dir in .ddev/test/files/src/*/; do
        ln -sr "$dir" "$BASE_PATH/src/$(basename "$dir")"
    done
}

function setup_composer() {
    composer init --name="sourcebroker/typo3-$VERSION" --description="TYPO3 $VERSION" --no-interaction --working-dir "$BASE_PATH"
    composer config extra.typo3/cms.web-dir public --working-dir "$BASE_PATH"
    composer config repositories.src path 'src/*' --working-dir "$BASE_PATH"
    composer config --no-interaction allow-plugins.typo3/cms-composer-installers true --working-dir "$BASE_PATH"
    composer config --no-interaction allow-plugins.typo3/class-alias-loader true --working-dir "$BASE_PATH"
    composer config --no-plugins allow-plugins.cweagans/composer-patches true --working-dir "$BASE_PATH"
    mkdir -p "$BASE_PATH/patches"
    jq '.extra.patches += {"typo3/cms-impexp": {"Disable error on new sys_file warning.": "patches/typo3-cms-impexp-disable-error-on-sys-file-warning.patch"}}' "$BASE_PATH/composer.json" > "$BASE_PATH/composer.json.tmp" && mv "$BASE_PATH/composer.json.tmp" "$BASE_PATH/composer.json"
    cp ".ddev/test/files/patches/typo3-cms-impexp-disable-error-on-sys-file-warning.patch" "$BASE_PATH/patches/"
}

function setup_typo3() {
    $TYPO3_BIN install:setup -n --database-name "$DATABASE"
    $TYPO3_BIN configuration:set 'BE/debug' 1
    $TYPO3_BIN configuration:set 'BE/lockSSL' true
    $TYPO3_BIN configuration:set 'FE/debug' 1
    $TYPO3_BIN configuration:set 'SYS/devIPmask' '*'
    $TYPO3_BIN configuration:set 'SYS/displayErrors' 1
    $TYPO3_BIN configuration:set 'SYS/trustedHostsPattern' '.*.*'
    $TYPO3_BIN configuration:set 'MAIL/transport' 'smtp'
    $TYPO3_BIN configuration:set 'MAIL/transport_smtp_server' 'localhost:1025'
    $TYPO3_BIN configuration:set 'GFX/processor' 'ImageMagick'
    $TYPO3_BIN configuration:set 'GFX/processor_path' '/usr/bin/'
    ln -srf ".ddev/test/files/config/sites/main/config.yaml" "$BASE_PATH/config/sites/main/config.yaml"
}

function import_data() {
    .ddev/commands/web/data import "$VERSION"
}

function update_typo3() {
    $TYPO3_BIN database:updateschema
    $TYPO3_BIN cache:flush
}
