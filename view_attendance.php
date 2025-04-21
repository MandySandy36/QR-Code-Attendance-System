<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

// Fetch subjects for filtering
$subjects = $conn->query("SELECT * FROM subjects");

// Fetch attendance records with filtering
$filter_subject = isset($_GET["subject_id"]) ? $_GET["subject_id"] : "";
$start_date = isset($_GET["start_date"]) && !empty($_GET["start_date"]) ? $_GET["start_date"] : null;
$end_date = isset($_GET["end_date"]) && !empty($_GET["end_date"]) ? $_GET["end_date"] : null;

$query = "SELECT attendance.attendance_time, students.name, students.roll_no, subjects.subject_name 
          FROM attendance
          JOIN students ON attendance.student_id = students.id
          JOIN subjects ON attendance.subject_id = subjects.id";

$conditions = [];
if ($filter_subject) {
    $conditions[] = "attendance.subject_id = '$filter_subject'";
}
if ($start_date && $end_date) {
    $conditions[] = "DATE(attendance.attendance_time) BETWEEN '$start_date' AND '$end_date'";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$attendance_records = $conn->query($query);

// Fetch subject-wise attendance percentages
$attendance_summary_query = "SELECT subjects.subject_name, COUNT(attendance.id) AS attendance_count 
                             FROM attendance 
                             JOIN subjects ON attendance.subject_id = subjects.id";

$conditions = [];
if ($start_date && $end_date) {
    $conditions[] = "DATE(attendance.attendance_time) BETWEEN '$start_date' AND '$end_date'";
}

if (!empty($conditions)) {
    $attendance_summary_query .= " WHERE " . implode(" AND ", $conditions);
}

$attendance_summary_query .= " GROUP BY subjects.subject_name";

$attendance_summary = $conn->query($attendance_summary_query);
$chart_data = [];
while ($row = $attendance_summary->fetch_assoc()) {
    $chart_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container mt-4">
    <h2 class="mb-4">View Attendance</h2>

    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <select name="subject_id" class="form-control">
                    <option value="">Select Subject</option>
                    <?php while ($subject = $subjects->fetch_assoc()) { ?>
                        <option value="<?php echo $subject["id"]; ?>" <?php if ($subject["id"] == $filter_subject) echo "selected"; ?>>
                            <?php echo $subject["subject_name"]; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Student Name</th>
                <th>Roll No</th>
                <th>Subject</th>
                <th>Attendance Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($record = $attendance_records->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $record["name"]; ?></td>
                    <td><?php echo $record["roll_no"]; ?></td>
                    <td><?php echo $record["subject_name"]; ?></td>
                    <td><?php echo $record["attendance_time"]; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="mt-4">
        <a href="export_attendance.php?format=csv" class="btn btn-primary">Export CSV</a>
        <a href="export_attendance.php?format=pdf" class="btn btn-danger">Export PDF</a>
    </div> 

    <h3 class="mt-4">Subject-wise Defaulters (Attendance < 75%)</h3>

<form method="GET" class="mb-3">
    <div class="row">
        <div class="col-md-3">
            <select name="defaulter_subject_id" class="form-control">
                <option value="">Select Subject</option>
                <?php
                $subjects->data_seek(0); // Reset result pointer
                while ($subject = $subjects->fetch_assoc()) { ?>
                    <option value="<?php echo $subject["id"]; ?>" <?php if (isset($_GET["defaulter_subject_id"]) && $_GET["defaulter_subject_id"] == $subject["id"]) echo "selected"; ?>>
                        <?php echo $subject["subject_name"]; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-warning">Show Defaulters</button>
        </div>
    </div>
</form>

<?php
if (isset($_GET["defaulter_subject_id"]) && !empty($_GET["defaulter_subject_id"])) {
    $subject_id = $_GET["defaulter_subject_id"];

    // Total lectures for this subject
    $total_lectures_query = "SELECT COUNT(*) AS total FROM qr_codes WHERE subject_id = $subject_id";
    $total_lectures_result = $conn->query($total_lectures_query);
    $total_lectures = $total_lectures_result->fetch_assoc()["total"];

    // Student attendance per subject
    $defaulters_query = "SELECT students.name, students.roll_no, COUNT(attendance.id) AS attended 
                         FROM students
                         LEFT JOIN attendance ON students.id = attendance.student_id AND attendance.subject_id = $subject_id
                         GROUP BY students.id
                         HAVING attended < (0.75 * $total_lectures)";

    $defaulters = $conn->query($defaulters_query);

    if ($defaulters->num_rows > 0) {
        ?>
        <table class="table table-bordered mt-3">
            <thead class="table-danger">
                <tr>
                    <th>Student Name</th>
                    <th>Roll No</th>
                    <th>Lectures Attended</th>
                    <th>Total Lectures</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $defaulters->fetch_assoc()) {
                    $percentage = $total_lectures > 0 ? round(($row["attended"] / $total_lectures) * 100, 2) : 0;
                    ?>
                    <tr>
                        <td><?php echo $row["name"]; ?></td>
                        <td><?php echo $row["roll_no"]; ?></td>
                        <td><?php echo $row["attended"]; ?></td>
                        <td><?php echo $total_lectures; ?></td>
                        <td><?php echo $percentage; ?>%</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php
    } else {
        echo '<div class="alert alert-info mt-3">No defaulters found for this subject.</div>';
    }

    // ✅ Export buttons shown regardless of whether defaulters exist
    ?>
    <div class="mt-3">
        <a href="export_attendance.php?export_type=defaulters&subject_id=<?php echo $subject_id; ?>&format=csv" class="btn btn-success">Export Defaulters (CSV)</a>
        <a href="export_attendance.php?export_type=defaulters&subject_id=<?php echo $subject_id; ?>&format=pdf" class="btn btn-danger">Export Defaulters (PDF)</a>
    </div>
<?php } ?>

    <h3 class="mt-4">Subject-wise Attendance Chart</h3>
    <canvas id="attendanceChart"></canvas>

    <div class="mt-4">
        <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("attendanceChart").getContext("2d");
            var chartData = <?php echo json_encode($chart_data); ?>;
            var labels = chartData.map(item => item.subject_name);
            var data = chartData.map(item => item.attendance_count);

            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Attendance Count",
                        data: data,
                        backgroundColor: "rgba(54, 162, 235, 0.5)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
