<?php
session_start();
include 'config.php'; // Подключаем конфигурацию

// Проверяем, есть ли код авторизации
if (!isset($_GET['code'])) {
    exit('Ошибка: отсутствует код авторизации.');
}

$code = $_GET['code'];

// Получаем access_token
$tokenResponse = file_get_contents(
    "https://oauth.vk.com/access_token?client_id=" . ID .
    "&client_secret=" . SECRET .
    "&redirect_uri=" . urlencode(URL) .
    "&code=" . $code
);

$token = json_decode($tokenResponse, true);

if (isset($token['error'])) {
    exit("Ошибка авторизации: " . $token['error_description']);
}

// Получаем данные пользователя
$userResponse = file_get_contents(
    "https://api.vk.com/method/users.get?access_token=" . $token['access_token'] .
    "&fields=photo_200&v=5.131"
);

$data = json_decode($userResponse, true);

if (!isset($data['response'][0])) {
    exit('Ошибка получения данных пользователя.');
}

$user = $data['response'][0];

// Данные пользователя
$vk_id = $user['id'];
$first_name = $user['first_name'];
$last_name = $user['last_name'];
$photo_url = $user['photo_200'];

// Проверяем данные
if (empty($vk_id) || empty($first_name) || empty($last_name)) {
    die("Ошибка: Данные пользователя неполные.");
}

// Сохраняем пользователя в базу данных
$mysqli = new mysqli(
    "localhost",
    "u2991630_hill",
    "oL4oD5aR1jiQ9qT8",
    "u2991630_hill");

// Проверяем подключение
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

// Проверяем, есть ли пользователь в базе
$stmt = $mysqli->prepare("SELECT id FROM users WHERE vk_id = ?");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $mysqli->error);
}

$stmt->bind_param("i", $vk_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Если пользователя нет, добавляем в базу
    $stmt = $mysqli->prepare("INSERT INTO users (vk_id, first_name, last_name, photo_url) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Ошибка подготовки запроса при добавлении: " . $mysqli->error);
    }
    $stmt->bind_param("isss", $vk_id, $first_name, $last_name, $photo_url);
    if (!$stmt->execute()) {
        die("Ошибка выполнения запроса при добавлении: " . $stmt->error);
    }
}

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
