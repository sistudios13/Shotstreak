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
require 'db/db_connect.php';



$user_id = $_SESSION['id'];
$coach_name = $_SESSION['name'];
$email = $_SESSION['email'];

$tmn = $con->prepare("SELECT team_name, coach_id, goal FROM coaches WHERE email = ?");
$tmn->bind_param('s', $email);
$tmn->execute();
$tmnres = $tmn->get_result();
$tmninfo = $tmnres->fetch_assoc();

$team_name = $tmninfo["team_name"];
$coach_id = $tmninfo["coach_id"];
$goal = $tmninfo["goal"];

session_regenerate_id();
$_SESSION['coach_id'] = $coach_id;

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}



// Query to get the list of players associated with the coach
$sql = "SELECT id, player_name FROM players WHERE coach_id = ?";
$stmt = $con->prepare($sql);

// Check if the statement was prepared correctly
if ($stmt === false) {
    die("SQL error: " . $con->error);
}

$stmt->bind_param("i", $coach_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all the players into an array
$players = [];
while ($row = $result->fetch_assoc()) {
    $players[] = $row;
}

// Close the statement to reuse later
$stmt->close();

// Fetch quick stats
$sql_stats = "SELECT SUM(shots.shots_made) AS total_shots, 
			  SUM(shots.shots_taken) AS total_taken
               
             
              FROM players 
                JOIN shots ON players.id = shots.player_id
              WHERE coach_id = ?";
$stmt = $con->prepare($sql_stats);
$stmt->bind_param("i", $coach_id);
$stmt->execute();
$result_stats = $stmt->get_result();
$stats_data = $result_stats->fetch_assoc();

// Query to get the invites
$sql2 = "SELECT player_name, player_email, token FROM invitations WHERE coach_id = ? AND status = 'pending'";
$stmt2 = $con->prepare($sql2);

// Check if the statement was prepared correctly
if ($stmt2 === false) {
    die("SQL error: " . $con->error);
}

$stmt2->bind_param("i", $coach_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

// Fetch all the players into an array
$invites = [];
while ($row2 = $result2->fetch_assoc()) {
    $invites[] = $row2;
}

// Close the statement to reuse later
$stmt2->close();

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
                <button id="theme-toggle"><img class="size-5 dark:hidden" src="assets/dark.svg" alt="dark"><img
                        class="size-5 hidden dark:block" src="assets/light.svg" alt="dark"></button>

                <a href="coachprofile.php" class="text-almostblack dark:text-lightgray md:hover:text-coral">Profile</a>
                <a href="logout.php" class="text-almostblack dark:text-lightgray md:hover:text-coral">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8 pb-3">
        <!-- Welcome Banner -->
        <div class="bg-coral text-white dark:text-lightgray rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold">Welcome back, <?php echo htmlspecialchars($coach_name); ?>!</h2>
            <p class="mt-2">Welcome to your dashboard</p>
        </div>
    </div>

    <div class="container mx-auto px-6 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Quick Stats Card -->
            <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Team Stats</h3>
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
                        <span>Team Shooting:</span>
                        <span
                            class="font-semibold text-dark-gray"><?php if($stats_data['total_taken'] == 0) {echo 0;} else {echo round($stats_data['total_shots'] / $stats_data['total_taken'] * 100, 0);} ?>%
                            Accuracy</span>
                    </li>

                </ul>
            </div>

            <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md">
                <div>
                    <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Team Goal</h3>
                    <h3 class="text-2xl font-bold text-coral mb-4"><?php echo $goal; ?></h3>
                    <p class="text-almostblack dark:text-lightgray">Each player needs to take <b class="text-coral"><?php echo $goal; ?></b> shots per day</p>
                </div>
                <a href="c_changegoal.php"><button class="mt-3 text-coral bg-coral font-bold p-1 px-1.5 md:px-5 md:py-3 w-fit mx-auto border-2 border-coral md:hover:bg-white md:hover:text-coral dark:md:hover:bg-darkslate text-white transition-colors rounded-md ">Change Shot Goal</button></a>


            </div>
        </div>
    </div>

    <!-- Players -->
    <div class="container mx-auto px-6 py-8">
        <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md flex flex-col gap-4">
            <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Your Players:</h3>
            <table class="table-auto min-w-full bg-white dark:bg-darkslate shadow-md rounded-lg">
                <thead>
                    <tr class="bg-coral text-lightgray uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Player Name</th>
                        <th class="py-3 px-6 text-left">Shooting Percentage</th>
                        <th class="py-3 px-3"></th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">

                    <?php
                    // Loop through each player and fetch their shot data
                    for ($i = 0; $i < count($players); $i++) {
                        $player_id = $players[$i]['id'];
                        $player_name = $players[$i]['player_name'];

                        // Query to get the total shots made and shots taken for each player
                        $sql = "SELECT SUM(shots_made) as total_shots_made, SUM(shots_taken) as total_shots_taken 
                            FROM shots 
                            WHERE player_id = ?";

                        $stmt = $con->prepare($sql);
                        if ($stmt === false) {
                            die("SQL error: " . $con->error);
                        }

                        $stmt->bind_param("i", $player_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $shot_data = $result->fetch_assoc();

                        // Calculate shooting percentage
                        $shots_made = $shot_data['total_shots_made'];
                        $shots_taken = $shot_data['total_shots_taken'];

                        if ($shots_taken > 0) {
                            $shooting_percentage = ($shots_made / $shots_taken) * 100;
                        } else {
                            $shooting_percentage = 0;
                        }

                        // Output the player's data in a table row
                        echo "<tr class='border-b border-lightgray dark:border-almostblack dark:bg-darkslate dark:text-lightgray bg-white dark:hover:bg-almostblack hover:bg-lightgray'>";
                        echo "<td class='py-3 px-6 text-left break-all'>$player_name</td>";
                        echo "<td class='py-3 px-6 text-left'>" . number_format($shooting_percentage, 2) . "%</td>";
                        echo '<td class="pr-3"><a href="playerprofile.php?player_id=' . $player_id . '"><svg class="fill-almostblack dark:fill-lightgray size-5 transition-transform md:hover:rotate-45" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z"/></svg></a></td>';
                        echo "</tr>";

                        // Close statement
                        $stmt->close();
                    }

                    // Close the connection
                    
                    ?>

                </tbody>
            </table>

            <div class="flex flex-row justify-between">
            <a href="inviteplayer.php"><button class="mt-3 text-coral bg-coral font-bold p-1 px-1.5 md:px-5 md:py-3 w-fit mx-auto border-2 border-coral md:hover:bg-white md:hover:text-coral dark:md:hover:bg-darkslate text-white transition-colors rounded-md ">Invite Player</button></a>




            </div>
        </div>
    </div>



    <!-- Invites -->
    <div class="container mx-auto px-6 py-8">
        <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md flex flex-col gap-4">
            <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Pending Invites</h3>
            <table class="table-auto min-w-full bg-white dark:bg-darkslate shadow-md rounded-lg">
                <thead>
                    <tr class="bg-coral text-lightgray uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Player Name</th>
                        <th class="py-3 px-6 text-left">Email:</th>
                        <th class="py-3 px-3 text-left"></th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">

                    <?php
                    // Loop through each player and fetch their shot data
                    for ($i = 0; $i < count($invites); $i++) {
                        $player_name = $invites[$i]['player_name'];
                        $player_email = $invites[$i]['player_email'];
                        $token = $invites[$i]['token'];


                        // Output the player's data in a table row
                        echo "<tr class='border-b border-lightgray dark:border-almostblack dark:bg-darkslate dark:text-lightgray bg-white dark:hover:bg-almostblack hover:bg-lightgray'>";
                        echo "<td class='py-3 px-6 text-left break-all'>$player_name</td>";
                        echo "<td class='py-3 px-6 text-left break-all'>$player_email</td>";
                        echo "<td class='pr-3  text-left'><form id='removeform' onsubmit='return confirm(`Are you sure you want to delete this invite? This action is permanent`)' action='delete_invite.php' method='POST'><input type='hidden' name='token' value='$token'><button type='submit' ><svg class='fill-almostblack dark:fill-lightgray size-5' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d='M135.2 17.7C140.6 6.8 151.7 0 163.8 0L284.2 0c12.1 0 23.2 6.8 28.6 17.7L320 32l96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 96C14.3 96 0 81.7 0 64S14.3 32 32 32l96 0 7.2-14.3zM32 128l384 0 0 320c0 35.3-28.7 64-64 64L96 512c-35.3 0-64-28.7-64-64l0-320zm96 64c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16z'/></svg></button></form></td>";

                        echo "</tr>";

                        // Close statement
                    
                    }

                    // Close the connection
                    $con->close();
                    ?>

                </tbody>
            </table>


        </div>
    </div>




    <footer
        class="bg-lightgray py-8 text-almostblack dark:text-lightgray dark:bg-almostblack static bottom-0 left-0 w-full">
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