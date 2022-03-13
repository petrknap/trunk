const script = process.argv[1],
    processArgs = process.argv.slice(2),
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
        console.log('Unsupported action %o', action);
        console.log('');
    case '-h':
    case '--help':
    case 'help':
        console.log('Usage: node %s scan-network', script);
        console.log('       node %s run-server location [USN1 ...]', script);
        console.log('');
        console.log('Visit https://github.com/petrknap/ssdp-faker for more information.');
        break;
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
