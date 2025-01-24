<?php

session_start();

// Redirect if not logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: index.php");
    exit();
}

require "../db/db_connect.php";

if (!isset($_POST['id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $uid = $_POST["id"];

    $stmt = $con->prepare('SELECT banned FROM accounts WHERE id = ?');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $sel = $stmt->get_result();
    $rel = $sel->fetch_assoc();
    $banned = $rel['banned'];

    $one = 1;
    $zero = 0;

    $stmt = $con->prepare('UPDATE accounts SET banned = ? WHERE id = ?');
    
    if($banned == 0) {
        $stmt->bind_param('ii', $one, $uid);
    } elseif($banned == 1) {
        $stmt->bind_param('ii', $zero, $uid);
    }

    $stmt->execute();
    header('Location: dashboard.php');
    exit;
}