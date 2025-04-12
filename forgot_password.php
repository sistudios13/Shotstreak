<?php

require 'db/db_connect.php';
$conn = $con;

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(50)); // Generate random token
    $expires = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token valid for 1 hour

    // Check if the email exists
    $query = "SELECT * FROM accounts WHERE email = ?";
    $stmt = $conn->prepare(query: $query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // update reset token and expiration date
        $update = "UPDATE accounts SET reset_token = ?, token_expiration = ? WHERE email = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();


        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Shotstreak <shotstreak@shotstreak.ca> \r\n";
        $reset_link = "https://localhost/shotstreak/reset_password.php?token=$token";

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
                <h1>Reset Your Shotstreak Password</h1>
                <img title='logo' src='https://shotstreak.simonsites.com/assets/isoLogo.svg' alt='Logo' height='200' width='200'>
            </div>
            
            <div class='email-body'>
                <p><b>Reset Your Shotstreak Password</b></p>
                
                <a href='$reset_link' class='cta-button'>Reset Password</a>
                
                <p>If you did not expect this email, feel free to ignore it.</p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Shotstreak. All rights reserved.</p>
            </div>
        </div>

        </body>
        </html>

        ";
        mail($email, "Shotstreak Password Reset", $message, $headers);
        header("Location: success.php?b=reset.php");

    } else {
        header("Location: error.php?a=User Not Found&b=login.php");
        exit();
    }
}