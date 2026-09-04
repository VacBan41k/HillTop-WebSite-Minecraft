<?php
session_start();
include 'config.php';
include 'db_connection.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['user'])) {
    header("Location: login.actions");
    exit;
}

$profile_vk = $_SESSION['user']['vk_id'];
$content = trim($_POST['content'] ?? '');

// Получаем значение галочки: если установлена — 1, если нет — 0
$is_public = isset($_POST['is_public']) ? 1 : 0;

// Инициализация переменных для хранения медиа
$media_url = '';
$media_type = '';

// Обработка загружаемого файла, если он есть
if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadError = $_FILES['media_file']['error'];
    if ($uploadError !== UPLOAD_ERR_OK) {
        // Сопоставление кодов ошибок с сообщениями
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => "Размер файла превышает директиву upload_max_filesize в actions.ini.",
            UPLOAD_ERR_FORM_SIZE  => "Размер файла превышает MAX_FILE_SIZE, указанную в HTML-форме.",
            UPLOAD_ERR_PARTIAL    => "Файл был загружен только частично.",
            UPLOAD_ERR_NO_FILE    => "Файл не был загружен.",
            UPLOAD_ERR_NO_TMP_DIR => "Отсутствует временная папка.",
            UPLOAD_ERR_CANT_WRITE => "Не удалось записать файл на диск.",
            UPLOAD_ERR_EXTENSION  => "PHP-расширение остановило загрузку файла."
        ];
        $errorMessage = isset($errorMessages[$uploadError]) ? $errorMessages[$uploadError] : "Неизвестная ошибка загрузки.";
        die("Ошибка загрузки файла (код ошибки: $uploadError). " . $errorMessage);
    }

    $uploadDir = '../uploads/posts/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            die("Не удалось создать папку для загрузок.");
        }
    }

    // Формирование уникального имени файла
    $fileName = time() . "_" . basename($_FILES['media_file']['name']);
    $targetFile = $uploadDir . $fileName;

    // Перемещаем загруженный файл в целевую папку
    if (!move_uploaded_file($_FILES['media_file']['tmp_name'], $targetFile)) {
        die("Ошибка перемещения файла в папку: $targetFile");
    }

    // Если файл является изображением, проверяем его размеры и масштабируем при необходимости
    $imageInfo = @getimagesize($targetFile);
    if ($imageInfo !== false) {
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $maxDimension = 1920; // Максимальная ширина/высота после масштабирования
        if ($width > $maxDimension || $height > $maxDimension) {
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new Imagick($targetFile);
                    $imagick->thumbnailImage($maxDimension, $maxDimension, true);
                    $imagick->setImageCompressionQuality(85);
                    $imagick->writeImage($targetFile);
                    $imagick->clear();
                    $imagick->destroy();
                } catch (Exception $e) {
                    error_log("Imagick error: " . $e->getMessage());
                }
            } else {
                error_log("Расширение Imagick не установлено – изображение не масштабируется.");
            }
        }
    }

    // Определяем тип медиафайла по расширению
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $media_type = 'image';
    } elseif ($ext === 'gif') {
        $media_type = 'gif';
    } elseif (in_array($ext, ['mp4', 'avi', 'mov'])) {
        $media_type = 'video';
    }

    $media_url = $targetFile;
}

$stmt = $mysqli->prepare("INSERT INTO posts (profile_vk, content, media_url, media_type, is_public) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $mysqli->error);
}
$stmt->bind_param("ssssi", $profile_vk, $content, $media_url, $media_type, $is_public);
$stmt->execute();

header("Location: /profile.php");
exit;
?>
