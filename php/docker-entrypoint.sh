#!/bin/bash -eu

setting() {
    setting="${1}"
    value="${2}"
    file="${3}"

    if [ -n "${value}" ]; then
        if grep --quiet --extended-regexp "${setting}\s*=" /app/application/configs/"${file}"; then
            sed --in-place "s|.*${setting}\s*=.*|${setting}=${value}|" /app/application/configs/"${file}"
        else
            echo "${setting}=${value}" >>/app/application/configs/"${file}"
        fi
    fi
}

setting "resources.db.params.host" "${BDQ_MYSQL_HOST:-mysql}" application.ini
setting "resources.db.params.username" "${BDQ_MYSQL_USER:-bdq}" application.ini
setting "resources.db.params.password" "${BDQ_MYSQL_PASSWORD:-bdq}" application.ini
setting "resources.db.params.dbname" "${BDQ_MYSQL_DATABASE:-bdq}" application.ini
setting "host" "${BDQ_SOLR_HOST:-solr}" solr.ini
setting "urlDirectory" "${BDQ_SOLR_URI:-solr}" solr.ini
setting "port" "${BDQ_SOLR_PORT:-8983}" solr.ini
setting "timeout" "${BDQ_SOLR_TIMEOUT:-30}" solr.ini

exec php-fpm
