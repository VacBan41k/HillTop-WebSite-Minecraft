<?php
session_start();
include 'config.php';
include 'db_connection.php';

// Проверяем авторизацию
if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Пользователь не авторизован.']));
}

// Получаем данные из запроса
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : null;

if ($post_id <= 0 || !in_array($action, ['like', 'dislike'])) {
    die(json_encode(['status' => 'error', 'message' => 'Некорректные данные.']));
}

$voter = $_SESSION['user']['vk_id']; // ID пользователя
$new_vote = $action; // "like" или "dislike"

// Проверяем, голосовал ли уже пользователь за этот пост
$stmt_check = $mysqli->prepare("SELECT vote FROM post_votes WHERE post_id = ? AND voter = ?");
if (!$stmt_check) {
    die(json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $mysqli->error]));
}
$stmt_check->bind_param("is", $post_id, $voter);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if (!$result_check) {
    die(json_encode(['status' => 'error', 'message' => 'Ошибка выполнения запроса: ' . $mysqli->error]));
}

if ($result_check->num_rows > 0) {
    $row = $result_check->fetch_assoc();
    $current_vote = $row['vote'];

    if ($current_vote === $new_vote) {
        // Если голос совпадает, удаляем его (отмена)
        $stmt_delete = $mysqli->prepare("DELETE FROM post_votes WHERE post_id = ? AND voter = ?");
        if (!$stmt_delete) {
            die(json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса (удаление): ' . $mysqli->error]));
        }
        $stmt_delete->bind_param("is", $post_id, $voter);
        $stmt_delete->execute();

        // Уменьшаем счётчик
        $column = $new_vote === 'like' ? 'likes' : 'dislikes';
        $stmt_update = $mysqli->prepare("UPDATE posts SET $column = $column - 1 WHERE id = ?");
        if (!$stmt_update) {
            die(json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса (обновление счётчика): ' . $mysqli->error]));
        }
        $stmt_update->bind_param("i", $post_id);
        $stmt_update->execute();
    } else {
        // Пользователь меняет голос: обновляем запись и корректируем счетчики
        $stmt_update_vote = $mysqli->prepare("UPDATE post_votes SET vote = ? WHERE post_id = ? AND voter = ?");
        if (!$stmt_update_vote) {
            die(json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса (обновление голоса): ' . $mysqli->error]));
        }
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
    if (!$stmt_insert) {
        die(json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса (вставка): ' . $mysqli->error]));
    }
    $stmt_insert->bind_param("iss", $post_id, $voter, $new_vote);
    $stmt_insert->execute();

    // Увеличиваем счётчик
    $column = $new_vote === 'like' ? 'likes' : 'dislikes';
    $stmt_update = $mysqli->prepare("UPDATE posts SET $column = $column + 1 WHERE id = ?");
    if (!$stmt_update) {
        die(json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса (увеличение счётчика): ' . $mysqli->error]));
    }
    $stmt_update->bind_param("i", $post_id);
    $stmt_update->execute();
}

// Получаем обновленные значения лайков и дизлайков
$result = $mysqli->query("SELECT likes, dislikes FROM posts WHERE id = $post_id");
if (!$result) {
    die(json_encode(['status' => 'error', 'message' => 'Ошибка выполнения запроса (получение данных): ' . $mysqli->error]));
}
$data = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'likes' => $data['likes'],
    'dislikes' => $data['dislikes']
]);
?>
