<?php
session_start();

define("ADMIN_USERNAME", "admin");
define("ADMIN_HASHED_PASSWORD", '$2y$10$Od6FI0SS/dQMrTSjZtTqi.dg6XOhubDzcOL2vl9ERb2vBXsmEzOJu'); // "1234" Change obv

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check username and verify password
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_HASHED_PASSWORD)) {
        $_SESSION["admin_logged_in"] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>
    <link rel="icon" type="image/png" href="favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="favicon.svg" />
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak Admin" />
    <link rel="manifest" href="site.webmanifest" />
</head>
<body class="p-1">
    <h2 class="text-2xl font-bold pb-2">Admin Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" class="flex flex-col gap-2">
        <div>
            <label>Username:</label>
            <input class="border-2 border-black p-1" type="text" name="username" required>
        </div>
        <div>
            <label>Password:</label>
            <input class="border-2 border-black p-1" type="password" name="password" required>
        </div>
        <div><button type="submit" class="border-2 border-black p-1 m-1">Login</button></div>
    </form>
</body>
</html>
