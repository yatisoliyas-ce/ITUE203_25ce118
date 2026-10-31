<?php
require_once "db_mysqli.php";

$id = intval($_GET["id"] ?? 0);

$stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        header("Location: practical11.php?message=deleted");
    } else {
        header("Location: practical11.php?message=notfound");
    }
} else {
    header("Location: practical11.php?message=failed");
}

$stmt->close();
$conn->close();
exit();
?>