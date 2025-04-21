<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

$alert = ""; // Feedback to be shown on page

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["qr_code"])) {
    $qr_code = $_POST["qr_code"];

    // Fetch QR code details: student_id and subject_id
    $qr_stmt = $conn->prepare("SELECT student_id, subject_id FROM qr_codes WHERE qr_code = ?");
    $qr_stmt->bind_param("s", $qr_code);
    $qr_stmt->execute();
    $qr_result = $qr_stmt->get_result();

    if ($qr_result->num_rows > 0) {
        $qr_data = $qr_result->fetch_assoc();
        $student_id = $qr_data['student_id'];
        $subject_id = $qr_data['subject_id'];

        // Verify student exists
        $student_check = $conn->prepare("SELECT id FROM students WHERE id = ?");
        $student_check->bind_param("i", $student_id);
        $student_check->execute();
        $student_result = $student_check->get_result();

        if ($student_result->num_rows > 0) {
            // Check if attendance already marked today
            $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND subject_id = ? AND DATE(attendance_time) = CURDATE()");
            $check_stmt->bind_param("ii", $student_id, $subject_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows == 0) {
                // Mark attendance
                $insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, qr_code, attendance_status) VALUES (?, ?, ?, 'PRESENT')");
                $insert_stmt->bind_param("iis", $student_id, $subject_id, $qr_code);
                $insert_stmt->execute();
                $alert = "<div class='alert alert-success'>✅ Attendance Marked Successfully!</div>";
            } else {
                $alert = "<div class='alert alert-warning'>⚠ Attendance Already Marked for Today!</div>";
            }
        } else {
            $alert = "<div class='alert alert-danger'>❌ Invalid student associated with QR code.</div>";
        }
    } else {
        $alert = "<div class='alert alert-danger'>❌ Invalid QR Code!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Attendance Scanner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        #qr-result {
            margin-top: 20px;
            font-size: 18px;
            color: #333;
        }
        canvas {
            display: none;
        }
        #video, img {
            max-width: 100%;
            margin-top: 10px;
            max-height: 400px;
        }
    </style>
</head>
<body class="container mt-4">
    <h1 class="mb-3">QR Code Attendance Scanner</h1>

    <?php if ($alert) echo $alert; ?>

    <h3 class="mt-5 mb-3">Scan QR Code Using Camera</h3>
    <video id="video" width="100%" height="auto" autoplay></video>
    <canvas id="canvas"></canvas>

    <div id="qr-result">Scan a QR code to see the result.</div>

    <form id="attendance-form" method="POST" class="mt-3">
    <input type="hidden" name="qr_code" id="qr_code">
    <button type="submit" class="btn btn-success d-none" id="submit-btn">Mark Attendance</button>
</form>

    
    <div class="mt-4 d-flex justify-content-center gap-3">
        <a href="upload_qr.php" class="btn btn-secondary">Scan QR from Image</a>
        <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <script>
    const canvas = document.getElementById("canvas");
    const ctx = canvas.getContext("2d");
    const video = document.getElementById("video");
    const qrResult = document.getElementById("qr-result");
    const qrCodeInput = document.getElementById("qr_code");
    const submitBtn = document.getElementById("submit-btn");
    const form = document.getElementById("attendance-form");

    let lastScannedCode = "";

    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
            video.srcObject = stream;
            video.addEventListener('play', () => requestAnimationFrame(scanQRCodeFromCamera));
        } catch (e) {
            qrResult.textContent = "🚫 Cannot access camera.";
        }
    }

    function scanQRCodeFromCamera() {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, canvas.width, canvas.height);

            if (code && code.data !== lastScannedCode) {
                lastScannedCode = code.data;
                qrResult.textContent = `✅ QR Code Detected: ${code.data}`;
                qrCodeInput.value = code.data;
                submitBtn.disabled = false;
                form.submit(); // Auto-submit
            } else if (!code) {
                qrResult.textContent = "❌ No QR code found.";
                submitBtn.disabled = true;
            }
        }
        requestAnimationFrame(scanQRCodeFromCamera);
    }

    window.onload = startCamera;
</script>

</body>
</html>
