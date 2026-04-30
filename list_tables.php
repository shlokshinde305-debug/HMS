<?php
require_once __DIR__ . '/config/database.php';
try {
    $pdo = getDatabaseConnection();
    
    echo "--- student_fees ---\n";
    $stmt = $pdo->query("DESCRIBE student_fees");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
    
    echo "\n--- daily_bookings ---\n";
    $stmt = $pdo->query("DESCRIBE daily_bookings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
