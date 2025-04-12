<?php
require 'db/db_connect.php';
$conn = $con;

session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: index.php');
	exit;
}

if ($_SESSION['type'] != 'user') {
	header('Location: index.php');
	exit;
}

if (isset($_GET['token'])) {
    
    $token = $_GET['token'];
    
    $query = "SELECT * FROM accounts WHERE verification = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update
        $update = "UPDATE accounts SET verified = 1 WHERE verification = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        header("Location: success.php?b=login.php");
    } else {
        header("Location: error.php?a=Invalid Token, try verifying again!&b=login.php");
    }
}
