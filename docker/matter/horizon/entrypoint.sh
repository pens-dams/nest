#!/bin/bash
set -Eeo pipefail
set -o errexit    # Used to exit upon error, avoiding cascading errors

IFS=$'\n\t'

getent group ${USER:-dokar} || groupadd -g ${PGID:-1000} ${USER:-dokar}
getent passwd ${USER:-dokar} || useradd --gid ${PGID:-1000} --uid ${PUID:-1000} -m ${USER:-dokar}

runuser -l ${USER:-dokar} -c 'cd /var/www && php artisan horizon -q'
