<?php
// export.php
session_start();
include 'db/db_connect.php'; // Your database connection

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.php');
	exit;
}

if ($_SESSION['type'] != 'user') {
	header('Location: index.php');
	exit;
}


$user_id = $_SESSION['id'];
$conn = $con;

// Query to fetch the user's data
$sql = "SELECT * FROM user_shots WHERE user_id = ? ORDER BY shot_date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Output CSV headers
header('Content-Type: text/csv; charset=utf-8');
$filename = 'Content-Disposition: attachment; filename=';
$filename .= $_SESSION['name'] .'_shotstreak.csv';
header($filename);


// Open output stream
$output = fopen('php://output', 'w');

// Output column headings
fputcsv($output, ['Date', 'Shots Taken', 'Shots Made', 'Shooting Percentage']);

// Output user data rows
while ($row = $result->fetch_assoc()) {
    $percentage = ($row['shots_made'] / $row['shots_taken']) * 100;
    fputcsv($output, [$row['shot_date'], $row['shots_taken'], $row['shots_made'], round($percentage, 2) . '%']);
}

fclose($output);
$stmt->close();
?>
