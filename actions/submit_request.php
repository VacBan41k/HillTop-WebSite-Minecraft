<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(["error" => "Не авторизован"]);
    exit();
}

require_once __DIR__ . '/db_connection.php';

$vk_id = $_SESSION['user']['vk_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Неверный метод запроса"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$requiredQuestions = 15;
if (count($data) !== $requiredQuestions) {
    echo json_encode(["error" => "Не все вопросы заполнены"]);
    exit();
}

$checkQuery = "SELECT created_at FROM applications WHERE vk_id = ? ORDER BY created_at DESC LIMIT 1";
$checkStmt = $mysqli->prepare($checkQuery);
$checkStmt->bind_param("i", $vk_id);
$checkStmt->execute();
$result = $checkStmt->get_result();
$lastRequest = $result->fetch_assoc();
$checkStmt->close();

if ($lastRequest) {
    $lastTime = strtotime($lastRequest['created_at']);
    $currentTime = time();
    $diffHours = ($currentTime - $lastTime) / 3600;

    if ($diffHours < 24) {
        echo json_encode(["error" => "Вы уже отправили заявку. Попробуйте снова через " . round(24 - $diffHours, 1) . " ч."]);
        exit();
    }
}

$first_name = $_SESSION['user']['first_name'];

$query = "INSERT INTO applications 
    (vk_id, first_name, question1, question2, question3, question4, question5, question6, question7, question8, question9, question10, question11, question12, question13, question14, question15, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $mysqli->prepare($query);
if ($stmt === false) {
    echo json_encode(["error" => "Ошибка подготовки запроса: " . $mysqli->error]);
    exit();
}

$stmt->bind_param("issssssssssssssss", $vk_id, $first_name, ...array_values($data));

if ($stmt->execute()) {
    echo json_encode(["success" => "Заявка успешно отправлена! Ожидайте ответа."]);
} else {
    echo json_encode(["error" => "Ошибка при отправке заявки: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
