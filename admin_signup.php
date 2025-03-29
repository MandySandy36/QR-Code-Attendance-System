<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST["name"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash password

    // Prevent duplicate emails
    $check = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        // Insert new admin
        $stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();
        header("Location: admin_login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Signup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2 class="text-center mb-4">Admin Signup</h2>

    <div class="card p-4">
        <?php if (isset($error)) { echo "<p class='text-danger'>$error</p>"; } ?>
        <form method="POST">
            <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
            <button type="submit" class="btn btn-primary">Signup</button>
        </form>
    </div>

    <div class="mt-4 text-center">
        <p>Already have an account? <a href="admin_login.php">Login here</a></p>
    </div>

</body>
</html>
