#!/bin/bash
[[ ! -e /.dockerenv ]] && exit 0
set -xe
apt-get update -yqq
apt-get install git -yqq
apt-get install -y zip unzip

curl --location --output /usr/bin/phpunit https://phar.phpunit.de/phpunit-7.5.3.phar
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
chmod +x /usr/bin/phpunit

docker-php-ext-install pdo_mysql 
