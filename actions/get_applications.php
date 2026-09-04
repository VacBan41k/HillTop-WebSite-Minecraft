<?php
include 'config.php';
include 'db_connection.php';

$query = "SELECT * FROM applications WHERE status = 'pending' ORDER BY created_at DESC";
$result = $mysqli->query($query);

$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
header('Content-Type: application/json');
echo json_encode($applications);
?>
