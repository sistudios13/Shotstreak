<?php
require 'db/db_connect.php';

function respondError(string $message, string $fallback = 'coachreg.php')
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

try {
    $pdo = new PDO("mysql:host=$DATABASE_HOST;dbname=$DATABASE_NAME", $DATABASE_USER, $DATABASE_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Coach registration DB connection failed: ' . $e->getMessage());
    respondError('Database error', 'coachreg.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$coach_name = trim($_POST['coach_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password_input = $_POST['password'] ?? '';
$team_name = trim($_POST['team_name'] ?? '');

if ($coach_name === '' || $password_input === '' || $email === '' || $team_name === '') {
    respondError('Please complete the registration form', 'coachreg.php');
}

if (!preg_match('/^[a-zA-Z0-9 \-]+$/', $coach_name)) {
    respondError('Invalid Username', 'coachreg.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondError('Invalid email format', 'coachreg.php');
}

if (strlen($password_input) > 20 || strlen($password_input) < 5) {
    respondError('Password must be between 5 and 20 characters long', 'coachreg.php');
}

if (strlen($coach_name) > 50 || strlen($coach_name) < 2) {
    respondError('Username must be between 2 and 50 characters long', 'coachreg.php');
}

if (strlen($email) > 200) {
    respondError('Email must be less than 200 characters long', 'coachreg.php');
}

if (strlen($team_name) > 100) {
    respondError('Team name must be 100 characters or less', 'coachreg.php');
}

if ($stmt = $con->prepare('SELECT id FROM accounts WHERE username = ? OR email = ?')) {
    $stmt->bind_param('ss', $coach_name, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        respondError('User already exists', 'coachreg.php');
    }
    $stmt->close();
} else {
    error_log('Account lookup failed: ' . $con->error);
    respondError('Database error', 'coachreg.php');
}

$hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

try {
    $insertCoach = $pdo->prepare('INSERT INTO coaches (coach_name, email, password, team_name, goal) VALUES (:coach_name, :email, :password, :team_name, 100)');
    $insertCoach->execute([
        ':coach_name' => $coach_name,
        ':email' => $email,
        ':password' => $hashed_password,
        ':team_name' => $team_name,
    ]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        respondError('User already exists', 'coachreg.php');
    }
    error_log('Coach insert failed: ' . $e->getMessage());
    respondError('Database error', 'coachreg.php');
}

if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, user_type) VALUES (?, ?, ?, "coach")')) {
    $stmt->bind_param('sss', $coach_name, $hashed_password, $email);
    if (!$stmt->execute()) {
        error_log('Accounts insert failed: ' . $stmt->error);
        respondError('Database error', 'coachreg.php');
    }
    $stmt->close();
} else {
    error_log('Accounts insert prepare failed: ' . $con->error);
    respondError('Database error', 'coachreg.php');
}

$con->close();
redirectSuccess('success.php?b=login.php');
exit();

