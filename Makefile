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
	sudo docker build -f temp/docker/symfony.dockerfile -t petrknap/symfony temp/docker

docker-run-php:
	sudo docker run -v $$(pwd):/app -v $$(pwd):/mnt/read-only/app:ro --rm petrknap/php bash -c "cd /app && ${ARGS}"
	make clean

docker-run-symfony:
	sudo docker run -v $$(pwd):/app -v $$(pwd):/mnt/read-only/app:ro --rm petrknap/symfony bash -c "cd /app && ${ARGS}"
	make clean

composer:
	make docker-run-php ARGS="COMPOSER=php.composer.json COMPOSER_VENDOR_DIR=vendor/php composer ${ARGS}"
	make docker-run-symfony ARGS="COMPOSER=symfony.composer.json COMPOSER_VENDOR_DIR=vendor/symfony composer ${ARGS}"

composer-install:
	make composer ARGS="install"

composer-update:
	make composer ARGS="update"

static-analysis:
	bin/phpstan analyse packages/Php/*/src --autoload-file=vendor/php/autoload.php ${ARGS}
	bin/phpstan analyse packages/Symfony/*/src --autoload-file=vendor/symfony/autoload.php ${ARGS}

tests: composer-install
	make tests-php ARGS="${ARGS}"
	make tests-symfony ARGS="${ARGS}"

tests-php:
	make docker-run-php ARGS="vendor/php/bin/phpunit -c php.phpunit.xml --testdox-text php.phpunit.log ${ARGS}"

tests-symfony:
	make docker-run-symfony ARGS="vendor/symfony/bin/phpunit -c symfony.phpunit.xml --testdox-text symfony.phpunit.log ${ARGS}"

tests-on-packages:
	rsync -r --delete --exclude=composer.lock --exclude=vendor packages/ temp/packages/;
	for package in temp/packages/Php/*; do \
		make docker-run-php ARGS="cd $${package} && composer update && vendor/bin/phpunit ${ARGS}"; \
	done
	for package in temp/packages/Symfony/*; do \
		make docker-run-symfony ARGS="cd $${package} && composer update && vendor/bin/phpunit ${ARGS}"; \
	done

publish: publish-web publish-home publish-clock publish-ffmpeg publish-packages

publish-packages: static-analysis tests
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "packages/Php/Enum:git@github.com:petrknap/php-enum.git packages/Php/MigrationTool:git@github.com:petrknap/php-migrationtool.git packages/Php/Profiler:git@github.com:petrknap/php-profiler.git packages/Php/Singleton:git@github.com:petrknap/php-singleton.git packages/Php/SplitFilesystem:git@github.com:petrknap/php-splitfilesystem.git" #generated php
	rm -rf .subsplit

publish-web:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/petrknap.github.io:git@github.com:petrknap/petrknap.github.io.git"
	rm -rf .subsplit

publish-home:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/home:git@github.com:petrknap/home.git"
	rm -rf .subsplit

publish-clock:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/clock:git@github.com:petrknap/clock.git"
	rm -rf .subsplit

publish-ffmpeg:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/ffmpeg:git@github.com:petrknap/ffmpeg.git"
	rm -rf .subsplit
