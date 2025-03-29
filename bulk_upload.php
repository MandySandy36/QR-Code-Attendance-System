<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"]) && isset($_POST["upload_type"])) {
    $file = $_FILES["csv_file"]["tmp_name"];
    $upload_type = $_POST["upload_type"];
    $handle = fopen($file, "r");

    if ($handle !== FALSE) {
        fgetcsv($handle); // Skip the header row

        if ($upload_type == "students") {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $name = $data[0];
                $roll_no = $data[1];
                $email = $data[2];
                $password = password_hash($data[3], PASSWORD_DEFAULT); // Secure password
    
                // Prevent duplicate roll numbers
                $check = $conn->query("SELECT * FROM students WHERE roll_no='$roll_no'");
                if ($check->num_rows == 0) {
                    $conn->query("INSERT INTO students (name, roll_no, email, password) VALUES ('$name', '$roll_no', '$email', '$password')");
                }
            }
            // Log admin action
            $admin_id = $_SESSION["admin_id"];
            $conn->query("INSERT INTO admin_activity_log (admin_id, action) VALUES ('$admin_id', 'Bulk Uploaded Students')");
            echo "<script>alert('Students uploaded successfully!');</script>";
        } 
        elseif ($upload_type == "subjects") {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $subject_name = $data[0];
                $subject_code = $data[1];

                // Prevent duplicate subjects
                $check = $conn->query("SELECT * FROM subjects WHERE subject_code='$subject_code'");
                if ($check->num_rows == 0) {
                    $conn->query("INSERT INTO subjects (subject_name, subject_code) VALUES ('$subject_name', '$subject_code')");
                }
            }
            // Log admin action
            $admin_id = $_SESSION["admin_id"];
            $conn->query("INSERT INTO admin_activity_log (admin_id, action) VALUES ('$admin_id', 'Bulk Uploaded Subjects')");
            echo "<script>alert('Subjects uploaded successfully!');</script>";
        }
        fclose($handle);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bulk Upload Students & Subjects</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-center mb-4">Bulk Upload 📂</h2>
    
    <form method="POST" enctype="multipart/form-data" class="card p-4">
        <label class="form-label">Select Upload Type:</label>
        <select name="upload_type" class="form-select mb-3" required>
            <option value="students">Upload Students</option>
            <option value="subjects">Upload Subjects</option>
        </select>

        <input type="file" name="csv_file" accept=".csv" class="form-control mb-3" required>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <div class="mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
