<?php

session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.php');
	exit;
}
require 'db/db_connect.php';

// form validation
if (!isset($_POST['newpassword'])) {
	exit('Please complete the registration form!');
}

if (empty($_POST['newpassword'])) {
	exit('Please complete the registration form');
}

if ($stmt = $con->prepare('UPDATE accounts SET password = ? WHERE id = ?')) {
    $password = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);
    $stmt->bind_param('si', $password, $_SESSION['id']);
    $stmt->execute();
    
}

$stmt->close();
header('Location: logout.php')
?>