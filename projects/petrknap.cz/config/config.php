<?php

define('CONFIG', 'config');
define('CONFIG_DB_DSN', 'db_dsn');
define('CONFIG_DB_USER', 'db_user');
define('CONFIG_DB_PASSWORD', 'db_password');
define('CONFIG_DB_MIGRATIONS_DIR', 'db_migrations_dir');
define('CONFIG_CACHE_DIR', 'cache_dir');
define('CONFIG_CACHE_REMOTE_CONTENT_NAMESPACE', 'cache_remote_content_namespace');
define('CONFIG_CACHE_REMOTE_CONTENT_LIFETIME', 'cache_remote_content_lifetime');
define('CONFIG_GA_TRACKING_ID', 'ga_tracking_id');

${CONFIG} = [
    CONFIG_DB_MIGRATIONS_DIR => __DIR__ . '/../migrations',
    CONFIG_CACHE_DIR => __DIR__ . '/../var/cache',
    CONFIG_CACHE_REMOTE_CONTENT_NAMESPACE => 'remote_content',
    CONFIG_CACHE_REMOTE_CONTENT_LIFETIME => 7 * 24 * 3600,
    CONFIG_GA_TRACKING_ID => 'UA-88031264-1',
];

require_once __DIR__ . '/config.local.php';
require_once __DIR__ . '/services.php';

unset(${CONFIG});
