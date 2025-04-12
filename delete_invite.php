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

$stmt = $con->prepare('UPDATE invitations SET status = "revoked" WHERE token = ?');
$stmt->bind_param('s', $_POST['token']);
$stmt->execute();
header('Location: coach_dashboard.php');