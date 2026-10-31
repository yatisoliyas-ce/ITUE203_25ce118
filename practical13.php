<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 13 - Student AJAX CRUD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px #ccc;
        }

        h1 {
            text-align: center;
            color: #263238;
        }

        form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        input {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            background: #263b80;
        }

        .delete {
            background: #d32f2f;
        }

        .edit {
            background: #16834a;
            margin-right: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #263b80;
            color: white;
        }

        #message {
            margin: 15px 0;
            font-weight: bold;
        }

        @media(max-width: 650px) {
            form {
                grid-template-columns: 1fr;
            }

            .container {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Student Management - AJAX CRUD</h1>

    <form id="studentForm">
        <input type="hidden" id="student_id">

        <input type="text" id="full_name" placeholder="Full Name" required>
        <input type="email" id="email" placeholder="Email" required>
        <input type="text" id="mobile" placeholder="Mobile">
        <input type="text" id="roll_number" placeholder="Roll Number" required>
        <input type="text" id="department" placeholder="Department">

        <button type="submit" id="submitBtn">Add Student</button>
        <button type="button" id="resetBtn">Clear Form</button>
    </form>

    <div id="message">Loading students...</div>

    <h2>Student Records</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Roll Number</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="studentTable"></tbody>
    </table>
</div>

<script>
const API = "api/students.php";

const form = document.getElementById("studentForm");
const message = document.getElementById("message");
const table = document.getElementById("studentTable");
const submitBtn = document.getElementById("submitBtn");

function showMessage(text, success = true) {
    message.textContent = text;
    message.style.color = success ? "green" : "red";
}

async function loadStudents() {
    showMessage("Loading students...");

    try {
        const response = await fetch(API);

        if (!response.ok) {
            throw new Error("Unable to load students.");
        }

        const students = await response.json();

        table.innerHTML = "";

        students.forEach(student => {
            const row = document.createElement("tr");

            [
                student.student_id,
                student.full_name,
                student.email,
                student.mobile,
                student.roll_number,
                student.department
            ].forEach(value => {
                const cell = document.createElement("td");
                cell.textContent = value ?? "";
                row.appendChild(cell);
            });

            const actions = document.createElement("td");

            const editBtn = document.createElement("button");
            editBtn.textContent = "Edit";
            editBtn.className = "edit";
            editBtn.onclick = () => editStudent(student);

            const deleteBtn = document.createElement("button");
            deleteBtn.textContent = "Delete";
            deleteBtn.className = "delete";
            deleteBtn.onclick = () => deleteStudent(student.student_id);

            actions.append(editBtn, deleteBtn);
            row.appendChild(actions);
            table.appendChild(row);
        });

        showMessage("Student records loaded successfully.");
    } catch (error) {
        showMessage(error.message, false);
    }
}

form.addEventListener("submit", async function(event) {
    event.preventDefault();

    const id = document.getElementById("student_id").value;

    const student = {
        action: id ? "update" : "add",
        student_id: id,
        full_name: document.getElementById("full_name").value.trim(),
        email: document.getElementById("email").value.trim(),
        mobile: document.getElementById("mobile").value.trim(),
        roll_number: document.getElementById("roll_number").value.trim(),
        department: document.getElementById("department").value.trim()
    };

    try {
        submitBtn.disabled = true;
        showMessage("Saving student...");

        const response = await fetch(API, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(student)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Operation failed.");
        }

        showMessage(result.message);
        clearForm();
        await loadStudents();
    } catch (error) {
        showMessage(error.message, false);
    } finally {
        submitBtn.disabled = false;
    }
});

function editStudent(student) {
    document.getElementById("student_id").value = student.student_id;
    document.getElementById("full_name").value = student.full_name;
    document.getElementById("email").value = student.email;
    document.getElementById("mobile").value = student.mobile;
    document.getElementById("roll_number").value = student.roll_number;
    document.getElementById("department").value = student.department;

    submitBtn.textContent = "Update Student";
    showMessage("Editing student ID: " + student.student_id);
    window.scrollTo({ top: 0, behavior: "smooth" });
}

async function deleteStudent(id) {
    if (!confirm("Are you sure you want to delete this student?")) {
        return;
    }

    try {
        showMessage("Deleting student...");

        const response = await fetch(API, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "delete",
                student_id: id
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Delete failed.");
        }

        showMessage(result.message);
        await loadStudents();
    } catch (error) {
        showMessage(error.message, false);
    }
}

function clearForm() {
    form.reset();
    document.getElementById("student_id").value = "";
    submitBtn.textContent = "Add Student";
}

document.getElementById("resetBtn").addEventListener("click", clearForm);

loadStudents();
</script>

</body>
</html>