<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/razorpay.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Razorpay\Api\Api;

header('Content-Type: application/json');

try {
    $pdo = getDatabaseConnection();
    
    // Read JSON input or POST data
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $type = $input['type'] ?? '';
    $id = (int)($input['id'] ?? 0);
    
    if (!in_array($type, ['fee', 'booking'], true) || $id <= 0) {
        throw new RuntimeException('Invalid type or id.');
    }
    
    $amount = 0;
    
    if ($type === 'fee') {
        $stmt = $pdo->prepare('SELECT amount, status FROM student_fees WHERE id = ?');
        $stmt->execute([$id]);
        $fee = $stmt->fetch();
        
        if (!$fee) {
            throw new RuntimeException('Fee record not found.');
        }
        if ($fee['status'] === 'PAID') {
            throw new RuntimeException('Fee is already paid.');
        }
        $amount = (float)$fee['amount'];
    } else if ($type === 'booking') {
        $stmt = $pdo->prepare('SELECT total_amount, payment_status FROM daily_bookings WHERE id = ?');
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            throw new RuntimeException('Booking record not found.');
        }
        if ($booking['payment_status'] === 'PAID') {
            throw new RuntimeException('Booking is already paid.');
        }
        $amount = (float)$booking['total_amount'];
    }
    
    if ($amount <= 0) {
        throw new RuntimeException('Invalid amount.');
    }
    
    $amountInPaise = (int)round($amount * 100);
    
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    $order = $api->order->create([
        'receipt' => 'order_rcptid_' . time() . '_' . $id,
        'amount' => $amountInPaise,
        'currency' => 'INR',
        'notes' => [
            'type' => $type,
            'db_id' => $id
        ]
    ]);
    
    echo json_encode([
        'success' => true,
        'order_id' => $order['id'],
        'amount' => $amountInPaise,
        'key' => RAZORPAY_KEY_ID,
        'type' => $type,
        'db_id' => $id
    ]);
    
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
