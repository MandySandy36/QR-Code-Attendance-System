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

// Generate a temporary randomized QR code (hashed)
$randomized_qr = md5(uniqid($student_id . $subject_id, true));

// Store QR Code in the database (linked to student & subject)
$stmt = $conn->prepare("INSERT INTO qr_codes (student_id, subject_id, qr_code, generated_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $student_id, $subject_id, $randomized_qr);
$stmt->execute();

// Generate QR Code Image
$qrCode = new QrCode($randomized_qr);
$writer = new PngWriter();

// Display QR Code
header('Content-Type: image/png');
echo $writer->write($qrCode)->getString();
?>
