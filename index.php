<?php

define('THINK_PATH', './ThinkPHP');
define('APP_NAME', 'App');
define('APP_PATH', './App');
define('RUNTIME_PATH', sys_get_temp_dir());
require THINK_PATH . "/ThinkPHP.php";
App::run();
?>