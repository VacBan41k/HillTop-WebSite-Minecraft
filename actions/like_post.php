<?php
session_start();
include 'config.php';
include 'db_connection.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.actions");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['vote'])) {
    header("Location: /profile.php");
    exit;
}

$post_id = intval($_GET['id']);
$new_vote = $_GET['vote']; // "like" или "dislike"
$voter = $_SESSION['user']['vk_id'];

// Проверяем, голосовал ли уже пользователь за этот пост
$stmt_check = $mysqli->prepare("SELECT vote FROM post_votes WHERE post_id = ? AND voter = ?");
$stmt_check->bind_param("is", $post_id, $voter);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $row = $result_check->fetch_assoc();
    $current_vote = $row['vote'];
    if ($current_vote === $new_vote) {
        // Если голос совпадает, удаляем его (отмена)
        $stmt_delete = $mysqli->prepare("DELETE FROM post_votes WHERE post_id = ? AND voter = ?");
        $stmt_delete->bind_param("is", $post_id, $voter);
        $stmt_delete->execute();
        if ($new_vote === 'like') {
            $stmt_update = $mysqli->prepare("UPDATE posts SET likes = likes - 1 WHERE id = ?");
        } else {
            $stmt_update = $mysqli->prepare("UPDATE posts SET dislikes = dislikes - 1 WHERE id = ?");
        }
        $stmt_update->bind_param("i", $post_id);
        $stmt_update->execute();
    } else {
        // Пользователь меняет голос: обновляем запись и корректируем счетчики
        $stmt_update_vote = $mysqli->prepare("UPDATE post_votes SET vote = ? WHERE post_id = ? AND voter = ?");
        $stmt_update_vote->bind_param("sis", $new_vote, $post_id, $voter);
        $stmt_update_vote->execute();
        if ($current_vote === 'like' && $new_vote === 'dislike') {
            $stmt_update = $mysqli->prepare("UPDATE posts SET likes = likes - 1, dislikes = dislikes + 1 WHERE id = ?");
        } elseif ($current_vote === 'dislike' && $new_vote === 'like') {
            $stmt_update = $mysqli->prepare("UPDATE posts SET dislikes = dislikes - 1, likes = likes + 1 WHERE id = ?");
        }
        $stmt_update->bind_param("i", $post_id);
        $stmt_update->execute();
    }
} else {
    // Если голос не поставлен, вставляем новую запись
    $stmt_insert = $mysqli->prepare("INSERT INTO post_votes (post_id, voter, vote) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iss", $post_id, $voter, $new_vote);
    $stmt_insert->execute();
    if ($new_vote === 'like') {
        $stmt_update = $mysqli->prepare("UPDATE posts SET likes = likes + 1 WHERE id = ?");
    } elseif ($new_vote === 'dislike') {
        $stmt_update = $mysqli->prepare("UPDATE posts SET dislikes = dislikes + 1 WHERE id = ?");
    }
    $stmt_update->bind_param("i", $post_id);
    $stmt_update->execute();
}

echo "OK";
?>
