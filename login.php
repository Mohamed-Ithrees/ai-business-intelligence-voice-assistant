<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AI Business Intelligence - Login</title>

    <link rel="stylesheet" href="style.css">
    <style>
body {
    background: #f4f6f9;
}

.login-box {
    width: 420px;
    margin: 80px auto;
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.login-box h1 {
    text-align: center;
    margin-bottom: 10px;
}

.login-box h2 {
    text-align: center;
    margin: 20px 0;
}

.login-box input {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
}

.login-box button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
</style>
</head>

<body>

<div class="login-box">

    <h1>AI Business Intelligence 🤖</h1>

    <p>Business Management & Analytics System</p>

    <hr>

    <h2>🔐 Login</h2>
<?php
if (isset($_GET['error'])) {
    echo "<p style='color:red;'>❌ Invalid username or password</p>";
}
?>
    <form action="login_process.php" method="POST">

        <label>Username</label>
        <br>

        <input type="text"
               name="username"
               placeholder="Enter username"
               required>

        <br><br>

        <label>Password</label>
        <br>

        <input type="password"
               name="password"
               placeholder="Enter password"
               required>

        <br><br>

        <button type="submit">
            Login
        </button>

    </form>

</div>

</body>
</html>