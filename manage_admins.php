<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");

// Add Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"]; // No hashing since no security is required
    $conn->query("INSERT INTO admins (name, email, password) VALUES ('$name', '$email', '$password')");
    header("Location: manage_admins.php");
}

// Edit Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_admin'])) {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $conn->query("UPDATE admins SET name='$name', email='$email', password='$password' WHERE id=$id");
    header("Location: manage_admins.php");
}

// Delete Admin
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM admins WHERE id=$id");
    header("Location: manage_admins.php");
}

$admins = $conn->query("SELECT * FROM admins");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Admins</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2 class="text-center mb-4">Manage Admins</h2>

    <div class="card p-4">
        <h4>Add New Admin</h4>
        <form method="POST">
            <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="text" name="password" class="form-control mb-2" placeholder="Password" required>
            <button type="submit" name="add_admin" class="btn btn-primary">Add Admin</button>
        </form>
    </div>

    <div class="mt-4 card p-4">
        <h4>Admin List</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $admins->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo $row["name"]; ?></td>
                        <td><?php echo $row["email"]; ?></td>
                        <td>
                            <a href="edit_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_admins.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this admin?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="admin_dashboard.php" class="btn btn-primary">Dashboard</a>
    </div>

</body>
</html>
