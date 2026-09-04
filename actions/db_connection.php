<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u2991630_hill');
define('DB_PASSWORD', 'oL4oD5aR1jiQ9qT8');
define('DB_NAME', 'u2991630_hill');

// Подключение к базе данных
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}
?>
