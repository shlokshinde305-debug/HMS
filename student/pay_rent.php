<?php
require_once('../config/database.php');
require_once('../auth/auth_check.php');

requireRole('student');

$pdo = getDatabaseConnection();

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM rents WHERE id=?");
$stmt->execute([$id]);
$rent = $stmt->fetch();

$today = date('Y-m-d');

// OPTIONAL FINE
$fine = 0;
if ($today > $rent['due_date']) {
    $days = (strtotime($today) - strtotime($rent['due_date'])) / (60*60*24);
    $fine = $days * 10;
}

// UPDATE PAYMENT
$stmt = $pdo->prepare("
UPDATE rents 
SET status='paid', paid_date=?, fine=? 
WHERE id=?
");

$stmt->execute([$today, $fine, $id]);

header("Location: dashboard.php");
exit;