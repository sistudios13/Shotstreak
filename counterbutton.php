<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.php');
	exit;
}
if ($_SESSION['type'] != 'user') {
	header('Location: index.php');
	exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Shots - Shotstreak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" />
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
            touch-action: manipulation;
        }
    </style>
</head>
<body class="bg-lightgray h-screen dark:bg-almostblack">
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
                <li><a href="profile.php" class="cursor-pointer lg:hover:text-coral">Profile</a></li>
                <li><a href="logout.php" class="cursor-pointer lg:hover:text-coral">Logout</a></li>
                <li class="h-[24px]"><button id="theme-toggle"><img class="size-6 dark:hidden" src="assets/dark.svg" alt="dark"><img class="size-6 hidden dark:block" src="assets/light.svg" alt="dark"></button></li>
            </ul>
        </nav>
    </header> 
    <div x-data="{show : false}" class="flex flex-col bg-white dark:bg-darkslate justify-center mt-4 gap-4 items-center">
        <h1 class="font-bold pt-4 text-coral text-2xl">How It Works</h1>
        <span @click="show = !show " class="pb-4 cursor-pointer dark:text-lightgray">Show</span>
        <ul x-show="show" x-collapse class="w-11/12 text-lg flex flex-col py-4 gap-2 dark:text-lightgray">
            <li><b class="text-coral">1. </b>Take a shot</li>
            <li><b class="text-coral">2. </b>Your score will be kept and your shooting percentage will be automatically calculated.</li>
            <li><b class="text-coral">3. </b>When You're done, press the submit button. It will automatically submit your shot data.</li>
        </ul>
    </div>
<div class="bg-white mb-4 dark:text-lightgray dark:bg-darkslate p-8 rounded-lg shadow-md mt-6 mx-auto w-11/12 max-w-md text-center">
        <h1 class="text-3xl font-bold text-coral mb-6">Shot Counter</h1>

        <form action="input_daily.php" method="POST">
            <div class="flex justify-center gap-3">

            
            

            <div class="mb-6">
                <h2 class="text-xl font-semibold">Shots Made</h2>
                <p id="shotsMade" class="text-2xl font-bold">0</p>
                <input type="hidden" id="shotsMadeInput" name="shotsmade" value="0">
                
            </div>
            <div class="mb-4">
                <h2 class="text-xl font-semibold">Shots Taken</h2>
                <p id="shotsTaken" class="text-2xl font-bold">0</p>
                <input type="hidden" id="shotsTakenInput" name="shotstaken" value="0">
                
            </div>
            </div>
            <div>
                <h2 class="text-xl font-semibold">Shooting Percentage</h2>
                <p id="shootingPercentage" class="text-2xl font-bold">0%</p>
            </div>

            <button type="button" onclick="incrementShotsTaken()" class="bg-coral text-white text-xl font-bold size-32 px-4 py-2 rounded mt-2 active:bg-golden">
                    Missed <br>Shot
            </button>

            <button type="button" onclick="incrementShotsMade()" class="bg-coral text-white text-xl font-bold size-32 px-4 py-2 rounded mt-2 active:bg-golden">
                    Made <br> Shot
            </button>

            <div>
            <button type="button" onclick="resetCounts()" class="border border-coral font-bold text-coral px-4 py-2 rounded mt-4 ">
                Reset
            </button>

            <button type="submit" class="bg-golden text-almostblack px-4 py-2 rounded mt-4 hover:bg-coral-red">
                Submit
            </button>
            </div>
        </form>
        <div class="text-center mt-4">
                    <p class="text-sm text-gray-600"> <a href="home.php" class="text-coral font-semibold">Back to Dashboard</a></p>
                </div>
    </div>
    <footer class="bg-lightgray py-8 text-almostblack dark:text-lightgray dark:bg-almostblack static bottom-0 left-0 w-full">
          <p class="text-sm text-center">© 2024 Shotstreak. All rights reserved.</p>
    </footer>
    <script>
        let shotsTaken = 0;
        let shotsMade = 0;

        function incrementShotsTaken() {
            shotsTaken++;
            updateDisplay();
        }

        function incrementShotsMade() {
                shotsMade++;
                shotsTaken++;
                updateDisplay();
        }

        function updateDisplay() {
            document.getElementById('shotsTaken').innerText = shotsTaken;
            document.getElementById('shotsMade').innerText = shotsMade;
            document.getElementById('shotsTakenInput').value = shotsTaken;
            document.getElementById('shotsMadeInput').value = shotsMade;

            const percentage = shotsTaken > 0 ? ((shotsMade / shotsTaken) * 100).toFixed(2) : 0;
            document.getElementById('shootingPercentage').innerText = `${percentage}%`;
        }

        function resetCounts() {
            shotsTaken = 0;
            shotsMade = 0;
            updateDisplay();
        }
    </script>
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