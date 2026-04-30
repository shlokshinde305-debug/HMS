<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/auth_check.php';
require_once __DIR__ . '/includes/layout.php';

$successMessage = '';
$errorMessage = '';

if (isset($_GET['success'])) {
    $successMessage = 'Booking successful! Payment received.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    if (isset($input['action']) && $input['action'] === 'initiate_booking') {
        header('Content-Type: application/json');
        try {
            $pdo = getDatabaseConnection();
            
            $name = trim((string)($input['name'] ?? ''));
            $phone = trim((string)($input['phone'] ?? ''));
            $checkIn = trim((string)($input['check_in'] ?? ''));
            $checkOut = trim((string)($input['check_out'] ?? ''));
            
            if ($name === '' || $phone === '' || $checkIn === '' || $checkOut === '') {
                throw new RuntimeException('All fields are required.');
            }
            
            $date1 = new DateTime($checkIn);
            $date2 = new DateTime($checkOut);
            
            if ($date2 <= $date1) {
                throw new RuntimeException('Check-out date must be after check-in date.');
            }
            
            $interval = $date1->diff($date2);
            $totalDays = $interval->days;
            $totalAmount = $totalDays * 500;
            
            $stmt = $pdo->prepare(
                'INSERT INTO daily_bookings (name, phone, check_in, check_out, total_days, total_amount, payment_status, payment_method, transaction_id, paid_date)
                 VALUES (:name, :phone, :check_in, :check_out, :total_days, :total_amount, "PENDING", NULL, NULL, NULL)'
            );
            
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':check_in' => $checkIn,
                ':check_out' => $checkOut,
                ':total_days' => $totalDays,
                ':total_amount' => $totalAmount
            ]);
            
            echo json_encode(['success' => true, 'booking_id' => $pdo->lastInsertId()]);
        } catch (Throwable $exception) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $exception->getMessage()]);
        }
        exit;
    }
}

renderPageHeader('Daily Room Booking | Hostel Management', 'home');
require_once __DIR__ . '/includes/fake_razorpay.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="soft-panel p-4 p-md-5">
            <h2 class="mb-4 text-center">Daily Room Booking</h2>
            
            <?php if ($successMessage !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                <div class="text-center mt-4">
                    <a href="<?php echo htmlspecialchars(appUrl()); ?>" class="btn btn-outline-dark">Return to Home</a>
                </div>
            <?php else: ?>
                
                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
                <?php endif; ?>
                
                <form method="post" id="dailyBookingForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Check-in Date</label>
                            <input type="date" name="check_in" id="check_in" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-out Date</label>
                            <input type="date" name="check_out" id="check_out" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                    </div>
                    
                    <div class="card bg-light mb-4 border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Per Day Rent:</span>
                                <strong>₹500</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Days:</span>
                                <strong id="display_days">0</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fs-5">Total Amount:</span>
                                <strong class="fs-5 text-primary">₹<span id="display_amount">0</span></strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Old payment details removed for Razorpay -->
                    
                    <button type="button" class="btn btn-primary btn-lg w-100 shadow-sm" id="payBtn">
                        Pay Now & Book
                    </button>
                </form>
                
            <?php endif; ?>
        </div>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const displayDays = document.getElementById('display_days');
    const displayAmount = document.getElementById('display_amount');
    const payBtn = document.getElementById('payBtn');
    
    function calculateTotal() {
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);
        
        if (checkInInput.value && checkOutInput.value && checkOut > checkIn) {
            const timeDiff = checkOut.getTime() - checkIn.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            displayDays.textContent = daysDiff;
            displayAmount.textContent = (daysDiff * 500).toLocaleString('en-IN');
            
            payBtn.disabled = false;
            payBtn.textContent = 'Pay ₹' + (daysDiff * 500).toLocaleString('en-IN') + ' & Book';
        } else {
            displayDays.textContent = '0';
            displayAmount.textContent = '0';
            payBtn.disabled = false;
            payBtn.textContent = 'Pay Now & Book';
        }
        
        // Update check-out min date
        if (checkInInput.value) {
            const nextDay = new Date(checkIn);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.min = nextDay.toISOString().split('T')[0];
            
            if (checkOut <= checkIn) {
                checkOutInput.value = nextDay.toISOString().split('T')[0];
                calculateTotal(); // Recalculate
            }
        }
    }
    
    checkInInput.addEventListener('change', calculateTotal);
    checkOutInput.addEventListener('change', calculateTotal);

    document.getElementById('payBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        let totalAmount = 500; // Default amount if dates aren't fully filled
        
        if (checkInInput.value && checkOutInput.value) {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);
            
            if (checkOut > checkIn) {
                const timeDiff = checkOut.getTime() - checkIn.getTime();
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                totalAmount = daysDiff * 500;
            }
        }
        
        openFakeRazorpay(totalAmount, function() {
            alert("Payment Successful (Demo)!");
            window.location.href = 'daily_booking.php?success=1';
        });
    });
});
</script>

<?php renderPageFooter(); ?>
