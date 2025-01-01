<?php

include "validation/log_check.php";
include "db/db_connect.php";
include "validation/autolog.php";


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="Shotstreak">
    <meta name="description" content="Shotstreak helps you keep track of your basketball shotting data. It helps user's stay on track with their goals.">
    <meta name="keywords" content="basketball, shot tracking, shooting">
    <meta name="robots" content="index, follow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="English">
    <meta name="author" content="Simon P">
    <title>Shotstreak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" />
    <style>
      .box {
          --mask:
            radial-gradient(178.89px at 50% calc(100% - 240px),#000 99%,#0000 101%) calc(50% - 160px) 0/320px 100%,
            radial-gradient(178.89px at 50% calc(100% + 160px),#0000 99%,#000 101%) 50% calc(100% - 80px)/320px 100% repeat-x;
          -webkit-mask: var(--mask);
          mask: var(--mask);
        }
    </style>
</head>
<body class="bg-lightgray text-almostblack">
    <section>
        <div class="h-fit w-full bg-white flex flex-col px-4 pt-4 items-center box gap-6 pb-32">
          <div class="flex flex-row gap-4 items-center">
            <img src="assets/isoLogo.svg" class="size-24" alt="logo">
            <h1 class="text-2xl font-bold text-center">Shotstreak</h1>
          </div>
          <div class="space-y-4 flex flex-col items-center xl:flex-row xl:gap-12 gap-6 xl:px-12">
            <div class="space-y-4 md:px-16 md:pt-6">
              <h1 class="text-3xl font-bold">Track Your Shooting Progress With Shotstreak</h1>
              <p class="text-xl">See real progress and improve your skills with powerful analytics and statistics get started with Shotstreak today!</p>
              <div class="space-x-4 hidden xl:block">
                <a href="register.php"><button class="mt-6 bg-coral text-white md:hover:bg-coralhov text-lg px-6 py-3 rounded-full md:hover:scale-110  transition-all">Get Started</button></a>
                <a href="login.php" ><button class="outline-1 outline mt-6 py-3 px-6 rounded-full text-coral font-bold text-lg md:hover:text-coralhov md:hover:scale-110  transition-all">Log In</button></a>
              </div>
            </div>
            <div class="md:w-10/12 max-w-[37rem] 2xl:max-w-[50rem]">
              <img src="assets/dashcomputer.webp" alt="screen" class="md:w-full">
            </div>
            
          </div>
          <div>
            <div class="space-x-4 xl:hidden">
              <a href="register.php"><button class="mt-6 bg-coral text-white md:hover:bg-coralhov text-lg px-6 py-3 rounded-full md:hover:scale-110  transition-all">Get Started</button></a>
              <a href="login.php" ><button class="outline-1 outline mt-6 py-3 px-6 rounded-full text-coral font-bold text-lg md:hover:text-coralhov md:hover:scale-110  transition-all">Log In</button></a>
            </div>
          </div>
        </div>
    
      <section >
        <div class="container mx-auto my-8 grid grid-cols-1 lg:grid-cols-2 lg:gap-12 items-center">
          <div class="px-8">
            <h2 class="text-3xl font-bold">What Is Shotstreak?</h2>
            <p class="text-lg mt-4 text-gray-600">Shotstreak is your personal basketball shot-tracking assistant designed to help you improve your game. Whether you're practicing on your own or competing with friends, Shotstreak allows you to set daily shooting goals, track your progress, and stay motivated. With an easy-to-use interface and powerful analytics, you can visualize your performance, push your limits, and see real results.</p>
          </div>
          <!-- Progress Chart Card -->
          <div class="bg-white p-6 mt-6 rounded-lg shadow-md mx-4">
            <h3 class="text-lg font-semibold text-almostblack dark:text-lightgray mb-4">Progress Chart</h3>
            <canvas id="progressChart" width="680" height="340" class=" w-full" style="display: block; box-sizing: border-box; height: 340px; width: 680px;"></canvas>
          </div>
        </div>
      </section>
    </section>
    <section class="features bg-lightgray py-12 w-11/12 mx-auto">
      <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 text-center">
        <div class=" p-4 pt-8">
          <img src="assets/goal.svg" alt="Goal setting" class="mx-auto mb-4 size-32">
          <h3 class="text-2xl font-semibold">Daily Goal Setting</h3>
          <p class="mt-2 text-gray-600">Set your daily shot goals and stay on track.</p>
        </div>
        <div class="p-4 pt-8">
          <img src="assets/chart.svg" alt="Goal setting" class="mx-auto mb-4 size-32">
          <h3 class="text-2xl font-semibold">Progress Tracking</h3>
          <p class="mt-2 text-gray-600">Track your shooting percentage and see your improvement over time.</p>
        </div>
        <div class=" p-4 pt-8">
          <img src="assets/badge.svg" alt="Goal setting" class="mx-auto mb-4 size-32">
          <h3 class="text-2xl font-semibold">Achievements and Badges</h3>
          <p class="mt-2 text-gray-600">Earn badges and celebrate your milestones.</p>
        </div>
        <div class=" p-4 pt-8">
          <img src="assets/social.svg" alt="Goal setting" class="mx-auto mb-4 size-32">
          <h3 class="text-2xl font-semibold">Shotstreak for Teams</h3>
          <p class="mt-2 text-gray-600">Set up Shotstreak for your team. Coaches, track your player's progress</p>
          <a href="coachreg.php"><button class="mt-6 bg-coral text-white md:hover:bg-coralhov text-lg px-6 py-3 rounded-full md:hover:scale-110  transition-all">Create a team</button></a>
        </div>
        <!-- Repeat for other features -->
      </div>
    </section>
    <section>
      <div class="px-8 pb-24">
        <div>
          <h2 class="text-4xl font-bold my-12">How it Works</h2>
        </div>
        <div>
          <ol class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
            <li class="flex flex-col gap-2 shadow-md p-6 rounded-lg bg-white">
              <img class="size-20" src="assets/account.svg" alt="icon">
              <h3 class="text-2xl font-bold">Create an Account</h3>
              <p class="text-lg">Click on Get Started and create a username and password</p>
            </li>
            <li class="flex flex-col gap-2 shadow-md p-6 rounded-lg bg-white">
              <img class="size-20" src="assets/goalico.svg" alt="icon">
              <h3 class="text-2xl font-bold">Set Your Shot Goal</h3>
              <p class="text-lg">Set your daily shot goal and input every shot you take</p>
            </li>
            <li class="flex flex-col gap-2 shadow-md p-6 rounded-lg bg-white">
              <img class="size-20" src="assets/graphico.svg" alt="icon">
              <h3 class="text-2xl font-bold">Track Your Progress Daily</h3>
              <p class="text-lg">See your shooting percent as well as many other powerful analytics</p>
            </li>
            <li class="flex flex-col gap-2 shadow-md p-6 rounded-lg bg-white">
              <img class="size-20" src="assets/badgeico.svg" alt="icon">
              <h3 class="text-2xl font-bold">Leaderboard & Achievements</h3>
              <p class="text-lg">View your ranking among others and your achievements</p>
            </li>
          </ol>
        </div>
      </div>
    </section>
    <section>
      <div class="px-8 pb-24 xl:px-24">
        <div>
          <h2 class="text-4xl font-bold my-12">Why Use It?</h2>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 ">
        <div class="flex flex-col gap-2">
          <img class="size-20" src="assets/improve.svg" alt="icon">
          <h3 class="text-2xl font-bold">Improve Your Shooting</h3>
          <p class="text-lg">Shotstreak helps you track your daily shooting performance, giving you real-time data on shot attempts and accuracy. This lets you easily identify areas for improvement, refine your technique, and watch your shooting percentage rise over time.</p>
        </div>
        <div class="flex flex-col gap-2">
          <img class="size-20" src="assets/thumbsup.svg" alt="icon">
          <h3 class="text-2xl font-bold">Stay Consistent</h3>
          <p class="text-lg">Consistency is key, and Shotstreak keeps you on track by helping you set daily shot goals. With streak tracking, you'll stay motivated, knowing that every day counts toward reaching your basketball goals.</p>
        </div>
        <div class="flex flex-col gap-2">
          <img class="size-20" src="assets/motivate.svg" alt="icon">
          <h3 class="text-2xl font-bold">Motivate with Achievements</h3>
          <p class="text-lg">Shotstreak rewards your hard work with achievements and badges as you reach milestones. Whether it's making 100 shots or improving your accuracy, these rewards push you to stay motivated and improve further.</p>
        </div>
      </div>
    </section>
      
    <footer class="bg-darkslate py-8 text-white">
      <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="footer-links flex flex-col justify-center items-center">
          <a href="index.php" class="block mb-2 text-center">Home</a>
          <a href="register.php" class="block mb-2 text-center">Register</a>
          <a href="login.php" class="block mb-2 text-center">Login</a>
          <a href="support.php" class="block mb-2 text-center">Support</a>
          <!-- Add more links -->
        </div> 
        <div class="text-center flex flex-col justify-center items-center">
          <p class="text-xs">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
          <p class="text-xs">Website Created by Simon Papp - <a target="_blank" class="font-bold" href="https://simonsites.com">SimonSites</a></p>
        </div>
      </div>
    </footer>
    <!-- Chart.js Script -->
    <script>
      
      const ctx = document.getElementById('progressChart').getContext('2d');
        const progressChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ["2024-08-22","2024-08-29","2024-09-01","2024-09-03","2024-09-07","2024-09-09","2024-09-12"],
                datasets: [{
                    label: 'Shooting Accuracy (%)',
                    data: [57.27272727272727,46.53465346534654,51.21951219512195,52,65.45454545454545,52,64.86486486486487],
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
    
  </script>
  
</body>
</html>