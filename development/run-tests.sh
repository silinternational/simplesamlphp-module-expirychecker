#!/usr/bin/env bash


# Try to install composer dev dependencies
cd /data/vendor/simplesamlphp/simplesamlphp/modules/expirychecker

# If the following fails, exit.
set -e
COMPOSER_ROOT_VERSION=dev-develop composer install --no-interaction --no-scripts

cd /data/

# Run the feature tests
./vendor/bin/behat --config=features/behat.yml

# If they failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi
