<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");

if (isset($_GET['student_id']) && isset($_GET['subject_id'])) {
    $student_id = $_GET['student_id'];
    $subject_id = $_GET['subject_id'];

    $conn->query("DELETE FROM student_subjects WHERE student_id=$student_id AND subject_id=$subject_id");
}

header("Location: manage_students.php");
?>
