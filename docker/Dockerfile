# Start from the WordPress image
# using a bitnami image as it's a default rootless image that works well on OCP
FROM bitnami/wordpress-nginx:6.6.1

USER root

ENV COMPOSER_ALLOW_SUPERUSER=1

SHELL [ "/bin/bash", "-c" ]

# Install necessary packages
RUN apt-get update && \
    apt-get install -y apt-transport-https wget curl unzip vim htop memcached libmemcached-tools git && \
    rm -rf /var/lib/apt/lists/*

# Install composer for managing dependencies
RUN EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')" \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"; \
    \
    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then \
        # >&2 echo 'ERROR: Invalid installer checksum' \
        # rm composer-setup.php \
        # exit 1 \
        exit; \
    fi; \
    \
    php composer-setup.php ; \
    RESULT=$? ; \
    rm composer-setup.php ; \
    mv composer.phar /usr/local/bin/composer ; \
    exit "$RESULT"


# The /opt/bitnami/wordpress folder is the source folder that will then generate content in /bitnami/wordpress/wp-content (for R/W abilities)
WORKDIR /opt/bitnami/wordpress/

# custom config the nginx to provide metrics for prometheus sidecar
COPY nginx-prometheus-exporter-location-block.conf /opt/bitnami/nginx/conf/bitnami/nginx-prometheus-exporter-location-block.conf

# With cacheing and potentially other customizations in the future, just use our own nginx config based off the default
COPY wordpress-server-block.conf /opt/bitnami/nginx/conf/server_blocks/wordpress-server-block.conf
COPY nginx.conf /opt/bitnami/nginx/conf/nginx.conf

# Enable memcache in the run script
RUN sed -i 's|trap _forwardTerm TERM|trap _forwardTerm TERM\n\ninfo "Starting Memcached..."\n/usr/bin/memcached -d -u memcache -vv logfile /tmp/memcached.log -l 0.0.0.0 -p 11211 -c 1024 -m 128 -I 1m |g' /opt/bitnami/scripts/nginx-php-fpm/run.sh

# To tune for a high traffic site the default of 5 php-fpm children isn't enough so we override it here
# NOTE: pm.max_requests is default to 0 (endless) and pm.process_idle_timeout default is

# Changing FPM from pm = ondemand to dynamic to help with higher traffic
RUN sed -i 's|pm.ondemand|pm = static|g' /opt/bitnami/php/etc/php-fpm.d/www.conf
RUN sed -i 's/;clear_env/clear_env/g' /opt/bitnami/php/etc/php-fpm.d/www.conf
RUN sed -i 's|pm.max_children = 5|pm.max_children = 100|g' /opt/bitnami/php/etc/php-fpm.d/www.conf

# install the themes and plugins as per the composer.json definition
COPY mu-loader.php wp-content/mu-plugins/mu-loader.php
COPY composer.json /opt/bitnami/wordpress/composer.json
# TODO: REMOVE THIS auth.json in favour of using a secret
COPY auth.json /opt/bitnami/wordpress/auth.json
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer update

COPY mu-loader.php /opt/bitnami/wordpress/wp-content/mu-plugins/mu-loader.php

# after the plugins are installed this is used to config the W3 Cache plugin
RUN mkdir -p /opt/bitnami/wordpress/wp-content/w3tc-config
COPY w3_cache_config_file_master.php /opt/bitnami/wordpress/wp-content/w3tc-config/master.php

RUN chown -R daemon:root /opt/bitnami/wordpress/wp-content && \
    chmod -R 775 /opt/bitnami/wordpress/wp-content

# TODO: This is fine during debugging, but we should probably remove and/or manage the file in prod otherwise it'll just grow
# There's an issue with the output for PHP-FPM and nginx-error logs.  They both point to /proc/self/fd/1 and /proc/self/fd/2 respectively.
# Due to the way bitnami has generated the container using a parent control script 'self' ends up being a non-one process id
# which means it doesn't output to console. redirecting the symlinks to allow for output of PHP-FPM and Nginx error logs to go to console.
RUN unlink /opt/bitnami/php/logs/php-fpm.log ; \
    echo "error_log = /proc/1/fd/2" >> /opt/bitnami/php/etc/php.ini

# The plugins depend on access to the vendor folder for accessing shared libraries. Some of these plugins are designed specifically
# for the environment in which the plugin was installed which has different paths in place than this default WP install. For that
# reason we are creating some symlinks to allow the plugins to find the vendor folder.
# We have two because it's unclear at runtime which path the plugin would be using...since the wp-content folder is a symlink itself
# and different plugins use different methods of discovering the vendor folder.
# oh, and this doesn't account for all the methods.  Just the ones we've needed for the time being.
# TODO: This is ugly. There must be a better way within the code of not being dependant on the folder structure in this way.
RUN ln -sf /opt/bitnami/wordpress/vendor /opt/bitnami/vendor
RUN ln -sf /opt/bitnami/wordpress/vendor /bitnami/vendor

USER 1001
