<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';

requireRole('student');

$user = currentUser();
$successMessage = '';
$errorMessage = '';

try {
    $pdo = getDatabaseConnection();
    $studentId = (int) ($user['id'] ?? 0);

    if ($studentId <= 0) {
        throw new RuntimeException('Student identity is missing. Please login again.');
    }

    // ROOM DATA
    $activeAllocationStmt = $pdo->prepare(
        'SELECT a.id AS allocation_id, a.allocation_date, a.status AS allocation_status,
                r.id AS room_id, r.room_number, r.block_name, r.floor_number, r.room_type,
                r.capacity, r.occupied_beds, r.monthly_fee
         FROM allocations a
         INNER JOIN rooms r ON r.id = a.room_id
         WHERE a.student_id = :student_id AND a.status = "active"
         ORDER BY a.created_at DESC
         LIMIT 1'
    );
    $activeAllocationStmt->execute([':student_id' => $studentId]);
    $myRoom = $activeAllocationStmt->fetch();

    // COMPLAINT SUBMIT
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = sanitizeString((string) ($_POST['action'] ?? ''));

        if ($action === 'create_complaint') {
            $title = sanitizeString((string) ($_POST['title'] ?? ''));
            $description = sanitizeString((string) ($_POST['description'] ?? ''));
            $category = sanitizeString((string) ($_POST['category'] ?? 'other'));
            $priority = sanitizeString((string) ($_POST['priority'] ?? 'medium'));

            $roomId = $myRoom ? (int) $myRoom['room_id'] : null;

            $stmt = $pdo->prepare(
                'INSERT INTO complaints (student_id, room_id, title, description, category, priority, status)
                 VALUES (:student_id, :room_id, :title, :description, :category, :priority, "open")'
            );
            $stmt->execute([
                ':student_id' => $studentId,
                ':room_id' => $roomId,
                ':title' => $title,
                ':description' => $description,
                ':category' => $category,
                ':priority' => $priority
            ]);

            $successMessage = 'Complaint submitted successfully.';
        }

        // Old pay_fee logic removed for Razorpay integration
    }

    // COMPLAINTS
    $complaintsStmt = $pdo->prepare(
        'SELECT * FROM complaints WHERE student_id = :student_id ORDER BY created_at DESC'
    );
    $complaintsStmt->execute([':student_id' => $studentId]);
    $complaints = $complaintsStmt->fetchAll();

    // ✅ FEE FETCH (NEW)
    $feeStmt = $pdo->prepare("SELECT * FROM student_fees WHERE student_id = ? ORDER BY id DESC");
    $feeStmt->execute([$studentId]);
    $fees = $feeStmt->fetchAll();

} catch (Throwable $exception) {
    $errorMessage = $exception->getMessage();
    $complaints = $complaints ?? [];
    $fees = $fees ?? [];
    $myRoom = $myRoom ?? null;
}
?>

<?php renderPageHeader('Student Dashboard | Hostel Management', 'home'); ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<div class="role-shell student-dashboard">

<?php if ($successMessage !== ''): ?>
    <div class="container mt-3"><div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div></div>
<?php endif; ?>
<?php if ($errorMessage !== ''): ?>
    <div class="container mt-3"><div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div></div>
<?php endif; ?>

<section class="hero-shell p-4 p-lg-5 mb-4">
    <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
</section>
<div class="d-flex flex-wrap gap-2">
    <a class="btn btn-success px-4" href="<?php echo htmlspecialchars(appUrl('auth/logout.php')); ?>">
        Logout
    </a>
    <a class="btn btn-outline-dark px-4" href="<?php echo htmlspecialchars(appUrl()); ?>">
        Back to Home
    </a>
</div>

<section class="soft-panel p-4 mb-4">
    <h4>My Room</h4>

    <button class="btn btn-primary me-2" onclick="checkOut()">Check-Out</button>
    <button class="btn btn-success" onclick="checkIn()">Check-In</button>

    <?php if ($myRoom): ?>
        <p><b>Room:</b> <?php echo $myRoom['block_name'].'-'.$myRoom['room_number']; ?></p>
        <p><b>Type:</b> <?php echo $myRoom['room_type']; ?></p>
        <p><b>Fee:</b> ₹<?php echo $myRoom['monthly_fee']; ?></p>
    <?php else: ?>
        <p>No room assigned</p>
    <?php endif; ?>
</section>

<!-- ✅ NEW SECTION (NO DESIGN CHANGE) -->
<section class="soft-panel p-4 mb-4">
    <h4 class="mb-3">💰 Fee Details</h4>

    <?php if (!empty($fees)): ?>
        <?php foreach ($fees as $fee): ?>
            <div class="border rounded p-3 mb-2">

                <strong>Amount:</strong> ₹<?php echo $fee['amount']; ?><br>
                <strong>Due Date:</strong> <?php echo $fee['due_date']; ?><br>
                <strong>Status:</strong>
                <span class="badge <?php echo $fee['status']=='PAID'?'bg-success':'bg-danger'; ?>">
                    <?php echo $fee['status']; ?>
                </span><br>

                <?php if ($fee['status'] == 'PENDING'): ?>
                    <button type="button" class="btn btn-primary btn-sm mt-2" onclick="initiateFeePayment(<?php echo $fee['id']; ?>, <?php echo $fee['amount']; ?>)">
                        Pay Now
                    </button>
                <?php endif; ?>

                <?php if ($fee['fine'] > 0): ?>
                    <div class="text-danger mt-2">
                        Fine: ₹<?php echo $fee['fine']; ?>
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No fee assigned</p>
    <?php endif; ?>
</section>

<section class="soft-panel p-4 mb-4">
    <h4>Add Complaint</h4>
    <form method="post">
        <input type="hidden" name="action" value="create_complaint">
        <input name="title" class="form-control mb-2" placeholder="Title" required>
        <textarea name="description" class="form-control mb-2" placeholder="Description" required></textarea>
        <button class="btn btn-success">Submit</button>
    </form>
</section>

<section class="soft-panel p-4 mb-4">
    <h4>My Complaints</h4>
    <?php foreach ($complaints as $c): ?>
        <div><?php echo $c['title']; ?> (<?php echo $c['status']; ?>)</div>
    <?php endforeach; ?>
</section>

</div>

<?php require_once __DIR__ . '/../includes/fake_razorpay.php'; ?>
<script>
function initiateFeePayment(feeId, amount) {
    openFakeRazorpay(amount, function() {
        alert("Payment Successful (Demo)!");
        location.reload();
    });
}

const studentId = <?= $studentId ?>;

function sendLocation(status){
    navigator.geolocation.getCurrentPosition(pos=>{
        fetch('../modules/location.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`lat=${pos.coords.latitude}&lng=${pos.coords.longitude}&status=${status}&student_id=${studentId}`
        });
    });
}

function checkOut(){ sendLocation('OUT'); }
function checkIn(){ sendLocation('IN'); }

setInterval(()=>sendLocation('LIVE'),10000);
</script>

<?php renderPageFooter(); ?>