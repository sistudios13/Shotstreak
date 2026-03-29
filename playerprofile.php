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
$stmt->bind_param('s', $player_id);
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

$s_sql = "SELECT SUM(shots_made) AS total_shots_made, 
              SUM(shots_taken) AS total_shots_taken,
              SUM(IF(goal_type = 'make', IF(shots_made >= goal, 1, 0), IF(shots_taken >= goal, 1, 0))) AS days_count
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

// Fetch data for the progress chart (last 90 days by date)
$start_date_90 = date('Y-m-d', strtotime('-89 days'));
$sql_chart = "SELECT shot_date, SUM(shots_made) AS shots_made, SUM(shots_taken) AS shots_taken
            FROM shots
            WHERE player_id = ? AND shot_date >= ?
            GROUP BY shot_date
            ORDER BY shot_date ASC";
$stmt = $con->prepare($sql_chart);
$stmt->bind_param("is", $player_id, $start_date_90);
$stmt->execute();
$result_chart = $stmt->get_result();

$chart_rows = [];
while ($row = $result_chart->fetch_assoc()) {
    $chart_rows[$row['shot_date']] = $row;
}

function make_chart_range($rows, $days, $today)
{
    $labels = [];
    $values = [];
    $pointRadiuses = [];
    $hoverRadiuses = [];
    $pointHitRadiuses = [];
    $pointBackgrounds = [];
    $showTooltip = [];
    $lastValue = null;

    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("$today -$i days"));
        $labels[] = date('M j', strtotime($date));
        if (isset($rows[$date]) && $rows[$date]['shots_taken'] > 0) {
            $lastValue = round(($rows[$date]['shots_made'] / $rows[$date]['shots_taken']) * 100, 1);
            $values[] = $lastValue;
            $pointRadiuses[] = 4;
            $hoverRadiuses[] = 6;
            $pointHitRadiuses[] = 8;
            $pointBackgrounds[] = 'rgba(255, 111, 97, 1)';
            $showTooltip[] = true;
        } else {
            $values[] = $lastValue;
            $pointRadiuses[] = 0;
            $hoverRadiuses[] = 0;
            $pointHitRadiuses[] = 0;
            $pointBackgrounds[] = 'transparent';
            $showTooltip[] = false;
        }
    }

    return [
        'labels' => $labels,
        'values' => $values,
        'pointRadius' => $pointRadiuses,
        'pointHoverRadius' => $hoverRadiuses,
        'pointHitRadius' => $pointHitRadiuses,
        'pointBackgroundColor' => $pointBackgrounds,
        'showTooltip' => $showTooltip,
    ];
}

