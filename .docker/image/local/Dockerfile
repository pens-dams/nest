FROM ghcr.io/digital-entropy/dokar-php/octane:8.2

RUN apt update  \
    && pecl channel-update https://pecl.php.net/channel.xml \
    && pecl install xdebug \
    # cleaning...
    && pecl clear-cache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

COPY ./conf.d/20-xdebug.ini /etc/php/8.2/cli/conf.d/20-xdebug.ini
