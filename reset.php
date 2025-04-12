<?php
include "validation/log_check.php";
include "db/db_connect.php";
include "validation/autolog.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Shotstreak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" />
</head>

<body class="bg-light-gray">

    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <div class="text-center mb-6">
                <a href="index.php"><img src="assets/isoLogo.svg" alt="Shotstreak Logo" class="mx-auto h-16"></a>
                <h2 class="text-xl font-bold text-almostblack mt-4">Email Sent!</h2>
            </div>
            <div>
                <p class="text-lg">
                    We've sent a password reset link to your email. Please check your inbox and follow the instructions to reset your password.
                    <br>
                    <br>
                <div class="flex gap-3 items-center">
                    <svg width="40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                        <path fill="#ff6f61" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24l0 112c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-112c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" />
                    </svg>
                    <b>Make sure to check your <span class="text-coral">spam and junk</span> inboxes!</b>
                </div>
                <br>
                <br>
                <span class="text-base">If you have not received an email, feel free to try again</span>
                </p>
            </div>

            <!-- Already have an account -->
            <div class="text-center mt-4">
                <p class="text-sm text-gray-600"> <a href="login.php" class="text-coral font-semibold">Login</a></p>
            </div>
        </div>
    </div>
    <footer class="bg-darkslate py-8 text-white">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="footer-links flex flex-col justify-center items-center">
                <a href="index.php" class="block mb-2 text-center">Home</a>
                <a href="register.php" class="block mb-2 text-center">Register</a>
                <a href="login.php" class="block mb-2 text-center">Login</a>
                <a href="support.php" class="block mb-2 text-center">Support</a>
            </div>
            <div class="text-center flex flex-col justify-center items-center">
                <p class="text-xs">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
                <p class="text-xs">Website Created by Simon Papp - <a target="_blank" class="font-bold" href="https://simonsites.com">Simon Sites</a></p>
            </div>
        </div>
    </footer>
</body>

</html>