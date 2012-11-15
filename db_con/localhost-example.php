<?php if (!defined('IN_SCRIPT')) header("HTTP/1.0 404 Not Found");
define('DBUSER', 'XXX');
define('DBPASSWORD', 'XXX');
define('ROOT_DIR', '/var/www/');
define('DSN', 'sqlite:'.ROOT_DIR.'/core/db_con/database.db');
define('LOG_FILE', ROOT_DIR.'/core/custom.log');
define('EMAIL', 'you@youremail.com');