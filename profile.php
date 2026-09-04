<?php
// Задаём параметры cookie для сессии (30 дней = 2592000 секунд)
session_set_cookie_params([
    'lifetime' => 2592000,
    'path'     => '/',
    'domain'   => 'hilltoprp.ru', // укажите ваш домен
    'secure'   => true,         // если используется HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
ini_set('session.gc_maxlifetime', 2592000);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'actions/config.php';
include 'actions/check_admin.php';
include 'actions/db_connection.php';

// Если пользователь не авторизован, перенаправляем
if (!isset($_SESSION['user'])) {
    header("Location: login.actions");
    exit;
}

// Определяем текущего пользователя и целевой профиль.
// Для просмотра чужого профиля используется GET-параметр uid; если не передан, это ваш профиль.
$current_vk = $_SESSION['user']['vk_id'];
$target_vk  = isset($_GET['uid']) ? $_GET['uid'] : $current_vk;
$is_admin   = isAdmin($current_vk, $mysqli);
$isOwner    = ($target_vk == $current_vk);
// Дополнительные вкладки показываем, если владелец или админ.
$showExtraTabs = ($isOwner || $is_admin);

// Получаем данные профиля выбранного пользователя (включая photo_url для чужих профилей)
$stmt_profile = $mysqli->prepare("SELECT nickname, about_me, banner_url, background_url, avatar_url, photo_url FROM users WHERE vk_id = ?");
if (!$stmt_profile) {
    die("Ошибка подготовки запроса: " . $mysqli->error);
}
$stmt_profile->bind_param("s", $target_vk);
$stmt_profile->execute();
$result_profile = $stmt_profile->get_result();
$profile_data = $result_profile->fetch_assoc();
if (!$profile_data) {
    die("Профиль не найден.");
}
if ($isOwner && empty($profile_data['nickname'])) {
    header("Location: actions/update_profile.php");
    exit;
}

// Функция для получения URL аватарки:
// Для чужих профилей используем поле photo_url, для владельца – avatar_url (или фото из сессии)
function getAvatarUrl($profile_data, $isOwner, $sessionAvatar) {
    if (!$isOwner) {
        if (!empty($profile_data['photo_url'])) {
            return $profile_data['photo_url'];
        }
        return 'img/default-avatar.png';
    } else {
        if (!empty($profile_data['avatar_url'])) {
            return $profile_data['avatar_url'];
        } elseif (!empty($sessionAvatar)) {
            return $sessionAvatar;
        }
        return 'img/default-avatar.png';
    }
}
$avatar_url = ($target_vk == $current_vk)
    ? getAvatarUrl($profile_data, true, $_SESSION['user']['photo_url'] ?? '')
    : getAvatarUrl($profile_data, false, '');

// Получаем посты выбранного профиля
$stmt_posts = $mysqli->prepare("SELECT * FROM posts WHERE profile_vk = ? ORDER BY pinned DESC, created_at DESC");
$stmt_posts->bind_param("s", $target_vk);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();

// Получаем комментарии выбранного профиля
$stmt_comments = $mysqli->prepare("SELECT * FROM profile_comments WHERE profile_vk = ? ORDER BY created_at DESC");
if (!$stmt_comments) {
    die("Ошибка подготовки запроса комментариев: " . $mysqli->error);
}
$stmt_comments->bind_param("s", $target_vk);
$stmt_comments->execute();
$comments_result = $stmt_comments->get_result();

include 'header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hilltop - Профиль пользователя</title>
    <link rel="stylesheet" href="css/styles-profile.css">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <style>
        /* Стили для загрузки файлов */
        label[for="media_file"],
        label[for="banner_file"],
        label[for="background_file"] {
            display: block;
            margin-top: 15px;
            font-size: 16px;
            color: #ffffff;
        }
        input[type="file"] {
            margin-top: 5px;
            padding: 10px;
            font-size: 14px;
            color: #ffffff;
            background-color: #1e1e2e;
            border: 1px solid #444;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="file"]::-webkit-file-upload-button {
            background-color: #6c5ce7;
            color: #ffffff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="file"]::-webkit-file-upload-button:hover {
            background-color: #5a4ecb;
        }
        /* Запрет горизонтального изменения размера textarea */
        textarea {
            resize: vertical;
        }
    </style>
</head>
<body <?php if (!empty($profile_data['background_url'])) { echo 'style="background: url(\''.htmlspecialchars($profile_data['background_url']).'\') no-repeat center center fixed; background-size: cover;"'; } ?>>
<div class="profile-content">
    <!-- Вкладки профиля -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="wall">Стена</button>
        <?php if ($showExtraTabs): ?>
            <button class="tab-btn" data-tab="applications">Мои заявки</button>
            <button class="tab-btn" data-tab="editor">Редактор страницы</button>
        <?php endif; ?>
    </div>

    <!-- Вкладка "Стена": посты и комментарии -->
    <div id="wall" class="tab-content active">
        <h1>Профиль пользователя</h1>
        <div class="about-me"
            <?php
            if (!empty($profile_data['banner_url'])) {
                echo 'style="background: url(\''.htmlspecialchars($profile_data['banner_url']).'\') no-repeat center center; background-size: cover;"';
            }
            ?>
        >
            <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Аватар" class="avatar">
            <h3><?= htmlspecialchars($profile_data['nickname']) ?></h3>
            <?php if (!empty($profile_data['about_me'])): ?>
                <p><?= nl2br(htmlspecialchars($profile_data['about_me'])) ?></p>
            <?php endif; ?>
        </div>

        <!-- Форма создания нового поста (только для владельца профиля) -->
        <?php if ($isOwner): ?>
            <h2>Новый пост</h2>
            <form method="post" action="actions/add_post.php" enctype="multipart/form-data" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                <textarea name="content" placeholder="Напишите текст вашего поста..." required style="width:100%; padding:10px; border:1px solid #444; border-radius:4px; background-color:#1e1e2e; color:#fff;"></textarea>
                <br>
                <label for="media_file">Загрузите медиафайл (изображение, GIF или видео):</label>
                <input type="file" name="media_file" id="media_file" accept="image/*,video/*">
                <br><br>
                <label>
                    <input type="checkbox" name="is_public" value="1" checked style="margin-right:5px;">
                    Показать в общей ленте
                </label>
                <br><br>
                <button type="submit" style="padding:10px 20px; background-color:#6c5ce7; border:none; border-radius:4px; color:#fff; cursor:pointer;">Опубликовать пост</button>
            </form>
        <?php endif; ?>

        <!-- Список постов пользователя -->
        <h2>Посты</h2>
        <?php if ($result_posts->num_rows > 0): ?>
            <?php while ($post = $result_posts->fetch_assoc()): ?>
                <div class="post-card" id="post-<?= $post['id'] ?>">
                    <div class="post-header">
                        <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Аватар" class="avatar">
                        <div class="user-info">
                            <span class="nickname"><?= htmlspecialchars($profile_data['nickname']) ?></span>
                            <span class="post-date"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                        </div>
                        <?php if ($isOwner || $is_admin): ?>
                            <a href="actions/delete_post.php?id=<?= $post['id'] ?>" class="ajax-delete delete-button" data-post-id="<?= $post['id'] ?>">Удалить</a>
                            <?php $pinLabel = ($post['pinned'] == 1) ? 'Открепить' : 'Закрепить'; ?>
                            <a href="actions/pin_post.php?id=<?= $post['id'] ?>&action=<?= ($post['pinned'] == 1) ? 'unpin' : 'pin' ?>" class="ajax-pin pin-button" data-post-id="<?= $post['id'] ?>"><?= $pinLabel ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="post-content">
                        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        <?php if (!empty($post['media_url'])): ?>
                            <?php if ($post['media_type'] === 'video'): ?>
                                <video controls class="post-media">
                                    <source src="<?= htmlspecialchars($post['media_url']) ?>" type="video/mp4">
                                    Ваш браузер не поддерживает видео.
                                </video>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($post['media_url']) ?>" alt="Медиа" class="post-media">
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="post-footer">
                        <a href="actions/like_post.php?id=<?= $post['id'] ?>&vote=like" class="ajax-like like-button" data-post-id="<?= $post['id'] ?>">
                            ▲ <span class="vote-count"><?= $post['likes'] > 0 ? htmlspecialchars($post['likes']) : '' ?></span>
                        </a>
                        <a href="actions/like_post.php?id=<?= $post['id'] ?>&vote=dislike" class="ajax-dislike dislike-button" data-post-id="<?= $post['id'] ?>">
                            ▼ <span class="vote-count"><?= $post['dislikes'] > 0 ? htmlspecialchars($post['dislikes']) : '' ?></span>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Постов пока нет.</p>
        <?php endif; ?>

        <!-- Секция комментариев профиля -->
        <h2>Комментарии профиля</h2>
        <form id="commentForm" method="post" action="actions/add_comment.php" onsubmit="event.preventDefault(); ajaxAddComment(this);">
            <input type="hidden" name="profile_vk" value="<?= htmlspecialchars($target_vk); ?>">
            <textarea name="comment" class="comment-input" placeholder="Ваш комментарий" style="resize: vertical;"></textarea>
            <button type="submit" class="comment-submit">Отправить</button>
        </form>
        <div class="profile-comments" id="commentsContainer">
            <?php if ($comments_result->num_rows > 0): ?>
                <?php while ($comment = $comments_result->fetch_assoc()): ?>
                    <div class="comment" id="comment-<?= $comment['id']; ?>">
                        <p><strong><?= htmlspecialchars($comment['author_vk']); ?>:</strong> <?= nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        <small><?= htmlspecialchars($comment['created_at']); ?></small>
                        <?php if (strtolower($comment['author_vk']) === strtolower($current_vk) || $is_admin): ?>
                            <button class="ajax-delete-comment delete-button" data-comment-id="<?= $comment['id']; ?>">Удалить</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Комментариев нет.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($showExtraTabs): ?>
        <!-- Вкладки "Мои заявки" и "Редактор страницы" (только для владельца или админа) -->
        <div id="applications" class="tab-content">
            <h2>Мои заявки</h2>
            <?php
            $stmt_apps = $mysqli->prepare("SELECT id, created_at, status, comment FROM applications WHERE vk_id = ? ORDER BY created_at DESC");
            $stmt_apps->bind_param("s", $target_vk);
            $stmt_apps->execute();
            $result_apps = $stmt_apps->get_result();
            if ($result_apps->num_rows > 0):
                ?>
                <table class="applications-table">
                    <thead>
                    <tr>
                        <th>Номер заявки</th>
                        <th>Время подачи</th>
                        <th>Статус</th>
                        <th>Комментарий</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($app = $result_apps->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['id']); ?></td>
                            <td><?= htmlspecialchars($app['created_at']); ?></td>
                            <td>
                                <?php
                                switch ($app['status']) {
                                    case 'accepted':
                                        echo "Ваша заявка принята";
                                        break;
                                    case 'rejected':
                                        echo "Ваша заявка отклонена";
                                        break;
                                    default:
                                        echo "Ожидает рассмотрения";
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($app['comment']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Вы ещё не подавали заявок.</p>
            <?php endif; ?>
        </div>

        <div id="editor" class="tab-content">
            <h2>Редактор страницы</h2>
            <form method="post" action="actions/update_profile.php" class="profile-customization" enctype="multipart/form-data">
                <label for="nickname">Никнейм (уникальный):</label>
                <input type="text" name="nickname" id="nickname" placeholder="Введите ваш ник" value="<?= htmlspecialchars($profile_data['nickname'] ?? ''); ?>" required>
                <label for="about_me">О себе:</label>
                <textarea name="about_me" id="about_me" placeholder="Расскажите о себе..."><?= htmlspecialchars($profile_data['about_me'] ?? ''); ?></textarea>
                <label for="banner_url">Баннер (URL изображения):</label>
                <input type="text" name="banner_url" id="banner_url" placeholder="Ссылка на изображение баннера" value="<?= htmlspecialchars($profile_data['banner_url'] ?? ''); ?>">
                <label for="banner_file">Или загрузите файл баннера:</label>
                <input type="file" name="banner_file" id="banner_file" accept="image/*">
                <label for="background_url">Фон (URL изображения):</label>
                <input type="text" name="background_url" id="background_url" placeholder="Ссылка на изображение фона" value="<?= htmlspecialchars($profile_data['background_url'] ?? ''); ?>">
                <label for="background_file">Или загрузите файл фона:</label>
                <input type="file" name="background_file" id="background_file" accept="image/*">
                <button type="submit">Сохранить настройки</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<!-- Подключаем JS-файлы -->
<script src="js/profile.js"></script>
<script src="js/main-script.js"></script>
</body>
</html>
