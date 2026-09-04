<?php
// Запускаем сессию, если она ещё не запущена
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Подключаем конфигурацию и вспомогательные файлы
require_once 'actions/config.php';
require_once 'actions/check_admin.php';
require_once 'actions/db_connection.php';

// Определяем переменные для шапки
$vk_id = isset($_SESSION['user']['vk_id']) ? (string)$_SESSION['user']['vk_id'] : null;
$is_admin = $vk_id ? isAdmin($vk_id, $mysqli) : false;
// Если пользователь авторизован, то считаем его авторизованным (даже если он не админ)
$isAuthorized = isset($_SESSION['user']);
?>

<!-- Подключаем стили для шапки -->
<link rel="stylesheet" href="css/styles-header.css">
<link rel="icon" href="img/favicon.png" type="image/png">

<!-- Подключаем скрипты для шапки -->
<script src="js/script-index.js"></script>
<script src="js/main-script.js"></script>
<script src="js/auth-handler.js"></script>

<header>
    <?php include 'actions/config.php'; ?>
    <div class="logo">
        <a href="index.php">
            <img src="img/logo.png" alt="Hilltop Logo" class="logo-img">
            <span class="logo-text">Hilltop</span>
        </a>
        <div class="burger-menu" id="burger-menu">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>
    </div>

    <nav class="nav-center" id="nav-menu">

        <a href="wiki.php">Wiki</a>

        <?php if ($isAuthorized): ?>
            <a href="tape.php">Лента</a>
        <?php endif; ?>
        <?php if ($isAuthorized): ?>
            <a href="https://disk.yandex.ru/d/spvF6OdbF6bHVQ">Сборка</a>
        <?php endif; ?>
        <?php if ($is_admin): ?>
            <a href="viewing.php">Заявки</a>
        <?php endif; ?>
    </nav>

    <?php if (isset($_SESSION['user'])): ?>
        <div class="user-info">
            <img src="<?= $_SESSION['user']['photo_url'] ?>" alt="Аватар" class="user-avatar" id="user-avatar">
            <div class="user-menu" id="user-menu">
                <a href="profile.php?id=1" class="menu-item">Мой профиль</a>
                <a href="actions/logout.php" class="menu-item">Выход</a>
            </div>
        </div>
    <?php else: ?>
        <a href="#" class="request-button" id="login-button">Вход</a>
        <div class="modal" id="login-modal">
            <div class="modal-content">
                <h2>Вход в аккаунт</h2>
                <a class="login-option vk" href="https://oauth.vk.com/authorize?client_id=<?=ID?>&display=page&redirect_uri=<?=URL?>&scope=photos&response_type=code&v=5.131">VK ID</a>
            </div>
        </div>
    <?php endif; ?>
</header>

