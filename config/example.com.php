<?php 

/**
 * Example config file for Core.
 * Name your config file with the your server's hostname
 * E.g. example.com.php
 */

if (!defined('IN_SCRIPT')) {
  header("HTTP/1.0 404 Not Found");
  trigger_error('Core.php: IN_SCRIPT constant is not defined.')
}

return array(
  'username' => 'XXX',
  'password' => 'XXX',
  'root_dir' => '/var/www/',
  'dsn'      => 'sqlite:' . ROOT_DIR . '/database.db',
  'log_file' => ROOT_DIR . '/custom.log',
  'email'    => 'you@example.com',
);
