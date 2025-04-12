<?php

require 'db/db_connect.php';

try {
    $pdo = new PDO("mysql:host=$DATABASE_HOST;dbname=$DATABASE_NAME", $DATABASE_USER, $DATABASE_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $coach_id = $_POST['coach_id'];
    $token = $_POST['invite_token'];

    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format."); //ADD ERROR PAGE
    }

    if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
        exit('Please complete the registration form!');
    }
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
        // One or more values are empty.
        exit('Please complete the registration form');
    }

    if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
        exit('Password must be between 5 and 20 characters long!');
    }

    if (strlen($_POST['username']) > 50 || strlen($_POST['username']) < 2) {
        exit('Name must be between 2 and 50 characters long!');
    }

    if (strlen($_POST['email']) > 200) {
        exit('Email must be less than 200 characters long!');
    }

    $cn = $con->prepare("SELECT * FROM invitations WHERE token = ?");
    $cn->bind_param('s', $token);
    $cn->execute();
    $cr = $cn->get_result();
    $ca = $cr->fetch_assoc();

    if ($ca['coach_id'] != $coach_id) {
        echo "<script>setTimeout(() => window.location.href = 'error.php?a=Invalid Registration&b=index.php', 700);</script>";
        exit();
    }

    if ($ca['player_name'] != $username) {
        echo "<script>setTimeout(() => window.location.href = 'error.php?a=Invalid Registration&b=index.php', 700);</script>";
        exit();
    }

    if ($ca['player_email'] != $email) {
        echo "<script>setTimeout(() => window.location.href = 'error.php?a=Invalid Registration&b=index.php', 700);</script>";
    }


    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE email = ?')) {
        $stmt->bind_param('s', $_POST['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Username already exists
            echo "<script>setTimeout(() => window.location.href = 'error.php?a=User already exists&b=index.php', 700);</script>";
            exit();
        } else {
            $stmt = $pdo->prepare("INSERT INTO players (player_name, email, password, coach_id) VALUES (:player_name, :email,
:password, :coach_id)");
            $stmt->bindParam(':player_name', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':coach_id', $coach_id);

            try {
                $stmt->execute();




                if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, user_type) VALUES (?, ?, ?, "player")')) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt->bind_param('sss', $username, $password, $email);
                    $stmt->execute();

                }

                if ($stmt = $con->prepare('UPDATE invitations SET status = "accepted" WHERE token = ?')) {
                    $stmt->bind_param('s', $token);
                    $stmt->execute();
                    echo "<script>setTimeout(() => window.location.href = 'success.php?b=login.php', 700);</script>";

                } else {
                    echo 'Could not prepare statement!'; // ERROR PAGE
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    echo "<script>setTimeout(() => window.location.href = 'error.php?a=User already exists&b=index.php', 700);</script>";
                    exit(); //ERROR PAGE
                } else {
                    die("An error occurred: " . $e->getMessage());
                }
            }
        }
    }
}


