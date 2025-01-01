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
$conn = $con;


//Fetch player id
$pid = $conn->prepare('SELECT id FROM players WHERE email = ?');
$pid->bind_param('s', $_SESSION['email']);
$pid->execute();
$pidinfo = $pid->get_result();
$user_id = $pidinfo->fetch_assoc()['id'];

session_regenerate_id();
$_SESSION['player_id'] = $user_id;

$user_name = $_SESSION['name'];


$cid = $conn->prepare('SELECT coach_id FROM players WHERE id = ?');
$cid->bind_param('i', $user_id);
$cid->execute();
$cidinfo = $cid->get_result();
if ($cidinfo -> num_rows == 0) {
    header('Location: error.php?a=Your coach has deleted the team&b=delete_account.php');
    exit();
}
$coach_id = $cidinfo->fetch_assoc()['coach_id'];

session_regenerate_id();
$_SESSION['coach_id'] = $coach_id;

// Fetch today's shot goal
$date_today = date('Y-m-d');
$sql_today_goal = "SELECT goal FROM coaches WHERE coach_id = ?";
$stmt = $conn->prepare($sql_today_goal);
$stmt->bind_param("i", $coach_id);
$stmt->execute();
$result_today_goal = $stmt->get_result();
$today_goal = $result_today_goal->fetch_assoc()['goal'] ?? 0;


// Fetch today's shots made
$sql_today_shots = "SELECT SUM(shots_made) AS total_shots_made FROM shots WHERE player_id = ? AND shot_date = ?";
$stmt = $conn->prepare($sql_today_shots);
$stmt->bind_param("is", $user_id, $date_today);
$stmt->execute();
$result_today_shots = $stmt->get_result();
$today_shots_made = $result_today_shots->fetch_assoc()['total_shots_made'] ?? 0;

// Fetch today's shots taken
$sql_today_shots_taken = "SELECT SUM(shots_taken) AS total_shots_taken FROM shots WHERE player_id = ? AND shot_date = ?";
$stmt = $conn->prepare($sql_today_shots_taken);
$stmt->bind_param("is", $user_id, $date_today);
$stmt->execute();
$result_today_shots_taken = $stmt->get_result();
$today_shots_taken = $result_today_shots_taken->fetch_assoc()['total_shots_taken'] ?? 0;
// Calculate shots remaining
$shots_remaining = $today_goal - $today_shots_taken;

// Fetch data for the progress chart (last 7 days)
$sql_chart = "SELECT shot_date, shots_made, shots_taken FROM shots 
            WHERE player_id = ? 
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
$asql_chart = "SELECT shot_date, shots_made, shots_taken FROM shots 
            WHERE player_id = ? 
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
$bsql_chart = "SELECT shot_date, shots_made, shots_taken FROM shots 
            WHERE player_id = ? 
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
              FROM shots
              WHERE player_id = ? AND shots_taken > 0
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
              FROM shots 
              WHERE player_id = ?";
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
    if (($stats_data['total_shots'] / $stats_data['total_taken']) * 100 >= 40) {
        $badge2 = true;
    }
}

if ($stats_data['total_shots'] >= 1000) {
    $badge3 = true;
}


if ($stats_data['total_taken'] == 0) {
    $badge5 = false;
} else {
    if (($stats_data['total_shots'] / $stats_data['total_taken']) * 100 >= 70) {
        $badge5 = true;
    }
}
//Leaderboard


//Streak

// Fetch the user's daily shot records and goal data from the database
$query = "SELECT shots_taken, shot_date, goal FROM shots WHERE player_id = ? ORDER BY shot_date DESC";
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

