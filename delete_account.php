<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}
require 'db/db_connect.php';
$conn = $con;


// user type and user ID
$user_id = $_SESSION['id'];
$user_type = $_POST['user_type'];

if ($user_type == 'coach') {
    // delete all players linked to coach and their stats

    $coach_id = $_SESSION['coach_id'];

    $delete_coach = "DELETE FROM coaches WHERE coach_id = ?";
    $delete_players = "DELETE FROM players WHERE coach_id = ?";
    $delete_inv = "DELETE FROM invitations WHERE coach_id = ?";

    $stmt = $conn->prepare($delete_coach);
    $stmt->bind_param('i', $coach_id);
    $stmt->execute();

    $stmt = $conn->prepare($delete_players);
    $stmt->bind_param('i', $coach_id);
    $stmt->execute();

    $delete_user = "DELETE FROM accounts WHERE id = ?";

    $stmt = $conn->prepare($delete_user);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    echo "Coach account and all related data deleted successfully.";

} elseif ($user_type == 'player') {

    $player_id = $_SESSION['player_id'];

    // delete player and their shooting data
    $delete_player = "DELETE FROM players WHERE id = ?";
    $delete_shots = "DELETE FROM shots WHERE player_id = ?";

    $stmt = $conn->prepare($delete_player);
    $stmt->bind_param('i', $player_id);
    $stmt->execute();

    $stmt = $conn->prepare($delete_shots);
    $stmt->bind_param('i', $player_id);
    $stmt->execute();

    $delete_user = "DELETE FROM accounts WHERE id = ?";

    $stmt = $conn->prepare($delete_user);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    echo "Player account and stats deleted successfully.";

} else {
    // delete just the user account
    $delete_user = "DELETE FROM accounts WHERE id = ?";

    $stmt = $conn->prepare($delete_user);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    echo "User account deleted successfully.";
}

// Logout and redirect to the homepage
session_destroy();
header("Location: success.php?b=index.php");
exit();

