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

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shot Goal - Shotstreak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>

    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" /></head>
    <body class="bg-lightgray dark:bg-almostblack">
    <!-- Navbar -->
    <nav id="test" class="bg-white dark:bg-darkslate shadow-md py-2 md:py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <a href="index.php" class="text-2xl font-bold text-coral">
                <img src="assets/isoLogo.svg" class="size-12 md:hidden" alt="logo">
                <span class="hidden md:block">Shotstreak</span>
            </a>
            <div class="flex items-center gap-2">
                <button id="theme-toggle"><img class="size-5 dark:hidden" src="assets/dark.svg" alt="dark"><img class="size-5 hidden dark:block" src="assets/light.svg" alt="dark"></button>
                
                <a href="coach_dashboard.php" class="text-almostblack dark:text-lightgray md:hover:text-coral">Dashboard</a>
                <a href="logout.php" class="text-almostblack dark:text-lightgray md:hover:text-coral">Logout</a>
            </div>
        </div>
    </nav>

        <!-- Registration Form Container -->
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white dark:bg-darkslate p-8 rounded-lg shadow-lg max-w-md w-full">
                <!-- Logo -->
                <div class="text-center mb-6">
                    <img src="assets/isoLogo.svg" alt="Shotstreak Logo" class="mx-auto h-16">
                    <h1 class="text-2xl font-bold text-almostblack dark:text-lightgray mt-4">Daily Shot Goal</h1>
                </div>
    
                <!-- Registration Form -->
                <form action="c_newgoal.php" method="POST" class="flex flex-col justify-center gap-4">
                    
                    <label for="shotgoal" class="block text-lg text-gray-700 dark:text-lightgray">Enter the new shot goal for your team:</label>
                    <input type="number" name="shotgoal" id="shotgoal" placeholder="100" class="mt-1 p-2 w-10/12 dark:bg-darkslate dark:text-lightgray mx-auto border rounded-md focus-visible:outline-coral" required min="1" max="999">
    
    
    
                    <!-- Submit Button -->
                    <button type="submit" class="w-full dark:text-lightgray bg-coral text-white py-2 rounded-md font-semibold hover:bg-coralhov transition-colors">Submit</button>
                </form>
                
                <!-- Already have an account -->
                <div class="text-center mt-4">
                    <p class="text-sm text-gray-600"> <a href="coach_dashboard.php" class="text-coral font-semibold">Back to Dashboard</a></p>
                </div>
            </div>
        </div>
        <footer class="bg-lightgray py-8 text-almostblack dark:text-lightgray dark:bg-almostblack static bottom-0 left-0 w-full">
          <p class="text-sm text-center">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
        </footer>
        <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;

        themeToggleBtn.addEventListener('click', () => {
            if (htmlElement.classList.contains('dark')) {
            htmlElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
            } else {
            htmlElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
            }
        });

        // Check local storage for theme preference on page load
        if (localStorage.getItem('theme') === 'dark') {
            htmlElement.classList.add('dark');
        }
    </script>
    </body>
    </html>


