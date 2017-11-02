<?php

define('CONFIG', 'config');
define('CONFIG_CACHE_DIR', 'cache_dir');
define('CONFIG_CACHE_REMOTE_CONTENT_NAMESPACE', 'cache_remote_content_namespace');
define('CONFIG_CACHE_REMOTE_CONTENT_LIFETIME', 'cache_remote_content_lifetime');

${CONFIG} = [
    CONFIG_CACHE_DIR => __DIR__ . '/../var/cache',
    CONFIG_CACHE_REMOTE_CONTENT_NAMESPACE => 'remote_content',
    CONFIG_CACHE_REMOTE_CONTENT_LIFETIME => 7 * 24 * 3600,
];

require_once __DIR__ . '/services.php';

unset(${CONFIG});
