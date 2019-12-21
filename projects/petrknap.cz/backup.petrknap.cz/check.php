<?php declare(strict_types=1);

try {
    if ((require __DIR__ . '/init.php')->check($_GET['id'])) {
        http_response_code(204);
    } else {
        http_response_code(500);
    }
} catch (\Exception $ignored) {
    http_response_code(404);
}
