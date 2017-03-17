.PHONY: *

all: docker synchronization composer-update tests

clean:
	_USER=$$USER && \
	sudo chown $$_USER:$$_USER . -R

add-package:
	[ "${ARGS}" != "" ] && \
	git subtree add --prefix=packages/${ARGS} git://github.com/petrknap/php-`echo ${ARGS} | tr A-Z a-z`.git master

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
	make docker-run ARGS="vendor/bin/phpunit"

tests-on-packages:
	rsync -r --delete --exclude=composer.lock --exclude=vendor packages/ temp/packages/;
	for package in temp/packages/*; do \
		make docker-run ARGS="cd $${package} && composer update && vendor/bin/phpunit"; \
	done

publish: tests
	git subsplit init https://github.com/petrknap/php
	git subsplit publish --heads=master --update "packages/Enum:git@github.com:petrknap/php-enum.git packages/Profiler:git@github.com:petrknap/php-profiler.git packages/ServiceManager:git@github.com:petrknap/php-servicemanager.git packages/Singleton:git@github.com:petrknap/php-singleton.git"
	rm -rf .subsplit
