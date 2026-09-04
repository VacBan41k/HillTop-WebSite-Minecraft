<?php
session_start();
include 'config.php';
include 'db_connection.php';
include 'check_admin.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Не авторизован.']);
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Некорректный запрос.']);
    exit;
}

$post_id = intval($_GET['id']);
$action = strtolower(trim($_GET['action']));
$vk_id = $_SESSION['user']['vk_id'];

// Проверяем наличие поста
$stmt_check = $mysqli->prepare("SELECT pinned, profile_vk FROM posts WHERE id = ?");
$stmt_check->bind_param("i", $post_id);
$stmt_check->execute();
$result = $stmt_check->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Пост не найден.']);
    exit;
}
$post = $result->fetch_assoc();
$stmt_check->close();

// Если пользователь не админ, проверяем, что пост принадлежит ему
$is_admin = isAdmin($vk_id, $mysqli);
if (!$is_admin && $post['profile_vk'] != $vk_id) {
    echo json_encode(['status' => 'error', 'message' => 'Нет прав для закрепления этого поста.']);
    exit;
}

// Определяем новое значение поля pinned
$newPinned = ($action === 'pin') ? 1 : 0;

$stmt = $mysqli->prepare("UPDATE posts SET pinned = ? WHERE id = ?");
$stmt->bind_param("ii", $newPinned, $post_id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'pinned' => $newPinned]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при обновлении поста.']);
}
exit;
?>
