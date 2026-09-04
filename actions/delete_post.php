<?php
session_start();
include 'config.php';
include 'db_connection.php';
include 'check_admin.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.actions");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: /profile.php");
    exit;
}

$post_id = intval($_GET['id']);
$vk_id = $_SESSION['user']['vk_id'];
$is_admin = isAdmin($vk_id, $mysqli);

// Если пользователь не админ, проверяем, что пост принадлежит ему
$stmt_check = $mysqli->prepare("SELECT id FROM posts WHERE id = ? AND profile_vk = ?");
$stmt_check->bind_param("is", $post_id, $vk_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0 && !$is_admin) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Ошибка: Вы не можете удалить этот пост.']);
    exit;
}

$stmt_delete = $mysqli->prepare("DELETE FROM posts WHERE id = ?");
$stmt_delete->bind_param("i", $post_id);
$stmt_delete->execute();

header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
exit;
?>
