<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.php');
	exit;
}
if ($_SESSION['type'] != 'player') {
	header('Location: index.php');
	exit;
}
require 'db/db_connect.php';
$conn = $con;
$userid = $_SESSION['player_id'];
$coach_id = $_SESSION['coach_id'];
//Get current
if ($_POST['shotsmade'] > $_POST['shotstaken']) {
	header("Location: error.php?a=Shots made cannot me greater than shots taken!&b=home.php");
	exit();
}
//Get master Goal
$stmt = $conn->prepare('SELECT goal FROM coaches WHERE coach_id = ?');
$stmt->bind_param('i', $coach_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$master_goal = $row['goal'];

$sql = 'SELECT shots_made, shots_taken FROM shots WHERE player_id = ? AND shot_date = CURDATE()';
$getshots = $conn->prepare($sql);
$getshots->bind_param('i', $userid);
$getshots->execute();
$gotshots = $getshots->get_result();
$shots = $gotshots->fetch_assoc();
$today_shots_made = $shots['shots_made'];
$today_shots_taken = $shots['shots_taken'];

$added_taken = $today_shots_taken + $_POST['shotstaken'];
$added_made = $today_shots_made + $_POST['shotsmade'];
if (!is_null($today_shots_made)){ //DUP
	$query = "UPDATE shots SET shots_taken = ?, shots_made = ?
            WHERE player_id = ? AND shot_date = CURDATE()";
	$stmt = $conn->prepare($query);
	$stmt->bind_param("iii",  $added_taken, $added_made, $userid);
	$stmt->execute();
	$stmt->close();
	
}

if (is_null($today_shots_made)){ //NEW
	$query = "INSERT INTO shots (player_id, shot_date, shots_taken, shots_made) 
            VALUES (?, CURDATE(), ?, ?)";
	$stmt = $conn->prepare($query);
	$stmt->bind_param("iii", $userid, $_POST['shotstaken'], $_POST['shotsmade']);
	$stmt->execute();

	$query2 = "UPDATE shots SET goal = ? WHERE player_id = ? AND shot_date = CURDATE()";
    $stmt = $conn->prepare($query2);
    $stmt->bind_param("ii",  $master_goal, $userid);
    $stmt->execute();
	$stmt->close();
}

header('Location: player_dashboard.php');