<?php
// Start the session and connect to the database
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
require 'db/db_connect.php';
$conn = $con;
// Get the user ID from the URL
$user_id = $_GET['user_id'];

// Fetch user data from the database
$query = "SELECT username FROM accounts WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!isset($user)) {
    header('Location: error.php?a=User does not exist&b=home.php');
            exit();
}



// Fetch quick stats
$sql_stats = "SELECT SUM(shots_made) AS total_shots, 
			  SUM(shots_taken) AS total_taken,
               
              SUM(IF(shots_taken >= goal, 1, 0))  AS days_count
              FROM user_shots 
              WHERE user_id = ?";
$stmt = $conn->prepare($sql_stats);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_stats = $stmt->get_result();
$stats_data = $result_stats->fetch_assoc();

$shooting_percentage = $stats_data['total_shots'] / ($stats_data['total_taken'] + 1) * 100;

// Badges

$badge1 = false;
$badge2 = false;
$badge3 = false;
$badge4 = false;
$badge5 = false;


if ($stats_data['total_taken'] >= 500) {
    $badge1 = true;
}
if ($stats_data['total_taken'] == 0) {
    $badge2 = false;
} else {
    if (($stats_data['total_shots'] / $stats_data['total_taken']) *100 >= 40 ) {
        $badge2 = true;
    }
}

if ($stats_data['total_shots'] >= 1000) {
    $badge3 = true;
}


if ($stats_data['total_taken'] == 0) {
    $badge5 = false;
} else {
if (($stats_data['total_shots'] / $stats_data['total_taken']) *100 >= 70 ) {
    $badge5 = true;
}
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile - Shotstreak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>
    <link rel="stylesheet" href="main.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" /></head>
</head>
<body class="bg-lightgray dark:bg-almostblack text-almostblack dark:text-lightgray">
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
    <div class="container mx-auto px-6 py-12 ">
        <div class="max-w-4xl mx-auto bg-white dark:bg-darkslate p-8 rounded-lg shadow-lg">
            <div class="flex items-center">
                
                <div>
                    <h2 class="text-3xl font-bold text-coral"><?php echo htmlspecialchars($user['username']); ?></h2>
                </div>
            </div>
            <div class="mt-8">
                <h3 class="text-2xl font-bold">Statistics</h3>
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div class="bg-lightgray dark:bg-almostblack p-4 rounded-lg text-center shadow-md">
                        <h4 class="text-xl font-bold text-light-gray">Total Shots Taken</h4>
                        <p class="text-2xl font-bold text-golden-yellow"><?php echo $stats_data['total_taken']; ?></p>
                    </div>
                    <div class="bg-lightgray dark:bg-almostblack p-4 rounded-lg text-center shadow-md">
                        <h4 class="text-xl font-bold text-light-gray">Total Shots Made</h4>
                        <p class="text-2xl font-bold text-golden-yellow"><?php echo $stats_data['total_shots']; ?></p>
                    </div>
                    <div class="bg-lightgray dark:bg-almostblack p-4 rounded-lg text-center shadow-md">
                        <h4 class="text-xl font-bold text-light-gray">Shooting Percentage</h4>
                        <p class="text-2xl font-bold text-golden-yellow"><?php echo round($shooting_percentage, 0); ?>%</p>
                    </div>
                </div>
            </div>
            <div class="mt-8 ">
                <h3 class="text-2xl font-bold text-coral-red">Achievements</h3>
                <div class="relative grid grid-cols-6 mt-2 lg:grid-cols-10" x-data="{b1 : false, b2 : false, b3 : false, b4: false, b5 : false}">
            
            <div class=" <?php if(!$badge1) { echo 'hidden'; }?> ">
                <img x-on:click="b1 = !b1" @click.away="b1 = false" class="h-16 cursor-pointer" src="assets/icebreaker.svg" alt="badge1">    
            </div>

            <div class=" <?php if(!$badge2) { echo 'hidden'; }?> ">
                <img x-on:click="b2 = !b2" @click.away="b2 = false" class="h-16 cursor-pointer" src="assets/precision.svg" alt="badge2">
            </div>
            <div class=" <?php if(!$badge3) { echo 'hidden'; }?> ">
                <img x-on:click="b3 = !b3" @click.away="b3 = false" class="h-16 cursor-pointer" src="assets/millenium.svg" alt="badge3">
            </div>
            <div class=" <?php if(!$badge4) { echo 'hidden'; }?> ">
                <img x-on:click="b4 = !b4" @click.away="b4 = false" class="h-16 cursor-pointer" src="assets/crusher.svg" alt="badge4">
            </div>
            <div class=" <?php if(!$badge5) { echo 'hidden'; }?> ">
                <img x-on:click="b5 = !b5" @click.away="b5 = false" class="h-16 cursor-pointer" src="assets/pinpoint.svg" alt="badge5">
            </div>
            <p x-show="b1" class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">Icebreaker: Take a total of over 500 shots</p>
            <p x-show="b2" class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">Precision Shooter: Maintain a total average of over 40%</p>
            <p x-show="b3" class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">Millenium Marksman: Make a total of over 1000 shots</p>
            <p x-show="b4" class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">On a Roll: Maintain a total streak over 3 days long. Keep it up!</p>
            <p x-show="b5" class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">Pinpoint Shooter: Maintain a total average of over 70%</p>
        </div>
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