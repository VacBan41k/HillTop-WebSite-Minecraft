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
include 'actions/check_admin.php'; // Функция проверки админа
include 'actions/db_connection.php';

$vk_id = isset($_SESSION['user']['vk_id']) ? (string)$_SESSION['user']['vk_id'] : null;
$is_admin = $vk_id ? isAdmin($vk_id, $mysqli) : false;
if (!$is_admin) { // Если не админ, перенаправляем на index.actions
    header("Location: /index.php");
    exit();
}

$isAuthorized = false;
if (isset($_SESSION['user'])) {
    $vk_id = $_SESSION['user']['vk_id'];
    $isAuthorized = isAdmin($vk_id, $mysqli);
}

// Получаем список колонок таблицы applications
$resultColumns = $mysqli->query("SHOW COLUMNS FROM applications");
$columns = [];
while ($row = $resultColumns->fetch_assoc()) {
    $columns[] = $row['Field'];
}

// Первичная выборка активных заявок (status = 'pending')
$resultActive = $mysqli->query("SELECT * FROM applications WHERE status = 'pending' ORDER BY created_at DESC");
$activeApplications = ($resultActive && $resultActive->num_rows > 0) ? $resultActive->fetch_all(MYSQLI_ASSOC) : [];

// Первичная выборка архивных заявок (status = 'accepted' или 'rejected')
$resultArchived = $mysqli->query("SELECT * FROM applications WHERE status IN ('accepted','rejected') ORDER BY created_at DESC");
$archivedApplications = ($resultArchived && $resultArchived->num_rows > 0) ? $resultArchived->fetch_all(MYSQLI_ASSOC) : [];
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
    <title>Hilltop - Сервер по Майнкрафту</title>
    <link rel="stylesheet" href="css/styles-index.css">
    <link rel="stylesheet" href="css/styles-viewing.css">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <!-- Передаём массив колонок в JS -->
    <script>
        var columns = <?= json_encode($columns) ?>;
    </script>
</head>
<body>


    <!-- Контент для просмотра заявок -->
    <div class="content-section">
        <div class="tabs">
            <button class="tab-button active" data-tab="applications" onclick="switchTab('applications')">Заявки</button>
            <button class="tab-button" data-tab="history" onclick="switchTab('history')">История заявок</button>
        </div>
        <!-- Вкладка: Заявки -->
        <div id="applications" class="tab-content" style="display: block;">
            <div class="table-container">
                <table class="applications-table">
                    <thead>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody id="applications-body">
                    <?php if (!empty($activeApplications)): ?>
                        <?php foreach ($activeApplications as $app): ?>
                            <tr id="app-<?= $app['id'] ?>">
                                <?php foreach ($columns as $col): ?>
                                    <td><?= htmlspecialchars(isset($app[$col]) ? $app[$col] : '') ?></td>
                                <?php endforeach; ?>
                                <td>
                                    <button class="accept-btn" onclick="processApplication(<?= $app['id'] ?>, 'accepted')">Принять</button>
                                    <button class="reject-btn" onclick="processApplication(<?= $app['id'] ?>, 'rejected')">Отклонить</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= count($columns) + 1 ?>">Нет активных заявок</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Вкладка: История заявок -->
        <div id="history" class="tab-content" style="display: none;">
            <label for="filter">Фильтр:</label>
            <select id="filter" onchange="filterHistory()">
                <option value="all">Все</option>
                <option value="accepted">Принятые</option>
                <option value="rejected">Отклоненные</option>
            </select>
            <div class="table-container">
                <table class="applications-table">
                    <thead>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody id="history-body">
                    <?php if (!empty($archivedApplications)): ?>
                        <?php foreach ($archivedApplications as $app): ?>
                            <tr class="<?= htmlspecialchars($app['status']) ?>">
                                <?php foreach ($columns as $col): ?>
                                    <td><?= htmlspecialchars(isset($app[$col]) ? $app[$col] : '') ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= count($columns) ?>">История пуста</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    <?php include 'footer.php'; ?>

<!-- Подключение скриптов -->

<script src="js/main-script.js"></script>
<script src="js/viewing.js"></script>
</body>
</html>
