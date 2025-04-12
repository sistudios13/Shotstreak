<?php

function autoLogin($con) {
    if (isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];

        // Find user
        $stmt = $con->prepare("SELECT id, user_type, username FROM accounts WHERE remember_token = ? AND remember_expiration > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->bind_result($id, $type, $usern);

        if ($stmt->fetch()) {
            // log in
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $usern;
            $_SESSION['id'] = $id;

            // different user types types
            if ($type === 'user') {
                
                $_SESSION['type'] = $type;
                echo "<script>window.location.href = 'home.php'</script>";
            }

            if ($type === 'coach') {
                
                $_SESSION['type'] = $type;
                $_SESSION['email'] = $_POST['email'];
                echo "<script>window.location.href = 'coach_dashboard.php'</script>";
            }

            if ($type === 'player') {
                $_SESSION['email'] = $_POST['email'];
                $_SESSION['type'] = $type;
                echo "<script>window.location.href = 'player_dashboard.php'</script>";
            }
        } else {
            // Token is invalid
            setcookie('remember_me', '', time() - 3600, '/'); // Delete the cookie
        }
    }
}


autoLogin($con);