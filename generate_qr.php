<?php
session_start();
require 'vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$conn = new mysqli("localhost", "root", "", "attendance_db");
$student_id = $_SESSION["student_id"];

if (!isset($_GET["subject_id"])) {
    die("Subject ID is missing.");
}

$subject_id = $_GET["subject_id"];

// Check if QR code for this student, subject, and today's date already exists
$today_date = date('Y-m-d');
$stmt = $conn->prepare("SELECT qr_code FROM qr_codes WHERE student_id = ? AND subject_id = ? AND DATE(generated_at) = ?");
$stmt->bind_param("iis", $student_id, $subject_id, $today_date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // QR code already exists for today, fetch it
    $existing_qr = $result->fetch_assoc()["qr_code"];
} else {
    // Generate a new QR code for today
    $existing_qr = md5(uniqid($student_id . $subject_id . $today_date, true));

    // Store the new QR code in the database
    $stmt = $conn->prepare("INSERT INTO qr_codes (student_id, subject_id, qr_code, generated_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $student_id, $subject_id, $existing_qr);
    $stmt->execute();
}

// Generate QR Code Image
$qrCode = new QrCode($existing_qr);
$writer = new PngWriter();

// Display QR Code
header('Content-Type: image/png');
echo $writer->write($qrCode)->getString();
?>
