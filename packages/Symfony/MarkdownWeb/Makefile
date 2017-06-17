docker-build:
	sudo docker build -t symfony/markdown_web .

docker-run:
	sudo docker run -v $$(pwd):/var/www/html --rm symfony/markdown_web bash -c "cd /var/www/html && ${ARGS}"
	make clean

web-server: clean
	make web-console ARGS="cache:warmup --env dev"
	make web-console ARGS="cache:warmup --env prod"
	sudo docker run -v $$(pwd):/var/www/html --rm symfony/markdown_web

web-console: clean
	make docker-run ARGS="php bin/console ${ARGS}"

web-cache:
	make web-console ARGS="markdown-web:build-cache ${ARGS}"

clean:
	(cd ../../../ && make clean)
	chmod 0777 ./var -R | true
	rm -rf var/cache
	rm -rf var/logs
	rm -rf var/sessions

composer:
	make docker-run ARGS="composer ${ARGS}"

composer-install:
	make composer ARGS="install"

composer-update:
	make composer ARGS="update"
