<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
<h3>Student Dashboard</h3>

<nav>
    <a href="student_dashboard.php">Dashboard</a> |
    <a href="practical12.php">Events</a> |
    <a href="logout.php">Logout</a>
</nav>

</body>
</html>