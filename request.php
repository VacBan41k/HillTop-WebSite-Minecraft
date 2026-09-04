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
if (!isset($_SESSION['user'])) {
    header("Location: /index.php");
    exit();
}

// Если сессия ещё не запущена, запускаем её
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подача заявки - Hilltop</title>
    <link rel="stylesheet" href="css/styles-request.css">
</head>
<body>

<!-- Шапка сайта -->

<main>
    <div class="form-container">
        <form id="request-form">
            <div class="question" id="question-container">
                <!-- Здесь будут подгружаться вопросы -->
            </div>
            <div class="navigation-buttons">
                <button type="button" id="prev-btn" disabled>Предыдущий вопрос</button>
                <button type="button" id="next-btn">Следующий вопрос</button>
            </div>
            <button type="button" id="submit-btn" style="display: none;">Отправить заявку</button>
        </form>
    </div>

    <!-- Контейнер для сообщения об успешной отправке -->
    <div id="message-container"></div>
</main>

<!-- Подвал сайта -->
<?php include 'footer.php'; ?>

<script src="js/main-script.js"></script>
<script src="js/request.js"></script>

</body>
</html>
