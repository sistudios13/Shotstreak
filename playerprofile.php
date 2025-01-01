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



$player_id = $_GET['player_id'];
$user_id = $_SESSION['id'];
$coach_name = $_SESSION['name'];
$email = $_SESSION['email'];
$coach_id = $_SESSION['coach_id'];

$stmt = $con->prepare('SELECT coach_id FROM players WHERE id = ?');
$stmt->bind_param( 's', $player_id);
$stmt->execute();
$fetchid = $stmt->get_result();
$fetched_coach = $fetchid->fetch_assoc();

if ($fetched_coach['coach_id'] != $coach_id) {
    header('Location: coach_dashboard.php');
}

//Get Player Data

$p_sql = "SELECT player_name, email, created_at
FROM players 
WHERE id = ?";

$stmt = $con->prepare($p_sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$p_result = $stmt->get_result();
$player_data = $p_result->fetch_assoc();

$player_name = $player_data['player_name'];
$player_email = $player_data['email'];
$created_at = $player_data['created_at'];


//Get SHooting Data

$query = "SELECT (shots_made / shots_taken) * 100 AS shooting_percentage
FROM shots
WHERE player_id = ? AND shots_taken > 0
ORDER BY shooting_percentage DESC
LIMIT 1";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();
$best_day = $result->fetch_assoc()['shooting_percentage'] ?? 0;

$s_sql = "SELECT SUM(shots_made) as total_shots_made, SUM(shots_taken) as total_shots_taken,
  SUM(IF(shots_taken >= goal, 1, 0))  AS days_count
FROM shots 
WHERE player_id = ?";

$stmt = $con->prepare($s_sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$s_result = $stmt->get_result();
$shot_data = $s_result->fetch_assoc();

// Calculate shooting percentage
$shots_made = $shot_data['total_shots_made'];
$shots_taken = $shot_data['total_shots_taken'];

if ($shots_taken > 0) {
$shooting_percentage = ($shots_made / $shots_taken) * 100;
} else {
$shooting_percentage = 0;
}

//Chart

// 7 DAYS
$sql_chart = "SELECT shot_date, shots_made, shots_taken FROM shots 
            WHERE player_id = ? 
            ORDER BY shot_date DESC 
            LIMIT 7";
$stmt = $con->prepare($sql_chart);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result_chart = $stmt->get_result();

$chart_data = [];
while ($row = $result_chart->fetch_assoc()) {
    $chart_data[] = $row;
}
// ---
// 14 DAYS
$asql_chart = "SELECT shot_date, shots_made, shots_taken FROM shots 
            WHERE player_id = ? 
            ORDER BY shot_date DESC 
            LIMIT 14";
$stmt = $con->prepare($asql_chart);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$aresult_chart = $stmt->get_result();

$achart_data = [];
while ($arow = $aresult_chart->fetch_assoc()) {
    $achart_data[] = $arow;
}
// ---
// 90 DAYS
$bsql_chart = "SELECT shot_date, shots_made, shots_taken FROM shots 
            WHERE player_id = ? 
            ORDER BY shot_date DESC 
            LIMIT 90";
$stmt = $con->prepare($bsql_chart);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$bresult_chart = $stmt->get_result();

$bchart_data = [];
while ($brow = $bresult_chart->fetch_assoc()) {
    $bchart_data[] = $brow;
}
// ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo("<title>".$player_name."'s Profile - Shotstreak</title>")?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>

    <link rel="stylesheet" href="main.css">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
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
             update()
             
             
             
        }
        
        
    </script>

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

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <!-- Welcome Banner -->
        <div class="bg-coral text-white dark:text-lightgray rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold"><?php echo($player_name."'s Profile")?></h2>

        </div>
    

    
    
        <div class="bg-white dark:bg-darkslate p-6 mb-6 rounded-lg shadow-md flex flex-col gap-4">
            <!-- Quick Stats Card -->
                <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Stats</h3>
                <ul class="space-y-2">
            <li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Total Shots Made:</span>
                <span class="font-semibold text-dark-gray"><?php echo $shots_made; ?></span>
            </li>
              
			<li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Total Shots Taken:</span>
                <span class="font-semibold text-dark-gray"><?php echo $shots_taken; ?></span>
            </li>
            			<li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Goal Reached:</span>
                <span class="font-semibold text-dark-gray"><?php echo $shot_data['days_count']; ?> Days</span>
            </li>
            <li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Best Shooting Day:</span>
                <span class="font-semibold text-dark-gray"><?php echo round($best_day, 1) ?>% Accuracy</span>
            </li>
            <li class="flex justify-between text-almostblack dark:text-lightgray">
                <span>Shooting Accuracy:</span>
                <span class="font-semibold text-dark-gray"><?php echo round($shooting_percentage, 1) ?>%</span>
            </li>

        </ul>
        </div>
    
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
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

            <div class="bg-white dark:bg-darkslate p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Player Info</h3>
                <ul class="space-y-2 mb-4">
                    <li class=" text-almostblack flex justify-between dark:text-lightgray"><b>Name:</b> <span><?php echo $player_name; ?></span></li>
                    <li class=" text-almostblack flex justify-between dark:text-lightgray"><b>Email:</b> <?php echo $player_email; ?></li>
                    <li class=" text-almostblack flex justify-between dark:text-lightgray"><b>Joined On:</b> <?php echo $created_at; ?></li>
                </ul>
                <div class="pt-2 flex items-center gap-2">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#ff6f61" d="M64 256l0-96 160 0 0 96L64 256zm0 64l160 0 0 96L64 416l0-96zm224 96l0-96 160 0 0 96-160 0zM448 256l-160 0 0-96 160 0 0 96zM64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32z"/></svg>
                    <form action="c_export.php" method="POST">
                        <input type="hidden" name="player_id" value="<?php echo htmlspecialchars($player_id); ?>">
                        <button type="submit" class="py-2 text-almostblack dark:text-lightgray font-semibold">Export All Data</button>
                    </form>
                </div>

                
                
               

                <div x-data="{de: false}" @click.away="de = false" class="pt-4 flex gap-2 items-center">
                    <a @click="de = !de" class=" select-none  h-[32px] pt-1 text-almostblack dark:text-lightgray font-semibold cursor-pointer">Remove Player</a>
                    <form action="remove_player.php"  method="POST" onsubmit="return confirm('Are you sure you want to remove this player? This action is permanent.')" x-show="de" x-collapse>
                        <input type="hidden" name="player_id" value="<?php echo htmlspecialchars($player_id); ?>">
                        <input type="hidden" name="player_email" value="<?php echo htmlspecialchars($player_email); ?>">
                        <button type="submit" class="bg-red-600 text-white p-1 px-2 rounded">Remove Player</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
        
    <footer class="bg-lightgray py-8 text-almostblack dark:text-lightgray dark:bg-almostblack static bottom-0 left-0 w-full">
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
        
        function update(){
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