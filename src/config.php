<?php
define('DB_PATH', __DIR__ . '/../data/assetry.db');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('SESSION_NAME', 'assetry_session');
define('APP_NAME', 'Assetry');
define('APP_VERSION', '1.0.0');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name(SESSION_NAME);
session_start();
