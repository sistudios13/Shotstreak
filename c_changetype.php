<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.php');
	exit;
}
if ($_SESSION['type'] != 'coach') {
	header('Location: index.php');
	exit;
}
require 'db/db_connect.php';
$conn = $con;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: coachprofile.php');
    exit;
}
$goal_type = $_POST['goal_type'] ?? '';

$allowed_types = ['make', 'take'];
if (!in_array($goal_type, $allowed_types, true)) {
    header('Location: coachprofile.php');
    exit;
}

// update goal type

$stmt = $conn->prepare('UPDATE coaches SET goal_type = ? WHERE coach_id = ?');
$stmt->bind_param('ss', $goal_type, $_SESSION['coach_id']);
$stmt->execute();

$new = $conn->prepare("UPDATE shots JOIN players ON (shots.player_id = players.id)
                SET goal_type = ?
              WHERE coach_id = ? AND shot_date = CURDATE()");
$new->bind_param("si",$goal_type, $_SESSION["coach_id"]);
$new->execute();

header("Location: coachprofile.php");