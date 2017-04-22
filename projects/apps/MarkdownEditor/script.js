const fileSystem = require('fs');
const path = require('path');
const electron = require('electron');
const remote = electron.remote;
const ipc = electron.ipcRenderer;
const dialog = remote.dialog;

window.editor = null;
window.files = {};
window.activeFile = null;
window.savedContent = "";

(function () {
    window.editor = new SimpleMDE({
        element: document.getElementById('editor'),
        forceSync: true,
        spellChecker: false,
        autofocus: true
    });

    window.savedContent = window.editor.value();
})();

ipc.on('closingWindow', function() {
    if (beforeDestroy()) {
        ipc.send('doClose');
    }
});

function beforeDestroy() {
    if (window.savedContent === window.editor.value()) {
        return true; // nothing to destroy
    }

    var choice = dialog.showMessageBox(remote.getCurrentWindow(), {
        type: 'warning',
        buttons: ['Cancel', 'Don\'t Save'],
        title: 'Destroy changes?',
        message: 'You have unsaved changes. Would you like to don\'t save them?',
        detail: 'Your changes will be lost if you don\'t save them.'
    });

    switch (choice) {
        case 1:
            window.editor.value(window.savedContent);
            return true;
        default:
            return false;
    }
}

function loadFile(file) {
    if (beforeDestroy()) {
        fileSystem.readFile(file, 'utf-8', function (err, data) {
            window.editor.value(data);
            window.savedContent = window.editor.value();
            window.activeFile = file;
            updateMenu();
        });
    }
}

function updateMenu() {
    var menu = '';
    for (var file in window.files) {
        menu = menu + '<a href="javascript:loadFile(\'' + file + '\')" class="list-group-item ' + (file === activeFile ? 'active' : '') + '">' + path.basename(file, '.md') + '</a>';
    }
    document.getElementById('menu').innerHTML = menu;
}

function openFile() {
    dialog.showOpenDialog({
        filters: [
            {
                name: 'Markdown',
                extensions: ['md']
            }
        ]
    }, function (fileNames) {
        if (fileNames === undefined) {
            return;
        }
        for (var i = 0; i < fileNames.length; i++) {
            window.files[fileNames[i]] = null;
        }
        loadFile(fileNames[0]);
    });
}

function saveFile(saveAs) {
    var save = function (file, content) {
        fileSystem.writeFile(file, content, 'utf-8', function (err) {
            if (!err) {
                window.savedContent = content;
            }
        });
    };
    if (saveAs || !window.activeFile) {
        dialog.showSaveDialog({
            filters: [
                {
                    name: 'Markdown',
                    extensions: ['md']
                }
            ]
        }, function (fileName) {
            save(fileName, window.editor.value());
        });
    } else {
        save(window.activeFile, window.editor.value());
    }
}
