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
    <header id="navbar" class="sticky shadow-md bg-white dark:bg-darkslate  top-0 w-full z-20">
        <nav class="flex justify-between lg:container mx-auto px-4 lg:px-6 py-3 lg:py-0 " x-data="{isOpen : false, current: 1}" @click.outside="() => { if(window.innerWidth < 1024) {isOpen = false} }" x-init="if(window.innerWidth >= 1024) {isOpen = true}">
            <a href="index.php" class="text-2xl font-semibold text-coral">
                <img src="assets/isoLogo.svg" class="size-12 lg:size-14 lg:my-2" alt="Shotstreak">
            </a>
            <!-- Menu Button -->
            <div id="bars" class="flex items-center lg:hidden">
                <button @click="isOpen = !isOpen" class="flex flex-col gap-1 items-center px-3 pr-0 py-2 text-gray-500 border-0 rounded">
                    <div id="bar1" class="w-5 rounded h-0.5 bg-almostblack dark:bg-lightgray transition-all" x-bind:class="{ '-rotate-45 translate-y-1.5 bg-coral dark:bg-coral': isOpen }"></div>
                    <div id="bar1" class="w-5 rounded h-0.5 bg-almostblack dark:bg-lightgray transition-all" x-bind:class="{ 'opacity-0': isOpen }"></div>
                    <div id="bar1" class="w-5 rounded h-0.5 bg-almostblack dark:bg-lightgray transition-all" x-bind:class="{ 'rotate-45 -translate-y-1.5 bg-coral dark:bg-coral': isOpen }"></div>
                </button>
            </div>
            <ul class="absolute shadow-md mt-[70px] lg:py-3 text-almostblack dark:text-lightgray bg-white dark:bg-darkslate pb-8 flex-col items-end flex w-full lg:static top-0 right-0 p-4 lg:text-lg float-right gap-4 lg:p-0 lg:justify-end lg:items-center lg:flex-row lg:shadow-none lg:mt-0 text-xl" x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0  translate-x-12"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 translate-x-4">
                <li><a href="index.php" class="cursor-pointer w-full text-right lg:hover:text-coral">Dashboard</a></li>
                <li><a href="coachprofile.php" class="cursor-pointer lg:hover:text-coral">Profile</a></li>
                <li><a href="logout.php" class="cursor-pointer lg:hover:text-coral">Logout</a></li>
                <li class="h-[24px]"><button id="theme-toggle"><img class="size-6 dark:hidden" src="assets/dark.svg" alt="dark"><img class="size-6 hidden dark:block" src="assets/light.svg" alt="dark"></button></li>
            </ul>
        </nav>
    </header> 
    
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