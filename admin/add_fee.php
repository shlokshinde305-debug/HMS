<?php
require_once('../config/database.php');
$pdo = getDatabaseConnection();

if($_POST){
    $student_id = $_POST['student_id'];
    $amount = $_POST['amount'];
    $due_date = $_POST['due_date'];

    $stmt = $pdo->prepare("INSERT INTO student_fees (student_id, amount, due_date) VALUES (?, ?, ?)");
    $stmt->execute([$student_id, $amount, $due_date]);

    echo "Fee Assigned!";
}
?>

<form method="POST">
    Student ID: <input name="student_id"><br>
    Amount: <input name="amount"><br>
    Due Date: <input type="date" name="due_date"><br>
    <button type="submit">Add Fee</button>
</form>