#!/bin/bash

function get_supported_typo3_versions() {
    if [ -z "${TYPO3_VERSIONS+x}" ]; then
        message red "TYPO3_VERSIONS is unset. Please set it before running this function."
        return 1
    else
        local TYPO3_VERSIONS_ARRAY=()
        IFS=' ' read -r -a TYPO3_VERSIONS_ARRAY <<< "$TYPO3_VERSIONS"
        if [ ${#TYPO3_VERSIONS_ARRAY[@]} -eq 0 ]; then
            message red "Error! No supported TYPO3 versions found in environment variables."
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
        message red "Error! There is no TYPO3_VERSIONS_${TYPO3_VERSION}_PHP variable."
        exit 1
    fi
    printf "%s\n" "${PHP_VERSIONS_ARRAY[@]}"
}

function check_typo3_version() {
    local TYPO3=$1
    local SUPPORTED_TYPO3_VERSIONS=()
    local found=0

    if [ -z "$TYPO3" ]; then
        message red "No TYPO3 version provided. Please set one of the supported TYPO3 versions as argument: $(get_supported_typo3_versions_comma_separated)"
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
        message red "TYPO3 version '$TYPO3' is not supported."
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
        message red "Invalid second argument. PHP version can only be one of: ${SUPPORTED_PHP_VERSIONS_ARRAY[*]} for TYPO3 ${TYPO3}."
        IFS=' '
        return 1
    fi

    return 0
}

function get_lowest_supported_typo3_versions() {
    local TYPO3_VERSIONS_ARRAY=()
    IFS=' ' read -r -a TYPO3_VERSIONS_ARRAY <<< "$TYPO3_VERSIONS"
    if [ ${#TYPO3_VERSIONS_ARRAY[@]} -eq 0 ]; then
        message red "Error! No supported TYPO3 versions found in environment variables."
        exit 1
    fi
    printf "%s\n" "${TYPO3_VERSIONS_ARRAY[@]}" | sort -V | head -n 1
}

function get_lowest_supported_php_versions_for_typo3() {
    local TYPO3=$1
    local PHP_VERSIONS_ARRAY=()
    eval "IFS=' ' read -r -a PHP_VERSIONS_ARRAY <<< \"\${TYPO3_VERSIONS_${TYPO3}_PHP}\""
    if [ ${#PHP_VERSIONS_ARRAY[@]} -eq 0 ]; then
        message red "Error! There is no TYPO3_VERSIONS_${TYPO3}_PHP variable."
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

stashComposerFiles() {
    cp composer.json composer.json.orig
}

restoreComposerFiles() {
    local exit_status=$?
    if [ -f "composer.json.orig" ]; then
        mv composer.json.orig composer.json
        local message='composer.json has been restored.'
        if [ $exit_status -eq 0 ]; then
            message green "${message}"
        else
            message red "${message}"
        fi
    fi
}

message() {
    local color=$1
    local message=$2

    case $color in
        red)
            echo -e "\033[31m$message\033[0m"
            ;;
        green)
            echo -e "\033[32m$message\033[0m"
            ;;
        yellow)
            echo -e "\033[33m$message\033[0m"
            ;;
        blue)
            echo -e "\033[34m$message\033[0m"
            ;;
        magenta)
            echo -e "\033[35m$message\033[0m"
            ;;
        cyan)
            echo -e "\033[36m$message\033[0m"
            ;;
        *)
            echo "$message"
            ;;
    esac
}
