
# Set up the default (i.e. - first) make entry.
start: web

bash:
	docker-compose run --rm idp bash

bashtests:
	docker-compose run --rm tests bash

behat:
	docker-compose run --rm tests bash -c "vendor/bin/behat --config=features/behat.yml --strict --stop-on-failure --append-snippets"

clean:
	docker-compose kill
	docker-compose rm -f

composer:
	docker-compose run --rm composer bash -c "composer install --no-scripts"
# 	touch vendor/simplesamlphp/simplesamlphp/www/saml2/idp/SSOService.php
# 	touch vendor/simplesamlphp/simplesamlphp/config/authsources.php
# 	touch vendor/simplesamlphp/simplesamlphp/metadata/saml20-idp-hosted.php
# 	touch vendor/simplesamlphp/simplesamlphp/metadata/saml20-sp-remote.php
# 	touch vendor/simplesamlphp/simplesamlphp/metadata/saml20-idp-remote.php

composerupdate:
	docker-compose run --rm composer bash -c "composer update --no-scripts"

enabledebug:
	docker-compose exec idp bash -c "/data/enable-debug.sh"

ps:
	docker-compose ps

test: composer web
	sleep 10
	make behat

web:
	docker-compose up -d idp sp
