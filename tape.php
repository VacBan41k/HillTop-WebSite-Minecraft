<?php
// Задаём параметры cookie для сессии (30 дней = 2592000 секунд)
session_set_cookie_params([
    'lifetime' => 2592000,      // 30 дней
    'path'     => '/',
    'domain'   => 'hilltoprp.ru',  // При необходимости укажите домен
    'secure'   => true,          // Если используется HTTPS, установите true
    'httponly' => true,
    'samesite' => 'Lax'          // Можно использовать 'Strict', если требуется
]);

// Задаём время жизни сессии для сборщика мусора
ini_set('session.gc_maxlifetime', 2592000);

// Если сессия ещё не запущена, запускаем её
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'actions/config.php';
include 'actions/check_admin.php'; // Функция проверки админа
include 'actions/db_connection.php';

// Если пользователь не авторизован, перенаправляем на страницу входа
if (!isset($_SESSION['user'])) {
    header("Location: login.actions");
    exit;
}

$current_user_vk = $_SESSION['user']['vk_id'];
$is_admin = isAdmin($current_user_vk, $mysqli);

// Получаем публичные посты с данными автора (nickname, photo_url, profile_vk)
$query = "
    SELECT posts.*, 
           users.nickname, 
           users.photo_url, 
           posts.profile_vk 
    FROM posts 
    JOIN users ON posts.profile_vk = users.vk_id 
    WHERE posts.is_public = 1 
    ORDER BY posts.is_pinned DESC, posts.created_at DESC
";
$posts = $mysqli->query($query);
if (!$posts) {
    die("Ошибка выполнения запроса: " . $mysqli->error);
}

// Подключаем шапку сайта
include 'header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hilltop - Сервер по Майнкрафту</title>
    <link rel="stylesheet" href="css/styles-tape.css">
    <link rel="icon" href="img/favicon.png" type="image/png">
</head>
<body>
<main>
    <div class="tape-container">
        <?php if ($posts->num_rows > 0): ?>
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="post-card <?= $post['is_pinned'] ? 'pinned' : '' ?>" id="post-<?= $post['id'] ?>">
                    <div class="post-header">
                        <!-- Ссылка на профиль автора с GET-параметром uid -->
                        <a href="profile.php?uid=<?= urlencode($post['profile_vk']) ?>">
                            <img src="<?= htmlspecialchars($post['photo_url']) ?>" alt="Аватар" class="avatar">
                        </a>
                        <div class="user-info">
                            <a href="profile.php?uid=<?= urlencode($post['profile_vk']) ?>">
                                <span class="nickname"><?= htmlspecialchars($post['nickname']) ?></span>
                            </a>
                            <span class="post-date"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                        </div>
                        <?php if ($post['profile_vk'] == $current_user_vk || $is_admin): ?>
                            <button class="delete-button" data-post-id="<?= $post['id'] ?>">Удалить</button>
                        <?php endif; ?>
                        <?php if ($is_admin): ?>
                            <form method="post" action="actions/toggle_pin.php" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" class="pin-button">
                                    <?= $post['is_pinned'] ? 'Открепить' : 'Закрепить' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="post-content">
                        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        <?php if (!empty($post['media_url'])): ?>
                            <?php if ($post['media_type'] === 'image' || $post['media_type'] === 'gif'): ?>
                                <img src="<?= htmlspecialchars($post['media_url']) ?>" alt="Изображение" class="post-media">
                            <?php elseif ($post['media_type'] === 'video'): ?>
                                <video controls class="post-media">
                                    <source src="<?= htmlspecialchars($post['media_url']) ?>" type="video/mp4">
                                    Ваш браузер не поддерживает воспроизведение видео.
                                </video>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="post-footer">
                        <button class="like-button" data-post-id="<?= $post['id'] ?>">
                            ▲ <span class="vote-count"><?= $post['likes'] > 0 ? $post['likes'] : '' ?></span>
                        </button>
                        <button class="dislike-button" data-post-id="<?= $post['id'] ?>">
                            ▼ <span class="vote-count"><?= $post['dislikes'] > 0 ? $post['dislikes'] : '' ?></span>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-posts">Постов пока нет. Будьте первым!</p>
        <?php endif; ?>
    </div>
</main>
<?php include 'footer.php'; ?>
<script src="js/main-script.js"></script>
<script src="js/tape.js"></script>
</body>
</html>
