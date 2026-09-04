<?php
global $user;
include 'config.php'; // Данные для подключения к базе

session_start();

// Данные пользователя, полученные из API ВКонтакте
$vk_id = $user['id'];
$first_name = $user['first_name'];
$last_name = $user['last_name'];
$photo_url = $user['photo_200'];

// Подключение к базе данных

include 'config.php'; // Данные для подключения к базе

session_start();

// Данные пользователя, полученные из API ВКонтакте
$vk_id = $user['id'];
$first_name = $user['first_name'];
$last_name = $user['last_name'];
$photo_url = $user['photo_200'];

// Подключение к базе данных
$mysqli = new mysqli(
    "localhost",
    "u2991630_hill",
    "oL4oD5aR1jiQ9qT8",
    "u2991630_hill"
);


if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

// Проверяем, есть ли пользователь в базе
$stmt = $mysqli->prepare("SELECT id FROM users WHERE vk_id = ?");
$stmt->bind_param("i", $vk_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Если пользователя нет, добавляем в базу
    $stmt = $mysqli->prepare("INSERT INTO users (vk_id, first_name, last_name, photo_url) 
        VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("isss", $vk_id, $first_name, $last_name, $photo_url);
    $stmt->execute();
}

// Закрываем соединение
$stmt->close();
$mysqli->close();

// Сохраняем данные пользователя в сессии
$_SESSION['user'] = [
    'vk_id' => $vk_id,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'photo_url' => $photo_url
];

// Перенаправляем на главную страницу
header("Location: /index.php");
exit();


// Проверка подключения к базе данных
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

// Установка кодировки соединения с базой данных
$mysqli->set_charset("utf8");

// Конвертация данных в UTF-8
$first_name = mb_convert_encoding($first_name, 'UTF-8', 'auto');
$last_name = mb_convert_encoding($last_name, 'UTF-8', 'auto');

// Проверяем, есть ли пользователь в базе
$stmt = $mysqli->prepare("SELECT id FROM users WHERE vk_id = ?");
$stmt->bind_param("i", $vk_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Если пользователя нет, добавляем в базу
    $stmt = $mysqli->prepare("INSERT INTO users (vk_id, first_name, last_name, photo_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $vk_id, $first_name, $last_name, $photo_url);
    $stmt->execute();
}

// Закрываем соединение
$stmt->close();
$mysqli->close();

// Сохраняем данные пользователя в сессии
$_SESSION['user'] = [
    'vk_id' => $vk_id,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'photo_url' => $photo_url
];

// Перенаправляем на главную страницу
header("Location: /index.php");
exit();
?>
