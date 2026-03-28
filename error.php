<?php
function safeTarget(string $target): string
{
    $target = trim($target);
    if ($target === '') {
        return 'index.php';
    }

    if (preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $target)) {
        return 'index.php';
    }

    $path = parse_url($target, PHP_URL_PATH) ?: $target;
    $path = ltrim($path, '/\\');
    $path = basename($path);

    return $path === '' ? 'index.php' : $path;
}

$redirectTarget = safeTarget($_GET['b'] ?? 'index.php');
$errorMessage = htmlspecialchars($_GET['a'] ?? 'none', ENT_QUOTES, 'UTF-8');
$redirectJs = json_encode($redirectTarget);
$redirectHref = htmlspecialchars($redirectTarget, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Shotstreak</title>
    <link rel="stylesheet" href="app.css">
    <link rel="icon" type="image/png" href="assets/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
    <link rel="shortcut icon" href="assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak" />
    <link rel="manifest" href="assets/site.webmanifest" />

    <script>
        function next() { document.location.href = <?php echo $redirectJs; ?>; }
        setTimeout(next, 3500);
    </script>
</head>

<body class="container mx-auto">
    <div class="flex flex-row gap-4 mt-6 justify-center items-center">
        <img src="assets/isoLogo.svg" class="size-24" alt="logo">
        <h1 class="text-2xl font-bold text-center">Shotstreak</h1>
    </div>
    <div>
        <h1 class="text-2xl mt-6 font-bold text-center">An Error Occurred</h1>
        <p class="text-xl text-center text-gray-600 mt-6"><?php echo $errorMessage; ?></p>
    </div>
    <div class="flex justify-center mt-6">
        <a class="text-lg text-coral text-center mx-auto font-bold" href="<?php echo $redirectHref; ?>">Back</a>
    </div>
    <div>
        <p class="text-center text-gray-600 mt-6">Support Page <a class="font-bold" href="support.php">Here</a></p>
    </div>
</body>

</html>