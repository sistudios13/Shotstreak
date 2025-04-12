<?php

session_start();
include 'db/db_connect.php';

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.php');
	exit;
}

if ($_SESSION['type'] != 'coach') {
	header('Location: index.php');
	exit;
}


$user_id = $_POST['player_id'];
$conn = $con;


$stmt = $conn->prepare("SELECT * FROM players WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$player_info = $res->fetch_assoc();

if ($player_info["coach_id"] != $_SESSION['coach_id']) {
	exit('No Acess!');
}

$name = $player_info['player_name'];


// get user data
$sql = "SELECT * FROM shots WHERE player_id = ? ORDER BY shot_date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// CSV headers
header('Content-Type: text/csv; charset=utf-8');
$filename = 'Content-Disposition: attachment; filename=';
$filename .= $name . '_shotstreak.csv';
header($filename);

$output = fopen('php://output', 'w');

fputcsv($output, ['Date', 'Shots Taken', 'Shots Made', 'Shooting Percentage']);

// data rows
while ($row = $result->fetch_assoc()) {
	$percentage = ($row['shots_made'] / $row['shots_taken']) * 100;
	fputcsv($output, [$row['shot_date'], $row['shots_taken'], $row['shots_made'], round($percentage, 2) . '%']);
}

fclose($output);
$stmt->close();
?>