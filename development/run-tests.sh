#!/usr/bin/env bash

# If the following fails, exit.
set -e

# Run the feature tests
./vendor/bin/behat --config=features/behat.yml
