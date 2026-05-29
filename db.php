<?php
$config = require __DIR__ . '/config.php';

$host = $config['db_host'];
$user = $config['db_user'];
$password = $config['db_password'];
$database = $config['db_name'];
$charset = 'utf8mb4';

$mysqli = new mysqli($host, $user, $password, $database);

if ($mysqli->connect_errno) {
    die('Ошибка подключения к базе данных: ' . $mysqli->connect_error);
}

$mysqli->set_charset($charset);