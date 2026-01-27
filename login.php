<?php
session_start();
include("db.php");

$error = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn,
        "SELECT * FROM users 
         WHERE username='$username' AND password='$password'"
    );

    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - College Lab Management</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            display: flex;
            width: 800px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .login-left {
            background-color: #0b3d2e;
            color: white;
            flex: 1;
            padding: 50px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-left h2 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }

        .login-left p {
            font-size: 16px;
            text-align: center;
            max-width: 250px;
        }

        .login-right {
            flex: 1;
            padding: 50px 30px;
        }

        .login-right h2 {
            color: #0b3d2e;
            text-align: center;
            margin-bottom: 30px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #0b3d2e;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #145a45;
        }

        .error {
            color: #dc2626;
            margin-bottom: 15px;
            text-align: center;
        }

        a {
            display: block;
            margin-top: 15px;
            text-align: center;
            color: #0b3d2e;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }

        @media screen and (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 90%;
            }

            .login-left {
                padding: 30px;
            }

            .login-right {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-left">
        <h2>College Lab Management</h2>
        <p>Manage laboratory resources, classroom issues, and campus operations efficiently.</p>
    </div>

    <div class="login-right">
        <h2>Login</h2>

        <?php if ($error != ''): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button name="login">Login</button>
        </form>

        <a href="index.php">⬅ Back to Home</a>
    </div>
</div>

</body>
</html>
