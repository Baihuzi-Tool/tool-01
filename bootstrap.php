<?php
require_once __DIR__ . "/vendor/autoload.php";

if (!defined('PROJECT_DIR')) {
    define('PROJECT_DIR', __DIR__);
}

\Tool01\Route::init();