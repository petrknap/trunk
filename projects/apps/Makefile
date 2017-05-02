all: linux win mac packages

docker-build:
	sudo docker build -t petrknap/apps .

docker-run:
	sudo docker run -v $$(pwd):/app --rm petrknap/apps bash -c "cd /app && ${ARGS}" -rm
	(cd ../../ && make clean)

.build:
	make docker-run ARGS="cd MarkdownEditor && npm install && electron-packager ./ --overwrite --arch=ia32 ${ARGS} --prune=true --out=../builds --asar=true && electron-packager ./ --overwrite --arch=x64 ${ARGS} --prune=true --out=../builds --asar=true"

linux:
	make .build ARGS="--platform=linux --icon=assets/icons/png/1024x1024.png"

win:
	make .build ARGS="--platform=win32 --icon=assets/icons/win/icon.ico"

mac:
	make .build ARGS="--platform=darwin --icon=assets/icons/mac/icon.icns"

packages:
	for D in $$(ls -d ./builds/*/); do (cd $$D && rm $$(pwd).zip || true && zip -r $$(pwd).zip .); done;
	(cd ./builds/ && sha1sum *.zip > checksums.sha1 && md5sum *.zip > checksums.md5)
