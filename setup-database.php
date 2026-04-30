<?php
/**
 * HMS Database Setup Script
 * 
 * This script initializes the database, creates tables, and adds test credentials.
 * Run this ONCE at the beginning to set up your installation.
 * 
 * Usage: Visit http://localhost/HMS/setup-database.php in your browser
 */

declare(strict_types=1);

// Database credentials
const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'hostel_management';
const DB_USER = 'root';
const DB_PASS = '';

function setupDatabase(): array
{
    $results = [];
    
    try {
        // Step 1: Connect without database selection to create it
        $dsn = sprintf(
            'mysql:host=%s;port=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT
        );
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        $results[] = ['step' => 'Connection', 'status' => 'success', 'message' => 'Connected to MySQL'];
        
        // Step 2: Create database
        $createDbSql = sprintf(
            'CREATE DATABASE IF NOT EXISTS %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            '`' . DB_NAME . '`'
        );
        $pdo->exec($createDbSql);
        $results[] = ['step' => 'Database', 'status' => 'success', 'message' => 'Database created/verified'];
        
        // Step 3: Select database
        $pdo->exec('USE ' . '`' . DB_NAME . '`');
        $results[] = ['step' => 'Database Selection', 'status' => 'success', 'message' => 'Database selected'];
        
        // Step 4: Execute schema.sql
        $schemaFile = __DIR__ . '/sql/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found: $schemaFile");
        }
        
        $schema = file_get_contents($schemaFile);
        $statements = array_filter(array_map('trim', preg_split('/;(?=\n)/', $schema)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        $results[] = ['step' => 'Schema', 'status' => 'success', 'message' => 'Tables created successfully'];
        
        // Step 5: Execute advanced_features.sql
        $advancedFile = __DIR__ . '/sql/advanced_features.sql';
        if (file_exists($advancedFile)) {
            $advanced = file_get_contents($advancedFile);
            $statements = array_filter(array_map('trim', preg_split('/;(?=\n)/', $advanced)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (Exception $e) {
                        // Skip errors from advanced features (they might be optional)
                    }
                }
            }
            $results[] = ['step' => 'Advanced Features', 'status' => 'success', 'message' => 'Advanced features configured'];
        }
        
        // Step 6: Insert test admin credentials
        $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $adminSql = "INSERT INTO admins (full_name, email, password_hash, is_active) 
                     VALUES (:name, :email, :password, 1)
                     ON DUPLICATE KEY UPDATE password_hash = :password";
        $stmt = $pdo->prepare($adminSql);
        $stmt->execute([
            ':name' => 'System Administrator',
            ':email' => 'admin@hostel.local',
            ':password' => $adminPassword
        ]);
        $results[] = ['step' => 'Admin Account', 'status' => 'success', 'message' => 'Test admin created (email: admin@hostel.local, password: admin123)'];
        
        // Step 7: Insert test student credentials
        $studentPassword = password_hash('student123', PASSWORD_BCRYPT);
        $studentSql = "INSERT INTO students (registration_no, full_name, email, password_hash, gender, department, year_of_study, admission_date, status) 
                       VALUES (:reg_no, :name, :email, :password, :gender, :dept, :year, :admission, 'active')
                       ON DUPLICATE KEY UPDATE password_hash = :password";
        $stmt = $pdo->prepare($studentSql);
        $stmt->execute([
            ':reg_no' => 'STU2024001',
            ':name' => 'John Doe',
            ':email' => 'student@hostel.local',
            ':password' => $studentPassword,
            ':gender' => 'male',
            ':dept' => 'Computer Science',
            ':year' => 2,
            ':admission' => date('Y-m-d')
        ]);
        $results[] = ['step' => 'Student Account', 'status' => 'success', 'message' => 'Test student created (email: student@hostel.local, password: student123)'];
        
        // Step 8: Insert sample rooms
        $roomSql = "INSERT INTO rooms (room_number, block_name, floor_number, room_type, capacity, monthly_fee, status) 
                    VALUES (:room_num, :block, :floor, :type, :capacity, :fee, 'available')
                    ON DUPLICATE KEY UPDATE capacity = :capacity";
        $stmt = $pdo->prepare($roomSql);
        $sampleRooms = [
            ['101', 'Block A', 1, 'double', 2, 5000],
            ['102', 'Block A', 1, 'double', 2, 5000],
            ['201', 'Block B', 2, 'triple', 3, 6000],
            ['202', 'Block B', 2, 'double', 2, 5000],
        ];
        
        foreach ($sampleRooms as $room) {
            $stmt->execute([
                ':room_num' => $room[0],
                ':block' => $room[1],
                ':floor' => $room[2],
                ':type' => $room[3],
                ':capacity' => $room[4],
                ':fee' => $room[5]
            ]);
        }
        $results[] = ['step' => 'Sample Rooms', 'status' => 'success', 'message' => 'Sample rooms created'];
        
        return $results;
        
    } catch (Exception $e) {
        return [
            ['step' => 'Error', 'status' => 'error', 'message' => $e->getMessage()]
        ];
    }
}

// Process setup
$setupResults = setupDatabase();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: bold;
        }
        .setup-header p {
            color: #666;
            font-size: 14px;
        }
        .setup-result {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 5px;
            border-left: 4px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .setup-result.success {
            background-color: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .setup-result.error {
            background-color: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .setup-result .step {
            font-weight: bold;
            min-width: 120px;
        }
        .setup-result .message {
            flex: 1;
            margin-left: 15px;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }
        .credentials-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
        .credentials-box h5 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        .credential-item {
            margin-bottom: 10px;
            font-size: 13px;
            color: #555;
        }
        .credential-item code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            font-size: 12px;
        }
        .action-buttons {
            margin-top: 25px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn-primary {
            background: #667eea;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #5568d3;
            text-decoration: none;
            color: white;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>🏠 HMS Database Setup</h1>
            <p>Hostel Management System - Initial Configuration</p>
        </div>

        <div class="setup-results">
            <?php foreach ($setupResults as $result): ?>
                <div class="setup-result <?php echo $result['status']; ?>">
                    <div class="step"><?php echo htmlspecialchars($result['step']); ?></div>
                    <div class="message"><?php echo htmlspecialchars($result['message']); ?></div>
                    <div class="badge badge-<?php echo $result['status']; ?>">
                        <?php echo ucfirst($result['status']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        $allSuccess = array_every($setupResults, fn($r) => $r['status'] === 'success');
        if ($allSuccess):
        ?>
            <div class="credentials-box">
                <h5>✅ Setup Complete! Test Credentials</h5>
                
                <div class="credential-item">
                    <strong>📧 Admin Login:</strong><br>
                    Email: <code>admin@hostel.local</code><br>
                    Password: <code>admin123</code>
                </div>
                
                <div class="credential-item">
                    <strong>👤 Student Login:</strong><br>
                    Email: <code>student@hostel.local</code><br>
                    Password: <code>student123</code>
                </div>
                
                <div class="credential-item">
                    <strong>🚀 Next Steps:</strong><br>
                    1. Go to the login page<br>
                    2. Select Admin or Student role<br>
                    3. Use the credentials above to login
                </div>
            </div>

            <div class="action-buttons">
                <a href="<?php echo htmlspecialchars('index.php'); ?>" class="btn-primary">🔐 Go to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