$chart_7 = make_chart_range($chart_rows, 7, date('Y-m-d'));
$chart_14 = make_chart_range($chart_rows, 14, date('Y-m-d'));
$chart_90 = make_chart_range($chart_rows, 90, date('Y-m-d'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo ("<title>" . $player_name . "'s Profile - Shotstreak</title>") ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="app.css">
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
        var time = 1;
        function atime(number) {
            time = number;
            aupdate();
        }
    </script>

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
            <ul class="absolute shadow-md mt-[70px] lg:py-3 text-almostblack dark:text-lightgray bg-white dark:bg-darkslate pb-8 flex-col items-end flex w-full lg:static top-0 right-0 p-4 lg:text-lg float-right gap-4 lg:p-0 lg:justify-end lg:items-center lg:flex-row lg:shadow-none lg:mt-0 text-xl" x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0  translate-x-12" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 translate-x-4">
                <li><a href="index.php" class="cursor-pointer w-full text-right lg:hover:text-coral">Dashboard</a></li>
                <li><a href="coachprofile.php" class="cursor-pointer lg:hover:text-coral">Profile</a></li>
                <li><a href="logout.php" class="cursor-pointer lg:hover:text-coral">Logout</a></li>
                <li class="h-[24px]"><button id="theme-toggle"><img class="size-6 dark:hidden" src="assets/dark.svg" alt="dark"><img class="size-6 hidden dark:block" src="assets/light.svg" alt="dark"></button></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <!-- Welcome Banner -->
        <div class="bg-coral text-white dark:text-lightgray rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold"><?php echo ($player_name . "'s Profile") ?></h2>

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
                        <button type="button" @click="isOpen = ! isOpen" class="inline-flex cursor-pointer items-center gap-2 whitespace-nowrap rounded-md border border-neutral-300 bg-neutral-50 px-4 py-2 text-sm font-medium tracking-wide transition hover:opacity-75 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-800 dark:border-neutral-700 dark:bg-neutral-900 dark:focus-visible:outline-neutral-300" aria-haspopup="true" @keydown.space.prevent="openedWithKeyboard = true" @keydown.enter.prevent="openedWithKeyboard = true" @keydown.down.prevent="openedWithKeyboard = true" :class="isOpen || openedWithKeyboard ? 'text-neutral-900 dark:text-white' : 'text-neutral-600 dark:text-neutral-300'" :aria-expanded="isOpen || openedWithKeyboard">
                            <span id="btn-label"> 7 Days</span>
                            <svg aria-hidden="true" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 totate-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
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
                <div>
                    <canvas id="progressChart" width="400" height="300"></canvas>
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
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                        <path fill="#ff6f61" d="M64 256l0-96 160 0 0 96L64 256zm0 64l160 0 0 96L64 416l0-96zm224 96l0-96 160 0 0 96-160 0zM448 256l-160 0 0-96 160 0 0 96zM64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32z" />
                    </svg>
                    <form action="c_export.php" method="POST">
                        <input type="hidden" name="player_id" value="<?php echo htmlspecialchars($player_id); ?>">
                        <button type="submit" class="py-2 text-almostblack dark:text-lightgray font-semibold">Export All Data</button>
                    </form>
                </div>





                <div x-data="{de: false}" @click.away="de = false" class="pt-4 flex gap-2 items-center">
                    <a @click="de = !de" class=" select-none  h-[32px] pt-1 text-almostblack dark:text-lightgray font-semibold cursor-pointer">Remove Player</a>
                    <form action="remove_player.php" method="POST" onsubmit="return confirm('Are you sure you want to remove this player? This action is permanent.')" x-show="de" x-collapse>
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
        var time = 1;
        function atime(number) {
            time = number;
            aupdate();
        }

        const chartRanges = {
            7: {
                labels: <?php echo json_encode($chart_7['labels']); ?>,
                values: <?php echo json_encode($chart_7['values']); ?>,
                pointRadius: <?php echo json_encode($chart_7['pointRadius']); ?>,
                pointHoverRadius: <?php echo json_encode($chart_7['pointHoverRadius']); ?>,
                pointHitRadius: <?php echo json_encode($chart_7['pointHitRadius']); ?>,
                pointBackgroundColor: <?php echo json_encode($chart_7['pointBackgroundColor']); ?>,
                showTooltip: <?php echo json_encode($chart_7['showTooltip']); ?>
            },
            14: {
                labels: <?php echo json_encode($chart_14['labels']); ?>,
                values: <?php echo json_encode($chart_14['values']); ?>,
                pointRadius: <?php echo json_encode($chart_14['pointRadius']); ?>,
                pointHoverRadius: <?php echo json_encode($chart_14['pointHoverRadius']); ?>,
                pointHitRadius: <?php echo json_encode($chart_14['pointHitRadius']); ?>,
                pointBackgroundColor: <?php echo json_encode($chart_14['pointBackgroundColor']); ?>,
                showTooltip: <?php echo json_encode($chart_14['showTooltip']); ?>
            },
            90: {
                labels: <?php echo json_encode($chart_90['labels']); ?>,
                values: <?php echo json_encode($chart_90['values']); ?>,
                pointRadius: <?php echo json_encode($chart_90['pointRadius']); ?>,
                pointHoverRadius: <?php echo json_encode($chart_90['pointHoverRadius']); ?>,
                pointHitRadius: <?php echo json_encode($chart_90['pointHitRadius']); ?>,
                pointBackgroundColor: <?php echo json_encode($chart_90['pointBackgroundColor']); ?>,
                showTooltip: <?php echo json_encode($chart_90['showTooltip']); ?>
            }
        };

        const ctx = document.getElementById('progressChart').getContext('2d');
        const progressChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartRanges[7].labels,
                datasets: [{
                    label: 'Shooting Accuracy (%)',
                    data: chartRanges[7].values,
                    borderColor: '#FF6F61',
                    backgroundColor: 'rgba(255, 111, 97, 0.18)',
                    borderWidth: 3,
                    pointRadius: chartRanges[7].pointRadius,
                    pointHoverRadius: chartRanges[7].pointHoverRadius,
                    pointHitRadius: chartRanges[7].pointHitRadius,
                    pointBackgroundColor: chartRanges[7].pointBackgroundColor,
                    showTooltip: chartRanges[7].showTooltip,
                    tension: 0.35,
                    fill: true,
                    spanGaps: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'nearest',
                        intersect: false,
                        backgroundColor: 'rgba(255, 111, 97, 0.95)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: 'rgba(255, 111, 97, 1)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        filter: function (tooltipItem) {
                            const showTooltip = tooltipItem.dataset && tooltipItem.dataset.showTooltip;
                            return Array.isArray(showTooltip) ? showTooltip[tooltipItem.dataIndex] : true;
                        },
                        callbacks: {
                            title: function (items) {
                                return items[0] ? items[0].label : '';
                            },
                            label: function (context) {
                                if (context.parsed.y === null || context.parsed.y === undefined) {
                                    return 'No data available';
                                }
                                return 'Accuracy: ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 12,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Accuracy (%)'
                        }
                    }
                }
            }
        });

        function aupdate() {
            let days = 7;
            if (time === 2) {
                days = 14;
            } else if (time === 3) {
                days = 90;
            }

            const range = chartRanges[days];
            progressChart.data.labels = range.labels;
            progressChart.data.datasets[0].data = range.values;
            progressChart.data.datasets[0].pointRadius = range.pointRadius;
            progressChart.data.datasets[0].pointHoverRadius = range.pointHoverRadius;
            progressChart.data.datasets[0].pointHitRadius = range.pointHitRadius;
            progressChart.data.datasets[0].pointBackgroundColor = range.pointBackgroundColor;
            progressChart.data.datasets[0].showTooltip = range.showTooltip;
            progressChart.update();
            document.getElementById('btn-label').innerHTML = `${days} Days`;
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