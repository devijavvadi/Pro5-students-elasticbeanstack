<?php
// Include the secure database connection layer
require_once 'db.php';

$message = '';
$message_type = '';

// ==============================================================================
// 🤖 AUTOMATED AUTO-MIGRATION LAYER
// Checks if the table exists on page load. If not, it builds it and seeds baseline records.
// ==============================================================================
try {
    // 1. Verify if the students table exists by checking system schemas
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'students'")->rowCount();
    
    if ($tableCheck === 0) {
        // 2. The table was not found—execute the structural blueprint to build it
        $createTableSql = "CREATE TABLE students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(50) NOT NULL UNIQUE,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            course VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($createTableSql);

        // 3. Seed initial baseline records so the system dashboard roster isn't blank
        $seedSql = "INSERT INTO students (student_id, first_name, last_name, email, course) VALUES 
            ('STU-1001', 'Jane', 'Doe', 'jane.doe@university.edu', 'Computer Science'),
            ('STU-1002', 'John', 'Smith', 'john.smith@university.edu', 'Data Analytics')
            ON DUPLICATE KEY UPDATE student_id=student_id;";
            
        $pdo->exec($seedSql);
    }
} catch (PDOException $e) {
    // Quietly records errors behind the scenes without breaking application workflows
    error_log("Auto-migration system notice: " . $e->getMessage());
}

// ==============================================================================
// 📥 HANDLE FORM SUBMISSIONS (CREATE STUDENT)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_id = trim($_POST['student_id'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $course     = trim($_POST['course'] ?? '');

    // Server-Side Field Validation Checks
    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($email) || empty($course)) {
        $message = "All form field inputs are strictly required.";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please provide a structurally valid email address.";
        $message_type = "danger";
    } else {
        try {
            // Uses secure bound parameters to systematically block SQL Injection vectors
            $sql = "INSERT INTO students (student_id, first_name, last_name, email, course) 
                    VALUES (:student_id, :first_name, :last_name, :email, :course)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':student_id' => $student_id,
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':email'      => $email,
                ':course'     => $course
            ]);
            
            $message = "Student record registered successfully! Succesfully";
            $message_type = "success";
        } catch (PDOException $e) {
            // Evaluates structural constraint codes to identify registration duplicate collisions
            if ($e->getCode() == 23000) { 
                $message = "Registration failed: Student ID reference or Email already exists.";
            } else {
                $message = "A backend error occurred while saving the record to the database.";
            }
            $message_type = "danger";
        }
    }
}

// ==============================================================================
// 📊 FETCH CURRENT RECORDS (READ ROSTER)
// ==============================================================================
try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
    $message = "System warning: Unable to pull current records from the remote database.";
    $message_type = "warning";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information System</title>
    <!-- Stable Bootstrap 5 CSS CDN reference configuration link -->
    <link href="https://jsdelivr.net" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">🏫 Student Information System</h2>

        <!-- Dynamic Dismissible Framework Alert Notification Banner -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Layout Grid Column: Interactive Form Panel -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">Register New Student</div>
                    <div class="card-body">
                        <form action="index.php" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label">Student ID Reference</label>
                                <input type="text" name="student_id" class="form-control" placeholder="e.g., STU-1002" required pattern="[A-Za-z0-9-]+">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" placeholder="Jane" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" placeholder="Doe" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="jane.doe@university.edu" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Target Program / Course</label>
                                <input type="text" name="course" class="form-control" placeholder="Computer Science" required>
                            </div>
                            <button type="submit" name="add_student" class="btn btn-primary w-100 mt-2">Submit Enrollment</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Layout Grid Column: Real-Time Table Output Workspace -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white fw-bold">Current Student Roster</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 align-middle">
                                <thead class="table-secondary">
                                    <tr>
                                        <th class="ps-3">Student ID</th>
                                        <th>Full Name</th>
                                        <th>Email Address</th>
                                        <th>Course Program</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($students)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">No student records found in the database layer.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td class="fw-bold ps-3"><?= htmlspecialchars($student['student_id']); ?></td>
                                                <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                <td><?= htmlspecialchars($student['email']); ?></td>
                                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($student['course']); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
