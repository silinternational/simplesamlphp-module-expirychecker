#!/usr/bin/env bash

cd /data

# If the following fails, exit.
set -e
composer install --no-scripts

./vendor/bin/phpunit -v /data/unittests/