<?php
session_start();
include 'config.php';
include 'db_connection.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// SQL-запрос для получения всех постов (возможно, добавьте условия, если нужно показывать только определённые посты)
$sql = "SELECT * FROM posts ORDER BY id DESC";
$result = $mysqli->query($sql);

// Проверяем наличие постов
if ($result->num_rows > 0) {
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = [
            'id' => $row['id'],
            'profile_vk' => $row['profile_vk'],
            'content' => $row['content'],
            'media_url' => $row['media_url'],
            'media_type' => $row['media_type'],
            'created_at' => $row['created_at']
        ];
    }
    echo json_encode($posts); // Возвращаем JSON с постами
} else {
    echo json_encode(['error' => 'No posts found']);
}
?>
