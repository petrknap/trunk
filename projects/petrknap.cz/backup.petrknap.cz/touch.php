<?php declare(strict_types=1);

(require __DIR__ . '/init.php')->touch($_GET['id']);
http_response_code(204);
