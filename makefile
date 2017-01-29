.PHONY: *

docker:
	sudo docker build -t petrknap/php-migrationtool .

docker-run:
	sudo docker run -v $$(pwd):/app --rm petrknap/php-migrationtool bash -c 'cd /app && ${ARGS}'

composer:
	make docker-run ARGS="composer ${ARGS}"

composer-install:
	make composer ARGS="install"

composer-update:
	make composer ARGS="update"

tests:
	make docker-run ARGS="php ./vendor/bin/phpunit ${ARGS}"
