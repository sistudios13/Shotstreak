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


require 'db/db_connect.php';
$conn = $con;


$user_id = $_SESSION['id'];
$user_name = $_SESSION['name'];

$veri = $conn->prepare("SELECT verified FROM accounts WHERE id = ?");
$veri->bind_param("i", $user_id);
$veri->execute();
$verif = $veri->get_result();
$verified = $verif->fetch_assoc()['verified'];

// Fetch today's shot goal
$date_today = date('Y-m-d');
$sql_today_goal = "SELECT shots_goal FROM user_goals WHERE user_id = ? ";
$stmt = $conn->prepare($sql_today_goal);
$stmt->bind_param("i", $user_id,);
$stmt->execute();
$result_today_goal = $stmt->get_result();
$today_goal = $result_today_goal->fetch_assoc()['shots_goal'] ?? 0;

// Fetch today's shots made
$sql_today_shots = "SELECT SUM(shots_made) AS total_shots_made FROM user_shots WHERE user_id = ? AND shot_date = ?";
$stmt = $conn->prepare($sql_today_shots);
$stmt->bind_param("is", $user_id, $date_today);
$stmt->execute();
$result_today_shots = $stmt->get_result();
$today_shots_made = $result_today_shots->fetch_assoc()['total_shots_made'] ?? 0;

// Fetch today's shots taken
$sql_today_shots_taken = "SELECT SUM(shots_taken) AS total_shots_taken FROM user_shots WHERE user_id = ? AND shot_date = ?";
$stmt = $conn->prepare($sql_today_shots_taken);
$stmt->bind_param("is", $user_id, $date_today);
$stmt->execute();
$result_today_shots_taken = $stmt->get_result();
$today_shots_taken = $result_today_shots_taken->fetch_assoc()['total_shots_taken'] ?? 0;
// Calculate shots remaining
$shots_remaining = $today_goal - $today_shots_taken;

// Fetch data for the progress chart (last 7 days)
$sql_chart = "SELECT shot_date, shots_made, shots_taken FROM user_shots 
            WHERE user_id = ? 
            ORDER BY shot_date DESC 
            LIMIT 7";
$stmt = $conn->prepare($sql_chart);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_chart = $stmt->get_result();

$chart_data = [];
while ($row = $result_chart->fetch_assoc()) {
    $chart_data[] = $row;
}

// Fetch data for the progress chart (last 14 days)
$asql_chart = "SELECT shot_date, shots_made, shots_taken FROM user_shots 
            WHERE user_id = ? 
            ORDER BY shot_date DESC 
            LIMIT 14";
$astmt = $conn->prepare($asql_chart);
$astmt->bind_param("i", $user_id);
$astmt->execute();
$aresult_chart = $astmt->get_result();

$achart_data = [];
while ($arow = $aresult_chart->fetch_assoc()) {
    $achart_data[] = $arow;
}

// Fetch data for the progress chart (last 90 days)
$bsql_chart = "SELECT shot_date, shots_made, shots_taken FROM user_shots 
            WHERE user_id = ? 
            ORDER BY shot_date DESC 
            LIMIT 90";
$bstmt = $conn->prepare($bsql_chart);
$bstmt->bind_param("i", $user_id);
$bstmt->execute();
$bresult_chart = $bstmt->get_result();

$bchart_data = [];
while ($brow = $bresult_chart->fetch_assoc()) {
    $bchart_data[] = $brow;
}

// Best day %

    $query = "SELECT (shots_made / shots_taken) * 100 AS shooting_percentage
              FROM user_shots
              WHERE user_id = ? AND shots_taken > 0
              ORDER BY shooting_percentage DESC
              LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $best_day = $result->fetch_assoc()['shooting_percentage'] ?? 0;


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


//Leaderboard
$leaderquery = "
    SELECT 
    u.id,
    u.username,
    SUM(s.shots_made) AS total_shots_made,
    SUM(s.shots_taken) AS total_shots_taken,
    (SUM(s.shots_made) / SUM(s.shots_taken)) * 100 AS shooting_percentage, 
    u.verified,
    u.banned
FROM 
    accounts u
JOIN 
    user_shots s ON u.id = s.user_id
GROUP BY 
    u.id, u.username
HAVING 
    total_shots_taken > 100 AND verified AND NOT banned  -- Ensure users who have taken shots are considered
