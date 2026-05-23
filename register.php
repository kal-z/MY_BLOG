<?php
session_start();

require_once "config.php";
require_once "User.php";

$db = new Database();
$conn = $db->getConnection();

$user = new User($conn);

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];

    $email = $_POST['email'];

    $password = $_POST['password'];

    // CHECK IF USER EXISTS
    if($user->userExists($username, $email)) {

        $message = "User already registered!";

    } else {

        // REGISTER USER
        if($user->register($username, $email, $password)) {

            // LOGIN USER AUTOMATICALLY
            $loggedInUser = $user->login($email, $password);

            // SAVE SESSION
            $_SESSION['user_id'] = $loggedInUser['id'];

            $_SESSION['username'] = $loggedInUser['username'];

            $_SESSION['profile_image'] = $loggedInUser['profile_image'];

            // GO TO DASHBOARD
            header("Location: dashboard.php");

            exit();

        } else {

            $message = "Something went wrong. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Register</title>

    <!-- FONT AWESOME -->
    <link 
    rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="auth-container">

    <h2>
        Create Account
    </h2>

    <form method="POST">

        <div class="input-group">

            <label>
                <i class="fa-solid fa-user"></i>
                Username
            </label>

            <input 
                type="text" 
                name="username" 
                placeholder="Enter username"
                required
            >

        </div>

        <div class="input-group">

            <label>
                <i class="fa-solid fa-envelope"></i>
                Email
            </label>

            <input 
                type="email" 
                name="email" 
                placeholder="Enter email"
                required
            >

        </div>

        <div class="input-group">

            <label>
                <i class="fa-solid fa-lock"></i>
                Password
            </label>

            <input 
                type="password" 
                name="password" 
                placeholder="Enter password"
                required
            >

        </div>

        <button type="submit" class="auth-btn">

            <i class="fa-solid fa-user-plus"></i>
            Register

        </button>

    </form>

    <?php if($message != ""): ?>

        <p class="message">
            <?= $message ?>
        </p>

    <?php endif; ?>

    <div class="login-link">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </div>

</div>

</body>
</html>