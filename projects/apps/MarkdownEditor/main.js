const electron = require('electron');
const ipc = electron.ipcMain;
const app = electron.app;
const BrowserWindow = electron.BrowserWindow;

var mainWindow, forceQuit = false;

app.on('ready', function() {
    mainWindow = new BrowserWindow({
        height: 600,
        width: 800
    });

    mainWindow.loadURL('file://' + __dirname + '/window.html');

    mainWindow.on('close', function(e) {
        if (!forceQuit) {
            e.preventDefault();
            mainWindow.webContents.send('closingWindow');
        }
    });

    ipc.on('doClose', function () {
        forceQuit = true;
        mainWindow.close();
    });

    mainWindow.on('closed', function() {
        mainWindow = null;
        app.quit();
    });
});
