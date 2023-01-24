#!/usr/bin/env bash

# If the following fails, exit.
set -e

COMPOSER_ROOT_VERSION=dev-develop composer install --no-interaction --no-scripts

# Run the feature tests
./vendor/bin/behat --config=features/behat.yml
