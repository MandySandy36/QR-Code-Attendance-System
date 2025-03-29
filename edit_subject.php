<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");

// Fetch subject data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM subjects WHERE id=$id");
    $subject = $result->fetch_assoc();
}

// Update subject details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $subject_name = $_POST["subject_name"];
    $conn->query("UPDATE subjects SET subject_name='$subject_name' WHERE id=$id");
    header("Location: manage_subjects.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Subject</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2 class="text-center mb-4">Edit Subject</h2>

    <div class="card p-4">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
            <input type="text" name="subject_name" class="form-control mb-2" value="<?php echo $subject['subject_name']; ?>" required>
            <button type="submit" class="btn btn-success">Update Subject</button>
        </form>
    </div>

    <div class="mt-4">
        <a href="manage_subjects.php" class="btn btn-primary">Back to Manage Subjects</a>
    </div>

</body>
</html>
