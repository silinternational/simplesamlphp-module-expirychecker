#!/usr/bin/env bash

cd /data
composer install --no-scripts

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

./vendor/bin/phpunit -v /data/unittests/