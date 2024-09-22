<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}
if ($_SESSION['type'] != 'player') {
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
$userid = $_SESSION['player_id'];
$coach_id = $_SESSION['coach_id'];
//Get current

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
// Insert or update the shots data for the user
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