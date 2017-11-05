.PHONY: *

docker-build:
	sudo docker build -t storyboard .

docker-run:
	sudo docker run -v $$(pwd):/usr/src/myapp --rm storyboard bash -c "cd /usr/src/myapp && ${ARGS}"
	make clean

clean:
	(cd ../../ && make clean)

composer:
	make docker-run ARGS="composer ${ARGS}"

composer-install:
	make composer ARGS="install"

tests:
	make docker-run ARGS="./vendor/bin/phpunit ${ARGS}"
