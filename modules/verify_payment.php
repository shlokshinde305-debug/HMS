<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/razorpay.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $payment_id = $input['razorpay_payment_id'] ?? '';
    $order_id = $input['razorpay_order_id'] ?? '';
    $signature = $input['razorpay_signature'] ?? '';
    $type = $input['type'] ?? '';
    $db_id = (int)($input['db_id'] ?? 0);
    
    if (!$payment_id || !$order_id || !$signature || !in_array($type, ['fee', 'booking'], true) || $db_id <= 0) {
        throw new RuntimeException('Missing required parameters.');
    }
    
    // Verify signature
    $generated_signature = hash_hmac("sha256", $order_id . "|" . $payment_id, RAZORPAY_KEY_SECRET);
    
    if (!hash_equals($generated_signature, $signature)) {
        throw new RuntimeException('Invalid payment signature.');
    }
    
    $pdo = getDatabaseConnection();
    
    if ($type === 'fee') {
        $stmt = $pdo->prepare(
            'UPDATE student_fees 
             SET status = "PAID", payment_method = "Razorpay", transaction_id = ?, paid_date = CURDATE()
             WHERE id = ?'
        );
        $stmt->execute([$payment_id, $db_id]);
    } else if ($type === 'booking') {
        $stmt = $pdo->prepare(
            'UPDATE daily_bookings 
             SET payment_status = "PAID", payment_method = "Razorpay", transaction_id = ?, paid_date = CURDATE()
             WHERE id = ?'
        );
        $stmt->execute([$payment_id, $db_id]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
