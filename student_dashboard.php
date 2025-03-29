<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

// Ensure student is logged in
if (!isset($_SESSION["student_id"])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION["student_id"];

// Fetch assigned subjects with attendance data
$query = "SELECT subjects.id, subjects.subject_name, 
          COUNT(attendance.id) AS attended_classes,
          (SELECT COUNT(DISTINCT DATE(attendance_time)) FROM attendance WHERE subject_id = subjects.id) AS total_classes
          FROM subjects
          LEFT JOIN attendance ON subjects.id = attendance.subject_id AND attendance.student_id = $student_id
          JOIN student_subjects ON subjects.id = student_subjects.subject_id
          WHERE student_subjects.student_id = $student_id
          GROUP BY subjects.id";

$subjects = $conn->query($query);

// Prepare data for Chart.js
$subjectNames = [];
$attendancePercentages = [];

while ($row = $subjects->fetch_assoc()) {
    $subjectNames[] = $row["subject_name"];
    $total_classes = max($row["total_classes"], 1); // Avoid division by zero
    $attendancePercentage = ($row["attended_classes"] / $total_classes) * 100;
    $attendancePercentages[] = round($attendancePercentage, 2);
}

// Fetch attendance history
$attendance_history = $conn->query("SELECT subjects.subject_name, attendance.attendance_time
                                    FROM attendance 
                                    JOIN subjects ON attendance.subject_id = subjects.id
                                    WHERE attendance.student_id = $student_id
                                    ORDER BY attendance.attendance_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container mt-4">
    <h2 class="mb-4">Welcome, <?php echo $_SESSION["student_name"]; ?>!</h2>

    <!-- Attendance Summary Table -->
    <h4>Your Attendance Summary</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Subject</th>
                <th>Attended Classes</th>
                <th>Total Classes</th>
                <th>Attendance %</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $subjects->data_seek(0); // Reset pointer to re-iterate
            while ($row = $subjects->fetch_assoc()) { 
                $attendancePercentage = round(($row["attended_classes"] / max($row["total_classes"], 1)) * 100, 2);
            ?>
                <tr>
                    <td><?php echo $row["subject_name"]; ?></td>
                    <td><?php echo $row["attended_classes"]; ?></td>
                    <td><?php echo $row["total_classes"]; ?></td>
                    <td><?php echo $attendancePercentage; ?>%</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Attendance Chart -->
    <div class="mt-5">
        <h4>Attendance Overview</h4>
        <canvas id="attendanceChart"></canvas>
    </div>

    <!-- Attendance History -->
    <div class="mt-5">
        <h4>Your Attendance History</h4>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Subject</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($history = $attendance_history->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $history["subject_name"]; ?></td>
                        <td><?php echo $history["attendance_time"]; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- QR Code Generation Section -->
    <div class="mt-5">
        <h4>Select Subject for QR Code</h4>
        <ul class="list-group">
            <?php 
            $subjects->data_seek(0); // Reset pointer
            while ($sub = $subjects->fetch_assoc()) { ?>
                <li class="list-group-item d-flex justify-content-between">
                    <?= $sub["subject_name"]; ?>
                    <a href="generate_qr.php?subject_id=<?= $sub["id"] ?>" class="btn btn-primary btn-sm">Get QR</a>
                </li>
            <?php } ?>
        </ul>
    </div>

    <div class="mt-4">
        <a href="slogout.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
    </div>

    <script>
        // Chart.js Attendance Graph
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($subjectNames); ?>,
                datasets: [{
                    label: 'Attendance %',
                    data: <?php echo json_encode($attendancePercentages); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    </script>

</body>
</html>
