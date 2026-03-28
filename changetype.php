<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.php');
	exit;
}
if ($_SESSION['type'] != 'user') {
	header('Location: index.php');
	exit;
}
require 'db/db_connect.php';
$conn = $con;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}
$goal_type = $_POST['goal_type'] ?? '';

$allowed_types = ['make', 'take'];
if (!in_array($goal_type, $allowed_types, true)) {
    header('Location: profile.php');
    exit;
}

// update goal type

$stmt = $conn->prepare('UPDATE user_goals SET goal_type = ? WHERE user_id = ?');
$stmt->bind_param('ss', $goal_type, $_SESSION['id']);
$stmt->execute();

$new = $conn->prepare("UPDATE user_shots SET goal_type = ?
              WHERE user_id = ? AND shot_date = CURDATE()");
$new->bind_param("si",$goal_type, $_SESSION["id"]);
$new->execute();

header("Location: profile.php");