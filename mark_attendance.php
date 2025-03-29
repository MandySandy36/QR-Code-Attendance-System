<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");
$qr_code = $_GET["qr_code"];
$result = $conn->query("SELECT * FROM attendance WHERE qr_code='$qr_code'");

if ($result->num_rows > 0) {
    echo "Attendance Marked!";
} else {
    echo "Invalid QR Code!";
}
?>
