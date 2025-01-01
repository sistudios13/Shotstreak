<?php
session_start();
include "db/db_connect.php";
function autoLogin($con) {
  if (isset($_COOKIE['remember_me']) && !isset($_SESSION['loggedin'])) {
      $token = $_COOKIE['remember_me'];

      // Query to find the user with this token
      $stmt = $con->prepare("SELECT id, user_type, username FROM accounts WHERE remember_token = ? AND remember_expiration > NOW()");
      $stmt->bind_param("s", $token);
      $stmt->execute();
      $stmt->bind_result($id, $type, $usern);

      if ($stmt->fetch()) {
          // Log in the user by setting the session
          
          // Optionally, regenerate session ID for security
          session_regenerate_id();
          $_SESSION['loggedin'] = TRUE;
          $_SESSION['name'] = $usern;
          $_SESSION['id'] = $id;

          if ($type === 'user') {
              
              $_SESSION['type'] = $type;
              echo "<script>window.location.href = 'home.php'</script>";
          }

          if ($type === 'coach') {
              
              $_SESSION['type'] = $type;
              $_SESSION['email'] = $_POST['email'];
              echo "<script>window.location.href = 'coach_dashboard.php'</script>";
          }

          if ($type === 'player') {
              $_SESSION['email'] = $_POST['email'];
              $_SESSION['type'] = $type;
              echo "<script>window.location.href = 'player_dashboard.php'</script>";
          }
      } else {
          // Token is invalid or expired, remove the cookie
          setcookie('remember_me', '', time() - 3600, '/'); // Delete the cookie
      }
  }
}


autoLogin($con);
$logged = false;

