<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}
if ($_SESSION['type'] != 'user') {
    header('Location: index.php');
    exit;
}
require 'db/db_connect.php';
$conn = $con;
function setDailyGoal($conn, $user_id, $shots_goal) {
    $query = "INSERT INTO user_goals (user_id, shots_goal) 
              VALUES (?, ?)
              ON DUPLICATE KEY UPDATE shots_goal = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $shots_goal, $shots_goal);
    $stmt->execute();

    $query = "UPDATE user_shots SET goal = ? WHERE user_id = ? AND shot_date = CURDATE()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $shots_goal, $user_id);
    $stmt->execute();

    $stmt->close();
    header("Location: home.php");
}

setDailyGoal($conn, $_SESSION['id'], $_POST['shotgoal']);