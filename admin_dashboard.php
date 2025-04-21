<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: admin_login.php");
    exit();
}


$conn = new mysqli("localhost", "root", "", "attendance_db");

// Count students
$students_count = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];

// Count subjects
$subjects_count = $conn->query("SELECT COUNT(*) AS total FROM subjects")->fetch_assoc()['total'];

// Count attendance records
$attendance_count = $conn->query("SELECT COUNT(*) AS total FROM attendance")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2 class="mb-4">Admin Dashboard</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h4>Total Students</h4>
                <p><?php echo $students_count; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h4>Total Subjects</h4>
                <p><?php echo $subjects_count; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h4>Total Attendance Records</h4>
                <p><?php echo $attendance_count; ?></p>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <a href="manage_students.php" class="btn btn-primary">Manage Students</a>
        <a href="manage_subjects.php" class="btn btn-secondary">Manage Subjects</a>
        <a href="view_attendance.php" class="btn btn-success">View Attendance</a>
        <a href="scan_qr.php" class="btn btn-warning">Scan QR</a>
        <a href="bulk_upload.php" class="btn btn-dark">Bulk Upload</a>
        <a href="mark_attendance_manually.php" class="btn btn-info">Manual Attendance</a>
        <a href="alogout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>