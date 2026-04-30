<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';

requireRole('admin');

$user = currentUser();
$successMessage = '';
$errorMessage = '';

try {
    $pdo = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = sanitizeString((string) ($_POST['action'] ?? ''));

        if ($action === 'create_room') {
            $roomNumber = sanitizeString((string) ($_POST['room_number'] ?? ''));
            $blockName = sanitizeString((string) ($_POST['block_name'] ?? ''));
            $floorNumber = (int) ($_POST['floor_number'] ?? 1);
            $roomType = sanitizeString((string) ($_POST['room_type'] ?? 'double'));
            $capacity = (int) ($_POST['capacity'] ?? 0);
            $monthlyFee = (float) ($_POST['monthly_fee'] ?? 0);
            $status = sanitizeString((string) ($_POST['room_status'] ?? 'available'));

            $allowedRoomTypes = ['single', 'double', 'triple', 'dormitory'];
            $allowedStatuses = ['available', 'full', 'maintenance'];

            if (
                $roomNumber === ''
                || $blockName === ''
                || $capacity <= 0
                || $monthlyFee <= 0
                || !in_array($roomType, $allowedRoomTypes, true)
                || !in_array($status, $allowedStatuses, true)
            ) {
                throw new RuntimeException('Room number, block, valid type, capacity, fee, and status are required.');
            }

            $createRoomStmt = $pdo->prepare(
                'INSERT INTO rooms (room_number, block_name, floor_number, room_type, capacity, occupied_beds, monthly_fee, status)
                 VALUES (:room_number, :block_name, :floor_number, :room_type, :capacity, 0, :monthly_fee, :status)'
            );
            $createRoomStmt->execute([
                ':room_number' => $roomNumber,
                ':block_name' => $blockName,
                ':floor_number' => $floorNumber,
                ':room_type' => $roomType,
                ':capacity' => $capacity,
                ':monthly_fee' => $monthlyFee,
                ':status' => $status
            ]);

            $successMessage = 'Room added successfully.';
        }

     if ($action === 'allocate_room') {

    $studentId = (int) ($_POST['student_id'] ?? 0);
    $roomId = (int) ($_POST['room_id'] ?? 0);
    $allocationDate = sanitizeString((string) ($_POST['allocation_date'] ?? date('Y-m-d')));

    if ($studentId <= 0 || $roomId <= 0) {
        throw new RuntimeException('Invalid student or room.');
    }

    $pdo->beginTransaction();

    // check existing allocation
    $check = $pdo->prepare("SELECT id FROM allocations WHERE student_id = ? AND status='active'");
    $check->execute([$studentId]);

    if ($check->fetch()) {
        throw new RuntimeException('Student already has room.');
    }

    // get room
    $roomStmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ? FOR UPDATE");
    $roomStmt->execute([$roomId]);
    $room = $roomStmt->fetch();

    if (!$room) throw new RuntimeException('Room not found');
    if ($room['occupied_beds'] >= $room['capacity']) throw new RuntimeException('Room full');

    // insert allocation
    $insert = $pdo->prepare("
        INSERT INTO allocations (student_id, room_id, allocated_by_admin_id, allocation_date, status)
        VALUES (?, ?, ?, ?, 'active')
    ");

    $insert->execute([
        $studentId,
        $roomId,
        $user['id'],
        $allocationDate
    ]);

    $allocationId = $pdo->lastInsertId();

    // ✅ CREATE FEE (MAIN FIX)
    $dueDate = date('Y-m-d', strtotime('+7 days'));

    $fee = $pdo->prepare("
        INSERT INTO student_fees (student_id, amount, due_date, status)
        VALUES (?, ?, ?, 'PENDING')
    ");

    $fee->execute([
        $studentId,
        $room['monthly_fee'],
        $dueDate
    ]);

    // update room
    $beds = $room['occupied_beds'] + 1;
    $status = ($beds >= $room['capacity']) ? 'full' : 'available';

    $pdo->prepare("UPDATE rooms SET occupied_beds=?, status=? WHERE id=?")
        ->execute([$beds, $status, $roomId]);

    $pdo->commit();

    $successMessage = "Room allocated + fee created";
}

        if ($action === 'create_fee') {
            $studentId = (int) ($_POST['fee_student_id'] ?? 0);
            $allocationIdRaw = (int) ($_POST['fee_allocation_id'] ?? 0);
            $allocationId = $allocationIdRaw > 0 ? $allocationIdRaw : null;
            $amountDue = (float) ($_POST['amount_due'] ?? 0);
            $dueDate = sanitizeString((string) ($_POST['due_date'] ?? ''));

            if ($studentId <= 0 || $amountDue <= 0 || $dueDate === '') {
                throw new RuntimeException('Fee creation requires student, amount due, and due date.');
            }

            $createFeeStmt = $pdo->prepare(
                'INSERT INTO fees (student_id, allocation_id, amount_due, amount_paid, due_date, status)
                 VALUES (:student_id, :allocation_id, :amount_due, 0, :due_date, "unpaid")'
            );
            $createFeeStmt->execute([
                ':student_id' => $studentId,
                ':allocation_id' => $allocationId,
                ':amount_due' => $amountDue,
                ':due_date' => $dueDate
            ]);

            $successMessage = 'Rent/fee entry created successfully.';
        }

        if ($action === 'update_fee') {
            $feeId = (int) ($_POST['fee_id'] ?? 0);
            $amountPaid = (float) ($_POST['amount_paid'] ?? 0);
            $status = sanitizeString((string) ($_POST['status'] ?? 'unpaid'));
            $paymentMethod = sanitizeString((string) ($_POST['payment_method'] ?? ''));
            $transactionRef = sanitizeString((string) ($_POST['transaction_ref'] ?? ''));
            $notes = sanitizeString((string) ($_POST['notes'] ?? ''));

            $allowedFeeStatuses = ['unpaid', 'partial', 'paid', 'overdue'];
            $allowedMethods = ['cash', 'card', 'upi', 'bank_transfer', 'other', ''];

            if ($feeId <= 0 || !in_array($status, $allowedFeeStatuses, true) || !in_array($paymentMethod, $allowedMethods, true)) {
                throw new RuntimeException('Invalid fee update request.');
            }

            $feeStmt = $pdo->prepare('SELECT amount_due FROM fees WHERE id = :id LIMIT 1');
            $feeStmt->execute([':id' => $feeId]);
            $fee = $feeStmt->fetch();

            if (!$fee) {
                throw new RuntimeException('Fee record not found.');
            }

            if ($amountPaid < 0 || $amountPaid > (float) $fee['amount_due']) {
                throw new RuntimeException('Paid amount must be between 0 and amount due.');
            }

            $paidDate = $amountPaid > 0 ? date('Y-m-d') : null;

            $updateFeeStmt = $pdo->prepare(
                'UPDATE fees
                 SET amount_paid = :amount_paid,
                     status = :status,
                     paid_date = :paid_date,
                     payment_method = :payment_method,
                     transaction_ref = :transaction_ref,
                     notes = :notes
                 WHERE id = :id'
            );
            $updateFeeStmt->execute([
                ':amount_paid' => $amountPaid,
                ':status' => $status,
                ':paid_date' => $paidDate,
                ':payment_method' => $paymentMethod !== '' ? $paymentMethod : null,
                ':transaction_ref' => $transactionRef !== '' ? $transactionRef : null,
                ':notes' => $notes !== '' ? $notes : null,
                ':id' => $feeId
            ]);

            $successMessage = 'Rent/fee record updated successfully.';
        }

        if ($action === 'update_complaint') {
            $complaintId = (int) ($_POST['complaint_id'] ?? 0);
            $status = sanitizeString((string) ($_POST['complaint_status'] ?? 'open'));
            $resolutionNotes = sanitizeString((string) ($_POST['resolution_notes'] ?? ''));

            $allowedComplaintStatuses = ['open', 'in_progress', 'resolved', 'closed'];

            if ($complaintId <= 0 || !in_array($status, $allowedComplaintStatuses, true)) {
                throw new RuntimeException('Invalid complaint update request.');
            }

            $resolvedAt = in_array($status, ['resolved', 'closed'], true) ? date('Y-m-d H:i:s') : null;

            $complaintStmt = $pdo->prepare(
                'UPDATE complaints
                 SET status = :status,
                     resolution_notes = :resolution_notes,
                     resolved_at = :resolved_at
                 WHERE id = :id'
            );
            $complaintStmt->execute([
                ':status' => $status,
                ':resolution_notes' => $resolutionNotes !== '' ? $resolutionNotes : null,
                ':resolved_at' => $resolvedAt,
                ':id' => $complaintId
            ]);

            $successMessage = 'Complaint updated successfully.';
        }
    }

    $students = $pdo->query(
        'SELECT id, registration_no, full_name, status
         FROM students
         ORDER BY full_name ASC'
    )->fetchAll();

    $availableRooms = $pdo->query(
        'SELECT id, room_number, block_name, capacity, occupied_beds, monthly_fee, status
         FROM rooms
         WHERE status != "maintenance" AND occupied_beds < capacity
         ORDER BY block_name ASC, room_number ASC'
    )->fetchAll();

    $allRooms = $pdo->query(
        'SELECT id, room_number, block_name, floor_number, room_type, capacity, occupied_beds, monthly_fee, status
         FROM rooms
         ORDER BY block_name ASC, room_number ASC'
    )->fetchAll();

    $activeAllocations = $pdo->query(
        'SELECT a.id, a.student_id, a.room_id, s.full_name AS student_name,
                r.block_name, r.room_number, a.allocation_date, a.status
         FROM allocations a
         INNER JOIN students s ON s.id = a.student_id
         INNER JOIN rooms r ON r.id = a.room_id
         WHERE a.status = "active"
         ORDER BY a.created_at DESC'
    )->fetchAll();

    $fees = $pdo->query(
        'SELECT f.id, f.student_id, f.amount AS amount_due, f.amount AS amount_paid, f.due_date, f.status, f.paid_date,
                f.payment_method, f.transaction_id, s.full_name AS student_name
         FROM student_fees f
         INNER JOIN students s ON s.id = f.student_id
         ORDER BY f.created_at DESC'
    )->fetchAll();

    $complaints = $pdo->query(
        'SELECT c.id, c.student_id, c.title, c.category, c.priority, c.status,
                c.description, c.resolution_notes, c.reported_at, c.resolved_at,
                s.full_name AS student_name
         FROM complaints c
         INNER JOIN students s ON s.id = c.student_id
         ORDER BY c.created_at DESC'
    )->fetchAll();

    $dailyBookings = $pdo->query(
        'SELECT id, name, phone, check_in, check_out, total_days, total_amount, payment_status, created_at, payment_method, transaction_id, paid_date
         FROM daily_bookings
         ORDER BY created_at DESC'
    )->fetchAll();

    $totalDailyEarnings = 0;
    foreach ($dailyBookings as $booking) {
        if ($booking['payment_status'] === 'PAID') {
            $totalDailyEarnings += (float)$booking['total_amount'];
        }
    }
} catch (Throwable $exception) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Admin dashboard error: ' . $exception->getMessage());
    $errorMessage = $exception->getMessage();
    if ($errorMessage === '') {
        $errorMessage = 'Unable to complete the admin action right now.';
    }

    $students = $students ?? [];
    $availableRooms = $availableRooms ?? [];
    $allRooms = $allRooms ?? [];
    $activeAllocations = $activeAllocations ?? [];
    $fees = $fees ?? [];
    $complaints = $complaints ?? [];
    $dailyBookings = $dailyBookings ?? [];
    $totalDailyEarnings = $totalDailyEarnings ?? 0;
}
?>
<?php renderPageHeader('Admin Dashboard | Hostel Management', 'home'); ?>

