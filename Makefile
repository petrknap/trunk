.PHONY: *

all: docker synchronization composer-update

docker:
	sudo docker build -t petrknap/php .

docker-run:
	sudo docker run -v $$(pwd):/app --rm petrknap/php bash -c 'cd /app && ${ARGS}'

composer:
	make docker-run ARGS="composer ${ARGS}"

composer-install:
	make composer ARGS="install"

composer-update:
	make composer ARGS="update"

synchronization:
	make docker-run ARGS="./bin/synchronize.php"

tests:
	rsync -r ./packages/ ./temp/packages/
	make .forPackages ARGS=".testPackage"

.forPackages:
	for package in enum; do \
		make ${ARGS} ARGS="$$package"; \
	done;

.testPackage:
	make docker-run ARGS="cd ./temp/packages/${ARGS} && composer install && ./vendor/bin/phpunit"
