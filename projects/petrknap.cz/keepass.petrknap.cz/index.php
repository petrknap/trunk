<?php

define('HISTORY_FILE', __DIR__ . '/history.json');
define('REQUESTED_FILE', __DIR__ . '/' . ($_GET['file'] ?? 'index.php'));
define('PRIVATE_FILES', [
    __DIR__ . '/.htaccess',
    __DIR__ . '/index.php',
]);

if (file_exists(HISTORY_FILE)) {
    $historyContent = file_get_contents(HISTORY_FILE);
    if ($historyContent === false) {
        http_response_code(500);
        exit;
    }
    $history = json_decode($historyContent, true);
    if ($history === null) {
        http_response_code(500);
        exit;
    }
} else {
    $history = [];
}
$fileHistory = &$history[REQUESTED_FILE];
$fileHistory = $fileHistory ?? [];
$fileHistory[] = $_SERVER['REMOTE_ADDR'];
$fileHistory = array_unique($fileHistory);
$historyEncoded = json_encode($history, JSON_PRETTY_PRINT);
if ($historyEncoded === false) {
    http_response_code(500);
    exit;
}
if (file_put_contents(HISTORY_FILE, $historyEncoded) === false) {
    http_response_code(500);
    exit;
}


if (in_array(REQUESTED_FILE, PRIVATE_FILES)) {
    http_response_code(403);
    exit;
} elseif (!file_exists(REQUESTED_FILE)) {
    http_response_code(404);
    exit;
} else {
    header('Content-Description: File Transfer');
    header('Content-Type: ' . mime_content_type(REQUESTED_FILE));
    header('Content-Disposition: attachment; filename=' . basename(REQUESTED_FILE));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize(REQUESTED_FILE));
    readfile(REQUESTED_FILE);
    exit;
}
