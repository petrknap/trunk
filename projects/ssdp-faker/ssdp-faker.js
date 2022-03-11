const processArgs = process.argv.slice(2),
    ssdp = require('node-ssdp'),
    action = processArgs[0],
    location = processArgs[1],
    usns = processArgs.slice(2);

switch (action) {
    case 'scan-network':
        scanNetwork();
        break;
    case 'run-server':
        runServer(location, usns);
        break;
    default:
        console.log('Unsupported action %o, please read https://github.com/petrknap/ssdp-faker/blob/master/README.md', action);
}

function scanNetwork() {
    var client = new ssdp.Client(),
        serviceType= 'ssdp:all';

    client.on('response', function (headers) {
        console.log(headers);
    });

    console.log('Scanning network for %o', serviceType)
    client.search(serviceType);

    setTimeout(function () {
        console.log('Scan complete')
    }, 5000);
}

function runServer(location, usns) {
    var server = new ssdp.Server({
        'location': location
    });
    console.log('Location set to %o', location);

    server.addUSN('upnp:rootdevice');
    usns.forEach(function (usn) {
        server.addUSN(usn);
        console.log('USN %o added', usn);
    });

    server.start();
    console.log('Server started');

    process.on('exit', function () {
        server.stop();
        console.log('Server stopped');
    });
}
