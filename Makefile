
# Set up the default (i.e. - first) make entry.
start: web

bash:
	docker-compose run --rm web bash

bashtests:
	docker-compose run --rm tests bash

#behat:
#	docker-compose run --rm tests bash -c "vendor/bin/behat --config=features/behat.yml --stop-on-failure"
#
#behatappend:
#	docker-compose run --rm tests bash -c "vendor/bin/behat --config=features/behat.yml --append-snippets"
#
#behatv:
#	docker-compose run --rm tests bash -c "vendor/bin/behat --config=features/behat.yml --stop-on-failure -v"

clean:
	docker-compose kill
	docker system prune -f

composer:
	docker-compose run --rm tests bash -c "composer install --no-scripts"

composerupdate:
	docker-compose run --rm tests bash -c "composer update --no-scripts"

enabledebug:
	docker-compose exec web bash -c "/data/enable-debug.sh"

ps:
	docker-compose ps

#test: composer behat

web:
	docker-compose up -d web
