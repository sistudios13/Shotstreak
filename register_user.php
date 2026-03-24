<?php
// Change this to your connection info.
require 'db/db_connect.php';

// Now we check if the data was submitted, isset() function will check if the data exists.
if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
	// Could not get the data that should have been sent.
	exit('Please complete the registration form!');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
	// One or more values are empty.
	exit('Please complete the registration form');
}
// We need to check if the account with that username exists.
// Parameters

$token = bin2hex(random_bytes(50));

if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
	echo "<script>setTimeout(() => window.location.href = 'error.php?a=Invalid Username&b=register.php', 700);</script>";
    exit();
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	exit("Invalid email format."); //ADD ERROR PAGE
}

if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	exit('Password must be between 5 and 20 characters long!');
}

if (strlen($_POST['username']) > 20 || strlen($_POST['username']) < 2) {
	exit('Username must be between 2 and 20 characters long!');
}

if (strlen($_POST['email']) > 200) {
	exit('Email must be less than 200 characters long!');
}
//
if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ? OR email = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
	$stmt->bind_param('ss', $_POST['username'], $_POST['email']);
	$stmt->execute();
	$stmt->store_result();
	// Store the result so we can check if the account exists in the database.
	if ($stmt->num_rows > 0) {
		// Username already exists
		echo "<script>setTimeout(() => window.location.href = 'error.php?a=User already exists&b=register.php', 700);</script>";
            exit();
        
	} else {
		// Username doesn't exists, insert new account
        if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, user_type, verification) VALUES (?, ?, ?, "user", ?)')) {
            // We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->bind_param('ssss', $_POST['username'], $password, $_POST['email'], $token);
            $stmt->execute();	
        }
		else {
            // Something is wrong with the SQL statement, so you must check to make sure your accounts table exists with all three fields.
            echo 'Could not prepare statement!';
        }
		
		$stmt_id = $con->prepare('SELECT id FROM accounts WHERE username = ?');
		$stmt_id->bind_param('s', $_POST['username']);
		$stmt_id->execute();
		$get_id = $stmt_id->get_result();
		$userid = $get_id->fetch_assoc()['id'] ?? 0;

		$stmt_goal = $con->prepare('INSERT INTO user_goals (user_id, goal_date, shots_goal) VALUES (?, CURDATE(), 100)');
		$stmt_goal->bind_param('i', $userid);
		$stmt_goal->execute();
		echo "<script>setTimeout(() => window.location.href = 'success.php?b=login.php', 700);</script>";
        exit();
		}
		

	}
	
	
else {
	// Something is wrong with the SQL statement
	echo 'Could not prepare statement!';
}
$con->close();
?>