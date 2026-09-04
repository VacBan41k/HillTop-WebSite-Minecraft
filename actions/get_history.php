<?php
include 'config.php';
include 'db_connection.php';

$query = "SELECT * FROM applications WHERE status IN ('accepted','rejected') ORDER BY created_at DESC";
$result = $mysqli->query($query);

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
header('Content-Type: application/json');
echo json_encode($history);
?>
