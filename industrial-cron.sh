#!/bin/bash

W_DIR='/home/casqui/industrial-ferretera-ia'
PHP_BIN='/usr/bin/php83'

function main()
{
    $PHP_BIN unifersa u:download-csv &>/dev/null || exit 1
    $PHP_BIN unifersa u:export-discontinued-products-to-csv &>/dev/null || exit 1

    if [[ "$1" -eq 'full' ]]
    then
        $PHP_BIN unifersa u:improve-texts-with-ai &>/dev/null || exit 1
        $PHP_BIN unifersa u:export-db-to-csv --last &>/dev/null || exit 1
    fi

    exit 0
}

main "$@"