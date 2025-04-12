<?php

require 'db/db_connect.php';
$conn = $con;

if (isset($_POST['password'], $_POST['token'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $token = $_POST['token'];
    
    // Check if the token is valid and not expired
    $query = "SELECT * FROM accounts WHERE reset_token = ? AND token_expiration > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $update = "UPDATE accounts SET password = ?, reset_token = NULL, token_expiration = NULL WHERE reset_token = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("ss", $password, $token);
        $stmt->execute();
        
        header("Location: success.php?b=login.php");
    } else {
        header("Location: error.php?a=Invalid or expired token, try resetting again!&b=login.php");
    }
}
