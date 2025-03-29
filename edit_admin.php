<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");

if (!isset($_GET["id"])) {
    die("Admin ID is missing.");
}

$id = $_GET["id"];
$admin = $conn->query("SELECT * FROM admins WHERE id=$id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $conn->query("UPDATE admins SET name='$name', email='$email', password='$password' WHERE id=$id");
    header("Location: manage_admins.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2 class="text-center mb-4">Edit Admin</h2>

    <div class="card p-4">
        <form method="POST">
            <input type="text" name="name" class="form-control mb-2" value="<?php echo $admin['name']; ?>" required>
            <input type="email" name="email" class="form-control mb-2" value="<?php echo $admin['email']; ?>" required>
            <input type="text" name="password" class="form-control mb-2" value="<?php echo $admin['password']; ?>" required>
            <button type="submit" class="btn btn-success">Update Admin</button>
        </form>
    </div>

    <div class="mt-4">
        <a href="manage_admins.php" class="btn btn-primary">Back</a>
    </div>

</body>
</html>
