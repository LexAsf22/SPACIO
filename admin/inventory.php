<?php
include("../db.php");

if (isset($_POST['add'])) {
    $name = $_POST['item_name'];
    $type = $_POST['type'];
    $status = $_POST['status'];
    $campus = $_POST['campus'];

    mysqli_query($conn,
        "INSERT INTO inventory (item_name, type, status, campus)
         VALUES ('$name', '$type', '$status', '$campus')"
    );
}

$result = mysqli_query($conn, "SELECT * FROM inventory");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management</title>

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
            margin-bottom: 40px;
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

        a {
            display: inline-block;
            margin-top: 30px;
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

<h2>Laboratory Inventory Management</h2>

<form method="post" class="card">
    <h3>Add New Resource</h3>

    <input type="text" name="item_name" placeholder="Item Name" required>

    <select name="type">
        <option>Computer</option>
        <option>Equipment</option>
        <option>Chemical</option>
    </select>

    <select name="status">
        <option>Available</option>
        <option>In Use</option>
        <option>Maintenance</option>
    </select>

    <input type="text" name="campus" placeholder="Campus" required>

    <button name="add">Add Item</button>
</form>

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
        <td><?= $row['status'] ?></td>
        <td><?= $row['campus'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="../dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>
