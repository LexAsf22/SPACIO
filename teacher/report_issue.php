<?php
session_start();
include("../db.php");

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$user = $_SESSION['user'];

// Handle form submission
if (isset($_POST['submit'])) {
    mysqli_query($conn,
        "INSERT INTO issues (room, category, priority, status, reported_by, campus)
         VALUES (
            '{$_POST['room']}',
            '{$_POST['category']}',
            '{$_POST['priority']}',
            'Pending',
            '{$user['id']}',
            '{$user['campus']}'
         )"
    );

    $message = "Issue reported successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Classroom / Lab Issue</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f2f5f9;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #0b3d2e;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }

        .card {
            width: 400px;
            margin: auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .card h3 {
            margin-top: 0;
            color: #0b3d2e;
            text-align: center;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        button {
            width: 100%;
            background-color: #0b3d2e;
            color: white;
            border: none;
            padding: 12px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #145a45;
        }

        .message {
            width: 400px;
            margin: 20px auto;
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            margin-left: 5%;
            text-decoration: none;
            color: #0b3d2e;
            font-weight: 600;
            border: 1px solid #0b3d2e;
            padding: 8px 16px;
            border-radius: 6px;
            transition: 0.3s;
        }

        a:hover {
            background-color: #0b3d2e;
            color: white;
        }
    </style>
</head>
<body>

<h2>Report Classroom / Lab Issue</h2>

<?php if (isset($message)): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="post" class="card">
    <h3>Submit an Issue</h3>

    <label for="room">Room:</label>
    <input type="text" name="room" id="room" required>

    <label for="category">Category:</label>
    <select name="category" id="category" required>
        <option>Equipment</option>
        <option>Furniture</option>
        <option>Air-conditioning</option>
        <option>Internet</option>
    </select>

    <label for="priority">Priority:</label>
    <select name="priority" id="priority" required>
        <option>Low</option>
        <option>Medium</option>
        <option>High</option>
    </select>

    <button name="submit">Submit Issue</button>
</form>

<a href="../dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>
