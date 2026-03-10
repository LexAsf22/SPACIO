<?php
// frontend/includes/header.php
include("../../backend/config/auth.php");
include("../../backend/config/database.php");
checkLogin();

$user = $_SESSION['user'];
$role = $user['role'];

$dashboardLinks = [
    'student' => '../student/dashboard.php',
    'teacher' => '../teacher/dashboard.php',
    'admin'   => '../admin/dashboard.php',
];

$dashboardLink = $dashboardLinks[$role] ?? '../../index.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Campus System</title>
    <style>
        body {margin:0; font-family:Arial;}
        .sidebar {
            width:200px;
            background:#2c5f2e;
            color:white;
            height:100vh;
            position:fixed;
            padding:20px 10px;
        }
        .sidebar h3 {text-align:center; margin-bottom:30px;}
        .sidebar a {
            display:block;
            color:white;
            text-decoration:none;
            padding:10px 5px;
            margin-bottom:5px;
            border-radius:5px;
        }
        .sidebar a:hover {background:#1e3d1a;}
        .content {margin-left:220px; padding:20px;}
        .topbar {
            background:#4caf50;
            color:white;
            padding:15px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><?php echo ucfirst($role); ?> Menu</h3>

    <a href="<?php echo $dashboardLink; ?>">Dashboard</a>

    <?php if($role == "student"){ ?>
        <a href="../student/reserve_lab.php">Reserve Lab</a>
        <a href="../student/reserve_equipment.php">Reserve Equipment</a>
        <a href="../student/my_reservations.php">My Reservations</a>
        <a href="../student/view_computers.php">View Computers</a>
    <?php } ?>

    <?php if($role == "teacher"){ ?>
        <a href="../teacher/lab_usage.php">Lab Usage</a>
        <a href="../teacher/report_issue.php">Report Issue</a>
        <a href="../teacher/issue_status.php">Issue Status</a>
    <?php } ?>

    <?php if($role == "admin"){ ?>
        <a href="../admin/approvals.php">Approve Reservations</a>
        <a href="../admin/inventory.php">Inventory Management</a>
        <a href="../admin/maintenance.php">Maintenance Requests</a>
        <a href="../admin/reports.php">Reports & Analytics</a>
    <?php } ?>

    <a href="../../logout.php" style="margin-top:20px; color:#ffdddd;">Logout</a>
</div>

<div class="content">
<div class="topbar">
    Welcome, <?php echo htmlspecialchars($user['name']); ?> | Campus: <?php echo htmlspecialchars($user['campus']); ?>
</div>