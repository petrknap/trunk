.PHONY: *

all: docker synchronization composer-update tests

clean:
	_USER=$$USER && \
	sudo chown $$_USER:$$_USER . -R

add-package:
	read -p "Enter package context: " context; \
	read -p "Enter package name: " name; \
	[ "$$context" != "" ] && [ "$$name" != "" ] && \
	git subtree add --prefix=packages/$$context/$$name git://github.com/petrknap/`echo $$context | tr A-Z a-z`-`echo $$name | tr A-Z a-z`.git master

synchronization:
	sudo docker run -v $$(pwd):/app --rm php:7.0-cli bash -c "cd /app && ./bin/synchronize.php"
	make clean

docker:
	mkdir temp/docker || true
	cp *.dockerfile temp/docker
	sudo docker build -f temp/docker/php.dockerfile -t petrknap/php temp/docker
	sudo docker build -f temp/docker/nette.dockerfile -t petrknap/nette temp/docker

docker-run-php:
	sudo docker run -v $$(pwd):/app -v $$(pwd):/mnt/read-only/app:ro --rm petrknap/php bash -c "cd /app && ${ARGS}"
	make clean

docker-run-nette:
	sudo docker run -v $$(pwd):/app -v $$(pwd):/mnt/read-only/app:ro --rm petrknap/nette bash -c "cd /app && ${ARGS}"
	make clean

composer:
	make docker-run-php ARGS="COMPOSER=php.composer.json COMPOSER_VENDOR_DIR=vendor/php composer ${ARGS}"
	make docker-run-nette ARGS="COMPOSER=nette.composer.json COMPOSER_VENDOR_DIR=vendor/nette composer ${ARGS}"

composer-install:
	make composer ARGS="install"

composer-update:
	make composer ARGS="update"

tests: composer-install
	make docker-run-php ARGS="vendor/php/bin/phpunit packages/Php -c php.phpunit.xml --testdox-text php.phpunit.log ${ARGS}"
	make docker-run-nette ARGS="vendor/nette/bin/phpunit packages/Nette -c nette.phpunit.xml --testdox-text nette.phpunit.log ${ARGS}"

tests-on-packages:
	rsync -r --delete --exclude=composer.lock --exclude=vendor packages/ temp/packages/;
	for package in temp/packages/Php/*; do \
		make docker-run-php ARGS="cd $${package} && composer update && vendor/bin/phpunit ${ARGS}"; \
	done
	for package in temp/packages/Nette/*; do \
		make docker-run-nette ARGS="cd $${package} && composer update && vendor/bin/phpunit ${ARGS}"; \
	done

publish: publish-web tests
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "packages/Php/Enum:git@github.com:petrknap/php-enum.git packages/Php/FileStorage:git@github.com:petrknap/php-filestorage.git packages/Php/Profiler:git@github.com:petrknap/php-profiler.git packages/Php/ServiceManager:git@github.com:petrknap/php-servicemanager.git packages/Php/Singleton:git@github.com:petrknap/php-singleton.git" #generated php
	git subsplit publish --heads=master --update "packages/Nette/Bootstrap:git@github.com:petrknap/nette-bootstrap.git" #generated nette
	rm -rf .subsplit

publish-web:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/petrknap.github.io:git@github.com:petrknap/petrknap.github.io.git"
	rm -rf .subsplit
