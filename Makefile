.PHONY: test behat phpspec server-start server-stop fixtures install clear clear-dev clear-prod

test: behat phpspec

behat:
	./vendor/bin/behat

phpspec:
	./vendor/bin/phpspec run

server-start:
	php bin/console server:start

server-stop:
	php bin/console server:stop

fixtures:
	php bin/console moodvalue:fixtures:load -vvv

install:
	composer install

clear: clear-dev

clear-dev:
	php bin/console c:c --env=dev

clear-prod:
	php bin/console c:c --env=prod
