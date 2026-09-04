<?php
include 'config.php';
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($id > 0 && in_array($status, ['accepted', 'rejected'])) {
        if ($status === 'rejected') {
            // Получаем комментарий (причину отказа)
            $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
            $stmt = $mysqli->prepare("UPDATE applications SET status = ?, comment = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $comment, $id);
        } else {
            // Для принятия сбрасываем комментарий
            $stmt = $mysqli->prepare("UPDATE applications SET status = ?, comment = NULL WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Ошибка запроса: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Некорректные данные"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Неверный метод запроса"]);
}
?>
