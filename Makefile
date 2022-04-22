LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_VERSION := $(shell cat projects/letsencrypt-nginx-reverse-proxy/Dockerfile | sed -n -e '/^ARG PROJECT_VERSION=/p' | sed 's/ARG PROJECT_VERSION=//g')
LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_MAJOR_MINOR_VERSION := $(shell echo $(LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_VERSION) | cut -d . -f -2)
LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_MAJOR_VERSION := $(shell echo $(LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_VERSION) | cut -d . -f -1)
LETSENCRYPT_NGINX_REVERSE_PROXY_NGINX_MAJOR_MINOR_VERSION := $(shell cat projects/letsencrypt-nginx-reverse-proxy/Dockerfile | sed -n -e '/^ARG NGINX_VERSION=/p' | sed 's/ARG NGINX_VERSION=//g')
LETSENCRYPT_NGINX_REVERSE_PROXY_NGINX_MAJOR_VERSION := $(shell echo $(LETSENCRYPT_NGINX_REVERSE_PROXY_NGINX_MAJOR_MINOR_VERSION) | cut -d . -f -1)

SSDP_FAKER_PROJECT_VERSION := $(shell cat projects/ssdp-faker/Dockerfile | sed -n -e '/^ARG PROJECT_VERSION=/p' | sed 's/ARG PROJECT_VERSION=//g')
SSDP_FAKER_PROJECT_MAJOR_MINOR_VERSION := $(shell echo $(SSDP_FAKER_PROJECT_VERSION) | cut -d . -f -2)
SSDP_FAKER_PROJECT_MAJOR_VERSION := $(shell echo $(SSDP_FAKER_PROJECT_VERSION) | cut -d . -f -1)
SSDP_FAKER_NODE_MAJOR_MINOR_VERSION := $(shell cat projects/ssdp-faker/Dockerfile | sed -n -e '/^ARG NODE_VERSION=/p' | sed 's/ARG NODE_VERSION=//g')
SSDP_FAKER_NODE_MAJOR_VERSION := $(shell echo $(SSDP_FAKER_NODE_MAJOR_MINOR_VERSION) | cut -d . -f -1)



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
	sudo docker build -f temp/docker/doctrine.dockerfile -t petrknap/doctrine temp/docker
	sudo docker build -f temp/docker/symfony.dockerfile -t petrknap/symfony temp/docker

docker-run-php:
	sudo docker run -v $$(pwd):/app -v $$(pwd):/mnt/read-only/app:ro --rm petrknap/php bash -c "cd /app && ${ARGS}"
	make clean

docker-run-doctrine:
	sudo docker run -v $$(pwd):/app -v $$(pwd):/mnt/read-only/app:ro --rm petrknap/doctrine bash -c "cd /app && ${ARGS}"
	make clean

docker-run-symfony:
	sudo docker run -v $$(pwd):/app -v $$(pwd):/mnt/read-only/app:ro --rm petrknap/symfony bash -c "cd /app && ${ARGS}"
	make clean

composer:
	make docker-run-php ARGS="COMPOSER=php.composer.json COMPOSER_VENDOR_DIR=vendor/php composer ${ARGS}"
	make docker-run-doctrine ARGS="COMPOSER=doctrine.composer.json COMPOSER_VENDOR_DIR=vendor/doctrine composer ${ARGS}"
	make docker-run-symfony ARGS="COMPOSER=symfony.composer.json COMPOSER_VENDOR_DIR=vendor/symfony composer ${ARGS}"

composer-install:
	make composer ARGS="install"

composer-update:
	make composer ARGS="update"

