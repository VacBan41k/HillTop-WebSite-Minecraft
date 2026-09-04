<?php
session_start();
include 'config.php';
include 'db_connection.php';
include 'check_admin.php'; // Проверяем, что пользователь администратор

// Проверяем авторизацию
if (!isset($_SESSION['user'])) {
    die("Ошибка: Пользователь не авторизован.");
}

// Проверяем, является ли пользователь администратором
$vk_id = $_SESSION['user']['vk_id'];
if (!isAdmin($vk_id, $mysqli)) {
    die("Ошибка: Недостаточно прав для выполнения действия.");
}

// Получаем ID поста
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
if ($post_id <= 0) {
    die("Ошибка: Некорректный ID поста.");
}

// Проверяем текущий статус закрепления
$query = "SELECT is_pinned FROM posts WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Ошибка: Пост не найден.");
}

$post = $result->fetch_assoc();
$new_status = $post['is_pinned'] ? 0 : 1;

// Обновляем статус закрепления
$update_query = "UPDATE posts SET is_pinned = ? WHERE id = ?";
$stmt = $mysqli->prepare($update_query);
$stmt->bind_param("ii", $new_status, $post_id);
$stmt->execute();

header("Location: /tape.php");
exit;
