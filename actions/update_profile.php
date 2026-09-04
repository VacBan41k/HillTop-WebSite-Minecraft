<?php
session_start();
require_once 'config.php';
require_once 'db_connection.php';

// Если пользователь не авторизован, перенаправляем на страницу входа
if (!isset($_SESSION['user'])) {
    header("Location: ../login.actions");
    exit();
}

$vk_id = $_SESSION['user']['vk_id'];
$error = "";

// Получаем данные пользователя из базы данных
$stmt = $mysqli->prepare("SELECT nickname, about_me, banner_url, background_url FROM users WHERE vk_id = ?");
$stmt->bind_param("s", $vk_id);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if (!$userData) {
    die("Пользователь не найден.");
}

// Определяем, первый ли раз пользователь заполняет профиль (если nickname пустой)
$isFirstTime = empty($userData['nickname']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($isFirstTime) {
        // Режим первого заполнения — обновляем только nickname
        $nickname = trim($_POST['nickname'] ?? '');
        if (empty($nickname)) {
            $error = "Никнейм не может быть пустым.";
        }
        if (empty($error)) {
            $stmtUpdate = $mysqli->prepare("UPDATE users SET nickname = ? WHERE vk_id = ?");
            if (!$stmtUpdate) {
                $error = "Ошибка подготовки запроса: " . $mysqli->error;
            } else {
                $stmtUpdate->bind_param("ss", $nickname, $vk_id);
                if ($stmtUpdate->execute()) {
                    $_SESSION['user']['nickname'] = $nickname;
                    header("Location: /profile.php");
                    exit();
                } else {
                    $error = "Ошибка обновления профиля.";
                }
            }
        }
    } else {
        // Режим редактирования: обновляем остальные поля (nickname остаётся неизменным)
        $about_me = trim($_POST['about_me'] ?? '');
        $banner_url = trim($_POST['banner_url'] ?? '');
        $background_url = trim($_POST['background_url'] ?? '');

        // Обработка загрузки файла баннера
        if (!empty($_FILES['banner_file']['name'])) {
            if ($_FILES['banner_file']['error'] === UPLOAD_ERR_OK) {
                // Абсолютный путь для сохранения файла баннера
                $banner_uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/banners/';
                // URL для отображения баннера (относительно корня сайта)
                $banner_uploadUrl = '/uploads/banners/';
                if (!is_dir($banner_uploadDir)) {
                    if (!mkdir($banner_uploadDir, 0777, true)) {
                        $error = "Не удалось создать папку для баннеров.";
                    }
                }
                $uniqueBannerFilename = time() . "_" . basename($_FILES['banner_file']['name']);
                $bannerTargetPath = $banner_uploadDir . $uniqueBannerFilename;
                if (move_uploaded_file($_FILES['banner_file']['tmp_name'], $bannerTargetPath)) {
                    $banner_url = $banner_uploadUrl . $uniqueBannerFilename;
                } else {
                    $error = "Ошибка загрузки файла баннера.";
                }
            } else {
                $error = "Ошибка загрузки файла баннера. Код ошибки: " . $_FILES['banner_file']['error'];
            }
        }

        // Обработка загрузки файла фона
        if (!empty($_FILES['background_file']['name'])) {
            if ($_FILES['background_file']['error'] === UPLOAD_ERR_OK) {
                // Абсолютный путь для сохранения файла фона
                $background_uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/backgrounds/';
                // URL для отображения фона (относительно корня сайта)
                $background_uploadUrl = '/uploads/backgrounds/';
                if (!is_dir($background_uploadDir)) {
                    if (!mkdir($background_uploadDir, 0777, true)) {
                        $error = "Не удалось создать папку для фонов.";
                    }
                }
                $uniqueBackgroundFilename = time() . "_" . basename($_FILES['background_file']['name']);
                $backgroundTargetPath = $background_uploadDir . $uniqueBackgroundFilename;
                if (move_uploaded_file($_FILES['background_file']['tmp_name'], $backgroundTargetPath)) {
                    $background_url = $background_uploadUrl . $uniqueBackgroundFilename;
                } else {
                    $error = "Ошибка загрузки файла фона.";
                }
            } else {
                $error = "Ошибка загрузки файла фона. Код ошибки: " . $_FILES['background_file']['error'];
            }
        }

        if (empty($error)) {
            $stmtUpdate = $mysqli->prepare("UPDATE users SET about_me = ?, banner_url = ?, background_url = ? WHERE vk_id = ?");
            if (!$stmtUpdate) {
                $error = "Ошибка подготовки запроса: " . $mysqli->error;
            } else {
                $stmtUpdate->bind_param("ssss", $about_me, $banner_url, $background_url, $vk_id);
                if ($stmtUpdate->execute()) {
                    $_SESSION['user']['about_me'] = $about_me;
                    $_SESSION['user']['banner_url'] = $banner_url;
                    $_SESSION['user']['background_url'] = $background_url;
                    header("Location: /profile.php");
                    exit();
                } else {
                    $error = "Ошибка обновления профиля.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обновление профиля</title>
    <link rel="stylesheet" href="../css/styles-update-profile.css">
    <link rel="stylesheet" href="../css/styles-profile.css">
</head>
<body>
<div class="update-profile-container">
    <h1>Обновление профиля</h1>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" action="update_profile.php" enctype="multipart/form-data">
        <?php if ($isFirstTime): ?>
            <label for="nickname">Никнейм:</label>
            <input type="text" name="nickname" id="nickname" placeholder="Введите ваш ник" required>
            <button type="submit">Сохранить</button>
        <?php else: ?>
            <input type="hidden" name="nickname" value="<?php echo htmlspecialchars($userData['nickname']); ?>">
            <label for="about_me">О себе:</label>
            <textarea name="about_me" id="about_me" placeholder="Расскажите о себе..."><?php echo htmlspecialchars($userData['about_me'] ?? ''); ?></textarea>

            <label for="banner_url">Баннер (URL изображения):</label>
            <input type="text" name="banner_url" id="banner_url" placeholder="Ссылка на изображение баннера" value="<?php echo htmlspecialchars($userData['banner_url'] ?? ''); ?>">
            <label for="banner_file">Или загрузите файл баннера:</label>
            <input type="file" name="banner_file" id="banner_file" accept="image/*">

            <label for="background_url">Фон (URL изображения):</label>
            <input type="text" name="background_url" id="background_url" placeholder="Ссылка на изображение фона" value="<?php echo htmlspecialchars($userData['background_url'] ?? ''); ?>">
            <label for="background_file">Или загрузите файл фона:</label>
            <input type="file" name="background_file" id="background_file" accept="image/*">

            <button type="submit">Сохранить настройки</button>
        <?php endif; ?>
    </form>
</div>
</body>
</html>
