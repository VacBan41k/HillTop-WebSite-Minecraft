<?php
session_start();
header('Content-Type: application/json');
include 'config.php';
include 'db_connection.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован.']);
    exit;
}

$profile_vk = $_POST['profile_vk'] ?? '';
$comment    = trim($_POST['comment'] ?? '');

if (!$profile_vk || !$comment) {
    echo json_encode(['success' => false, 'message' => 'Пустой комментарий или не указан профиль.']);
    exit;
}

// Всегда запрашиваем nickname из базы для текущего пользователя
$stmtUser = $mysqli->prepare("SELECT nickname FROM users WHERE vk_id = ?");
if (!$stmtUser) {
    echo json_encode(['success' => false, 'message' => 'Ошибка подготовки запроса: ' . $mysqli->error]);
    exit;
}
$stmtUser->bind_param("s", $_SESSION['user']['vk_id']);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
if ($rowUser = $resultUser->fetch_assoc()) {
    $nickname = $rowUser['nickname'];
} else {
    $nickname = $_SESSION['user']['vk_id'];
}

$stmt = $mysqli->prepare("INSERT INTO profile_comments (profile_vk, author_vk, comment) VALUES (?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Ошибка подготовки запроса комментариев: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param("sss", $profile_vk, $nickname, $comment);
if ($stmt->execute()) {
    $comment_id = $stmt->insert_id;
    $created_at = date('Y-m-d H:i:s');
    // Здесь текущий пользователь может удалять свои комментарии
    $can_delete = true;
    echo json_encode([
        'success' => true,
        'comment' => [
            'id' => $comment_id,
            'author_vk' => $nickname,
            'comment' => $comment,
            'created_at' => $created_at,
            'can_delete' => $can_delete
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка выполнения запроса: ' . $stmt->error]);
}
exit;
?>
