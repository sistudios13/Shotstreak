<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.php');
	exit;
}
if ($_SESSION['type'] != 'player') {
	header('Location: index.php');
	exit;
}
require 'db/db_connect.php';
// We don't have the password or email info stored in sessions, so instead, we can get the results from the database.
$stmt = $con->prepare('SELECT password, email FROM accounts WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($password, $email);
$stmt->fetch();
$stmt->close();

$tmn = $con->prepare('SELECT team_name FROM coaches WHERE coach_id = ?');
$tmn->bind_param('i', $_SESSION['coach_id']);
$tmn->execute();
$res = $tmn->get_result();
$team_name = $res->fetch_assoc()['team_name'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Shotstreak</title>
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
</head>
<body class="bg-lightgray dark:bg-almostblack h-fit">

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
                <li><a href="p_profile.php" class="cursor-pointer text-coral">Profile</a></li>
                <li><a href="logout.php" class="cursor-pointer lg:hover:text-coral">Logout</a></li>
                <li class="h-[24px]"><button id="theme-toggle"><img class="size-6 dark:hidden" src="assets/dark.svg" alt="dark"><img class="size-6 hidden dark:block" src="assets/light.svg" alt="dark"></button></li>
            </ul>
        </nav>
    </header> 

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">


        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Daily Summary Card -->
            <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md flex flex-col gap-4">
                <h3 class="text-xl font-semibold text-almostblack dark:text-lightgray  mb-4">Your Information</h3>
                <div class="flex flex-col items-start justify-between gap-4 ">
                    <div>
                        <p class="text-lg font-bold text-coral">Username:</p>
                        <p class="text-almostblack dark:text-lightgray "><?=htmlspecialchars($_SESSION['name'], ENT_QUOTES)?></p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-coral">Email:</p>
                        <p class="text-almostblack dark:text-lightgray "><?=htmlspecialchars($email, ENT_QUOTES)?></p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-coral">Team:</p>
                        <p class="text-almostblack dark:text-lightgray "><?=htmlspecialchars($team_name, )?></p>
                    </div>
                    <div class="pt-2 flex items-center gap-2">
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#ff6f61" d="M64 256l0-96 160 0 0 96L64 256zm0 64l160 0 0 96L64 416l0-96zm224 96l0-96 160 0 0 96-160 0zM448 256l-160 0 0-96 160 0 0 96zM64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32z"/></svg>
                        <a href="export.php" class="py-2 dark:text-lightgray font-semibold">Export All Data</a>
                    </div>

                    <div class="flex gap-2">
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#ff6f61" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM169.8 165.3c7.9-22.3 29.1-37.3 52.8-37.3l58.3 0c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24l0-13.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1l-58.3 0c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg>
                        <a href="support.php" class="dark:text-lightgray font-semibold">Support Page</a>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md" x-data="{change: false}">
                <h3 class="text-lg font-semibold text-almostblack  dark:text-lightgray mb-4">Edit Account</h3>
                <div class="flex flex-col gap-3">

                        <div class="flex gap-2 items-center mb-3">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#ff6f61" d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160L0 416c0 53 43 96 96 96l256 0c53 0 96-43 96-96l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 96c0 17.7-14.3 32-32 32L96 448c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 64z"/></svg>
                            <a @click="change = !change" class="text-lg dark:text-lightgray font-bold select-none cursor-pointer">Change Password</a>
                        </div>
                        <form class="flex flex-col gap-3" action="change.php" method="POST" id="registerForm" x-show="change" x-collapse >
                            <div>
                                <label for="newpassword" class="block text-md font-medium text-gray-700 dark:text-lightgray ">New Password</label>
                                <input autofocus type="password" id="password" name="newpassword" minlength="5" maxlength="20" class="mt-1 p-2 w-full border dark:text-lightgray dark:bg-darkslate rounded-md focus-visible:outline-coral" required>
                            </div>
            
                            <!-- Confirm Password Input -->
                            <div>
                                <label for="confirm-password" class="block text-md font-medium text-gray-700 dark:text-lightgray ">Confirm New Password</label>
                                <input type="password" id="confirm-password" name="confirm-password" class="mt-1 p-2 w-full border dark:text-lightgray dark:bg-darkslate rounded-md focus-visible:outline-coral" required>
                            </div>
            
                            <!-- Submit Button -->
                            <button type="submit" class="w-full md:hover:bg-coralhov bg-coral dark:text-lightgray  text-white py-2 rounded-md font-semibold hover:bg-coral-red-light transition-colors">Change Password</button>
                        </form>
                </div>
                <div x-data="{de: false}" @click.away="de = false" class="pt-4 flex gap-2 items-center">
                    <a @click="de = !de" class=" select-none dark:text-lightgray h-[32px] pt-1 text-almostblack font-semibold cursor-pointer">Delete Account</a>
                    <form action="delete_account.php"  method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.')" x-show="de" x-collapse>
                        <input type="hidden" name="user_type" value="<?php echo $_SESSION["type"]; ?>"> <!-- 'coach', 'player', or 'user' -->
                        <button type="submit" class="bg-red-600 text-white p-1 px-2 rounded">Delete Account</button>
                    </form>
                </div>
            </div>

            

            
    </div>
    </div>
    <footer class="bg-lightgray py-8 text-almostblack dark:text-lightgray dark:bg-almostblack static bottom-0 left-0 w-full">
          <p class="text-sm text-center">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
    </footer>
    <script src="confirmpass.js"></script>
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