<?php
require 'db/db_connect.php';

if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
	exit('Please complete the registration form!');
}
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
	// One or more values are empty.
	exit('Please complete the registration form');
}
// params
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
	$stmt->bind_param('ss', $_POST['username'], $_POST['email']);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		// Username already exists
		echo "<script>setTimeout(() => window.location.href = 'error.php?a=User already exists&b=register.php', 700);</script>";
		exit();

	} else {
		if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, user_type, verification) VALUES (?, ?, ?, "user", ?)')) {
			$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
			$stmt->bind_param('ssss', $_POST['username'], $password, $_POST['email'], $token);
			$stmt->execute();
		} else {
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


} else {
	echo 'Could not prepare statement!';
}
$con->close();
?>