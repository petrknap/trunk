docker-build:
	sudo docker build -t marketass.cz .

docker-run:
	sudo docker run -v $$(pwd)/../..:/var/www/html --rm marketass.cz bash -c "cd /var/www/html/projects/marketass.cz && ${ARGS}"
	make clean

docker-web: clean
	sudo docker run -v $$(pwd)/../..:/var/www/html --rm marketass.cz

clean:
	(cd ../../ && make clean)
	sudo chmod 0777 ./var -R

composer:
	make docker-run ARGS="composer ${ARGS}"

composer-install:
	make composer ARGS="install"
