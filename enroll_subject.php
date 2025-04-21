<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

if (!isset($_SESSION["student_id"])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION["student_id"];
$subject_id = isset($_GET["subject_id"]) ? intval($_GET["subject_id"]) : 0;

if ($subject_id > 0) {
    // Check if already enrolled
    $check = $conn->query("SELECT * FROM student_subjects WHERE student_id = $student_id AND subject_id = $subject_id");
    if ($check->num_rows === 0) {
        // Enroll
        $conn->query("INSERT INTO student_subjects (student_id, subject_id) VALUES ($student_id, $subject_id)");
    }
}

header("Location: student_dashboard.php");
exit();
