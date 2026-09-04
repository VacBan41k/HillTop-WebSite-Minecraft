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
$isAuthorized = false;
if (isset($_SESSION['user'])) {
    $vk_id = $_SESSION['user']['vk_id'];
    $isAuthorized = isAdmin($vk_id, $mysqli);
}


?>

<!-- Подключаем стили для шапки -->
<link rel="stylesheet" href="css/styles-footer.css">
<link rel="icon" href="img/favicon.png" type="image/png">

<!-- Подключаем скрипты для шапки -->
<script src="js/script-index.js"></script>
<script src="js/main-script.js"></script>
<script src="js/auth-handler.js"></script>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <div class="footer-logo">
                <img src="img/logo.png" class="footer-logo-img" alt="HillTop Logo">
                <span>HillTop</span>
            </div>
            <p>
                Copyright © <?= date('Y'); ?> HillTop. <br> Все права защищены
            </p>
        </div>
        <div class="footer-section">
            <h3>Свяжитесь с нами</h3>
            <div class="links">
                <a href="mailto:support@hilltoprp.ru">support@hilltoprp.ru</a>
                <a href="https://t.me/hilltopSup">Телеграм поддержка</a>
                <a href="https://vk.com/hilltoprp6">Группа ВКонтакте</a>
                <a href="https://t.me/hilltoprp">Телеграм канал</a>
            </div>
        </div>
        <div class="footer-right">
            <img src="img/krisa2.gif" alt="krisa" class="footer-gif">
        </div>
    </div>
</footer>
