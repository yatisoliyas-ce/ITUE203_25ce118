<?php
session_start();

require_once "db_mysqli.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

if (time() - $_SESSION["last_activity"] > 300) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

$_SESSION["last_activity"] = time();
$stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)");
$action = "Viewed Admin Dashboard";
$details = "Admin opened the dashboard.";
$stmt->bind_param("iss", $_SESSION["user_id"], $action, $details);
$stmt->execute();
$stmt->close();
$student_count = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()["total"];
$event_count = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()["total"];
$user_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()["total"];

$registrations = $conn->query("
    SELECT registration_id, student_id, event_id, status
    FROM registrations
    ORDER BY registration_id DESC
    LIMIT 5
");

$logs = $conn->query("
    SELECT action, details, created_at
    FROM audit_logs
    ORDER BY log_id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial;
            margin: 30px;
            background: #f4f4f4;
        }

        .box {
            display: inline-block;
            background: white;
            padding: 20px;
            margin: 10px;
            width: 180px;
            border-radius: 8px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            margin-bottom: 25px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #333;
            color: white;
        }
    </style>
</head>
<body>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
<h3>Admin Dashboard</h3>
<nav>
    <a href="admin_dashboard.php">Dashboard</a> |
    <a href="practical11.php">Manage Students</a> |
    <a href="practical12.php">Manage Events</a> |
    <a href="logout.php">Logout</a>
</nav>
<br>
<div class="box">
    <h3>Total Users</h3>
    <h2><?php echo $user_count; ?></h2>
</div>

<div class="box">
    <h3>Total Students</h3>
    <h2><?php echo $student_count; ?></h2>
</div>

<div class="box">
    <h3>Total Events</h3>
    <h2><?php echo $event_count; ?></h2>
</div>

<h3>Latest Registrations</h3>

<table>
    <tr>
        <th>Registration ID</th>
        <th>Student ID</th>
        <th>Event ID</th>
        <th>Status</th>
    </tr>

    <?php while ($row = $registrations->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row["registration_id"]); ?></td>
        <td><?php echo htmlspecialchars($row["student_id"]); ?></td>
        <td><?php echo htmlspecialchars($row["event_id"]); ?></td>
        <td><?php echo htmlspecialchars($row["status"]); ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<h3>Recent Audit Logs</h3>

<table>
    <tr>
        <th>Action</th>
        <th>Details</th>
        <th>Date</th>
    </tr>

    <?php while ($row = $logs->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row["action"]); ?></td>
        <td><?php echo htmlspecialchars($row["details"] ?? ""); ?></td>
        <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="logout.php">Logout</a>

</body>
</html>