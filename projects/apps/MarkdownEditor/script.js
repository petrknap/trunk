const fileSystem = require('fs');
const path = require('path');
const electron = require('electron');
const remote = electron.remote;
const ipc = electron.ipcRenderer;
const dialog = remote.dialog;

window.editor = null;
window.activeFile = null;
window.savedContent = null;
window.titlePrefix = document.title + " - ";

(function () {
    document.title = window.titlePrefix + "New file";
    window.editor = new SimpleMDE({
        element: document.getElementById('editor'),
        forceSync: true,
        spellChecker: false,
        autofocus: true,
        autoDownloadFontAwesome: false,
        toolbar: [
            {
                name: "open",
                action: function () {
                    return openFile();
                },
                className: "fa fa-file-text-o",
                title: "Open file"
            }, {
                name: "save",
                action: function () {
                    return saveFile(false);
                },
                className: "fa fa-floppy-o ",
                title: "Save file"
            }, "|",
            "undo", "redo", "|",
            "bold", "italic", "heading", "|",
            "code", "quote", "unordered-list", "ordered-list", "|",
            "link", "image", "table", "|",
            "preview", "side-by-side", "|",
            "guide"
        ]
    });

    var setOption = editor.codemirror.setOption;
    window.editor.codemirror.setOption = function(option, value) {
        if ('fullScreen' === option && false === value) {
            window.editor.codemirror.setOption('fullScreen', true);
        } else {
            setOption.apply(this, arguments);
        }
    };
    window.editor.codemirror.setOption('fullScreen', true);
    window.savedContent = window.editor.value();
})();

ipc.on('closingWindow', function() {
    if (beforeDestroy()) {
        ipc.send('doClose');
    }
});

function beforeDestroy() {
    if (window.editor.value() === window.savedContent) {
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
        });
        document.title = window.titlePrefix + file;
    }
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
        loadFile(fileNames[0]);
    });
}

function saveFile(saveAs) {
    var save = function (file, content) {
        fileSystem.writeFile(file, content, 'utf-8', function (err) {
            if (!err) {
                window.savedContent = content;
                document.title = window.titlePrefix + file;
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