ORDER BY 
    total_shots_taken DESC
LIMIT 10;
";

$result = $conn->query($leaderquery);

$leaderboard = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
} 

//Streak

// Fetch the user's daily shot records and goal data from the database
$query = "SELECT shots_taken, shot_date, goal FROM user_shots WHERE user_id = ? ORDER BY shot_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$streak = 0;
$previous_day = null;

// Check each record to calculate the streak
while ($row = $result->fetch_assoc()) {
    $shots_taken = $row['shots_taken'];
    $shot_date = $row['shot_date'];
    $goal = $row['goal'];
    
    // If the user met their goal on that day
    if ($shots_taken >= $goal) {
        // If this is the first day we're checking
        if ($previous_day === null) {
            $streak++;  // Start the streak
        } else {
            // Check if the previous day is exactly one day before the current day
            $days_diff = (strtotime($previous_day) - strtotime($shot_date)) / (60 * 60 * 24);
            if ($days_diff == 1) {
                $streak++;  // Continue the streak
            } else {
                break;  // Break the streak if there's a gap
            }
        }
        $previous_day = $shot_date;  // Update the last day checked
    } else {
        break;  // End the streak if the goal wasn't met
    }
}

//Streak Badge
if ($streak >= 3) {
    $badge4 = true;
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Shotstreak</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>

    <link rel="stylesheet" href="main.css">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" />
    <!-- Open Graph Tags -->
    <meta property="og:title" content="Shotstreak – See my profile!" />
    <meta property="og:description" content="Stay on top of your game! Track your shots, view your stats, and keep up with the competition with Shotstreak." />
    <meta property="og:image" content="https://shotstreak.ca/assets/fullLogo.svg" /> 
    <meta property="og:url" content="https://shotstreak.ca/view.php?user_id=<?php echo $user_id?>" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Shotstreak – See my profile!" />
    <meta name="twitter:description" content="Stay on top of your game! Track your shots, view your stats, and keep up with the competition with Shotstreak." />
    <meta name="twitter:image" content="https://shotstreak.ca/assets/fullLogo.svg" /> 
    <script>
        var time = 2;
        function atime(number) {
             time = number;
             aupdate()    
        }  
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>


</head>
<body class="bg-lightgray dark:bg-almostblack min-h-screen" >
    
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
                <li><a href="index.php" class="cursor-pointer w-full text-right text-coral">Dashboard</a></li>
                <li><a href="profile.php" class="cursor-pointer lg:hover:text-coral">Profile</a></li>
                <li><a href="logout.php" class="cursor-pointer lg:hover:text-coral">Logout</a></li>
                <li class="h-[24px]"><button id="theme-toggle"><img class="size-6 dark:hidden" src="assets/dark.svg" alt="dark"><img class="size-6 hidden dark:block" src="assets/light.svg" alt="dark"></button></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <!-- Welcome Banner -->
        <div class="bg-coral text-white dark:text-lightgray rounded-lg p-6">
            
            <h2 class="text-xl font-bold">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
            
            <div class="flex flex-col-reverse md:flex-row md:justify-between">
                <p class="mt-2">Here's your progress for today:</p>
                <a href="dailyshots.php" class=" text-white md:-translate-y-3 font-bold mt-4 md:mt-0 p-3 md:px-6 md:py-4 w-fit border-2 border-golden  md:hover:bg-golden md:hover:text-almostblack transition-colors rounded-md ">Input Today's Shots</a>
            </div>
            
            
        </div>



        
        <div class="flex justify-between">
            <h2 class="text-2xl font-bold dark:text-lightgray py-8">&#x1F525; Streak: <span class="text-coral"><?php echo htmlspecialchars($streak)?></span></h2>
            <button id="share-btn" title="Share My Profile" onclick="shareProfile()">
                <svg fill="#ff6f61" class="mr-4" height="30px" width="30px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
                    viewBox="0 0 481.6 481.6" xml:space="preserve">
                <g>
                    <path d="M381.6,309.4c-27.7,0-52.4,13.2-68.2,33.6l-132.3-73.9c3.1-8.9,4.8-18.5,4.8-28.4c0-10-1.7-19.5-4.9-28.5l132.2-73.8
                        c15.7,20.5,40.5,33.8,68.3,33.8c47.4,0,86.1-38.6,86.1-86.1S429,0,381.5,0s-86.1,38.6-86.1,86.1c0,10,1.7,19.6,4.9,28.5
                        l-132.1,73.8c-15.7-20.6-40.5-33.8-68.3-33.8c-47.4,0-86.1,38.6-86.1,86.1s38.7,86.1,86.2,86.1c27.8,0,52.6-13.3,68.4-33.9
                        l132.2,73.9c-3.2,9-5,18.7-5,28.7c0,47.4,38.6,86.1,86.1,86.1s86.1-38.6,86.1-86.1S429.1,309.4,381.6,309.4z M381.6,27.1
                        c32.6,0,59.1,26.5,59.1,59.1s-26.5,59.1-59.1,59.1s-59.1-26.5-59.1-59.1S349.1,27.1,381.6,27.1z M100,299.8
                        c-32.6,0-59.1-26.5-59.1-59.1s26.5-59.1,59.1-59.1s59.1,26.5,59.1,59.1S132.5,299.8,100,299.8z M381.6,454.5
                        c-32.6,0-59.1-26.5-59.1-59.1c0-32.6,26.5-59.1,59.1-59.1s59.1,26.5,59.1,59.1C440.7,428,414.2,454.5,381.6,454.5z"/>
                </g>
                </svg>
            </button>
        </div>

        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Daily Summary Card -->
            <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md flex flex-col gap-4">
                <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Today's Goal</h3>
                <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:gap-0">
                    <div class="w-full md:w-fit">
                        <p class="text-4xl font-bold text-golden"><?php echo $today_goal; ?></p>
                        <p class="text-almostblack dark:text-lightgray">Daily Shot Goal</p>
                        <hr class="mt-4 border-gray-200 dark:border-almostblack md:hidden">
                    </div>
                    
                    <div class="w-full md:w-fit">
                        <p class="text-4xl font-bold text-almostblack dark:text-lightgray"><?php echo $today_shots_made; ?></p>
                        <p class="text-almostblack dark:text-lightgray">Shots Made</p>
                        <hr class="mt-4 border-gray-200 dark:border-almostblack md:hidden">
                    </div>
					<div class="w-full md:w-fit">
                        <p class="text-4xl font-bold text-coral"><?php echo $today_shots_taken; ?></p>
                        <p class="text-almostblack dark:text-lightgray">Shots Taken</p>
                        
                    </div>
                </div>
                <div class="w-full bg-coral rounded-lg h-6 ring-2 ring-golden">
                    <div style="width: 0;" id="progressBar" class="bg-golden h-6 rounded-lg text-darkslate transition-all duration-700 ease-in-out text-sm text-center font-semibold"></div>
                </div>
                <p class=" text-almostblack dark:text-lightgray"><?php echo $shots_remaining > 0 ? "You need to take <b class='text-coral'>$shots_remaining</b> more shots to meet your goal!" : "Goal achieved!"; ?></p>
                
                <div class="flex flex-row justify-between gap-4 mt-auto">
                    <a href="shotgoal.php"><button class="mt-1 text-coral font-bold p-1 px-1.5 md:px-6 md:py-4 w-fit mx-auto border-2 border-coral md:hover:bg-coral md:hover:text-white transition-colors rounded-md ">Change Goal</button></a>
                    <a href="dailyshots.php"><button class="mt-1 text-coral bg-coral font-bold p-1 px-1.5 md:px-6 md:py-4 w-fit mx-auto border-2 border-coral md:hover:bg-white md:hover:text-coral dark:md:hover:bg-darkslate text-white transition-colors rounded-md ">Input Today's Shots</button></a>

                </div>
            </div>

            <!-- Progress Chart Card -->
        <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md">

            
            <div class="flex justify-between mb-4">

    <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray">Progress Chart</h3>
    <div x-data="{ isOpen: false, openedWithKeyboard: false }" class="relative" @keydown.esc.window="isOpen = false, openedWithKeyboard = false">
        <!-- Toggle Button -->
        <button type="button" @click="isOpen = ! isOpen" class="inline-flex cursor-pointer items-center gap-2 whitespace-nowrap rounded-md border border-neutral-300 bg-neutral-50 px-4 py-2 text-sm font-medium tracking-wide hover:opacity-75 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-800 dark:border-neutral-700 dark:bg-neutral-900 dark:focus-visible:outline-neutral-300" aria-haspopup="true" @keydown.space.prevent="openedWithKeyboard = true" @keydown.enter.prevent="openedWithKeyboard = true" @keydown.down.prevent="openedWithKeyboard = true" :class="isOpen || openedWithKeyboard ? 'text-neutral-900 dark:text-white' : 'text-neutral-600 dark:text-neutral-300'" :aria-expanded="isOpen || openedWithKeyboard">
            <span id="btn-label"> 7 Days</span>
            <svg aria-hidden="true" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 totate-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
            </svg>        
        </button>
        <!-- Dropdown Menu -->
        <div x-cloak x-show="isOpen || openedWithKeyboard" x-transition x-trap="openedWithKeyboard" @click.outside="isOpen = false, openedWithKeyboard = false" @keydown.down.prevent="$focus.wrap().next()" @keydown.up.prevent="$focus.wrap().previous()" class="absolute top-11 left-0 flex w-full min-w-[8rem] flex-col overflow-hidden rounded-md border border-neutral-300 bg-neutral-50 py-1.5 dark:border-neutral-700 dark:bg-neutral-900" role="menu">
            <a onclick="atime(1)" class="bg-neutral-50 cursor-pointer px-4 py-2 text-sm text-neutral-600 hover:bg-neutral-900/5 hover:text-neutral-900 focus-visible:bg-neutral-900/10 focus-visible:text-neutral-900 focus-visible:outline-none dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-50/5 dark:hover:text-white dark:focus-visible:bg-neutral-50/10 dark:focus-visible:text-white" role="menuitem">7 Days</a>
            <a onclick="atime(2)" class="bg-neutral-50 cursor-pointer px-4 py-2 text-sm text-neutral-600 hover:bg-neutral-900/5 hover:text-neutral-900 focus-visible:bg-neutral-900/10 focus-visible:text-neutral-900 focus-visible:outline-none dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-50/5 dark:hover:text-white dark:focus-visible:bg-neutral-50/10 dark:focus-visible:text-white" role="menuitem">14 Days</a>
            <a onclick="atime(3)" class="bg-neutral-50 cursor-pointer px-4 py-2 text-sm text-neutral-600 hover:bg-neutral-900/5 hover:text-neutral-900 focus-visible:bg-neutral-900/10 focus-visible:text-neutral-900 focus-visible:outline-none dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-50/5 dark:hover:text-white dark:focus-visible:bg-neutral-50/10 dark:focus-visible:text-white" role="menuitem">90 Days</a>
        </div>
    </div>

        </div>
            <div id="pc1">
                <canvas id="progressChart" width="400" height="200"></canvas>
            </div>



            <div id="pc2" style="display: none;">
        
        
                <canvas id="progressChart2" width="400" height="200"></canvas>
            </div>



            <div id="pc3" style="display: none;">
        
        
                <canvas id="progressChart3" width="400" height="200"></canvas>
            </div>
        </div>

            <!-- Quick Stats Card -->
            <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Quick Stats</h3>
                <ul class="space-y-2">
            <li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Total Shots Made:</span>
                <span class="font-semibold text-dark-gray"><?php echo $stats_data['total_shots']; ?></span>
            </li>
			<li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Total Shots Taken:</span>
                <span class="font-semibold text-dark-gray"><?php echo $stats_data['total_taken']; ?></span>
            </li>
            <li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Best Shooting Day:</span>
                <span class="font-semibold text-dark-gray"><?php echo round($best_day, 0) ?>% Accuracy</span>
            </li>
            <li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Goal Reached:</span>
                <span class="font-semibold text-dark-gray"><?php echo $stats_data['days_count']; ?> Days</span>
            </li>
            <li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Shooting Accuracy:</span>
                <span class="font-semibold text-dark-gray"><?php if($stats_data['total_taken'] == 0) {echo 0;} else {echo round($stats_data['total_shots'] / $stats_data['total_taken'] * 100, 0);} ?>% Accuracy</span>
            </li>
            
        </ul>
    </div>
    <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Badges</h3>
        <div class="relative grid grid-cols-6 lg:grid-cols-10" x-data="{b1 : false, b2 : false, b3 : false, b4: false, b5 : false}">
            
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
            <p x-show="b4" class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">On a Roll: Maintain a current streak over 3 days long. Keep it up!</p>
            <p x-show="b5" class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">Pinpoint Shooter: Maintain a total average of over 70%</p>
        </div>
    </div>
    <div class="container mx-auto text-almostblack">
        <div class="bg-white dark:bg-darkslate p-8 rounded-lg shadow-md ">
            <div class="flex items-center justify-between pb-2">
                <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray">Leaderboard</h3><?php if($verified !== 1) {echo '<a href="support.php#faq" class="text-base font-semibold text-right text-coral">Not on the leaderboard?</a>';} ?>
            </div>
            <table class="min-w-full table-auto text-left dark:text-lightgray">
                <thead class="">
                    <tr>
                        <th class="px-2 py-2 w-1/6">Rank</th>
                        <th class="px-2 py-2 w-2/6">Username</th>
                        <th class="px-2 py-2 w-1/6">Shots</th>
                        <th class="px-2 py-2 w-1/6">%</th>
                    </tr>
                </thead>
                <tbody class="bg-lightgray dark:bg-almostblack">
                    <?php if (!empty($leaderboard)): ?>
                        <?php foreach ($leaderboard as $index => $user): ?>
                            <tr>
                                <td class="border px-2 py-2"><?php echo $index + 1;?></td>
                                <td class="border px-2 py-2 text-coral break-all"><a href="viewprofile.php?user_id=<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></a></td>
                                <td class="border px-2 py-2"><?php echo $user['total_shots_taken']; ?></td>
                                <td class="border px-2 py-2"><?php echo number_format($user['shooting_percentage'], 0); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="border px-4 py-2 text-center">No leaderboard data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div> 
    </div>
    </div>
    </div>

    <div id="add-to" class="w-full hidden" x-data="{showModal : $persist(true)}">
        <div x-show="showModal" x-cloak class="fixed top-0 lg:flex lg:justify-center backdrop-blur-sm lg:items-center p-4 w-full h-full">
            <div class="bg-white dark:bg-darkslate shadow-lg lg:max-w-lg lg:max-h-[630px] h-full w-full rounded-md h-full">
                <div class="flex justify-end p-4 w-full">
                    <button @click="showModal = false">
                        <svg fill="#000000" height="17" width="17px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                            viewBox="0 0 460.775 460.775" xml:space="preserve">
                            <path d="M285.08,230.397L456.218,59.27c6.076-6.077,6.076-15.911,0-21.986L423.511,4.565c-2.913-2.911-6.866-4.55-10.992-4.55
                            c-4.127,0-8.08,1.639-10.993,4.55l-171.138,171.14L59.25,4.565c-2.913-2.911-6.866-4.55-10.993-4.55
                            c-4.126,0-8.08,1.639-10.992,4.55L4.558,37.284c-6.077,6.075-6.077,15.909,0,21.986l171.138,171.128L4.575,401.505
                            c-6.074,6.077-6.074,15.911,0,21.986l32.709,32.719c2.911,2.911,6.865,4.55,10.992,4.55c4.127,0,8.08-1.639,10.994-4.55
                            l171.117-171.12l171.118,171.12c2.913,2.911,6.866,4.55,10.993,4.55c4.128,0,8.081-1.639,10.992-4.55l32.709-32.719
                            c6.074-6.075,6.074-15.909,0-21.986L285.08,230.397z"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 pt-0 text-white dark:text-lightgray space-y-2">
                <div class="text-center mb-6">
                        <img src="assets/isoLogo.svg" alt="Shotstreak Logo" class="mx-auto h-16">
                        <h1 class="text-2xl font-bold dark:text-lightgray text-almostblack mt-4">Enjoying Shotstreak?</h1>
                    </div>
                    <p class="font-semibold text-almostblack dark:text-lightgray text-lg">Add Shotstreak to your home screen with these steps:</p>
                    <ol class="list-decimal ml-6 text-almostblack dark:text-lightgray marker:font-bold marker:text-coral">
                        <li>Click the share button</li>
                        <li>Then find "<b>Add to Home Screen</b>"</li>
                        <li>Press "<b>Add</b>"</li>
                    </ol>
                    <img class="rounded-lg hidden lg:inline w-full max-w-80 ring ring-coral" src="assets/iosScreen.jpg" alt="screenshot">
                    <div class="flex justify-center items-end w-full py-6">
                        <button @click="showModal = false" class="p-2 px-4 bg-coral rounded-md mx-auto text-white">
                            Done!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var isIphone = /(iPhone)/i.test(navigator.userAgent);
        var isSafari = !!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/);
        if(isIphone && isSafari){
            document.getElementById('add-to').classList.remove('hidden')
        }
    </script>

    <footer class="bg-lightgray py-8 text-almostblack dark:text-lightgray dark:bg-almostblack static bottom-0 left-0 w-full">
          <p class="text-sm text-center">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
    </footer>
    
    <!-- Share Script -->
    <script>
        function shareProfile() {
            const shareData = {
                title: 'Shotstreak – See my profile!',
                text: 'Stay on top of your game! Track your shots, view your stats, and keep up with the competition with Shotstreak.',
                url: "https://shotstreak.ca/view.php?user_id=<?php echo $user_id?>"
            };

            if (navigator.share) {
                navigator.share(shareData)
                    .then(() => console.log('Content shared successfully'))
                    .catch((error) => console.error('Error sharing:', error));
            } else {
                alert('Unfortunately, your browser does not support this feature!');
            }
        }
    </script>

    <!-- Chart.js Script -->
    <script>
        const ctx = document.getElementById('progressChart').getContext('2d');
            const progressChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_reverse(array_column($chart_data, 'shot_date'))); ?>,
                    datasets: [{
                        label: 'Shooting Accuracy (%)',
                        data: <?php echo json_encode(array_reverse(array_map(function($row) {
                            return ($row['shots_made'] / $row['shots_taken']) * 100;
                        }, $chart_data))); ?>,
                        borderColor: '#FF6F61',
                        backgroundColor: 'rgba(255, 90, 95, 0.2)',
                        borderWidth: 2,
                        fill: true,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
        });

       
            
            const ctx2 = document.getElementById('progressChart2').getContext('2d');
            const progressChart2 = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_reverse(array_column($achart_data, 'shot_date'))); ?>,
                    datasets: [{
                        label: 'Shooting Accuracy (%)',
                        data: <?php echo json_encode(array_reverse(array_map(function($arow) {
                            return ($arow['shots_made'] / $arow['shots_taken']) * 100;
                        }, $achart_data))); ?>,
                        borderColor: '#FF6F61',
                        backgroundColor: 'rgba(255, 90, 95, 0.2)',
                        borderWidth: 2,
                        fill: true,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
        });

        
            const ctx3 = document.getElementById('progressChart3').getContext('2d');
            const progressChart3 = new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_reverse(array_column($bchart_data, 'shot_date'))); ?>,
                    datasets: [{
                        label: 'Shooting Accuracy (%)',
                        data: <?php echo json_encode(array_reverse(array_map(function($brow) {
                            return ($brow['shots_made'] / $brow['shots_taken']) * 100;
                        }, $bchart_data))); ?>,
                        borderColor: '#FF6F61',
                        backgroundColor: 'rgba(255, 90, 95, 0.2)',
                        borderWidth: 2,
                        fill: true,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
        });
        
        function aupdate(){
        if (time == 1) {
            document.getElementById('pc1').style.display = 'block';
            document.getElementById('pc2').style.display = 'none';
            document.getElementById('pc3').style.display = 'none';
            document.getElementById('btn-label').innerHTML = '7 Days'
        }

        if (time == 2) {
            document.getElementById('pc1').style.display = 'none';
            document.getElementById('pc2').style.display = 'block';
            document.getElementById('pc3').style.display = 'none';
            document.getElementById('btn-label').innerHTML = '14 Days'
        }

        if (time == 3) {
            document.getElementById('pc1').style.display = 'none';
            document.getElementById('pc2').style.display = 'none';
            document.getElementById('pc3').style.display = 'block';
            document.getElementById('btn-label').innerHTML = '90 Days'
        }
    }

    
    </script>
    <script>
        function update() {
            const progress = Math.min((<?php echo $today_shots_taken ?> / <?php echo $today_goal ?>) * 100, 100).toFixed(2)
            document.getElementById('progressBar').style.width = `${progress}%`;
        }
        update();

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