docker-build:
	sudo docker build -t marketass.cz .

docker-run:
	sudo docker run -v $$(pwd)/../..:/var/www/html --rm marketass.cz bash -c "cd /var/www/html/projects/marketass.cz && ${ARGS}"
	make clean

web-server: clean
	make web-console ARGS="cache:clear --env dev"
	make web-console ARGS="cache:clear --env prod"
	sudo docker run -v $$(pwd)/../..:/var/www/html --rm marketass.cz

web-console: clean
	make docker-run ARGS="php bin/console ${ARGS}"

web-cache:
	make web-console ARGS="markdown-web:build-cache ${ARGS} --env prod"

clean:
	(cd ../../ && make clean)
	sudo chmod u+r+w-x+X,g+r-w-x+X,o+r-w-x+X ./ -R
	sudo chmod u+x ./bin -R
	sudo chmod g+w,o+w ./var -R
	rm -rf ./var/cache || true

composer:
	make docker-run ARGS="php -d memory_limit=4G /usr/local/bin/composer ${ARGS}"

composer-install:
	make composer ARGS="install"
	make web-console ARGS="assets:install www --symlink --relative"

deployment:
	make docker-run ARGS="./vendor/bin/deploy"
