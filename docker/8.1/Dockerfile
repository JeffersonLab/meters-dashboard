FROM ubuntu:22.04

LABEL maintainer="Theo Larrieu"

ARG WWWGROUP
ARG NODE_VERSION=16
ARG POSTGRES_VERSION=14

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# To be able to download `ca-certificates` with `apk add` command
COPY JLabCA.crt /usr/local/share/ca-certificates/
COPY acc-ca.crt /usr/local/share/ca-certificates/
COPY openssl.conf /usr/local/share/ca-certificates/

RUN apt-get update && apt-get install -y ca-certificates \
&& chmod 644 /usr/local/share/ca-certificates/JLabCA.crt \
&& chmod 644 /usr/local/share/ca-certificates/acc-ca.crt \
&& update-ca-certificates

RUN export NODE_EXTRA_CA_CERTS=/usr/local/share/ca-certificates/JLabCA.crt \
    && export OPENSSL_CONF=/usr/local/share/ca-certificates/openssl.conf \
    && apt-get clean && apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && echo "keyserver hkps://keyserver.ubuntu.com:443" >> ~/.gnupg/dirmngr.conf \
    && gpg --recv-key 0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c \
    && gpg --export 0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c > /usr/share/keyrings/ppa_ondrej_php.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.1-cli php8.1-dev \
       php8.1-pgsql php8.1-sqlite3 php8.1-gd \
       php8.1-curl \
       php8.1-imap php8.1-mysql php8.1-mbstring \
       php8.1-xml php8.1-zip php8.1-bcmath php8.1-soap \
       php8.1-intl php8.1-readline \
       php8.1-ldap \
       php8.1-msgpack php8.1-igbinary php8.1-redis php8.1-swoole \
       php8.1-memcached php8.1-pcov php8.1-xdebug \
    && php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -sLS https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && curl -k -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarn.gpg >/dev/null \
    && echo "deb [signed-by=/usr/share/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && curl -k -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /usr/share/keyrings/pgdg.gpg >/dev/null \
    && echo "deb [signed-by=/usr/share/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt jammy-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y yarn \
    && apt-get install -y mysql-client \
    && apt-get install -y postgresql-client-$POSTGRES_VERSION \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Is there some way to avoid having _21_9 in the instantclient path?
RUN echo "Options = UnsafeLegacyRenegotiation" >> /etc/ssl/openssl.cnf \
    && mkdir /opt/oracle && cd /opt/oracle \
    && curl -sS -O https://download.oracle.com/otn_software/linux/instantclient/instantclient-basic-linuxx64.zip \
    && curl -sS -O https://download.oracle.com/otn_software/linux/instantclient/instantclient-sqlplus-linuxx64.zip \
    && curl -sS -O https://download.oracle.com/otn_software/linux/instantclient/instantclient-sdk-linuxx64.zip \
    && unzip instantclient-basic-linuxx64.zip && unzip instantclient-sqlplus-linuxx64.zip && unzip instantclient-sdk-linuxx64.zip \
    && rm instantclient-basic-linuxx64.zip instantclient-sqlplus-linuxx64.zip instantclient-sdk-linuxx64.zip \
    && apt-get update && apt-get install -y libaio1 \
    && sh -c "echo /opt/oracle/instantclient_21_9 > /etc/ld.so.conf.d/oracle-instantclient.conf" \
    && ldconfig

# Enable legacy content renegotation
RUN cd /var/tmp \
    && curl -sS -O https://pecl.php.net/get/oci8-3.2.1.tgz \
    && tar xf oci8-3.2.1.tgz && cd oci8-3.2.1 \
    && phpize && ./configure --with-oci8=instantclient,/opt/oracle/instantclient_21_9 \
    && make && make install \
    && echo "extension=oci8.so" > /etc/php/8.1/cli/conf.d/20-oci8.ini



RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.1

RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail



COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.1/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

ENTRYPOINT ["start-container"]
