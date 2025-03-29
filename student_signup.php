<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $roll_no = $_POST["roll_no"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if roll number or email already exists
    $check = $conn->query("SELECT * FROM students WHERE roll_no='$roll_no' OR email='$email'");
    if ($check->num_rows > 0) {
        $error = "Roll number or Email already registered!";
    } else {
        $conn->query("INSERT INTO students (name, roll_no, email, password) VALUES ('$name', '$roll_no', '$email', '$password')");
        header("Location: student_login.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Signup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2 class="text-center mb-4">Student Signup</h2>

    <div class="card p-4">
        <?php if (isset($error)) { echo "<p class='text-danger'>$error</p>"; } ?>
        <form method="POST">
            <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
            <input type="text" name="roll_no" class="form-control mb-2" placeholder="Roll Number" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
            <button type="submit" class="btn btn-primary">Signup</button>
        </form>
    </div>

    <div class="mt-4 text-center">
        <p>Already have an account? <a href="student_login.php">Login here</a></p>
    </div>

</body>
</html>
