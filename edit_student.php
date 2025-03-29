<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");

// Fetch student data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM students WHERE id=$id");
    $student = $result->fetch_assoc();
}

// Update student details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $roll_no = $_POST["roll_no"];
    $email = $_POST["email"];
    $conn->query("UPDATE students SET name='$name', roll_no='$roll_no', email='$email' WHERE id=$id");
    header("Location: manage_students.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2 class="text-center mb-4">Edit Student</h2>

    <div class="card p-4">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
            <input type="text" name="name" class="form-control mb-2" value="<?php echo $student['name']; ?>" required>
            <input type="text" name="roll_no" class="form-control mb-2" value="<?php echo $student['roll_no']; ?>" required>
            <input type="email" name="email" class="form-control mb-2" value="<?php echo $student['email']; ?>" required>
            <button type="submit" class="btn btn-success">Update Student</button>
        </form>
    </div>

    <div class="mt-4">
        <a href="manage_students.php" class="btn btn-primary">Back to Manage Students</a>
    </div>

</body>
</html>
