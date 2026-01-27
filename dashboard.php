<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f2f5f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background-color: #0b3d2e;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .container {
            display: flex;
            min-height: 90vh;
        }

        .sidebar {
            width: 220px;
            background-color: #145a45;
            padding: 20px;
            color: white;
            flex-shrink: 0;
        }

        .sidebar a {
            display: block;
            text-decoration: none;
            color: white;
            padding: 10px 12px;
            margin-bottom: 8px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #0b3d2e;
        }

        .content {
            flex-grow: 1;
            padding: 40px;
        }

        .welcome {
            margin-bottom: 30px;
            font-size: 16px;
        }

        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .card a {
            color: #0b3d2e;
            text-decoration: none;
            font-weight: 600;
            display: block;
            margin-bottom: 10px;
            padding: 8px 12px;
            border: 1px solid #0b3d2e;
            border-radius: 6px;
            width: fit-content;
            transition: 0.3s;
        }

        .card a:hover {
            background-color: #0b3d2e;
            color: white;
        }

        .logout {
            margin-top: 30px;
            display: inline-block;
            text-decoration: none;
            color: #0b3d2e;
            font-weight: 600;
            border: 1px solid #0b3d2e;
            padding: 8px 16px;
            border-radius: 6px;
            transition: 0.3s;
        }

        .logout:hover {
            background-color: #0b3d2e;
            color: white;
        }
    </style>
</head>
<body>

<header>College Laboratory & Classroom Management System</header>

<div class="container">

    <div class="sidebar">
        <div class="welcome">
            Welcome,<br><b><?= $user['name'] ?></b><br>
            Role: <?= ucfirst($user['role']) ?><br>
            Campus: <?= $user['campus'] ?>
        </div>

        <?php if ($user['role'] == 'student'): ?>
            <a href="student/view_inventory.php">View Lab Resources</a>
            <a href="student/reserve.php">Reserve Equipment</a>
            <a href="student/report_issue.php">Report Classroom Issue</a>

        <?php elseif ($user['role'] == 'teacher'): ?>
            <a href="teacher/report_issue.php">Report Classroom Issue</a>

        <?php else: // admin/staff ?>
            <a href="admin/inventory.php">Manage Inventory</a>
            <a href="admin/bookings.php">View Bookings</a>
            <a href="admin/maintenance.php">Maintenance Reports</a>
        <?php endif; ?>

        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="card">
            <h2>Dashboard Overview</h2>
            <p>Use the sidebar to navigate through your available features.</p>
        </div>
    </div>

</div>

</body>
</html>
