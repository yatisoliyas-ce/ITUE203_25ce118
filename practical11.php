<?php
require_once "db_mysqli.php";

$message = "";

if (isset($_GET["message"])) {
    if ($_GET["message"] === "updated") {
        $message = "Student updated successfully!";
    } elseif ($_GET["message"] === "deleted") {
        $message = "Student deleted successfully!";
    } elseif ($_GET["message"] === "notfound") {
        $message = "Student record not found.";
    } elseif ($_GET["message"] === "failed") {
        $message = "Failed to delete student.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_student"])) {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $mobile = trim($_POST["mobile"]);
    $roll_number = trim($_POST["roll_number"]);
    $department = trim($_POST["department"]);

    $stmt = $conn->prepare("INSERT INTO students (full_name, email, mobile, roll_number, department) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $full_name, $email, $mobile, $roll_number, $department);

    if ($stmt->execute()) {
        $message = "Student added successfully!";
    } else {
        $message = "Failed to add student. Check for duplicate values.";
    }

    $stmt->close();
}

$search = trim($_GET["search"] ?? "");
$department_filter = trim($_GET["department"] ?? "");

$sql = "SELECT * FROM students WHERE 1=1";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR department LIKE ?)";
    $term = "%" . $search . "%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $types .= "sss";
}

if ($department_filter !== "") {
    $sql .= " AND department = ?";
    $params[] = $department_filter;
    $types .= "s";
}

$sql .= " ORDER BY student_id ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>StudentHub - Student Management</title>
</head>
<body>

<h2>Student Management System</h2>

<?php if ($message !== ""): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<h3>Add New Student</h3>

<form method="POST">
    <input type="text" name="full_name" placeholder="Full Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="text" name="mobile" placeholder="Mobile" required><br><br>
    <input type="text" name="roll_number" placeholder="Roll Number" required><br><br>
    <input type="text" name="department" placeholder="Department" required><br><br>
    <button type="submit" name="add_student">Add Student</button>
</form>

<hr>

<h3>Search and Filter Students</h3>

<form method="GET">
    <input type="text" name="search" placeholder="Search name, email or department"
           value="<?php echo htmlspecialchars($search); ?>">

    <input type="text" name="department" placeholder="Filter by department"
           value="<?php echo htmlspecialchars($department_filter); ?>">

    <button type="submit">Search</button>
    <a href="practical11.php">Reset</a>
</form>

<hr>

<h3>Student Records</h3>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>Roll Number</th>
        <th>Department</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row["student_id"]); ?></td>
            <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
            <td><?php echo htmlspecialchars($row["email"]); ?></td>
            <td><?php echo htmlspecialchars($row["mobile"]); ?></td>
            <td><?php echo htmlspecialchars($row["roll_number"]); ?></td>
            <td><?php echo htmlspecialchars($row["department"]); ?></td>
            <td>
                <a href="edit_student.php?id=<?php echo $row["student_id"]; ?>">Edit</a> |
                <a href="delete_student.php?id=<?php echo $row["student_id"]; ?>"
                   onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>

</table>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>