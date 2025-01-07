<?php
session_start();
// Change this to your connection info.
require 'db/db_connect.php';
// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if (!isset($_POST['email'], $_POST['password']) ) {
	// Could not get the data that should have been sent.
    echo "<script>setTimeout(() => window.location.href = 'error.php?a=Please fill both the username and password fields!&b=login.php', 700);</script>";
    exit();
}

 
// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
if ($stmt = $con->prepare('SELECT id, password, username, user_type FROM accounts WHERE email = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
	$stmt->bind_param('s', $_POST['email']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password, $usern, $type);
        $stmt->fetch();
        // Account exists, now we verify the password.
        // Note: remember to use password_hash in your registration file to store the hashed passwords.
        if (password_verify($_POST['password'], $password)) {
            // Verification success! User has logged-in!
            // Create sessions, so we know the user is logged in, they basically act like cookies but remember the data on the server.
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $usern;
            $_SESSION['id'] = $id;

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

if (isset($_POST['remember_me'])) {
    $token = bin2hex(random_bytes(32)); // Generate a secure token
    $expiration = date('Y-m-d H:i:s', strtotime('+30 days')); // Set expiration for 30 days

    // Store token and expiration in database
    $stmt = $con->prepare("UPDATE accounts SET remember_token = ?, remember_expiration = ? WHERE id = ?");
    $stmt->bind_param("ssi", $token, $expiration, $id);
    $stmt->execute();

    // Store token in the cookie
    setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // Secure and HttpOnly
}

?>