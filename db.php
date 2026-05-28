<?php
$config = file_exists(__DIR__ . '/config.php')
    ? require __DIR__ . '/config.php'
    : require __DIR__ . '/config.example.php';

$host = $config['db_host'];
$user = $config['db_user'];
$password = $config['db_password'];
$database = $config['db_name'];

$mysqli = new mysqli($host, $user, $password, $database);

if ($mysqli->connect_errno) {
    die('Ошибка подключения к базе данных: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');