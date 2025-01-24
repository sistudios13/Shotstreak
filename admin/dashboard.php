<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: index.php");
    exit();
}

require "../db/db_connect.php";

$stmt = $con->prepare('SELECT id, user_type, username, email, verified, banned  FROM accounts');
$stmt->execute();
$results = $stmt->get_result();
$fetch = $results->fetch_all();

$tot = $con->prepare('SELECT COUNT(*) AS total FROM accounts');
$tot->execute();
$res = $tot->get_result();
$total = $res->fetch_assoc();

$stmt = $con->prepare('SELECT SUM(shots_taken) AS total FROM user_shots');
$stmt->execute();
$shot = $stmt->get_result();
$sh = $shot->fetch_assoc();
$taken = $sh['total'];



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwindextras.js"></script>
    <link rel="icon" type="image/png" href="favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="favicon.svg" />
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Shotstreak Admin" />
    <link rel="manifest" href="site.webmanifest" />
</head>
<body class="p-1">
    <div class="pb-6 flex justify-between w-full">
        <p>Welcome Back! </p> <a href="admin_logout.php" class="p-1 bg-blue-300">Logout</a>
    </div>
    <table>
        <thead>
            <tr class="bg-slate-100 w-full">
                <td>Id</td>
                <td>Type</td>
                <td>Username</td>
                <td>Email</td>
                <td class="pr-2">Verified</td>
                <td class="pr-2">Banned</td>
                <td>Actions</td>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($fetch as $item) {
                echo "<tr class='border'>";
                foreach($item as $data) {
                    ?>
                    <td class='p-1'>
                    <?php echo $data; ?>
                    </td>
                    <?php
                }
                ?>
                <td>
                    <form method="POST" action="actions.php" onsubmit="return confirm('Are you sure you want to ban this player')">
                        <input type="hidden" name="id" id="id" value="<?php echo $item[0]; ?>">
                        <button type="submit" class="p-1 <?php
                                if ($item[5] == 1) {
                                    echo "bg-green-500";
                                }

                                if($item[5] == 0) {
                                    echo "bg-red-500";
                                }
                            ?>
                             w-full">

                            <?php
                                if ($item[5] == 1) {
                                    echo "Unban";
                                }

                                if($item[5] == 0) {
                                    echo "Ban";
                                }
                            ?>
                        </button>
                    </form>
                </td>
                <?php
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <div class="pt-10">
        <span>User Count: <?php echo $total['total']?></span>
        <span>Shots Taken: <?php echo $taken ?></span>
    </div>
</body>
</html>
