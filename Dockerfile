FROM alpine:3.18

ENV TZ=UTC \
    WORKDIR=/var/www/ \
    USER=rinha \
    GROUP=rinha \
    SUPERV=/etc/supervisor.d/ \
    COMPOSER=/usr/bin/composer

WORKDIR ${WORKDIR}

COPY ./.build/app.conf /etc/nginx/conf.d/default.conf
COPY ./.build/www.conf /etc/php82/php-fpm.d/www.conf
COPY ./.build/nginx.conf /etc/nginx/nginx.conf
COPY ./.build/repositories /etc/apk/repositories
COPY ./.build/supervisord.conf /etc/supervisord.conf

RUN apk update && apk upgrade --no-cache --no-progress && apk add \
    php82 php82-fpm php82-bcmath php82-bz2 php82-calendar php82-cgi php82-common php82-ctype \
    php82-curl php82-dba php82-dev php82-doc php82-dom php82-embed \
    php82-enchant php82-exif php82-fileinfo php82-fpm php82-ftp php82-gd \
    php82-gettext php82-gmp php82-iconv php82-imap php82-intl php82-json \
    php82-ldap php82-litespeed php82-mbstring php82-mysqli php82-mysqlnd \
    php82-odbc php82-opcache php82-openssl php82-pcntl php82-pdo php82-pdo_dblib \
    php82-pdo_mysql php82-pdo_odbc php82-pdo_pgsql php82-pdo_sqlite php82-pear \
    php82-pgsql php82-phar php82-phpdbg php82-posix php82-pspell \
    php82-session php82-shmop php82-simplexml php82-snmp php82-soap php82-sockets \
    php82-sodium php82-sqlite3 php82-sysvmsg php82-sysvsem php82-sysvshm \
    php82-tidy php82-tokenizer php82-xml php82-xmlreader \
    php82-xmlwriter php82-xsl php82-zip php82-pecl-xhprof php82-pecl-xhprof-assets \
    php82-pecl-uuid php82-pecl-protobuf php82-pecl-memcached \
    php82-pecl-ssh2 php82-pecl-imagick php82-pecl-imagick-dev \
    php82-pecl-ast php82-pecl-redis php82-pecl-apcu \
    php82-pecl-msgpack php82-pecl-yaml php82-brotli php82-pecl-amqp \
    php82-pecl-igbinary php82-pecl-lzf \
    py3-pip curl tzdata libjpeg-turbo-dev libjpeg-turbo oniguruma oniguruma-dev icu-data-full nginx zip libcap git \
    --no-cache --no-progress && \
    \
    ln -s /usr/bin/php82 /usr/bin/php && \
    \
    ln -s /usr/sbin/php-fpm82 /usr/bin/php-fpm && \
    \
    ln -s /usr/bin/pecl82 /usr/bin/pecl && \
    \
    PHP_VERSION=$(php -r 'echo PHP_VERSION;') && \
    curl https://raw.githubusercontent.com/php/php-src/php-${PHP_VERSION}/php.ini-production --output php.ini-production && \
    mv php.ini-production /etc/php82 && \
    \
    cp /usr/share/zoneinfo/${TZ} /etc/localtime && \
    \
    pip install supervisor -q && \
    \
    mkdir -p /run/nginx ${SUPERV} && \
    \
    setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx && \
    \
    addgroup -g 1000 ${GROUP} && \
    adduser -G ${GROUP} -H -D -s /sbin/nologin -u 1000 ${USER} && \
    \
    chmod 770 -R ${WORKDIR} && \
    \
    chown -R ${USER}:${GROUP} ${WORKDIR} ${SUPERV} /run /var

COPY ./.build/php-ini-overrides.ini /etc/php82/conf.d/99-overrides.ini

COPY --chown=${USER}:${GROUP} ./ ${WORKDIR}

# Opcache settings
COPY ./.build/opcache.ini /etc/php82/conf.d/90-opcache.ini

# PHP production settings
RUN rm /etc/php82/php.ini && mv /etc/php82/php.ini-production /etc/php82/php.ini

# Don't remove
USER ${USER}

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisord.conf", "-s"]
