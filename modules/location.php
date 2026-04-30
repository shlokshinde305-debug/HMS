<?php
require_once(__DIR__ . '/../config/database.php');

$pdo = getDatabaseConnection();

$student_id = $_POST['student_id'] ?? 1;
$student_name = $_POST['student_name'] ?? 'Unknown';
$lat = $_POST['lat'] ?? '';
$lng = $_POST['lng'] ?? '';
$status = $_POST['status'] ?? '';

$stmt = $pdo->prepare("
INSERT INTO student_location (student_id, student_name, latitude, longitude, status)
VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([$student_id, $student_name, $lat, $lng, $status]);

echo "saved";
?>