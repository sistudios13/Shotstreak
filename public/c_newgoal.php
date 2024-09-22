<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}
if ($_SESSION['type'] != 'coach') {
	header('Location: index.html');
	exit;
}
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'shotstreak';
$conn = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

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