if (isset($_SESSION['loggedin'])) {
	$logged = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Shotstreak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" />
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
      .box {
          --mask:
            radial-gradient(178.89px at 50% calc(100% - 240px),#000 99%,#0000 101%) calc(50% - 160px) 0/320px 100%,
            radial-gradient(178.89px at 50% calc(100% + 160px),#0000 99%,#000 101%) 50% calc(100% - 80px)/320px 100% repeat-x;
          -webkit-mask: var(--mask);
          mask: var(--mask);
        }

        html {
          scroll-behavior: smooth;
        }
    </style>
</head>
<body class="bg-lightgray text-almostblack">
    <section>
        <div class="h-fit w-full bg-white flex flex-col px-4 pt-4 items-center box gap-6 pb-32">
          <div class="flex flex-row gap-4 items-center">
            <a href="index.php"><img src="assets/isoLogo.svg" class="size-24" alt="logo"></a>
            <h1 class="text-2xl font-bold text-left">Shotstreak <br><span class="text-coral"> Support</span></h1>
          </div>
          <div class="space-y-4 flex flex-col text-center items-center xl:flex-row xl:gap-12 gap-6 xl:px-12">
            <div class="space-y-4 md:px-16 md:pt-6">
              <h1 class="text-3xl font-bold">Welcome to Shotstreak Support!</h1>
              <p class="text-xl max-w-lg">See real progress and improve your skills with powerful analytics and statistics get started with Shotstreak today!</p>
              <div class="space-y-4 flex flex-col ">
                <a href="#faq"><button class="mt-6 bg-coral text-white md:hover:bg-coralhov text-lg px-6 py-3 rounded-full md:hover:scale-110  transition-all">See our FAQ</button></a>
                <?php if ($logged): ?>
                  <a href="index.php" class="text-coral text-sm font-semibold">Back to Dashboard</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
    </section>

    <section id="troubleshooting">
      <div class="px-8 xl:px-24 pb-12">
        <h2 class="text-3xl font-bold">
          Troubleshooting Tips
        </h2>
        <div>
          <h3 class="text-2xl font-semibold mb-6 mt-2 text-coral">Common Issues</h3>
          <div>
            <ul class="space-y-4">
              <li>
                <p class="text-lg ml-1 font-bold">Having trouble logging in?</p>
                <p>Double-check your credentials or use the “Forgot Password” link to reset your login.</p>
              </li>
              <li>
                <p class="text-lg ml-1 font-bold">Shot tracking not saving?</p>
                <p>Make sure you’re connected to the internet and refresh your page. If the issue persists, try clearing your browser’s cache.</p>
              </li>
              <li>
                <p class="text-lg ml-1 font-bold">Leaderboard not updating?</p>
                <p>This can be due to recent changes. Wait a moment and refresh your dashboard to see the latest results.</p>
              </li>
              
            </ul>
          </div>
        </div>
      </div>
    </section>
    <section id="faq">
      <div class="px-8 xl:px-24 pb-12">
        <div class="w-full divide-y divide-neutral-300 overflow-hidden rounded-2xl border border-neutral-300 bg-neutral-50/40 dark:divide-neutral-700 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-300">
          <div x-data="{ isExpanded: false }" class="divide-y divide-neutral-300 dark:divide-neutral-700">
              <button id="controlsAccordionItemOne" type="button" class="flex w-full items-center justify-between gap-4 bg-neutral-50 p-4 text-left underline-offset-2 hover:bg-neutral-50/75 focus-visible:bg-neutral-50/75 focus-visible:underline focus-visible:outline-none dark:bg-neutral-900 dark:hover:bg-neutral-900/75 dark:focus-visible:bg-neutral-900/75" aria-controls="accordionItemOne" @click="isExpanded = ! isExpanded" :class="isExpanded ? 'text-onSurfaceStrong dark:text-onSurfaceDarkStrong font-bold'  : 'text-onSurface dark:text-onSurfaceDark font-medium'" :aria-expanded="isExpanded ? 'true' : 'false'">
                  How do I set a new shot goal?
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true" :class="isExpanded  ?  'rotate-180'  :  ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                  </svg>
              </button>
              <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region" aria-labelledby="controlsAccordionItemOne" x-collapse>
                  <div class="p-4 text-sm sm:text-base text-pretty">
                    You can set or update your daily shot goal from your profile settings or dashboard. Just head to the “Change Goal” section, enter your new target, and save it.
                  </div>
              </div>
          </div>
          <div x-data="{ isExpanded: false }" class="divide-y divide-neutral-300 dark:divide-neutral-700">
              <button id="controlsAccordionItemTwo" type="button" class="flex w-full items-center justify-between gap-4 bg-neutral-50 p-4 text-left underline-offset-2 hover:bg-neutral-50/75 focus-visible:bg-neutral-50/75 focus-visible:underline focus-visible:outline-none dark:bg-neutral-900 dark:hover:bg-neutral-900/75 dark:focus-visible:bg-neutral-900/75" aria-controls="accordionItemTwo" @click="isExpanded = ! isExpanded" :class="isExpanded ? 'text-onSurfaceStrong dark:text-onSurfaceDarkStrong font-bold'  : 'text-onSurface dark:text-onSurfaceDark font-medium'" :aria-expanded="isExpanded ? 'true' : 'false'">
                  How can I contact support?
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true" :class="isExpanded  ?  'rotate-180'  :  ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                  </svg>
              </button>
              <div x-cloak x-show="isExpanded" id="accordionItemTwo" role="region" aria-labelledby="controlsAccordionItemTwo" x-collapse>
                  <div class="p-4 text-sm sm:text-base text-pretty">
                      Reach out to our support team via email at <a href="mailto:support@shotstreak.ca" class="underline underline-offset-2 text-black dark:text-white">support@shotstreak.ca</a>.
                  </div>
              </div>
          </div>
          <div x-data="{ isExpanded: false }" class="divide-y divide-neutral-300 dark:divide-neutral-700">
              <button id="controlsAccordionItemThree" type="button" class="flex w-full items-center justify-between gap-4 bg-neutral-50 p-4 text-left underline-offset-2 hover:bg-neutral-50/75 focus-visible:bg-neutral-50/75 focus-visible:underline focus-visible:outline-none dark:bg-neutral-900 dark:hover:bg-neutral-900/75 dark:focus-visible:bg-neutral-900/75" aria-controls="accordionItemThree" @click="isExpanded = ! isExpanded" :class="isExpanded ? 'text-onSurfaceStrong dark:text-onSurfaceDarkStrong font-bold'  : 'text-onSurface dark:text-onSurfaceDark font-medium'" :aria-expanded="isExpanded ? 'true' : 'false'">
              Can I see my overall shooting percentage?
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true" :class="isExpanded  ?  'rotate-180'  :  ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                  </svg>
              </button>
              <div x-cloak x-show="isExpanded" id="accordionItemThree" role="region" aria-labelledby="controlsAccordionItemThree" x-collapse>
                  <div class="p-4 text-sm sm:text-base text-pretty">
                  Yes! Your dashboard includes stats on your shooting performance, including your daily and overall shot percentages.
                  </div>
              </div>
          </div>
          <div x-data="{ isExpanded: false }" class="divide-y divide-neutral-300 dark:divide-neutral-700">
              <button id="controlsAccordionItemFour" type="button" class="flex w-full items-center justify-between gap-4 bg-neutral-50 p-4 text-left underline-offset-2 hover:bg-neutral-50/75 focus-visible:bg-neutral-50/75 focus-visible:underline focus-visible:outline-none dark:bg-neutral-900 dark:hover:bg-neutral-900/75 dark:focus-visible:bg-neutral-900/75" aria-controls="accordionItemThree" @click="isExpanded = ! isExpanded" :class="isExpanded ? 'text-onSurfaceStrong dark:text-onSurfaceDarkStrong font-bold'  : 'text-onSurface dark:text-onSurfaceDark font-medium'" :aria-expanded="isExpanded ? 'true' : 'false'">
              How do I export my shot data?
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true" :class="isExpanded  ?  'rotate-180'  :  ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                  </svg>
              </button>
              <div x-cloak x-show="isExpanded" id="accordionItemFour" role="region" aria-labelledby="controlsAccordionItemFour" x-collapse>
                  <div class="p-4 text-sm sm:text-base text-pretty">
                  Go to your profile and look for the “Export All Data” option. You can download your shot history as a CSV file to keep track and share with others.
                  </div>
              </div>
          </div>
        </div>
      </div>
    </section>
    <section id="support">
      <div class="px-8 xl:px-24 pb-12">
        <h2 class="text-3xl font-bold">
          Get in Touch
        </h2>
        <div>
          <h3 class="text-2xl font-semibold mb-6 mt-2 text-coral">Need more help? We’re here to support you!</h3>
          <div>
            <ul class="space-y-4">
              <li>
                <p class="text-lg font-bold">Email:</p>
                <a href="mailto:support@shotstreak.ca">support@shotstreak.ca</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
    
      <footer class="bg-darkslate py-8 text-white">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="footer-links flex flex-col justify-center items-center">
            <?php if ($logged): ?>
              <a href="index.php" class="block text-center">Dashboard</a>
            <?php else: ?>
              <a href="index.php" class="block mb-2 text-center">Home</a>
              <a href="register.php" class="block mb-2 text-center">Register</a>
              <a href="login.php" class="block mb-2 text-center">Login</a>
              <a href="support.php" class="block mb-2 text-center">Support</a>
            <?php endif;?>
          </div> 
          <div class="text-center flex flex-col justify-center items-center">
            <p class="text-xs">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
            <p class="text-xs">Website Created by Simon Papp - <a target="_blank" class="font-bold" href="https://simonsites.com">SimonSites</a></p>
          </div>
        </div>
      </footer>
  
  
</body>
</html>