<div class="role-shell admin-dashboard">

<section class="hero-shell p-4 p-lg-5 mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-7">
            <p class="text-uppercase fw-semibold text-success mb-2">Admin Workspace</p>
            <h1 class="display-title mb-3">Welcome, <?php echo htmlspecialchars((string) ($user['name'] ?? 'Administrator')); ?></h1>
            <p class="lead mb-4">
                This is your operations control center for room allocation, rent management, and complete complaint
                tracking.
            </p>
            <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-success px-4" href="<?php echo htmlspecialchars(appUrl('auth/logout.php')); ?>">Logout</a>
    <a class="btn btn-outline-dark px-4" href="<?php echo htmlspecialchars(appUrl()); ?>">Back to Home</a>

    <!-- ✅ ADD THIS -->
    <a href="<?php echo htmlspecialchars(appUrl('admin/live_map.php')); ?>" target="_blank" class="btn btn-primary px-4">
        🗺️ Live Student Tracking
    </a>
</div>
        </div>
        <div class="col-lg-5">
            <div class="soft-panel p-4 h-100">
                <h5 class="mb-3">Admin Scope</h5>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="feature-chip">S</span>
                    <div>
                        <div class="fw-semibold">Student Management</div>
                        <small class="muted-text">Review registrations and manage hostel records.</small>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="feature-chip">R</span>
                    <div>
                        <div class="fw-semibold">Room Allocation</div>
                        <small class="muted-text">Assign rooms to students and track occupancy live.</small>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="feature-chip">F</span>
                    <div>
                        <div class="fw-semibold">Fees and Complaints</div>
                        <small class="muted-text">Manage rents and resolve all complaints quickly.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($successMessage !== ''): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>

