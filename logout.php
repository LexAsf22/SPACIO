<?php
session_start();
session_destroy();

// Optional: Show a message before redirecting
echo "<!DOCTYPE html>
<html>
<head>
    <title>Logged Out</title>
    <meta http-equiv='refresh' content='2;url=login.php'>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f2f5f9;
            color: #0b3d2e;
            text-align: center;
        }
        .message {
            background: white;
            padding: 30px 50px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class='message'>
        <h2>You have been logged out.</h2>
        <p>Redirecting to login page...</p>
    </div>
</body>
</html>";
exit();
?>
