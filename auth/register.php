<?php

declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$errors = [];
$successMessage = '';
$formData = [
    'registration_no' => '',
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'gender' => 'male',
    'department' => '',
    'year_of_study' => '',
    'date_of_birth' => '',
    'admission_date' => date('Y-m-d'),
    'guardian_name' => '',
    'guardian_phone' => '',
    'address' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $key => $value) {
        $formData[$key] = sanitizeString((string) ($_POST[$key] ?? ''));
    }

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($formData['registration_no'] === '' || strlen($formData['registration_no']) < 4) {
        $errors[] = 'Registration number must be at least 4 characters.';
    }

    if ($formData['full_name'] === '' || strlen($formData['full_name']) < 3) {
        $errors[] = 'Full name is required.';
    }

    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (!in_array($formData['gender'], ['male', 'female', 'other'], true)) {
        $errors[] = 'Please select a valid gender.';
    }

    if ($formData['department'] === '') {
        $errors[] = 'Department is required.';
    }

    if (!ctype_digit($formData['year_of_study']) || (int) $formData['year_of_study'] < 1 || (int) $formData['year_of_study'] > 8) {
        $errors[] = 'Year of study must be a number between 1 and 8.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if ($formData['admission_date'] === '') {
        $errors[] = 'Admission date is required.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDatabaseConnection();

            $checkStmt = $pdo->prepare(
                'SELECT id FROM students WHERE email = :email OR registration_no = :registration_no LIMIT 1'
            );
            $checkStmt->execute([
                ':email' => $formData['email'],
                ':registration_no' => $formData['registration_no']
            ]);

            if ($checkStmt->fetch()) {
                $errors[] = 'Student already exists with this email or registration number.';
            } else {
                $insertStmt = $pdo->prepare(
                    'INSERT INTO students (
                        registration_no, full_name, email, phone, password_hash, gender,
                        department, year_of_study, date_of_birth, admission_date,
                        guardian_name, guardian_phone, address
                    ) VALUES (
                        :registration_no, :full_name, :email, :phone, :password_hash, :gender,
                        :department, :year_of_study, :date_of_birth, :admission_date,
                        :guardian_name, :guardian_phone, :address
                    )'
                );

                $insertStmt->execute([
                    ':registration_no' => $formData['registration_no'],
                    ':full_name' => $formData['full_name'],
                    ':email' => $formData['email'],
                    ':phone' => $formData['phone'] !== '' ? $formData['phone'] : null,
                    ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    ':gender' => $formData['gender'],
                    ':department' => $formData['department'],
                    ':year_of_study' => (int) $formData['year_of_study'],
                    ':date_of_birth' => $formData['date_of_birth'] !== '' ? $formData['date_of_birth'] : null,
                    ':admission_date' => $formData['admission_date'],
                    ':guardian_name' => $formData['guardian_name'] !== '' ? $formData['guardian_name'] : null,
                    ':guardian_phone' => $formData['guardian_phone'] !== '' ? $formData['guardian_phone'] : null,
                    ':address' => $formData['address'] !== '' ? $formData['address'] : null
                ]);

                $successMessage = 'Registration completed. You can login now.';
                foreach ($formData as $key => $value) {
                    $formData[$key] = '';
                }
                $formData['gender'] = 'male';
                $formData['admission_date'] = date('Y-m-d');
            }
        } catch (Throwable $exception) {
            error_log('Registration error: ' . $exception->getMessage());
            $errors[] = 'Unable to register right now. Please try again later.';
        }
    }
}
?>
<?php renderPageHeader('Student Registration | Hostel Management', 'register'); ?>

<div class="form-wrap">
    <div class="auth-card">
        <div class="card-body p-4 p-lg-5">
            <h3 class="mb-2">Student Registration</h3>
            <p class="muted-text mb-4">Create your hostel account to start room and service requests.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($successMessage !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Registration Number</label>
                        <input type="text" name="registration_no" class="form-control" value="<?php echo htmlspecialchars($formData['registration_no']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($formData['full_name']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($formData['phone']); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="male" <?php echo $formData['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $formData['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo $formData['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($formData['department']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Year of Study</label>
                        <input type="number" min="1" max="8" name="year_of_study" class="form-control" value="<?php echo htmlspecialchars($formData['year_of_study']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($formData['date_of_birth']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Admission Date</label>
                        <input type="date" name="admission_date" class="form-control" value="<?php echo htmlspecialchars($formData['admission_date']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="<?php echo htmlspecialchars($formData['guardian_name']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guardian Phone</label>
                        <input type="text" name="guardian_phone" class="form-control" value="<?php echo htmlspecialchars($formData['guardian_phone']); ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($formData['address']); ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">Create Student Account</button>
                    <a href="<?php echo htmlspecialchars(appUrl('auth/login.php')); ?>" class="btn btn-outline-secondary">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php renderPageFooter(); ?>