<body class="bg-lightgray dark:bg-almostblack min-h-screen">

    <!-- Navbar -->
    <nav id="test" class="bg-white dark:bg-darkslate shadow-md py-2 md:py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <a href="index.php" class="text-2xl font-bold text-coral">
                <img src="assets/isoLogo.svg" class="size-12 md:hidden" alt="logo">
                <span class="hidden md:block">Shotstreak</span>
            </a>
            <div class="flex items-center gap-2">
                <button id="theme-toggle"><img class="size-5 dark:hidden" src="assets/dark.svg" alt="dark"><img
                        class="size-5 hidden dark:block" src="assets/light.svg" alt="dark"></button>

                <a href="p_profile.php" class="text-almostblack dark:text-lightgray md:hover:text-coral">Profile</a>
                <a href="logout.php" class="text-almostblack dark:text-lightgray md:hover:text-coral">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <!-- Welcome Banner -->
        <div class="bg-coral text-white dark:text-lightgray rounded-lg p-6">

            <h2 class="text-xl font-bold">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>

            <div class="flex flex-col-reverse md:flex-row md:justify-between">
                <p class="mt-2">Here's your progress for today:</p>
                <a href="p_dailyshots.php"><button
                        class=" text-white md:-translate-y-5 font-bold mt-4 p-3 md:px-6 md:py-4 w-fit mx-auto border-2 border-golden  md:hover:bg-golden md:hover:text-almostblack transition-colors rounded-md ">Input
                        Today's Shots</button></a>
            </div>


        </div>




        <div>
            <h2 class="text-2xl font-bold dark:text-lightgray py-8">&#x1F525; Streak: <span
                    class="text-coral"><?php echo htmlspecialchars($streak) ?></span></h2>
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
                        <p class="text-4xl font-bold text-almostblack dark:text-lightgray">
                            <?php echo $today_shots_made; ?>
                        </p>
                        <p class="text-almostblack dark:text-lightgray">Shots Made</p>
                        <hr class="mt-4 border-gray-200 dark:border-almostblack md:hidden">
                    </div>
                    <div class="w-full md:w-fit">
                        <p class="text-4xl font-bold text-coral"><?php echo $today_shots_taken; ?></p>
                        <p class="text-almostblack dark:text-lightgray">Shots Taken</p>

                    </div>
                </div>
                <div class="w-full bg-coral rounded-lg h-6 ring-2 ring-golden">
                    <div style="width: 0;" id="progressBar"
                        class="bg-golden h-6 rounded-lg text-darkslate transition-all duration-700 ease-in-out text-sm text-center font-semibold">
                    </div>
                </div>
                <p class=" text-almostblack dark:text-lightgray">
                    <?php echo $shots_remaining > 0 ? "You need to take <b class='text-coral'>$shots_remaining</b> more shots to meet your goal!" : "Goal achieved!"; ?>
                </p>

                <div class="flex flex-row justify-between mt-auto">
                    
                    <a href="p_dailyshots.php"><button
                            class="mt-1 text-coral bg-coral font-bold p-1 px-1.5 md:px-6 md:py-4 w-fit mx-auto border-2 border-coral md:hover:bg-white md:hover:text-coral dark:md:hover:bg-darkslate text-white transition-colors rounded-md ">Input
                            Today's Shots</button></a>

                </div>
            </div>

            <!-- Progress Chart Card -->
            <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md">


                <div class="flex justify-between mb-4">

                    <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray">Progress Chart</h3>
                    <div x-data="{ isOpen: false, openedWithKeyboard: false }" class="relative" @keydown.esc.window="isOpen = false, openedWithKeyboard = false">
                        <!-- Toggle Button -->
                        <button  type="button" @click="isOpen = ! isOpen" class="inline-flex cursor-pointer items-center gap-2 whitespace-nowrap rounded-md border border-neutral-300 bg-neutral-50 px-4 py-2 text-sm font-medium tracking-wide transition hover:opacity-75 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-800 dark:border-neutral-700 dark:bg-neutral-900 dark:focus-visible:outline-neutral-300" aria-haspopup="true" @keydown.space.prevent="openedWithKeyboard = true" @keydown.enter.prevent="openedWithKeyboard = true" @keydown.down.prevent="openedWithKeyboard = true" :class="isOpen || openedWithKeyboard ? 'text-neutral-900 dark:text-white' : 'text-neutral-600 dark:text-neutral-300'" :aria-expanded="isOpen || openedWithKeyboard">
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
                        <span
                            class="font-semibold text-dark-gray"><?php if($stats_data['total_taken'] == 0) {echo 0;} else {echo round($stats_data['total_shots'] / $stats_data['total_taken'] * 100, 0);} ?>%
                            Accuracy</span>
                    </li>

                </ul>
            </div>
            <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Badges</h3>
                <div class="relative grid grid-cols-6 lg:grid-cols-10"
                    x-data="{b1 : false, b2 : false, b3 : false, b4: false, b5 : false}">

                    <div class=" <?php if (!$badge1) {
                        echo 'hidden';
                    } ?> ">
                        <img x-on:click="b1 = !b1" @click.away="b1 = false" class="h-16 cursor-pointer"
                            src="assets/icebreaker.svg" alt="badge1">
                    </div>

                    <div class=" <?php if (!$badge2) {
                        echo 'hidden';
                    } ?> ">
                        <img x-on:click="b2 = !b2" @click.away="b2 = false" class="h-16 cursor-pointer"
                            src="assets/precision.svg" alt="badge2">
                    </div>
                    <div class=" <?php if (!$badge3) {
                        echo 'hidden';
                    } ?> ">
                        <img x-on:click="b3 = !b3" @click.away="b3 = false" class="h-16 cursor-pointer"
                            src="assets/millenium.svg" alt="badge3">
                    </div>
                    <div class=" <?php if (!$badge4) {
                        echo 'hidden';
                    } ?> ">
                        <img x-on:click="b4 = !b4" @click.away="b4 = false" class="h-16 cursor-pointer"
                            src="assets/crusher.svg" alt="badge4">
                    </div>
                    <div class=" <?php if (!$badge5) {
                        echo 'hidden';
                    } ?> ">
                        <img x-on:click="b5 = !b5" @click.away="b5 = false" class="h-16 cursor-pointer"
                            src="assets/pinpoint.svg" alt="badge5">
                    </div>
                    <p x-show="b1"
                        class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">
                        Icebreaker: Take a total of over 500 shots</p>
                    <p x-show="b2"
                        class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">
                        Precision Shooter: Maintain a total average of over 40%</p>
                    <p x-show="b3"
                        class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">
                        Millenium Marksman: Make a total of over 1000 shots</p>
                    <p x-show="b4"
                        class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">
                        On a Roll: Maintain a current streak over 3 days long. Keep it up!</p>
                    <p x-show="b5"
                        class="absolute w-60 bg-white dark:bg-darkslate text-almostblack dark:text-lightgray top-16 p-3 rounded-lg shadow-md">
                        Pinpoint Shooter: Maintain a total average of over 70%</p>
                </div>
            </div>

        </div>
    </div>
    </div>
    <div id="add-to" class="w-full hidden" x-data="{showModal : $persist(true)}">
        <div x-show="showModal" x-cloak class="t fixed top-0 lg:flex lg:justify-center backdrop-blur-sm lg:items-center p-4 w-full h-full">
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
                    <ol class="list-decimal ml-6 marker:font-bold text-almostblack dark:text-lightgray marker:text-coral">
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
    <footer
        class="py-8 text-almostblack dark:text-lightgray  dark:bg-almostblack static bottom-0 left-0 w-full">
        <p class="text-sm text-center">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
    </footer>



    <!-- Chart.js Script -->
    <script>





        const ctx = document.getElementById('progressChart').getContext('2d');
        const progressChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_reverse(array_column($chart_data, 'shot_date'))); ?>,
                datasets: [{
                    label: 'Shooting Accuracy (%)',
                    data: <?php echo json_encode(array_reverse(array_map(function ($row) {
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
                    data: <?php echo json_encode(array_reverse(array_map(function ($arow) {
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
                    data: <?php echo json_encode(array_reverse(array_map(function ($brow) {
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

        function aupdate() {
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