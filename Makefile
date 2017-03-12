.PHONY: *

all: docker synchronization composer-update tests

clean:
	_USER=$$USER && \
	sudo chown $$_USER:$$_USER . -R

docker:
	sudo docker build -t petrknap/php .

docker-run:
	sudo docker run -v $$(pwd):/app --rm petrknap/php bash -c "cd /app && ${ARGS}"
	make clean

composer:
	make docker-run ARGS="composer ${ARGS}"

composer-install:
	make composer ARGS="install"

composer-update:
	make composer ARGS="update"

synchronization:
	make docker-run ARGS="./bin/synchronize.php"

tests: composer-install
	make docker-run ARGS="./vendor/bin/phpunit ${ARGS}"
