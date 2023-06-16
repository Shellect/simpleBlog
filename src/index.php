<?php
session_start();

use engine\App;

require_once(__DIR__ . "/vendor/autoload.php");
$config = include('config.php');
$app = new App($config);
$app->start();
