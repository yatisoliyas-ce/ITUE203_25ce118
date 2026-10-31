
<?php
include "db_mysqli.php";

$message = "";

$upload_dir = "uploads/events/";

if (isset($_POST['add_event'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $venue = trim($_POST['venue']);
    $poster = "";

    if (!empty($_FILES['poster']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = mime_content_type($_FILES['poster']['tmp_name']);
        $file_size = $_FILES['poster']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $message = "Invalid poster type. Only JPG and PNG allowed.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $message = "Poster size must be less than 2 MB.";
        } else {
            $poster = time() . "_" . basename($_FILES['poster']['name']);
            move_uploaded_file($_FILES['poster']['tmp_name'], $upload_dir . $poster);
        }
    }

    if ($message == "") {
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, venue, poster) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $description, $event_date, $venue, $poster);

        if ($stmt->execute()) {
            $message = "Event added successfully!";
        } else {
            $message = "Failed to add event.";
        }
    }
}

if (isset($_POST['update_event'])) {
    $id = $_POST['event_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $venue = trim($_POST['venue']);

    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, venue=? WHERE event_id=?");
    $stmt->bind_param("ssssi", $title, $description, $event_date, $venue, $id);

    if ($stmt->execute()) {
        $message = "Event updated successfully!";
    } else {
        $message = "Failed to update event.";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM events WHERE event_id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Event deleted successfully!";
    } else {
        $message = "Failed to delete event.";
    }
}

$edit_event = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_event = $stmt->get_result()->fetch_assoc();
}

$result = $conn->query("SELECT * FROM events ORDER BY event_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Practical 12 - Event Management</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            padding: 25px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            box-sizing: border-box;
        }
        button {
            background: #222;
            color: white;
            padding: 10px 18px;
            border: none;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #222;
            color: white;
        }
        .message {
            color: green;
            font-weight: bold;
        }
        img {
            width: 80px;
        }
        a {
            margin-right: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Event Management System</h1>

    <?php if ($message != "") { ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <h2><?php echo $edit_event ? "Update Event" : "Add New Event"; ?></h2>

    <form method="POST" enctype="multipart/form-data">
        <?php if ($edit_event) { ?>
            <input type="hidden" name="event_id" value="<?php echo $edit_event['event_id']; ?>">
        <?php } ?>

        <input type="text" name="title" placeholder="Event Title" value="<?php echo htmlspecialchars($edit_event['title'] ?? ''); ?>" required>

        <textarea name="description" placeholder="Event Description" required><?php echo htmlspecialchars($edit_event['description'] ?? ''); ?></textarea>

        <input type="date" name="event_date" value="<?php echo htmlspecialchars($edit_event['event_date'] ?? ''); ?>" required>

        <input type="text" name="venue" placeholder="Venue" value="<?php echo htmlspecialchars($edit_event['venue'] ?? ''); ?>" required>

        <?php if (!$edit_event) { ?>
            <label>Upload Poster (JPG/PNG, Max 2 MB)</label>
            <input type="file" name="poster" accept=".jpg,.jpeg,.png">
        <?php } ?>

        <button type="submit" name="<?php echo $edit_event ? 'update_event' : 'add_event'; ?>">
            <?php echo $edit_event ? "Update Event" : "Add Event"; ?>
        </button>
    </form>

    <h2>All Events</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Date</th>
            <th>Venue</th>
            <th>Poster</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['event_id']; ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                <td><?php echo htmlspecialchars($row['venue']); ?></td>
                <td>
                    <?php if (!empty($row['poster'])) { ?>
                        <img src="<?php echo $upload_dir . htmlspecialchars($row['poster']); ?>">
                    <?php } else { ?>
                        No poster
                    <?php } ?>
                </td>
                <td>
                    <a href="?edit=<?php echo $row['event_id']; ?>">Edit</a>
                    <a href="?delete=<?php echo $row['event_id']; ?>" onclick="return confirm('Delete this event?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
