<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attendance_db");
$logs = $conn->query("SELECT admin_activity_log.*, admins.name AS admin_name 
                      FROM admin_activity_log 
                      JOIN admins ON admin_activity_log.admin_id = admins.id 
                      ORDER BY timestamp DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Activity Log</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="mb-4">Admin Activity Log</h2>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Admin</th>
                <th>Action</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($log = $logs->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $log["admin_name"]; ?></td>
                    <td><?php echo $log["action"]; ?></td>
                    <td><?php echo $log["timestamp"]; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
</body>
</html>
