docker-build:
	sudo docker build -t petrknap/apps .

docker-run:
	sudo docker run -v $$(pwd):/app --rm petrknap/apps bash -c "cd /app && ${ARGS}" -rm
	(cd ../../ && make clean)

build:
	make docker-run ARGS="cd MarkdownEditor && npm install && electron-packager ./ --all --overwrite"