static-analysis:
	bin/phpstan analyse packages/Php/*/src --autoload-file=vendor/php/autoload.php ${ARGS}
	bin/phpstan analyse packages/Doctrine/*/src --autoload-file=vendor/doctrine/autoload.php ${ARGS}
	bin/phpstan analyse packages/Symfony/*/src --autoload-file=vendor/symfony/autoload.php ${ARGS}

tests: composer-install
	make tests-php ARGS="${ARGS}"
	make tests-doctrine ARGS="${ARGS}"
	make tests-symfony ARGS="${ARGS}"

tests-php:
	make docker-run-php ARGS="vendor/php/bin/phpunit -c php.phpunit.xml --testdox-text php.phpunit.log ${ARGS}"

tests-doctrine:
	make docker-run-doctrine ARGS="vendor/doctrine/bin/phpunit -c doctrine.phpunit.xml --testdox-text doctrine.phpunit.log ${ARGS}"

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

publish: publish-web publish-home publish-ssdp-faker publish-clock publish-ffmpeg publish-docker publish-packages

publish-packages: static-analysis tests
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "packages/Php/Enum:git@github.com:petrknap/php-enum.git packages/Php/MigrationTool:git@github.com:petrknap/php-migrationtool.git packages/Php/Profiler:git@github.com:petrknap/php-profiler.git packages/Php/Singleton:git@github.com:petrknap/php-singleton.git packages/Php/SpaydQr:git@github.com:petrknap/php-spaydqr.git packages/Php/SplitFilesystem:git@github.com:petrknap/php-splitfilesystem.git" #generated php
	git subsplit publish --heads=master --update "packages/Doctrine/NamingStrategy:git@github.com:petrknap/doctrine-namingstrategy.git" #generated doctrine
	rm -rf .subsplit

publish-web:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/petrknap.github.io:git@github.com:petrknap/petrknap.github.io.git"
	rm -rf .subsplit

publish-home:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/home:git@github.com:petrknap/home.git"
	rm -rf .subsplit

publish-letsencrypt-nginx-reverse-proxy:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/letsencrypt-nginx-reverse-proxy:git@github.com:petrknap/letsencrypt-nginx-reverse-proxy.git"
	rm -rf .subsplit
	curl --fail https://github.com/petrknap/letsencrypt-nginx-reverse-proxy/releases/tag/v${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_VERSION} \
	&& docker build projects/letsencrypt-nginx-reverse-proxy \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_VERSION} \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_VERSION}-${LETSENCRYPT_NGINX_REVERSE_PROXY_NGINX_MAJOR_MINOR_VERSION} \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_VERSION}-${LETSENCRYPT_NGINX_REVERSE_PROXY_NGINX_MAJOR_VERSION} \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_MAJOR_MINOR_VERSION} \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_MAJOR_MINOR_VERSION}-${LETSENCRYPT_NGINX_REVERSE_PROXY_NGINX_MAJOR_MINOR_VERSION} \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_MAJOR_MINOR_VERSION}-${LETSENCRYPT_NGINX_REVERSE_PROXY_NGINX_MAJOR_VERSION} \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_MAJOR_VERSION} \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_MAJOR_VERSION}-${LETSENCRYPT_NGINX_REVERSE_PROXY_NGINX_MAJOR_MINOR_VERSION} \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:${LETSENCRYPT_NGINX_REVERSE_PROXY_PROJECT_MAJOR_VERSION}-${LETSENCRYPT_NGINX_REVERSE_PROXY_NGINX_MAJOR_VERSION} \
	--tag petrknap/letsencrypt-nginx-reverse-proxy:latest \
	|| docker build projects/letsencrypt-nginx-reverse-proxy --tag petrknap/letsencrypt-nginx-reverse-proxy:latest
	docker push petrknap/letsencrypt-nginx-reverse-proxy

publish-ssdp-faker:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/ssdp-faker:git@github.com:petrknap/ssdp-faker.git"
	rm -rf .subsplit
	curl --fail https://github.com/petrknap/ssdp-faker/releases/tag/v${SSDP_FAKER_PROJECT_VERSION} \
	&& docker build projects/ssdp-faker \
	--tag petrknap/ssdp-faker:${SSDP_FAKER_PROJECT_VERSION} \
	--tag petrknap/ssdp-faker:${SSDP_FAKER_PROJECT_VERSION}-${SSDP_FAKER_NODE_MAJOR_MINOR_VERSION} \
	--tag petrknap/ssdp-faker:${SSDP_FAKER_PROJECT_VERSION}-${SSDP_FAKER_NODE_MAJOR_VERSION} \
	--tag petrknap/ssdp-faker:${SSDP_FAKER_PROJECT_MAJOR_MINOR_VERSION} \
	--tag petrknap/ssdp-faker:${SSDP_FAKER_PROJECT_MAJOR_MINOR_VERSION}-${SSDP_FAKER_NODE_MAJOR_MINOR_VERSION} \
	--tag petrknap/ssdp-faker:${SSDP_FAKER_PROJECT_MAJOR_MINOR_VERSION}-${SSDP_FAKER_NODE_MAJOR_VERSION} \
	--tag petrknap/ssdp-faker:${SSDP_FAKER_PROJECT_MAJOR_VERSION} \
	--tag petrknap/ssdp-faker:${SSDP_FAKER_PROJECT_MAJOR_VERSION}-${SSDP_FAKER_NODE_MAJOR_MINOR_VERSION} \
	--tag petrknap/ssdp-faker:${SSDP_FAKER_PROJECT_MAJOR_VERSION}-${SSDP_FAKER_NODE_MAJOR_VERSION} \
	--tag petrknap/ssdp-faker:latest \
	|| docker build projects/ssdp-faker --tag petrknap/ssdp-faker:latest
	docker push petrknap/ssdp-faker

publish-clock:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/clock:git@github.com:petrknap/clock.git"
	rm -rf .subsplit

publish-ffmpeg:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "projects/ffmpeg:git@github.com:petrknap/ffmpeg.git"
	rm -rf .subsplit

publish-docker: publish-docker-myetherwallet-mew publish-docker-n2n-supernode publish-docker-selenium-needle publish-docker-syslog

publish-docker-myetherwallet-mew:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "docker/myetherwallet-mew:git@github.com:petrknap/docker-myetherwallet-mew.git"
	rm -rf .subsplit

publish-docker-n2n-supernode:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "docker/n2n-supernode:git@github.com:petrknap/docker-n2n-supernode.git"
	rm -rf .subsplit

publish-docker-selenium-needle:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "docker/selenium-needle:git@github.com:petrknap/docker-selenium-needle.git"
	rm -rf .subsplit

publish-docker-syslog:
	git subsplit init https://github.com/petrknap/trunk
	git subsplit publish --heads=master --update "docker/syslog:git@github.com:petrknap/docker-syslog.git"
	rm -rf .subsplit
