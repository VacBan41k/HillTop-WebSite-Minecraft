<?php
session_start();
header('Content-Type: application/json');
include 'config.php';
include 'db_connection.php';
include 'check_admin.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Пользователь не авторизован.']);
    exit;
}

$comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
if ($comment_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Неверный идентификатор комментария.']);
    exit;
}

$current_vk = $_SESSION['user']['vk_id'];
// Получаем текущий nickname из базы
$stmtUser = $mysqli->prepare("SELECT nickname FROM users WHERE vk_id = ?");
if (!$stmtUser) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $mysqli->error]);
    exit;
}
$stmtUser->bind_param("s", $current_vk);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
if ($rowUser = $resultUser->fetch_assoc()) {
    $currentNickname = $rowUser['nickname'];
} else {
    $currentNickname = '';
}

// Получаем автора комментария
$stmt = $mysqli->prepare("SELECT author_vk FROM profile_comments WHERE id = ?");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Комментарий не найден.']);
    exit;
}
$comment = $result->fetch_assoc();
$author_vk = $comment['author_vk'];

$is_admin = isAdmin($current_vk, $mysqli);
if (strtolower($author_vk) !== strtolower($currentNickname) && !$is_admin) {
    echo json_encode(['status' => 'error', 'message' => 'Нет прав для удаления комментария.']);
    exit;
}

$stmt_del = $mysqli->prepare("DELETE FROM profile_comments WHERE id = ?");
if (!$stmt_del) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса удаления: ' . $mysqli->error]);
    exit;
}
$stmt_del->bind_param("i", $comment_id);
if ($stmt_del->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Комментарий удалён.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка удаления комментария: ' . $mysqli->error]);
}
exit;
?>
