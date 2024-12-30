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
    <title>Login - Shotstreak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>


    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/htmx.org"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
    <body class="bg-light-gray">

        <!-- Registration Form Container -->
        <div class="flex items-center justify-center min-h-screen" x-data="{loading : false}">
            <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
                <!-- Logo -->
                <div class="text-center mb-6">
                    <a href="index.php"><img src="assets/isoLogo.svg" alt="Shotstreak Logo" class="mx-auto h-16"></a>
                    <h1 class="text-2xl font-bold text-almostblack mt-4">Sign In</h1>
                </div>
    
                <!-- Registration Form -->
                <form hx-post="authenticate.php" hx-trigger="submit" hx-target="#response" hx-swap="innerHTML" @submit="loading = true" class="space-y-4"  autocomplete="on">
                    <!-- Name Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                        <input type="email" name="email"  id="email" maxlength="200" class="mt-1 p-2 w-full border rounded-md focus-visible:outline-coral" required>
                    </div>



    
                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password"  id="password" class="mt-1 p-2 w-full border rounded-md focus-visible:outline-coral" required>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center gap-1">
                        <input type="checkbox" name="remember_me" id="remember_me" class="accent-coral">
                        <label for="remember_me" class=" select-none text-sm font-medium text-gray-700">Remember Me</label>
                    </div>
    

    
                    <!-- Submit Button -->
                    <button type="submit"  class="w-full bg-coral md:hover:bg-coralhov text-white py-2 rounded-md font-semibold hover:bg-coral-red-light transition-colors">Login</button>
                </form>
    
                <!-- Already have an account -->
                <div class="text-center mt-4">
                    <p class="text-sm text-gray-600">Don't have an account? <a href="register.php" class="text-coral font-semibold">Register</a></p>
                    <p class="text-sm text-gray-600"><a href="resetpassword.php" class="text-coral font-semibold">Forgot Password?</a></p>
                </div>
            </div>

             <!-- Response Div -->
            <div id="response"></div>


            <div class="fixed top-1/2 " x-show="loading" x-cloak>
                <svg  width="40" height="40" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><style>.spinner_qM83{animation:spinner_8HQG 1.05s infinite}.spinner_oXPr{animation-delay:.1s}.spinner_ZTLf{animation-delay:.2s}@keyframes spinner_8HQG{0%,57.14%{animation-timing-function:cubic-bezier(0.33,.66,.66,1);transform:translate(0)}28.57%{animation-timing-function:cubic-bezier(0.33,0,.66,.33);transform:translateY(-6px)}100%{transform:translate(0)}}</style><circle class="spinner_qM83" fill="#ff6f61" cx="4" cy="12" r="3"/><circle fill="#ff6f61" class="spinner_qM83 spinner_oXPr" cx="12" cy="12" r="3"/><circle fill="#ff6f61" class="spinner_qM83 spinner_ZTLf" cx="20" cy="12" r="3"/></svg>
            </div>
        </div>
        <footer class="bg-darkslate py-8 text-white">
            <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="footer-links flex flex-col justify-center items-center">
                <a href="index.php" class="block mb-2 text-center">Home</a>
                <a href="register.php" class="block mb-2 text-center">Register</a>
                <a href="login.php" class="block mb-2 text-center">Login</a>
                <a href="support.php" class="block mb-2 text-center">Support</a>
                <!-- Add more links -->
              </div> 
              <div class="text-center flex flex-col justify-center items-center">
                <p class="text-xs">© 2024 Shotstreak. All rights reserved.</p>
                <p class="text-xs">Website Created by Simon Papp - <a target="_blank" class="font-bold" href="https://simonsites.com">SimonSites</a></p>
              </div>
            </div>
          </footer>
    </body>
    </html>