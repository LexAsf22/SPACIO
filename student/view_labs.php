<?php
include("../db.php");

// Fetch all inventory
$result = mysqli_query($conn, "SELECT * FROM inventory");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Lab Resources</title>
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

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        th {
            background-color: #0b3d2e;
            color: white;
            padding: 14px;
            text-align: left;
            font-size: 15px;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }

        tr:nth-child(even) {
            background-color: #f7faf9;
        }

        tr:hover {
            background-color: #eef6f3;
        }

        /* Status color coding */
        td.status-Available {
            color: #16a34a; /* green */
            font-weight: bold;
        }
        td.status-In\ Use {
            color: #d97706; /* orange */
            font-weight: bold;
        }
        td.status-Maintenance {
            color: #dc2626; /* red */
            font-weight: bold;
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

<h2>Available Lab Resources</h2>

<table>
    <tr>
        <th>Item</th>
        <th>Type</th>
        <th>Status</th>
        <th>Campus</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= $row['item_name'] ?></td>
        <td><?= $row['type'] ?></td>
        <td class="status-<?= str_replace(' ', '\ ', $row['status']) ?>"><?= $row['status'] ?></td>
        <td><?= $row['campus'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="../dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>
