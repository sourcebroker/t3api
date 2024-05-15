TEXT_RED=$(tput setaf 1)
TEXT_RESET=$(tput sgr0)
POSTMAN_TEST_PATH=/var/www/html/./Tests/Postman/

if [[ ! -e "${POSTMAN_TEST_PATH}" ]]; then echo "${TEXT_RED}No POSTMAN_TEST_PATH folder: ${POSTMAN_TEST_PATH}${TEXT_RESET}" && exit 1; fi;
cd ${POSTMAN_TEST_PATH}

if [[ ! -e .nvmrc ]]; then echo "${TEXT_RED}No file .nvmrc with node version in folder: ${POSTMAN_TEST_PATH}${TEXT_RESET}" && exit 1; fi;

if [[ ! -d "node_modules" ]]; then
    npm ci
fi

POSSIBLE_ARGUMENTS=(v12 v13)
if [[ ! ${POSSIBLE_ARGUMENTS[*]} =~ $1 ]]; then
    INFO=$(printf ", %s" "${POSSIBLE_ARGUMENTS[@]}")
    INFO=${INFO:1}
    echo "${TEXT_RED}Error! Wrong argument. Possible arguments are:${INFO}${TEXT_RESET}."
    exit 1
fi

VERSION=v12
if [[ $1 = "v13" ]]; then
    VERSION=v13
fi

if [[ ! -d "/var/www/html/.test/$VERSION" ]]; then
        echo "${TEXT_RED}Can not test. Install first $VERSION with command 'ddev install-$VERSION'${TEXT_RESET}"
    else
        ./node_modules/.bin/newman run t3apinews.postman_collection.json --env-var "baseUrl=https://$VERSION.t3api.ddev.site"
fi
