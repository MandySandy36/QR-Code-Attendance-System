<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $result = $conn->query("SELECT * FROM students WHERE email='$email'");
    $student = $result->fetch_assoc();

    if ($student && password_verify($password, $student["password"])) {
        $_SESSION["student_id"] = $student["id"];
        $_SESSION["student_name"] = $student["name"];
        header("Location: student_dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2 class="text-center mb-4">Student Login</h2>

    <div class="card p-4">
        <?php if (isset($error)) { echo "<p class='text-danger'>$error</p>"; } ?>
        <form method="POST">
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
            <button type="submit" class="btn btn-success">Login</button>
        </form>
    </div>

    <div class="mt-4 text-center">
        <p>Don't have an account? <a href="student_signup.php">Signup here</a></p>
    </div>

</body>
</html>
