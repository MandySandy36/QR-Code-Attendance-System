<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");

// Add Subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $subject_name = $_POST["subject_name"];
    $conn->query("INSERT INTO subjects (subject_name) VALUES ('$subject_name')");
    header("Location: manage_subjects.php");
}

// Edit Subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_subject'])) {
    $id = $_POST["id"];
    $subject_name = $_POST["subject_name"];
    $conn->query("UPDATE subjects SET subject_name='$subject_name' WHERE id=$id");
    header("Location: manage_subjects.php");
}

// Delete Subject
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM subjects WHERE id=$id");
    header("Location: manage_subjects.php");
}

$subjects = $conn->query("SELECT * FROM subjects");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2 class="text-center mb-4">Manage Subjects</h2>

    <div class="card p-4">
        <h4>Add New Subject</h4>
        <form method="POST">
            <input type="text" name="subject_name" class="form-control mb-2" placeholder="Subject Name" required>
            <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
        </form>
    </div>

    <div class="mt-4 card p-4">
        <h4>Subject List</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Subject Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $subjects->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo $row["subject_name"]; ?></td>
                        <td>
                            <a href="edit_subject.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_subjects.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this subject?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="admin_dashboard.php" class="btn btn-primary">Dashboard</a>
        <a href="manage_students.php" class="btn btn-secondary">Manage Students</a>
        <a href="view_attendance.php" class="btn btn-success">View Attendance</a>
    </div>

</body>
</html>
