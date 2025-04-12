<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['type'] != 'coach') {
    header('Location: index.php');
    exit;
}

require 'db/db_connect.php';
$conn = $con;


$coach_id = $_SESSION['coach_id'];
$coach_name = $_SESSION['name'];

if (!filter_var($_POST['player_email'], FILTER_VALIDATE_EMAIL)) {
    exit("Invalid email format."); //ADD ERROR PAGE
}

if (strlen($_POST['player_name']) > 20 || strlen($_POST['player_name']) < 2) {
    exit('Username must be between 2 and 50 characters long!');
}

if (strlen($_POST['player_email']) > 200) {
    exit('Email must be less than 200 characters long!');
}

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Shotstreak <shotstreak@shotstreak.ca> \r\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player_email = $_POST['player_email'];
    $player_name = $_POST['player_name'];

    $token = bin2hex(random_bytes(16));

    // invitation into database
    $query = "INSERT INTO invitations (coach_id, player_name, player_email, token) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $coach_id, $player_name, $player_email, $token);
    if ($stmt->execute()) {
        $invite_link = "https://localhost/shotstreak/acceptinvite.php?token=" . $token;
        // email
        $message = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta http-equiv='X-UA-Compatible' content='IE=edge'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Invitation to Join Shotstreak</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    text-align: center;
                    padding-bottom: 20px;
                }
                .email-header h1 {
                    color: #ff6b6b;
                }
                .email-body {
                    color: #333;
                    line-height: 1.6;
                }
                .email-body p {
                    margin: 10px 0;
                }
                .cta-button {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #ff6b6b;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                    margin-bottom: 20px;
                    font-size: 16px;
                }
                .cta-button:hover {
                    background-color: #e65a5a;
                }
                .footer {
                    text-align: center;
                    color: #999;
                    font-size: 12px;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>

        <div class='email-container'>
            <div class='email-header'>
                <h1>Join Shotstreak!</h1>
                <img title='logo' src='https://shotstreak.simonsites.com/assets/isoLogo.svg' alt='Logo' height='200' width='200'>
            </div>
            
            <div class='email-body'>
                <p><b>Hello, $player_name</b></p>
                <p>You’ve been invited by <b>$coach_name</b> to join Shotstreak, a basketball shot tracking platform that helps you monitor your daily shot goals and performance.</p>
                <p>To get started, simply click the link below to register and join your coach's team:</p>
                
                <a href='$invite_link' class='cta-button'>Join Shotstreak</a>
                
                <p>If you did not expect this email, feel free to ignore it.</p>
                <p>Looking forward to seeing you on the court!</p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2024 Shotstreak. All rights reserved.</p>
            </div>
        </div>

        </body>
        </html>

        ";

        if (mail($player_email, "You've been invited to join Shotstreak!", $message, $headers)) {
            header("Location: coach_dashboard.php");
        } else {
            header('Location: error.php?a=Email failed to send&b=inviteplayer.php');
            exit();
        }

    } else {
        header('Location: error.php?a=An error occurred&b=coach_dashboard.php');
        exit();
    }
}
?>