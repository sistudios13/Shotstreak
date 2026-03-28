<?php
require 'db/db_connect.php';

function respondError(string $message, string $fallback = 'register.php')
{
	$location = 'error.php?a=' . rawurlencode($message) . '&b=' . rawurlencode($fallback);
	if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
		header('HX-Redirect: ' . $location);
	} else {
		header('Location: ' . $location);
	}
	exit();
}

function redirectSuccess(string $location)
{
	if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
		header('HX-Redirect: ' . $location);
	} else {
		header('Location: ' . $location);
	}
	exit();
}

if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
	respondError('Please complete the registration form', 'register.php');
}

$username = trim($_POST['username']);
$password_input = $_POST['password'];
$email = trim($_POST['email']);

if ($username === '' || $password_input === '' || $email === '') {
	respondError('Please complete the registration form', 'register.php');
}

if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
	respondError('Invalid Username', 'register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	respondError('Invalid email format', 'register.php');
}

if (strlen($password_input) > 20 || strlen($password_input) < 5) {
	respondError('Password must be between 5 and 20 characters long', 'register.php');
}

if (strlen($username) > 20 || strlen($username) < 2) {
	respondError('Username must be between 2 and 20 characters long', 'register.php');
}

if (strlen($email) > 200) {
	respondError('Email must be less than 200 characters long', 'register.php');
}

$token = bin2hex(random_bytes(50));

if ($stmt = $con->prepare('SELECT id FROM accounts WHERE username = ? OR email = ?')) {
	$stmt->bind_param('ss', $username, $email);
	$stmt->execute();
	$stmt->store_result();

	if ($stmt->num_rows > 0) {
		respondError('User already exists', 'register.php');
	}

	$stmt->close();
} else {
	error_log('Register user select prepare failed: ' . $con->error);
	respondError('Database error', 'register.php');
}

if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, user_type, verification) VALUES (?, ?, ?, "user", ?)')) {
	$password_hash = password_hash($password_input, PASSWORD_DEFAULT);
	$stmt->bind_param('ssss', $username, $password_hash, $email, $token);

	if (!$stmt->execute()) {
		error_log('Register user insert failed: ' . $stmt->error);
		respondError('Database error', 'register.php');
	}

	$userid = $con->insert_id;
	$stmt->close();
} else {
	error_log('Register user insert prepare failed: ' . $con->error);
	respondError('Database error', 'register.php');
}

if ($stmt_goal = $con->prepare('INSERT INTO user_goals (user_id, goal_date, shots_goal) VALUES (?, CURDATE(), 100)')) {
	$stmt_goal->bind_param('i', $userid);

	if (!$stmt_goal->execute()) {
		error_log('Insert user_goals failed: ' . $stmt_goal->error);
		respondError('Database error', 'register.php');
	}

	$stmt_goal->close();
} else {
	error_log('Prepare user_goals failed: ' . $con->error);
	respondError('Database error', 'register.php');
}

$con->close();
redirectSuccess('success.php?b=login.php');
exit();
?>