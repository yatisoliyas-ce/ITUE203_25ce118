<?php
require_once "db.php";

$department = "Computer Engineering";

$stmt = $pdo->prepare(
    "SELECT full_name, email, roll_number
     FROM students
     WHERE department = :department"
);

$stmt->execute([
    'department' => $department
]);

$students = $stmt->fetchAll();

echo "<h2>Students by Department</h2>";

foreach ($students as $student) {
    echo htmlspecialchars($student['full_name']) . " - ";
    echo htmlspecialchars($student['email']) . " - ";
    echo htmlspecialchars($student['roll_number']) . "<br>";
}
?>