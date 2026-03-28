<?php
session_start();
require 'db/db_connect.php';

function respondError(string $message, string $fallback = 'login.php')
{
    $location = 'error.php?a=' . rawurlencode($message) . '&b=' . rawurlencode($fallback);

    if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
        header('HX-Redirect: ' . $location);
    } else {
        header('Location: ' . $location);
    }
    exit();
}

if (!isset($_POST['email'], $_POST['password'])) {
    respondError('Please fill both the username and password fields!', 'login.php');
}

$email = trim($_POST['email']);
$password_input = $_POST['password'];

if ($email === '' || $password_input === '') {
    respondError('Please fill both the username and password fields!', 'login.php');
}

$redirectLocation = 'login.php';

if ($stmt = $con->prepare('SELECT id, password, username, user_type FROM accounts WHERE email = ?')) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password_hash, $usern, $type);
        $stmt->fetch();

        if (password_verify($password_input, $password_hash)) {
            session_regenerate_id(true);
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $usern;
            $_SESSION['id'] = $id;

            if ($type === 'user') {
                $_SESSION['type'] = $type;
                $redirectLocation = 'home.php';
            } elseif ($type === 'coach') {
                $_SESSION['type'] = $type;
                $_SESSION['email'] = $email;
                $redirectLocation = 'coach_dashboard.php';
            } elseif ($type === 'player') {
                $_SESSION['type'] = $type;
                $_SESSION['email'] = $email;
                $redirectLocation = 'player_dashboard.php';
            }

            $stmt->close();

            if (isset($_POST['remember_me'])) {
                $rememberToken = bin2hex(random_bytes(32));
                $expiration = date('Y-m-d H:i:s', strtotime('+30 days'));

                if ($rememberStmt = $con->prepare('UPDATE accounts SET remember_token = ?, remember_expiration = ? WHERE id = ?')) {
                    $rememberStmt->bind_param('ssi', $rememberToken, $expiration, $id);
                    $rememberStmt->execute();
                    $rememberStmt->close();
                }

                setcookie('remember_me', $rememberToken, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            }

            if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
                header('HX-Redirect: ' . $redirectLocation);
                exit();
            }

            header('Location: ' . $redirectLocation);
            exit();
        }
    }

    $stmt->close();
}

respondError('Invalid email or password', 'login.php');
?>