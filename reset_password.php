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

        <!-- Registration Form Container -->
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
                <!-- Logo -->
                <div class="text-center mb-6">
                    <a href="index.php"><img src="assets/isoLogo.svg" alt="Shotstreak Logo" class="mx-auto h-16"></a>
                    <h1 class="text-2xl font-bold text-almostblack mt-4">Reset Your Password</h1>
                </div>
    
                <!-- Registration Form -->
                <form id="registerForm" class="space-y-4" action="process_pass.php" method="post" autocomplete="off">
                    <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
                    <!-- Name Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password:</label>
                        <input type="password" name="password"  id="password" minlength="5" maxlength="20" class="mt-1 p-2 w-full border rounded-md focus-visible:outline-coral" required>
                    </div>
    

    
                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-coral md:hover:bg-coralhov text-white py-2 rounded-md font-semibold hover:bg-coral-red-light transition-colors">Reset Password</button>
                </form>
    
            </div>
        </div>
        <footer class="bg-darkslate py-8 text-white">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="footer-links flex flex-col justify-center items-center">
            <?php if ($logged): ?>
              <a href="index.php" class="block text-center">Home</a>
            <?php else: ?>
              <a href="index.php" class="block mb-2 text-center">Home</a>
              <a href="register.php" class="block mb-2 text-center">Register</a>
              <a href="login.php" class="block mb-2 text-center">Login</a>
              <a href="support.php" class="block mb-2 text-center">Support</a>
            <?php endif;?>
          </div> 
          <div class="text-center flex flex-col justify-center items-center">
            <p class="text-xs">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
            <p class="text-xs">Website Created by Simon Papp - <a target="_blank" class="font-bold" href="https://simonsites.com">SimonSites</a></p>
          </div>
        </div>
      </footer>
    </body>
    </html>