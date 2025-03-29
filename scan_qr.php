<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["qr_code"])) {
    $qr_code = $_POST["qr_code"];

    // Verify if QR code exists in qr_codes table
    $qr_stmt = $conn->prepare("SELECT * FROM qr_codes WHERE qr_code = ?");
    $qr_stmt->bind_param("s", $qr_code);
    $qr_stmt->execute();
    $qr_result = $qr_stmt->get_result();

    if ($qr_result->num_rows > 0) {
        // QR code found, now get the student details
        $qr_data = $qr_result->fetch_assoc();
        $student_id = $qr_data['student_id']; // Assuming qr_codes table has a student_id

        // Fetch the student details from students table
        $stmt = $conn->prepare("SELECT id, roll_no, email FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();

            // Fetch the subjects the student is enrolled in
            $subject_query = "SELECT subject_id FROM student_subjects WHERE student_id = ?";
            $subject_stmt = $conn->prepare($subject_query);
            $subject_stmt->bind_param("i", $student_id);
            $subject_stmt->execute();
            $subject_result = $subject_stmt->get_result();

            $attendanceMarked = false; // Initialize attendance flag

            // Loop over each subject
            while ($subject = $subject_result->fetch_assoc()) {
                $subject_id = $subject["subject_id"];

                // Check if attendance already exists for today
                $check_stmt = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND subject_id = ? AND DATE(attendance_time) = CURDATE()");
                $check_stmt->bind_param("ii", $student_id, $subject_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows == 0) {
                    // Mark attendance by inserting the record
                    $insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, qr_code, attendance_status) VALUES (?, ?, ?, 'PRESENT')");
                    $insert_stmt->bind_param("iis", $student_id, $subject_id, $qr_code);
                    $insert_stmt->execute();

                    // Set the flag to true as attendance has been marked
                    $attendanceMarked = true;
                }
            }

            // Display the appropriate message
            if ($attendanceMarked) {
                echo "<div class='alert alert-success'>✅ Attendance Marked Successfully!</div>";
            } else {
                echo "<div class='alert alert-warning'>⚠ Attendance Already Marked for Today!</div>";
            }

        } else {
            echo "<div class='alert alert-danger'>❌ Invalid student details associated with this QR code!</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>❌ Invalid QR Code!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        #qr-result { margin-top: 20px; font-size: 18px; color: #333; }
        canvas { display: none; } /* Hide canvas element */
        #video { max-width: 100%; margin-top: 10px; max-height: 400px; }
        #qr-result { margin-top: 20px; }
        #scan-button {
            margin-top: 20px;
        }
        img { max-width: 100%; margin-top: 10px; max-height: 400px; }
    </style>
</head>
<body class="container mt-4">
    <h1 class="mb-3">Upload QR Code</h1>
    <!-- Section for Live Camera QR Code Scanning -->
    <h3 class="mt-5 mb-3">Scan QR Code Using Camera</h3>
    <!-- Video element to show live camera feed -->
    <video id="video" width="100%" height="auto" autoplay></video>
    
    <!-- Canvas for image processing -->
    <canvas id="canvas"></canvas>

    <!-- Display decoded QR code result -->
    <div id="qr-result">Scan a QR code to see the result.</div>

    <form id="attendance-form" method="POST" class="mt-3">
        <input type="hidden" name="qr_code" id="qr_code">
        <button type="submit" class="btn btn-success" disabled id="submit-btn">Mark Attendance</button>
    </form>

    <div class="mt-4">
        <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <script>
        const qrInput = document.getElementById('qr-input');
        const preview = document.getElementById('preview');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const qrResult = document.getElementById('qr-result');
        const submitBtn = document.getElementById("submit-btn");
        const qrCodeInput = document.getElementById("qr_code");
        const video = document.getElementById("video");

        // Function to start camera and stream video
        async function startCamera() {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: "environment" }
            });
            video.srcObject = stream;

            video.addEventListener('play', function() {
                requestAnimationFrame(scanQRCodeFromCamera);
            });
        }

        // Function to scan QR Code from the camera feed
        function scanQRCodeFromCamera() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, canvas.width, canvas.height);

                if (code) {
                    qrResult.textContent = `✅ QR Code Detected: ${code.data}`;
                    qrCodeInput.value = code.data;
                    submitBtn.disabled = false; // Enable submit button
                } else {
                    qrResult.textContent = '❌ No QR code found. Please try again.';
                    submitBtn.disabled = true;
                }

                requestAnimationFrame(scanQRCodeFromCamera); // Continue scanning
            }
        }

        // Start the camera when the page loads
        window.onload = function() {
            startCamera();
        };

        qrInput.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;  // Display the image preview

                    // Process the image after it loads
                    preview.onload = function() {
                        canvas.width = preview.width;
                        canvas.height = preview.height;

                        ctx.drawImage(preview, 0, 0, preview.width, preview.height);
                        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        const code = jsQR(imageData.data, canvas.width, canvas.height);

                        if (code) {
                            qrResult.textContent = `✅ QR Code Detected: ${code.data}`;
                            document.getElementById("qr_code").value = code.data;
                            submitBtn.disabled = false; // Enable submit button
                        } else {
                            qrResult.textContent = '❌ No QR code found. Please try another image.';
                            submitBtn.disabled = true; // Disable submit button if invalid QR
                        }
                    };
                };

                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
