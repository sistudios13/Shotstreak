<?php
session_start();

include("db/db_connect.php");

setcookie('remember_me', '', time() - 3600, '/'); // Delete the cookie
$stmt = $con->prepare("UPDATE accounts SET remember_token = NULL, remember_expiration = NULL WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
session_destroy();

header('Location: index.php');