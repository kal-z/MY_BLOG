<?php
session_start();

require_once "config.php";
require_once "User.php";

$db = new Database();
$conn = $db->getConnection();

$user = new User($conn);

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $loggedInUser = $user->login($email, $password);

    if($loggedInUser) {

        $_SESSION['user_id'] = $loggedInUser['id'];
        $_SESSION['username'] = $loggedInUser['username'];
        $_SESSION['profile_image'] = $loggedInUser['profile_image'];

        header("Location: dashboard.php");
        exit();

    } else {

        $message = "Account not found. Please register first.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Login</title>

    <!-- FONT AWESOME -->
    <link 
    rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="post-card" style="max-width:450px; margin-top:80px;">

    <h2>
        Welcome Back
    </h2>

    <p style="text-align:center;">
        Login to your account
    </p>

    <?php if(!empty($message)): ?>

        <p style="color:red; text-align:center;">

            <?= htmlspecialchars($message); ?>

        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Email</label>

        <input 
            type="email" 
            name="email" 
            placeholder="Enter your email"
            required
        >

        <br><br>

        <label>Password</label>

        <input 
            type="password" 
            name="password"
            placeholder="Enter your password"
            required
        >

        <br><br>

        <button type="submit" style="width:100%;">

            <i class="fa-solid fa-right-to-bracket"></i>
            Login

        </button>

    </form>

    <br>

    <p style="text-align:center;">

        Don't have an account?

        <a href="register.php">

            Register here

        </a>

    </p>

</div>

<script>

function toggleDarkMode() {

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")) {

        localStorage.setItem("darkMode", "enabled");

    } else {

        localStorage.setItem("darkMode", "disabled");
    }
}


// LOAD SAVED MODE

window.onload = function() {

    if(localStorage.getItem("darkMode") === "enabled") {

        document.body.classList.add("dark-mode");
    }
};

</script>

</body>
</html>