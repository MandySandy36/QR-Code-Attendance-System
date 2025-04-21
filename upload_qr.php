<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

$alert = "";

// If submitted from JS with extracted QR code
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["qr_code"])) {
    $qr_code = $_POST["qr_code"];

    // Fetch QR code details
    $qr_stmt = $conn->prepare("SELECT student_id, subject_id FROM qr_codes WHERE qr_code = ?");
    $qr_stmt->bind_param("s", $qr_code);
    $qr_stmt->execute();
    $qr_result = $qr_stmt->get_result();

    if ($qr_result->num_rows > 0) {
        $qr_data = $qr_result->fetch_assoc();
        $student_id = $qr_data['student_id'];
        $subject_id = $qr_data['subject_id'];

        $student_check = $conn->prepare("SELECT id FROM students WHERE id = ?");
        $student_check->bind_param("i", $student_id);
        $student_check->execute();
        $student_result = $student_check->get_result();

        if ($student_result->num_rows > 0) {
            $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND subject_id = ? AND DATE(attendance_time) = CURDATE()");
            $check_stmt->bind_param("ii", $student_id, $subject_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows == 0) {
                $insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, qr_code, attendance_status) VALUES (?, ?, ?, 'PRESENT')");
                $insert_stmt->bind_param("iis", $student_id, $subject_id, $qr_code);
                $insert_stmt->execute();
                $alert = "<div class='alert alert-success'>✅ Attendance Marked Successfully from Uploaded QR!</div>";
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
    <title>Upload QR Code Image</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>
    <style>
        body { text-align: center; margin-top: 50px; font-family: Arial; }
        form { max-width: 400px; margin: auto; }
        #preview-img {
    max-width: 100%;
    max-height: 300px;
    margin-top: 15px;
    display: none;
    display: block;
    margin-left: auto;
    margin-right: auto;
}

    </style>
</head>
<body class="container">
    <h1 class="mb-4">📷 Upload QR Code to Mark Attendance</h1>

    <?php if ($alert) echo $alert; ?>

    <form method="POST" id="qr-form">
        <input type="hidden" name="qr_code" id="qr_code">
    </form>

    <input type="file" id="imageInput" accept="image/*" class="form-control mb-3" style="max-width: 400px; margin: auto;">
    <img id="preview-img"  >
    <canvas id="canvas" style="display:none;"></canvas>

    <div id="qr-result" class="mt-3"></div>

    <div class="mt-4 d-flex justify-content-center gap-3">
        <a href="scan_qr.php" class="btn btn-secondary">Scan QR from Camera</a>
        <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <script>
        const imageInput = document.getElementById("imageInput");
        const canvas = document.getElementById("canvas");
        const ctx = canvas.getContext("2d");
        const qrCodeInput = document.getElementById("qr_code");
        const qrForm = document.getElementById("qr-form");
        const qrResult = document.getElementById("qr-result");
        const previewImg = document.getElementById("preview-img");

        imageInput.addEventListener("change", () => {
            const file = imageInput.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function () {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);

                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);

                    previewImg.src = e.target.result;
                    previewImg.style.display = "block";

                    if (code) {
                        qrResult.innerHTML = `✅ QR Code Detected: <b>${code.data}</b>`;
                        qrCodeInput.value = code.data;
                        setTimeout(() => qrForm.submit(), 1000);
                    } else {
                        qrResult.innerHTML = "❌ No QR code found in the image.";
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>
