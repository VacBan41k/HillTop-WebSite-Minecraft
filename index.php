<?php

// Задаём параметры cookie для сессии (30 дней = 2592000 секунд)
session_set_cookie_params([
    'lifetime' => 2592000,      // 30 дней
    'path'     => '/',
    'domain'   => 'hilltoprp.ru',           // При необходимости укажите домен
    'secure'   => true,        // Если используется HTTPS, установите true
    'httponly' => true,
    'samesite' => 'Lax'         // Можно использовать 'Strict', если требуется
]);

// Задаём время жизни сессии для сборщика мусора
ini_set('session.gc_maxlifetime', 2592000);

// Если сессия ещё не запущена, запускаем её
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


session_start();

include 'actions/config.php';
include 'actions/check_admin.php'; // Подключаем функцию проверки админа
include 'actions/db_connection.php';

$vk_id = isset($_SESSION['user']['vk_id']) ? (string)$_SESSION['user']['vk_id'] : null;
$is_admin = $vk_id ? isAdmin($vk_id, $mysqli) : false;
$isAuthorized = false;
if (isset($_SESSION['user'])) {
    $vk_id = $_SESSION['user']['vk_id'];
    $isAuthorized = isAdmin($vk_id, $mysqli);
}


include 'header.php';
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hilltop - Сервер по Майнкрафту</title>
    <link rel="stylesheet" href="css/styles-index.css">
    <link rel="icon" href="img/favicon.png" type="image/png" class="icon">
</head>
<body>



<div>
    <section class="hero">
        <div class="hero-content">
            <h1>Уникальный<br>Майнкрафт сервер</h1>
            <p>HillTop - Это целая страна на анти-ванильном сервере майнкрафт.<br>Без гриферов, приватoв или доната.</p>
            <div class="buttons">
                <a href="request.php"
                   class="server-button"
                   id="request-button"
                   data-auth="<?= isset($_SESSION['user']) ? 'true' : 'false' ?>">
                    Подать заявку
                </a>
                <div class="server-ip" id="server-ip">IP: tps.hilltoprp.ru</div>
            </div>
        </div>
        <div class="hero-image">
            <img src="img/characters.png" alt="Minecraft Characters">
        </div>
    </section>
</div>

<!--<div class="about_server">-->
<!--    <h1>О сервере</h1>-->
<!--    <div class="info_card_left">-->
<!--        <img src="img/generation.png" alt="" class="info_card_img">-->
<!--        <span class="info_card_text">Изменённая генерация мира — теперь мир, ад и энд получили новую проработанную генерацию: больше интересных ландшафтов, деталей и атмосферности.</span>-->
<!--    </div>-->
<!--    <div class="info_card_right">-->
<!--        <span class="info_card_text">Строительство без ограничений — стройте города и реализуйте любые идеи с расширенными инструментами.</span>-->
<!--        <img src="img/build.png" alt="" class="info_card_img">-->
<!--    </div>-->
<!--    <div class="info_card_left">-->
<!--        <img src="img/paints.png" alt="" class="info_card_img">-->
<!--        <span class="info_card_text">Холст для рисования — бросьте яйцо в картину и создавайте собственные шедевры.</span>-->
<!--    </div>-->
<!--    <div class="info_card_right">-->
<!--        <span class="info_card_text">Рагу роста — меняйте рост персонажа с помощью особого рецепта из кувшинницы.</span>-->
<!--        <img src="img/height.png" alt="" class="info_card_img">-->
<!--    </div>-->
<!--</div>-->

<script src="js/script-index.js"></script>
<script src="js/main-script.js"></script>
<script src="js/auth-handler.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>
