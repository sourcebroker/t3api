#!/bin/bash

function get_supported_typo3_versions() {
    if [ -z "${TYPO3_VERSIONS+x}" ]; then
        echo_red "TYPO3_VERSIONS is unset. Please set it before running this function."
        return 1
    else
        local TYPO3_VERSIONS_ARRAY=()
        IFS=' ' read -r -a TYPO3_VERSIONS_ARRAY <<< "$TYPO3_VERSIONS"
        if [ ${#TYPO3_VERSIONS_ARRAY[@]} -eq 0 ]; then
            echo_red "Error! No supported TYPO3 versions found in environment variables."
            return 1
        fi
        printf "%s\n" "${TYPO3_VERSIONS_ARRAY[@]}"
    fi
}

function get_supported_typo3_versions_comma_separated() {
    local TYPO3_VERSIONS_ARRAY=()
    while IFS= read -r line; do
        TYPO3_VERSIONS_ARRAY+=("$line")
    done < <(get_supported_typo3_versions)

    (IFS=', '; echo "${TYPO3_VERSIONS_ARRAY[*]}")
}

function get_supported_php_versions_for_typo3() {
    local TYPO3_VERSION=$1
    local PHP_VERSIONS_ARRAY=()
    eval "IFS=' ' read -r -a PHP_VERSIONS_ARRAY <<< \"\${TYPO3_VERSIONS_${TYPO3_VERSION}_PHP}\""
    if [ ${#PHP_VERSIONS_ARRAY[@]} -eq 0 ]; then
        echo_red "Error! There is no TYPO3_VERSIONS_${TYPO3_VERSION}_PHP variable."
        exit 1
    fi
    printf "%s\n" "${PHP_VERSIONS_ARRAY[@]}"
}

function check_typo3_version() {
    local TYPO3=$1
    local SUPPORTED_TYPO3_VERSIONS=()
    local found=0

    if [ -z "$TYPO3" ]; then
        echo_red "No TYPO3 version provided. Please set one of the supported TYPO3 versions as argument: $(get_supported_typo3_versions_comma_separated)"
        exit 1
    fi

    while IFS= read -r line; do
        SUPPORTED_TYPO3_VERSIONS+=("$line")
    done < <(get_supported_typo3_versions)

    for version in "${SUPPORTED_TYPO3_VERSIONS[@]}"; do
        if [[ "$version" == "$TYPO3" ]]; then
            found=1
            break
        fi
    done

    if [[ $found -eq 0 ]]; then
        echo_red "TYPO3 version '$TYPO3' is not supported."
        exit 1
    fi

    return 0
}

function check_php_version_for_typo3() {
    local TYPO3=$1
    local PHP=$2
    local SUPPORTED_PHP_VERSIONS_ARRAY=()
    local found=0

    while IFS= read -r line; do
        SUPPORTED_PHP_VERSIONS_ARRAY+=("$line")
    done < <(get_supported_php_versions_for_typo3 "$TYPO3")

    for version in "${SUPPORTED_PHP_VERSIONS_ARRAY[@]}"; do
        if [[ "$version" == "$PHP" ]]; then
            found=1
            break
        fi
    done

    if [[ $found -eq 0 ]]; then
        IFS=', '
        echo_red "Invalid second argument. PHP version can only be one of: ${SUPPORTED_PHP_VERSIONS_ARRAY[*]} for TYPO3 ${TYPO3}."
        IFS=' '
        return 1
    fi

    return 0
}

function get_lowest_supported_typo3_versions() {
    local TYPO3_VERSIONS_ARRAY=()
    IFS=' ' read -r -a TYPO3_VERSIONS_ARRAY <<< "$TYPO3_VERSIONS"
    if [ ${#TYPO3_VERSIONS_ARRAY[@]} -eq 0 ]; then
        echo_red "Error! No supported TYPO3 versions found in environment variables."
        exit 1
    fi
    printf "%s\n" "${TYPO3_VERSIONS_ARRAY[@]}" | sort -V | head -n 1
}

function get_lowest_supported_php_versions_for_typo3() {
    local TYPO3=$1
    local PHP_VERSIONS_ARRAY=()
    eval "IFS=' ' read -r -a PHP_VERSIONS_ARRAY <<< \"\${TYPO3_VERSIONS_${TYPO3}_PHP}\""
    if [ ${#PHP_VERSIONS_ARRAY[@]} -eq 0 ]; then
        echo_red "Error! There is no TYPO3_VERSIONS_${TYPO3}_PHP variable."
        exit 1
    fi
    printf "%s\n" "${PHP_VERSIONS_ARRAY[@]}" | sort -V | head -n 1
}

# needs to be use when running in non ddev web container context
function export_ddev_env_vars() {
    local ENV_VARS
    ENV_VARS=$(ddev exec printenv | grep '^TYPO3_VERSIONS')
    while IFS= read -r line; do
        local name
        local value
        name=$(echo "$line" | cut -d '=' -f1)
        value=$(echo "$line" | cut -d '=' -f2-)
        export "$name"="$value"
    done <<< "$ENV_VARS"
}

function echo_magenta() {
    echo -e "\033[35m$1\033[0m"
}

function echo_red() {
    echo -e "\033[41m$1\033[0m"
}

function echo_green() {
    echo -e "\033[32m$1\033[0m"
}
