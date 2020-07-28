#!/bin/sh

cd $1

# activate maintenance mode
php artisan down

# update source code
git pull

# update PHP dependencies
export COMPOSER_HOME='/var/www/.composer'
composer install --no-interaction --no-dev
	# --no-interaction	Do not ask any interactive question
	# --no-dev		Disables installation of require-dev packages.
	# --prefer-dist		Forces installation from package dist even for dev versions.


# clear cache
# php artisan cache:clear

# config cache
# php artisan config:clear
# php artisan config:cache

# cache route
# php artisan route:clear
# php artisan route:cache

# restart queues
# php artisan -v queue:restart


# update database
php artisan migrate --force
	# --force		Required to run when in production.


# config cache
# php artisan config:clear
# php artisan config:cache


# stop maintenance mode
php artisan up


#su -s /bin/bash www-data -c "composer install"
#su -s /bin/bash www-data -c "php artisan config:clear"
#su -s /bin/bash www-data -c "php artisan migrate --force"
#su -s /bin/bash www-data -c "php artisan route:cache"
#su -s /bin/bash www-data -c "cd /var/www/$ENVIRONMENT/node_app/verify_signed_message && npm ci"
