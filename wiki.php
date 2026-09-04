<?php
// wiki.actions

// Задаём параметры cookie для сессии (30 дней = 2592000 секунд)
session_set_cookie_params([
    'lifetime' => 2592000,
    'path'     => '/',
    'domain'   => 'hilltoprp.ru', // укажите ваш домен
    'secure'   => true,          // если используется HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
ini_set('session.gc_maxlifetime', 2592000);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'actions/config.php';
require_once 'actions/check_admin.php';
require_once 'actions/db_connection.php';



include 'header.php';
?>

<div class="wiki-content">
    <!-- Встраиваем GitBook страницу в iframe -->
    <iframe src="https://hilltops-organization.gitbook.io/hilltop"
            style="width: 100%; height: calc(100vh - 150px); border: none;">
    </iframe>
</div>

<?php include 'footer.php'; ?>
<script src="js/main-script.js"></script>
<script src="js/wiki.js"></script>


