
<?php

session_start();

$configFile = __DIR__ . '/config.local.php';

if (!file_exists($configFile)) {
    http_response_code(500);
    exit('Login configuration is missing.');
}

$config = require $configFile;

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (
    hash_equals($config['admin_username'], $username) &&
    hash_equals($config['admin_password'], $password)
) {
    session_regenerate_id(true);
    $_SESSION['username'] = $username;

    header("Location: index.php");
    exit;
}

header("Location: login.php?error=1");
exit;