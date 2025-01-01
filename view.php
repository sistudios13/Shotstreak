<?php
require 'db/db_connect.php';
$conn = $con;
error_reporting(0);
// Get the user ID from the URL
$user_id = $_GET['user_id'];

// Fetch user data from the database
$query = "SELECT username FROM accounts WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$exists = true;
if (!isset($user)) {
    $exists = false;
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
    <nav id="test" class="bg-white dark:bg-darkslate shadow-md py-2 md:py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <a href="index.php" class="text-2xl font-bold text-coral">
                <img src="assets/isoLogo.svg" class="size-12 md:hidden" alt="logo">
                <span class="hidden md:block">Shotstreak</span>
            </a>
            <div class="flex items-center gap-2">
                <a href="register.php" class="text-almostblack font-bold dark:text-lightgray md:hover:text-coral">Join Shotstreak!</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-6 py-12 ">
        <?php if($exists): ?>
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
    <?php else: ?>
        <div class="flex justify-center text-xl font-bold">
            User Not Found!
        </div>
    <?php endif;?>
    <footer class="bg-lightgray py-8 text-almostblack dark:text-lightgray dark:bg-almostblack static bottom-0 left-0 w-full">
          <p class="text-sm text-center">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
    </footer>
</body>
</html>