<?php

require 'db/db_connect.php';
$conn = $con;


// Get the token from the URL
$token = $_GET['token'];



// Verify the token
$query = "SELECT coach_id, player_email, player_name FROM invitations WHERE token = ? AND status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $invite = $result->fetch_assoc();
    $coach_id = $invite['coach_id'];
    $player_email = $invite['player_email'];
    $player_name = $invite['player_name'];

    $stmt = $conn->prepare('SELECT team_name, coach_name FROM coaches WHERE coach_id = ?');
    $stmt->bind_param('i', $coach_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $names = $res->fetch_assoc();
    $team_name = $names['team_name'];
    $coach_name = $names['coach_name'];

    // Show registration form for the player (populate email, and let them set a password)
    // After registering, associate the player with the coach and update the invitation status
} else {
    header('Location: error.php?a=Invalid or exipred invitation');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Shotstreak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/htmx.org"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-light-gray">
    <div class="flex items-center justify-center min-h-screen" x-data="{loading : false}">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <div class="text-center mb-6">
                <img src="assets/isoLogo.svg" alt="Shotstreak Logo" class="mx-auto h-16">
                <h1 class="text-2xl font-bold text-almostblack mt-4">Join "<?php echo htmlspecialchars($team_name); ?>"</h1>
            </div>
            <!-- Form -->
            <form hx-post="register_player.php" hx-trigger="submit" hx-target="#response" hx-swap="innerHTML" @submit="loading = true" class="space-y-4" autocomplete="off">
                <input type="hidden" name="coach_id" value="<?php echo htmlspecialchars($coach_id); ?>">
                <input type="hidden" name="invite_token" value="<?php echo htmlspecialchars($token); ?>">

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="username" id="username" class="mt-1 p-2 w-full border rounded-md focus-visible:outline-coral" value="<?php echo htmlspecialchars($player_name); ?>" readonly>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                    <input type="email" name="email" id="email" class="mt-1 p-2 w-full border rounded-md focus-visible:outline-coral" value="<?php echo htmlspecialchars($player_email); ?>" readonly>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" minlength="5" maxlength="20" class="mt-1 p-2 w-full border rounded-md focus-visible:outline-coral" required>
                </div>

                <button type="submit" class="w-full md:hover:bg-coralhov bg-coral text-white py-2 rounded-md font-semibold hover:bg-coral-red-light transition-colors">Register</button>
            </form>


        </div>
        <!-- Response Div -->
        <div id="response"></div>


        <div class="fixed top-1/2 " x-show="loading" x-cloak>
            <svg width="40" height="40" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <style>
                    .spinner_qM83 {
                        animation: spinner_8HQG 1.05s infinite
                    }

                    .spinner_oXPr {
                        animation-delay: .1s
                    }

                    .spinner_ZTLf {
                        animation-delay: .2s
                    }

                    @keyframes spinner_8HQG {

                        0%,
                        57.14% {
                            animation-timing-function: cubic-bezier(0.33, .66, .66, 1);
                            transform: translate(0)
                        }

                        28.57% {
                            animation-timing-function: cubic-bezier(0.33, 0, .66, .33);
                            transform: translateY(-6px)
                        }

                        100% {
                            transform: translate(0)
                        }
                    }
                </style>
                <circle class="spinner_qM83" fill="#ff6f61" cx="4" cy="12" r="3" />
                <circle fill="#ff6f61" class="spinner_qM83 spinner_oXPr" cx="12" cy="12" r="3" />
                <circle fill="#ff6f61" class="spinner_qM83 spinner_ZTLf" cx="20" cy="12" r="3" />
            </svg>
        </div>
    </div>
    <footer class="bg-darkslate py-8 text-white">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="footer-links flex flex-col justify-center items-center">
                <a href="index.php" class="block mb-2 text-center">Home</a>
                <a href="register.php" class="block mb-2 text-center">Register</a>
                <a href="login.php" class="block mb-2 text-center">Login</a>
                <a href="support.php" class="block mb-2 text-center">Support</a>

            </div>
            <div class="text-center flex flex-col justify-center items-center">
                <p class="text-xs">© <?php echo date("Y") ?> Shotstreak. All rights reserved.</p>
                <p class="text-xs">Website Created by Simon Papp - <a target="_blank" class="font-bold" href="https://simonsites.com">Simon Sites</a></p>
            </div>
        </div>
    </footer>
</body>

</html>