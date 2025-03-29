<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

// Add Student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    $name = $_POST["name"];
    $roll_no = $_POST["roll_no"];
    $email = $_POST["email"];

    $conn->query("INSERT INTO students (name, roll_no, email) VALUES ('$name', '$roll_no', '$email')");
    
    // Log admin action
    $admin_id = $_SESSION["admin_id"];
    $conn->query("INSERT INTO admin_activity_log (admin_id, action) VALUES ('$admin_id', 'Added Student: $name')");

    header("Location: manage_students.php");
}

// Assign Subject to Student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_subject'])) {
    $student_id = $_POST["student_id"];
    $subject_id = $_POST["subject_id"];

    // Prevent duplicate assignment
    $check = $conn->query("SELECT * FROM student_subjects WHERE student_id = $student_id AND subject_id = $subject_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO student_subjects (student_id, subject_id) VALUES ($student_id, $subject_id)");

        // Log admin action
        $admin_id = $_SESSION["admin_id"];
        $conn->query("INSERT INTO admin_activity_log (admin_id, action) VALUES ('$admin_id', 'Assigned Subject ID: $subject_id to Student ID: $student_id')");
    }
    header("Location: manage_students.php");
}

// Remove Assigned Subject
if (isset($_GET['remove_subject'])) {
    $student_id = $_GET["student_id"];
    $subject_id = $_GET["subject_id"];

    $conn->query("DELETE FROM student_subjects WHERE student_id = $student_id AND subject_id = $subject_id");

    // Log admin action
    $admin_id = $_SESSION["admin_id"];
    $conn->query("INSERT INTO admin_activity_log (admin_id, action) VALUES ('$admin_id', 'Removed Subject ID: $subject_id from Student ID: $student_id')");

    header("Location: manage_students.php");
}

// Delete Student
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $student = $conn->query("SELECT name FROM students WHERE id=$id")->fetch_assoc();
    $student_name = $student['name'];

    $conn->query("DELETE FROM students WHERE id=$id");

    // Log admin action
    $admin_id = $_SESSION["admin_id"];
    $conn->query("INSERT INTO admin_activity_log (admin_id, action) VALUES ('$admin_id', 'Deleted Student: $student_name')");

    header("Location: manage_students.php");
}

// Fetch all students
$students = $conn->query("SELECT * FROM students");

// Fetch all subjects for dropdown
$all_subjects = $conn->query("SELECT * FROM subjects");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2 class="text-center mb-4">Manage Students</h2>
    <div class="mt-4 card p-4">
        <h4>Student List</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Roll No</th>
                    <th>Email</th>
                    <th>Assigned Subjects</th>
                    <th>Assign New Subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo $row["name"]; ?></td>
                        <td><?php echo $row["roll_no"]; ?></td>
                        <td><?php echo $row["email"]; ?></td>
                        <td>
                            <?php
                            // Fetch assigned subjects for each student
                            $student_id = $row["id"];
                            $subjects = $conn->query("SELECT subjects.id AS subject_id, subjects.subject_name FROM subjects 
                                JOIN student_subjects ON subjects.id = student_subjects.subject_id
                                WHERE student_subjects.student_id = $student_id");

                            if ($subjects->num_rows > 0) {
                                while ($subject = $subjects->fetch_assoc()) {
                                    echo "<span class='badge bg-primary me-1'>
                                            {$subject['subject_name']} 
                                            <a href='manage_students.php?remove_subject=true&student_id=$student_id&subject_id={$subject['subject_id']}' class='text-white ms-2' 
                                            onclick=\"return confirm('Remove subject?');\">✖</a>
                                          </span>";
                                }
                            } else {
                                echo "<span class='text-muted'>No subjects assigned</span>";
                            }
                            ?>
                        </td>
                        <td>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                <select name="subject_id" class="form-select me-2">
                                    <?php
                                    // Reload subjects list
                                    $all_subjects->data_seek(0); 
                                    while ($subject = $all_subjects->fetch_assoc()) {
                                        echo "<option value='{$subject['id']}'>{$subject['subject_name']}</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" name="assign_subject" class="btn btn-success">Assign</button>
                            </form>
                        </td>
                        <td>
                            <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_students.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this student?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="admin_dashboard.php" class="btn btn-primary">Dashboard</a>
        <a href="manage_subjects.php" class="btn btn-secondary">Manage Subjects</a>
        <a href="view_attendance.php" class="btn btn-success">View Attendance</a>
    </div>

</body>
</html>
