<?php
require_once __DIR__ . "/vendor/autoload.php";

use Tool01\Route;

if (!defined('PROJECT_DIR')) {
    define('PROJECT_DIR', __DIR__);
}

Route::init();