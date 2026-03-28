<?php
require 'db/db_connect.php';

function respondError(string $message, string $fallback = 'index.php')
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password_input = $_POST['password'] ?? '';
$coach_id = isset($_POST['coach_id']) ? (int) $_POST['coach_id'] : 0;
$token = trim($_POST['invite_token'] ?? '');

if ($username === '' || $password_input === '' || $email === '' || $coach_id <= 0 || $token === '') {
    respondError('Please complete the registration form', 'index.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondError('Invalid email format', 'index.php');
}

if (!preg_match('/^[a-zA-Z0-9 ]+$/', $username)) {
    respondError('Invalid registration', 'index.php');
}

if (strlen($password_input) > 20 || strlen($password_input) < 5) {
    respondError('Password must be between 5 and 20 characters long', 'index.php');
}

if (strlen($username) > 50 || strlen($username) < 2) {
    respondError('Name must be between 2 and 50 characters long', 'index.php');
}

if (strlen($email) > 200) {
    respondError('Email must be less than 200 characters long', 'index.php');
}

try {
    $pdo = new PDO("mysql:host=$DATABASE_HOST;dbname=$DATABASE_NAME", $DATABASE_USER, $DATABASE_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Player registration DB connection failed: ' . $e->getMessage());
    respondError('Database error', 'index.php');
}

if ($stmt = $con->prepare('SELECT coach_id, player_name, player_email FROM invitations WHERE token = ?')) {
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $invitation = $result->fetch_assoc();
    $stmt->close();

    if (!$invitation || (int) $invitation['coach_id'] !== $coach_id || $invitation['player_name'] !== $username || $invitation['player_email'] !== $email) {
        respondError('Invalid registration', 'index.php');
    }
} else {
    error_log('Invitation lookup failed: ' . $con->error);
    respondError('Database error', 'index.php');
}

if ($stmt = $con->prepare('SELECT id FROM accounts WHERE email = ?')) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        respondError('User already exists', 'index.php');
    }

    $stmt->close();
} else {
    error_log('Account lookup failed: ' . $con->error);
    respondError('Database error', 'index.php');
}

$hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

try {
    $playerInsert = $pdo->prepare('INSERT INTO players (player_name, email, password, coach_id) VALUES (:player_name, :email, :password, :coach_id)');
    $playerInsert->execute([
        ':player_name' => $username,
        ':email' => $email,
        ':password' => $hashed_password,
        ':coach_id' => $coach_id,
    ]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        respondError('User already exists', 'index.php');
    }
    error_log('Player insert failed: ' . $e->getMessage());
    respondError('Database error', 'index.php');
}

if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, user_type) VALUES (?, ?, ?, "player")')) {
    $stmt->bind_param('sss', $username, $hashed_password, $email);
    if (!$stmt->execute()) {
        error_log('Accounts insert failed: ' . $stmt->error);
        respondError('Database error', 'index.php');
    }
    $stmt->close();
} else {
    error_log('Accounts insert prepare failed: ' . $con->error);
    respondError('Database error', 'index.php');
}

if ($stmt = $con->prepare('UPDATE invitations SET status = "accepted" WHERE token = ?')) {
    $stmt->bind_param('s', $token);
    if (!$stmt->execute()) {
        error_log('Invitation update failed: ' . $stmt->error);
        respondError('Database error', 'index.php');
    }
    $stmt->close();
} else {
    error_log('Invitation update prepare failed: ' . $con->error);
    respondError('Database error', 'index.php');
}

$con->close();
redirectSuccess('success.php?b=login.php');
exit();




