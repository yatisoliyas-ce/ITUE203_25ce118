
<?php
$success = "";
$errors = [];

$name = "";
$email = "";
$mobile = "";
$roll_number = "";
$department = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $roll_number = trim($_POST["roll_number"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $message = trim($_POST["message"] ?? "");

    $name = strip_tags($name);
    $roll_number = strip_tags($roll_number);
    $department = strip_tags($department);
    $message = strip_tags($message);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $mobile = preg_replace("/[^0-9]/", "", $mobile);

    if ($name === "" || strlen($name) < 3) {
        $errors[] = "Please enter a valid full name.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Mobile number must contain exactly 10 digits.";
    }

    if ($roll_number === "") {
        $errors[] = "Roll number is required.";
    }

    $allowed_departments = [
        "Computer Engineering",
        "Information Technology",
        "Mechanical Engineering",
        "Civil Engineering",
        "Electrical Engineering"
    ];

    if (!in_array($department, $allowed_departments, true)) {
        $errors[] = "Please select a valid department.";
    }

    if ($message === "") {
        $errors[] = "Please enter a message.";
    }

    if (empty($errors)) {
        $dataDirectory = __DIR__ . "/data";
        $filePath = $dataDirectory . "/registrations.json";

        if (!is_dir($dataDirectory)) {
            mkdir($dataDirectory, 0755, true);
        }

        if (!file_exists($filePath)) {
            file_put_contents($filePath, "[]");
        }

        $file = fopen($filePath, "c+");

        if ($file !== false) {
            if (flock($file, LOCK_EX)) {
                $contents = stream_get_contents($file);
                $records = json_decode($contents, true);

                if (!is_array($records)) {
                    $records = [];
                }

                $records[] = [
                    "name" => $name,
                    "email" => $email,
                    "mobile" => $mobile,
                    "roll_number" => $roll_number,
                    "department" => $department,
                    "message" => $message,
                    "submitted_at" => date("Y-m-d H:i:s")
                ];

                $jsonData = json_encode(
                    $records,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                );

                rewind($file);
                ftruncate($file, 0);

                if (fwrite($file, $jsonData) !== false) {
                    $success = "Registration submitted successfully!";
                    $name = $email = $mobile = "";
                    $roll_number = $department = $message = "";
                } else {
                    $errors[] = "Unable to save the registration.";
                }

                flock($file, LOCK_UN);
            } else {
                $errors[] = "Unable to access the storage file.";
            }

            fclose($file);
        } else {
            $errors[] = "Unable to open the storage file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 7 | StudentHub</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .registration-card {
            max-width: 700px;
            margin: 24px auto;
            background: var(--surface);
            padding: 24px;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .registration-form {
            display: grid;
            gap: 16px;
        }

        .registration-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .registration-form input,
        .registration-form select,
        .registration-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        .success {
            color: #15803d;
            background: #e8f7ee;
            padding: 12px;
            border-radius: 6px;
        }

        .error-box {
            color: #b91c1c;
            background: #fee2e2;
            padding: 12px;
            border-radius: 6px;
        }

        .registration-form button {
            width: 100%;
        }
    </style>
</head>

<body>
    <a href="#main">Skip to main content</a>

    <header>
        <p><strong>StudentHub</strong> &mdash; Student Portal</p>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="events.html">Events</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="login.html">Login</a></li>
                <li><a href="register.html">Register</a></li>
            </ul>
        </nav>
    </header>

    <main id="main">
        <section class="registration-card">
            <h1>Practical 7: Student Registration</h1>
            <p>Enter your details. The server will validate and store your information.</p>

            <?php if ($success !== ""): ?>
                <p class="success" role="status">
                    <?= htmlspecialchars($success) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-box" role="alert">
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="registration-form" method="POST"
                  action="practical7.php">

                <div>
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($name) ?>" required>
                </div>

                <div>
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div>
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile"
                           maxlength="10" value="<?= htmlspecialchars($mobile) ?>"
                           required>
                </div>

                <div>
                    <label for="roll_number">Roll Number</label>
                    <input type="text" id="roll_number" name="roll_number"
                           value="<?= htmlspecialchars($roll_number) ?>" required>
                </div>

                <div>
                    <label for="department">Department</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <?php
                        foreach ([
                            "Computer Engineering",
                            "Information Technology",
                            "Mechanical Engineering",
                            "Civil Engineering",
                            "Electrical Engineering"
                        ] as $option):
                        ?>
                            <option value="<?= htmlspecialchars($option) ?>"
                                <?= $department === $option ? "selected" : "" ?>>
                                <?= htmlspecialchars($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="message">Message / Reason for Registration</label>
                    <textarea id="message" name="message"
                              rows="4" required><?= htmlspecialchars($message) ?></textarea>
                </div>

                <button type="submit">Submit Registration</button>
            </form>

            <p><a href="index.html">Back to StudentHub Home</a></p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub. Practical Project.</p>
    </footer>
</body>
</html>
