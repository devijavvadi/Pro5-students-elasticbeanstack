<?php
// Include the database connection layer
require_once 'db.php';

$message = '';
$message_type = '';

// 1. HANDLE FORM SUBMISSION (CREATE STUDENT)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_id = trim($_POST['student_id'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $course     = trim($_POST['course'] ?? '');

    // Basic Input Validation
    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($email) || empty($course)) {
        $message = "All field inputs are required.";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please provide a valid email address.";
        $message_type = "danger";
    } else {
        try {
            // Using prepared statements to block SQL Injection attacks entirely
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
            
            $message = "Student record registered successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            // Error handling for duplicate key constraints (e.g., Student ID or Email already exists)
            if ($e->getCode() == 23000) { 
                $message = "Registration failed: Student ID or Email already exists in the system.";
            } else {
                $message = "An error occurred while saving the record to the database.";
            }
            $message_type = "danger";
        }
    }
}

// 2. FETCH CURRENT RECORDS (READ STUDENTS)
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
    <!-- Responsive CSS Layout Framework -->
    <link href="https://jsdelivr.net" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">🏫 Student Information System</h2>

        <!-- Dynamically Injected Notification Alerts -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-with="alert" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Grid Column: Interactive Enrollment Form -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white font-weight-bold">Register New Student</div>
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

            <!-- Right Grid Column: Live Student Roster Output -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white font-weight-bold">Current Student Roster</div>
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
    <!-- Bootstrap JavaScript Bundle for closeable alert banners -->
    <script src="https://jsdelivr.net"></script>
</body>
</html>