<?php if ($errorMessage !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php endif; ?>

<section class="soft-panel p-4 mb-4">
    <h4 class="mb-3">Add Room</h4>
    <form method="post" class="row g-3">
        <input type="hidden" name="action" value="create_room">
        <div class="col-md-2">
            <label class="form-label">Room Number</label>
            <input type="text" name="room_number" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Block</label>
            <input type="text" name="block_name" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Floor</label>
            <input type="number" name="floor_number" class="form-control" min="0" value="1" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Type</label>
            <select name="room_type" class="form-select" required>
                <option value="single">Single</option>
                <option value="double" selected>Double</option>
                <option value="triple">Triple</option>
                <option value="dormitory">Dormitory</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Capacity</label>
            <input type="number" name="capacity" class="form-control" min="1" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Monthly Fee</label>
            <input type="number" name="monthly_fee" class="form-control" step="0.01" min="0" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="room_status" class="form-select" required>
                <option value="available" selected>Available</option>
                <option value="full">Full</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Add Room</button>
        </div>
    </form>

    <h4 class="mt-4 mb-3">All Rooms</h4>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Room</th>
                <th>Type</th>
                <th>Beds</th>
                <th>Monthly Fee</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($allRooms as $room): ?>
                <tr>
                    <td><?php echo (int) $room['id']; ?></td>
                    <td><?php echo htmlspecialchars($room['block_name'] . '-' . $room['room_number'] . ' (F' . $room['floor_number'] . ')'); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst((string) $room['room_type'])); ?></td>
                    <td><?php echo htmlspecialchars((string) $room['occupied_beds'] . '/' . (string) $room['capacity']); ?></td>
                    <td><?php echo htmlspecialchars((string) $room['monthly_fee']); ?></td>
                    <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars((string) $room['status']); ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($allRooms)): ?>
                <tr><td colspan="6" class="text-center text-secondary">No rooms added yet. Use the form above to add rooms.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="soft-panel p-4 mb-4">
    <h4 class="mb-3">Allocate Room to Student</h4>
    <form method="post" class="row g-3">
        <input type="hidden" name="action" value="allocate_room">
        <div class="col-md-4">
            <label class="form-label">Student</label>
            <select name="student_id" class="form-select" required>
                <option value="">Select student</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo (int) $student['id']; ?>">
                        <?php echo htmlspecialchars($student['full_name'] . ' (' . $student['registration_no'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Available Room</label>
            <select name="room_id" class="form-select" required>
                <option value="">Select room</option>
                <?php foreach ($availableRooms as $room): ?>
                    <option value="<?php echo (int) $room['id']; ?>">
                        <?php
                        echo htmlspecialchars(
                            $room['block_name'] . '-' . $room['room_number']
                            . ' | Beds: ' . $room['occupied_beds'] . '/' . $room['capacity']
                            . ' | Rent: ' . $room['monthly_fee']
                        );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Allocation Date</label>
            <input type="date" name="allocation_date" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d')); ?>" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-success">Allocate Room</button>
        </div>
    </form>
</section>

<section class="soft-panel p-4 mb-4">
    <h4 class="mb-3">Active Allocations</h4>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Room</th>
                <th>Allocation Date</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($activeAllocations as $allocation): ?>
                <tr>
                    <td><?php echo (int) $allocation['id']; ?></td>
                    <td><?php echo htmlspecialchars($allocation['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($allocation['block_name'] . '-' . $allocation['room_number']); ?></td>
                    <td><?php echo htmlspecialchars($allocation['allocation_date']); ?></td>
                    <td><span class="badge text-bg-success"><?php echo htmlspecialchars($allocation['status']); ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($activeAllocations)): ?>
                <tr><td colspan="5" class="text-center text-secondary">No active allocations yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="soft-panel p-4 mb-4">
    <h4 class="mb-3">Create Rent Entry</h4>
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="action" value="create_fee">
        <div class="col-md-3">
            <label class="form-label">Student</label>
            <select name="fee_student_id" class="form-select" required>
                <option value="">Select student</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo (int) $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Allocation (Optional)</label>
            <select name="fee_allocation_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($activeAllocations as $allocation): ?>
                    <option value="<?php echo (int) $allocation['id']; ?>">
                        <?php echo htmlspecialchars('#' . $allocation['id'] . ' - ' . $allocation['student_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Amount Due</label>
            <input type="number" name="amount_due" class="form-control" step="0.01" min="0" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Due Date</label>
            <input type="date" name="due_date" class="form-control" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Create</button>
        </div>
    </form>

    <h4 class="mb-3">Manage Rents</h4>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Payment Method</th>
                <th>Transaction ID</th>
                <th>Paid Date</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fees as $fee): ?>
                <tr>
                    <td><?php echo (int) $fee['id']; ?></td>
                    <td><?php echo htmlspecialchars($fee['student_name']); ?></td>
                    <td>₹<?php echo htmlspecialchars((string) $fee['amount_due']); ?></td>
                    <td><?php echo htmlspecialchars($fee['due_date']); ?></td>
                    <td><?php echo htmlspecialchars($fee['payment_method'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($fee['transaction_id'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($fee['paid_date'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($fee['status'] === 'PAID'): ?>
                            <span class="badge text-bg-success">PAID</span>
                        <?php else: ?>
                            <span class="badge text-bg-danger">PENDING</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($fees)): ?>
                <tr><td colspan="8" class="text-center text-secondary">No fee records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="soft-panel p-4 mb-4">
    <h4 class="mb-3">Manage Complaints</h4>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Title</th>
                <th>Priority</th>
                <th>Status Update</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($complaints as $complaint): ?>
                <tr>
                    <td><?php echo (int) $complaint['id']; ?></td>
                    <td><?php echo htmlspecialchars($complaint['student_name']); ?></td>
                    <td>
                        <div class="fw-semibold"><?php echo htmlspecialchars($complaint['title']); ?></div>
                        <small class="text-secondary"><?php echo htmlspecialchars((string) $complaint['description']); ?></small>
                    </td>
                    <td><span class="badge text-bg-warning"><?php echo htmlspecialchars($complaint['priority']); ?></span></td>
                    <td>
                        <form method="post" class="row g-2">
                            <input type="hidden" name="action" value="update_complaint">
                            <input type="hidden" name="complaint_id" value="<?php echo (int) $complaint['id']; ?>">
                            <div class="col-md-3">
                                <select name="complaint_status" class="form-select form-select-sm" required>
                                    <?php foreach (['open', 'in_progress', 'resolved', 'closed'] as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo $complaint['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $status)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="resolution_notes" class="form-control form-control-sm" placeholder="Resolution notes" value="<?php echo htmlspecialchars((string) ($complaint['resolution_notes'] ?? '')); ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-sm btn-success w-100">Save</button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($complaints)): ?>
                <tr><td colspan="5" class="text-center text-secondary">No complaints found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="soft-panel p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Daily Bookings</h4>
        <div class="badge bg-success fs-6 px-3 py-2">
            Total Earnings: ₹<?php echo number_format($totalDailyEarnings, 2); ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Guest Details</th>
                <th>Dates</th>
                <th>Days</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Transaction ID</th>
                <th>Paid Date</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dailyBookings as $booking): ?>
                <tr>
                    <td><?php echo (int) $booking['id']; ?></td>
                    <td>
                        <div class="fw-semibold"><?php echo htmlspecialchars($booking['name']); ?></div>
                        <small class="text-secondary"><?php echo htmlspecialchars($booking['phone']); ?></small>
                    </td>
                    <td>
                        <small class="d-block">In: <?php echo htmlspecialchars($booking['check_in']); ?></small>
                        <small class="d-block">Out: <?php echo htmlspecialchars($booking['check_out']); ?></small>
                    </td>
                    <td><?php echo (int) $booking['total_days']; ?></td>
                    <td>₹<?php echo number_format((float)$booking['total_amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($booking['payment_method'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($booking['transaction_id'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($booking['paid_date'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($booking['payment_status'] === 'PAID'): ?>
                            <span class="badge text-bg-success">PAID</span>
                        <?php else: ?>
                            <span class="badge text-bg-danger">PENDING</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($dailyBookings)): ?>
                <tr><td colspan="9" class="text-center text-secondary">No daily bookings found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

 </div>

<?php renderPageFooter(); ?>