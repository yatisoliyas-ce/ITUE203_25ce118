<?php
require_once "db_mysqli.php";

$message = "";
$id = intval($_GET["id"] ?? 0);

$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student record not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $mobile = trim($_POST["mobile"]);
    $roll_number = trim($_POST["roll_number"]);
    $department = trim($_POST["department"]);

    $stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, mobile = ?, roll_number = ?, department = ? WHERE student_id = ?");
    $stmt->bind_param("sssssi", $full_name, $email, $mobile, $roll_number, $department, $id);

    if ($stmt->execute()) {
        header("Location: practical11.php?message=updated");
        exit();
    } else {
        $message = "Failed to update student. Please check the details.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
</head>
<body>

<h2>Edit Student Details</h2>

<?php if ($message !== ""): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="full_name" value="<?php echo htmlspecialchars($student["full_name"]); ?>" required><br><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($student["email"]); ?>" required><br><br>
    <input type="text" name="mobile" value="<?php echo htmlspecialchars($student["mobile"]); ?>" required><br><br>
    <input type="text" name="roll_number" value="<?php echo htmlspecialchars($student["roll_number"]); ?>" required><br><br>
    <input type="text" name="department" value="<?php echo htmlspecialchars($student["department"]); ?>" required><br><br>

    <button type="submit">Update Student</button>
</form>

<br>
<a href="practical11.php">Back to Student List</a>

</body>
</html>

<?php
$conn->close();
?>