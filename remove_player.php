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



$player_id = $_POST['player_id'];
$coach_id = $_SESSION['coach_id'];
$player_email = $_POST['player_email'];


if ($stmt = $con->prepare('DELETE FROM players WHERE id = ? AND coach_id = ?')) {
    $stmt->bind_param('ii', $player_id, $coach_id);
    $stmt->execute();
} else {
    exit('An error occured');
}
if ($stmt = $con->prepare('DELETE FROM accounts WHERE user_type = "player" AND email = ?')) {
    $stmt->bind_param('i', $player_email);
    $stmt->execute();
} else {
    exit('An error occured');
}


header('Location: coach_dashboard.php');