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

$st = $conn->prepare('SELECT verified FROM accounts WHERE id = ?');
$st->bind_param('s', $_SESSION['id']);
$st->execute();
$str = $st->get_result();
$stf = $str->fetch_assoc();
$verified = $stf['verified'];

if ($verified == 0) {
    // get account info
    $query = "SELECT * FROM accounts WHERE id = ?";
    $stmt = $conn->prepare(query: $query);
    $stmt->bind_param("i", $_SESSION["id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $assoc = $result->fetch_assoc();
    
    if ($result->num_rows > 0) {
        $token = $assoc['verification'];
        $email = $assoc['email'];
        

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Shotstreak <shotstreak@shotstreak.ca> \r\n";


        // Send verification email
        $verify_link = "https://localhost/shotstreak/verify_email.php?token=$token";

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
                <h1>Verify Your Shotstreak Account Email</h1>
                <img title='logo' src='https://shotstreak.simonsites.com/assets/isoLogo.svg' alt='Logo' height='200' width='200'>
            </div>
            
            <div class='email-body'>
                <p><b>Verify your Shotstreak account email</b></p>
                
                <a href='$verify_link' class='cta-button'>Verify Email</a>
                
                <p>If you did not expect this email, feel free to ignore it.</p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Shotstreak. All rights reserved.</p>
            </div>
        </div>

        </body>
        </html>

        ";

        mail($email, "Shotstreak Email Verification", $message, $headers);
        
        header("Location: verify.php");
        exit();
        
    } else {
        header("Location: error.php?a=An error occurred&b=profile.php");
        exit();
    }
}

else {
    header("Location: index.php");
    exit();
}