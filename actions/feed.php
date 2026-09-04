<?php
include 'db_connection.php';

// Получаем публичные посты
$query = "SELECT * FROM posts WHERE is_public = 1 ORDER BY created_at DESC";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($post = $result->fetch_assoc()) {
        echo '<div class="post">';
        echo '<h3>' . htmlspecialchars($post['content']) . '</h3>';
        if (!empty($post['media_url'])) {
            if ($post['media_type'] === 'video') {
                echo '<video controls><source src="' . htmlspecialchars($post['media_url']) . '" type="video/mp4">Ваш браузер не поддерживает видео.</video>';
            } else {
                echo '<img src="' . htmlspecialchars($post['media_url']) . '" alt="Медиа">';
            }
        }
        echo '<p>Опубликовано: ' . htmlspecialchars($post['created_at']) . '</p>';
        echo '</div>';
    }
} else {
    echo '<p>Постов пока нет.</p>';
}
?>
