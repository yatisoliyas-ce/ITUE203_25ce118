<?php
header("Content-Type: application/json");

require_once "../db_mysqli.php";

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $result = $conn->query("SELECT * FROM students ORDER BY student_id DESC");

    $students = [];

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    echo json_encode($students);
}

elseif ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $action = $data["action"] ?? "";

    if ($action === "add") {
        $name = trim($data["full_name"] ?? "");
        $email = trim($data["email"] ?? "");
        $mobile = trim($data["mobile"] ?? "");
        $roll = trim($data["roll_number"] ?? "");
        $department = trim($data["department"] ?? "");

        if ($name === "" || $email === "" || $roll === "") {
            echo json_encode(["success" => false, "message" => "Required fields are missing."]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email address."]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO students (full_name, email, mobile, roll_number, department) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $mobile, $roll, $department);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Student added successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Unable to add student."]);
        }

        $stmt->close();
    }

    elseif ($action === "update") {
        $id = (int)($data["student_id"] ?? 0);
        $name = trim($data["full_name"] ?? "");
        $email = trim($data["email"] ?? "");
        $mobile = trim($data["mobile"] ?? "");
        $roll = trim($data["roll_number"] ?? "");
        $department = trim($data["department"] ?? "");

        if ($id <= 0 || $name === "" || $email === "" || $roll === "") {
            echo json_encode(["success" => false, "message" => "Invalid or missing student details."]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email address."]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE students SET full_name=?, email=?, mobile=?, roll_number=?, department=? WHERE student_id=?");
        $stmt->bind_param("sssssi", $name, $email, $mobile, $roll, $department, $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Student updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Unable to update student."]);
        }

        $stmt->close();
    }

    elseif ($action === "delete") {
        $id = (int)($data["student_id"] ?? 0);

        $stmt = $conn->prepare("DELETE FROM students WHERE student_id=?");
        $stmt->bind_param("i", $id);

        if ($id > 0 && $stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Student deleted successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Student not found or could not be deleted."]);
        }

        $stmt->close();
    }

    else {
        echo json_encode(["success" => false, "message" => "Invalid action."]);
    }
}

else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
}

$conn->close();
?>