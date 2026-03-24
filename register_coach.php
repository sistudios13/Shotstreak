<?php

require 'db/db_connect.php';

// Connect to the database


try {
    $pdo = new PDO("mysql:host=$DATABASE_HOST;dbname=$DATABASE_NAME", $DATABASE_USER, $DATABASE_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$coach_name = $_POST['coach_name'];
$email = $_POST['email'];
$password = $_POST['password'];
$team_name = $_POST['team_name'];
// Validate input
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>setTimeout(() => window.location.href = 'error.php?a=Invalid registration&b=index.php', 700);</script>";
    exit(); 
}

if (!isset($_POST['coach_name'], $_POST['password'], $_POST['email'])) {
	// Could not get the data that should have been sent.
	exit('Please complete the registration form!');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['coach_name']) || empty($_POST['password']) || empty($_POST['email'])) {
	// One or more values are empty.
	exit('Please complete the registration form');
}

if (preg_match('/^[a-zA-Z0-9 \-]+$/', $_POST['coach_name']) == 0) {
	echo "<script>setTimeout(() => window.location.href = 'error.php?a=Invalid Username&b=register.php', 700);</script>";
    exit();
}
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	exit('Password must be between 5 and 20 characters long!');
}

if (strlen($_POST['coach_name']) > 50 || strlen($_POST['coach_name']) < 2) {
	exit('Username must be between 2 and 20 characters long!');
}

if (strlen($_POST['email']) > 200) {
	exit('Email must be less than 200 characters long!');
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ? OR email = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
	$stmt->bind_param('ss', $_POST['coach_name'], $_POST['email']);
	$stmt->execute();
	$stmt->store_result();
	// Store the result so we can check if the account exists in the database.
	if ($stmt->num_rows > 0) {
		// Username already exists
		
        echo "<script>setTimeout(() => window.location.href = 'error.php?a=User already exists&b=coachreg.php', 700);</script>";
            exit();
	} else {

// Prepare SQL and bind parameters
$stmt = $pdo->prepare("INSERT INTO coaches (coach_name, email, password, team_name, goal) VALUES (:coach_name, :email, :password, :team_name, 100)");
$stmt->bindParam(':coach_name', $coach_name);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $hashed_password);
$stmt->bindParam(':team_name', $team_name);

// Execute the statement
try {
    $stmt->execute();
    // add the coach information to the accounts tables
    if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, user_type) VALUES (?, ?, ?, "coach")')) {
        // We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
        $stmt->bind_param('sss', $coach_name, $password, $email);
        $stmt->execute();
        echo "<script>setTimeout(() => window.location.href = 'success.php?b=login.php', 700);</script>";
    } else {
        // Something is wrong with the SQL statement, so you must check to make sure your accounts table exists with all three fields.
        echo 'Could not prepare statement!'; // ERROR PAGE
    }
}

catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Duplicate entry
        echo "<script>setTimeout(() => window.location.href = 'error.php?a=User already exists&b=coachreg.php', 700);</script>";
        exit(); //ERROR PAGE
    } else {
    die("An error occurred: " . $e->getMessage());
    }
    }
    }

}

//

} 
