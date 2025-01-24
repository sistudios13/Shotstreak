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
$user_id = $_SESSION['id'];
$stmt = $con->prepare('SELECT  verification, verified FROM accounts WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$result = $res->fetch_assoc();
// $verified = $res->fetch_assoc()['verified'];

if ($result['verified'] == 1) {
    header('Location: index.php');
	exit;
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verify Email - Shotstreak</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="tailwindextras.js"></script>
        <link rel="stylesheet" href="main.css">
        <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
        <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
        <link rel="shortcut icon" href="assets/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
        <meta name="apple-mobile-web-app-title" content="Shotstreak" />
        <link rel="manifest" href="assets/site.webmanifest" />
        <!-- Alpine Plugins -->
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
        <!-- Alpine Core -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    </head>
    <body class="bg-lightgray dark:bg-almostblack">
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
        <!-- Registration Form Container -->
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white dark:bg-darkslate p-8 rounded-lg shadow-lg max-w-md w-full">
                <!-- Logo -->
                <div class="text-center mb-6">
                    <a href="index.php"><img src="assets/isoLogo.svg" alt="Shotstreak Logo" class="mx-auto h-16"></a>
                    <h2 class="text-xl font-bold text-almostblack dark:text-lightgray mt-4">Verify Your email</h2>
                </div>
                <div>
                    
                    <p class="text-lg dark:text-lightgray">
                        We've sent you a verification email. Please check your inbox and follow the instructions in the email. 
                            <br>
                            <br>
                            <div class="flex gap-3 items-center">
                                <svg width="40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#ff6f61" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24l0 112c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-112c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg>
                                <b class="dark:text-lightgray">Make sure to check your <span class="text-coral">spam and junk</span> inboxes!</b>
                            </div>
                            <br>
                            <br>
                        <span class="dark:text-lightgray">If you have not received an email, go back to the profile page and attempt to verify your email again</span>
                    </p>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-sm text-gray-600"> <a href="home.php" class="text-coral font-semibold">Back to Dashboard</a></p>
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