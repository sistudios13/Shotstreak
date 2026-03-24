<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
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

// Insert or update the daily goal for the user

$stmt = $conn->prepare('UPDATE coaches SET goal = ? WHERE coach_id = ?');
$stmt->bind_param('is', $_POST['shotgoal'], $_SESSION['coach_id']);
$stmt->execute();

$new = $conn->prepare("UPDATE shots JOIN players ON (shots.player_id = players.id)
                SET goal = ?
              WHERE coach_id = ? AND shot_date = CURDATE()");
$new->bind_param("ii", $_POST["shotgoal"], $_SESSION["coach_id"]);
$new->execute();


header("Location: coach_dashboard.php");