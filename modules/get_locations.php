<?php
require_once(__DIR__ . '/../config/database.php');

$pdo = getDatabaseConnection();

$stmt = $pdo->query("SELECT * FROM student_location ORDER BY id DESC");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>