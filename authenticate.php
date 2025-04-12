<?php
session_start();
require 'db/db_connect.php';

if (!isset($_POST['email'], $_POST['password'])) {
    echo "<script>setTimeout(() => window.location.href = 'error.php?a=Please fill both the username and password fields!&b=login.php', 700);</script>";
    exit();
}


if ($stmt = $con->prepare('SELECT id, password, username, user_type FROM accounts WHERE email = ?')) {
    $stmt->bind_param('s', $_POST['email']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password, $usern, $type);
        $stmt->fetch();
        // Account exists
        if (password_verify($_POST['password'], $password)) {

            // log in
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $usern;
            $_SESSION['id'] = $id;

            // different user types
            if ($type === 'user') {

                $_SESSION['type'] = $type;
                echo "<script>setTimeout(() => window.location.href = 'home.php', 700);</script>";
            }

            if ($type === 'coach') {

                $_SESSION['type'] = $type;
                $_SESSION['email'] = $_POST['email'];
                echo "<script>setTimeout(() => window.location.href = 'coach_dashboard.php', 700);</script>";
            }

            if ($type === 'player') {
                $_SESSION['email'] = $_POST['email'];
                $_SESSION['type'] = $type;
                echo "<script>setTimeout(() => window.location.href = 'player_dashboard.php', 700);</script>";
            }
        } else {
            // Incorrect password
            echo "<script>setTimeout(() => window.location.href = 'error.php?a=Invalid email or password&b=login.php', 700);</script>";
            exit();

        }
    } else {
        // Incorrect username
        echo "<script>setTimeout(() => window.location.href = 'error.php?a=Invalid email or password&b=login.php', 700);</script>";
        exit();
    }

    $stmt->close();

}

// Remember me button
if (isset($_POST['remember_me'])) {
    $token = bin2hex(random_bytes(32)); // Gen token
    $expiration = date('Y-m-d H:i:s', strtotime('+30 days'));

    // token and expiration in database
    $stmt = $con->prepare("UPDATE accounts SET remember_token = ?, remember_expiration = ? WHERE id = ?");
    $stmt->bind_param("ssi", $token, $expiration, $id);
    $stmt->execute();

    // token in the cookie
    setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // Secure and HttpOnly
}

?>