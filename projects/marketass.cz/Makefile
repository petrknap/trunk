docker-build:
	sudo docker build -t marketass.cz .

docker-run:
	sudo docker run -v $$(pwd)/../..:/var/www/html --rm marketass.cz bash -c "cd /var/www/html/projects/marketass.cz && ${ARGS}"
	make clean

web-server: clean
	sudo docker run -v $$(pwd)/../..:/var/www/html --rm marketass.cz

web-console: clean
	make docker-run ARGS="php bin/console ${ARGS}"

web-cache:
	make web-console ARGS="markdown-web:build-cache ${ARGS}"

clean:
	(cd ../../ && make clean)
	sudo chmod 0777 ./app/cache -R
	sudo chmod 0777 ./app/logs -R
	sudo chmod 0777 ./app/sessions -R

composer:
	make docker-run ARGS="composer ${ARGS}"

composer-install:
	make composer ARGS="install"
