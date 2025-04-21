<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

// Fetch all subjects
$subjects = $conn->query("SELECT * FROM subjects");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mark_attendance"])) {
    $subject_id = $_POST["subject_id"];
    $attendance_date = $_POST["attendance_date"];
    $present_students = isset($_POST["present"]) ? $_POST["present"] : [];

    // Fetch all students enrolled in this subject
    $students_result = $conn->query("SELECT students.id FROM students
                                     JOIN student_subjects ON students.id = student_subjects.student_id
                                     WHERE student_subjects.subject_id = $subject_id");

    while ($row = $students_result->fetch_assoc()) {
        $student_id = $row['id'];
        $is_present = in_array($student_id, $present_students) ? 'PRESENT' : 'ABSENT';

        // Check if attendance already marked for the student on the selected date and subject
        $existing = $conn->query("SELECT id FROM attendance 
        WHERE student_id = $student_id 
        AND subject_id = $subject_id 
        AND DATE(attendance_time) = '$attendance_date'");

        if ($existing->num_rows == 0) {
        // Insert only if no record exists for that day
        $conn->query("INSERT INTO attendance (student_id, subject_id, qr_code, attendance_status, attendance_time)
        VALUES ($student_id, $subject_id, 'MANUAL', '$is_present', '$attendance_date')");
        }
    }
    $message = "Attendance successfully marked!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manual Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="mb-4">Mark Attendance Manually</h2>

    <?php if (isset($message)) { ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php } ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Subject</label>
                <select name="subject_id" class="form-control" required>
                    <option value="">Select Subject</option>
                    <?php while ($sub = $subjects->fetch_assoc()) { ?>
                        <option value="<?php echo $sub['id']; ?>"><?php echo $sub['subject_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Date</label>
                <input type="date" name="attendance_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" name="load_students" class="btn btn-primary">Load Students</button>
            </div>
        </div>
    </form>

    <?php
    // After loading students
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["load_students"])) {
        $subject_id = $_POST["subject_id"];

        $students = $conn->query("SELECT students.id, students.name, students.roll_no 
                                  FROM students
                                  JOIN student_subjects ON students.id = student_subjects.student_id
                                  WHERE student_subjects.subject_id = $subject_id");
        if ($students->num_rows > 0) {
    ?>
        <form method="POST">
            <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
            <input type="hidden" name="attendance_date" value="<?php echo $_POST["attendance_date"]; ?>">
            <table class="table table-bordered mt-4">
                <thead class="table-dark">
                    <tr>
                        <th>Student Name</th>
                        <th>Roll No</th>
                        <th>Present</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $students->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $student["name"]; ?></td>
                            <td><?php echo $student["roll_no"]; ?></td>
                            <td>
                            <?php
                                $student_id = $student["id"];
                                $attendance_date = $_POST["attendance_date"];
                                $check = $conn->query("SELECT attendance_status FROM attendance 
                                                    WHERE student_id = $student_id 
                                                    AND subject_id = $subject_id 
                                                    AND DATE(attendance_time) = '$attendance_date'");
                                
                                $is_checked = "checked"; // default
                                if ($check->num_rows > 0) {
                                    $status = $check->fetch_assoc()['attendance_status'];
                                    $is_checked = $status === 'PRESENT' ? 'checked' : '';
                                }
                            ?>
                            <input type="checkbox" name="present[]" value="<?php echo $student_id; ?>" <?php echo $is_checked; ?>>

                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <button type="submit" name="mark_attendance" class="btn btn-success">Submit Attendance</button>
        </form>
    <?php
        } else {
            echo "<div class='alert alert-warning mt-3'>No students found for this subject.</div>";
        }
    }
    ?>

    <div class="mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
