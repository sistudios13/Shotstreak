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
    <title>Invite player - Shotstreak</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>
    
    <link rel="stylesheet" href="main.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" />
</head>
<body class="bg-lightgray dark:bg-almostblack min-h-screen">

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
    
    <div class="container mx-auto px-6 py-8 pb-0">
        <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md flex flex-col gap-4">
            <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Invite a Player</h3>
            <div>
            <form class="space-y-4" action="invite.php" method="POST" autocomplete="off">
                    <!-- Name Input -->
                    <div>
                        <label for="player_name" class="block text-sm font-medium dark:text-lightgray text-gray-700">Player's Name:</label>
                        <input type="text" name="player_name" minlength="2"  maxlength="50" class="mt-1 p-2 w-full border dark:border-lightgray rounded-md dark:text-lightgray dark:bg-darkslate dark: focus-visible:outline-coral" required>
                    </div>



    
                    <!-- Email Input -->
                    <div>
                        <label for="player_email" class="block text-sm font-medium dark:text-lightgray text-gray-700">Player's Email</label>
                        <input type="email" maxlength="200" name="player_email"  class="mt-1 p-2 w-full border dark:border-lightgray rounded-md dark:text-lightgray dark:bg-darkslate focus-visible:outline-coral" required>
                    </div>
    

    
                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-coral md:hover:bg-coralhov text-white py-2 rounded-md font-semibold hover:bg-coral-red-light transition-colors">Send Invite</button>
                </form>
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