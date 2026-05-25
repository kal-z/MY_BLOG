<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php";

$db = new Database();
$conn = $db->getConnection();

$messageCount = 0;

if(isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $messageQuery = "
        SELECT COUNT(*) AS total
        FROM messages
        WHERE receiver_id = :user_id
        AND is_read = 0
    ";

    $messageStmt = $conn->prepare($messageQuery);

    $messageStmt->bindParam(':user_id', $user_id);

    $messageStmt->execute();

    $messageData = $messageStmt->fetch(PDO::FETCH_ASSOC);

    $messageCount = $messageData['total'];
}
?>

<!DOCTYPE html>
<html>

<head>

    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

</head>

<body>

<div class="navbar">

    <a href="dashboard.php" class="nav-link">
        My Profile
    </a>

    <a href="posts.php" class="nav-link">
        Blogs
    </a>

    <a href="create_post.php" class="nav-link">
        Create Post
    </a>

    <a href="my_likes.php" class="nav-link">
        My Likes
    </a>

    <!-- MESSAGE ICON -->
    <?php

/* =========================================
   UNREAD MESSAGES COUNT
========================================= */

$unreadQuery = "
    SELECT COUNT(*) AS total_unread
    FROM messages
    WHERE receiver_id = :user_id
    AND is_read = 0
";

$unreadStmt = $conn->prepare($unreadQuery);

$unreadStmt->bindParam(':user_id', $_SESSION['user_id']);

$unreadStmt->execute();

$unreadData = $unreadStmt->fetch(PDO::FETCH_ASSOC);

?>



    <!-- DARK MODE -->
    <button onclick="toggleDarkMode()" id="darkModeBtn">
     <i class="fa-solid fa-moon"></i>
    </button>

<?php

$notifQuery = "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = :user_id
    AND is_read = 0
";

$notifStmt = $conn->prepare($notifQuery);

$notifStmt->bindParam(':user_id', $_SESSION['user_id']);

$notifStmt->execute();

$notifData = $notifStmt->fetch(PDO::FETCH_ASSOC);

?>

<a href="notifications.php" class="notif-bell">

    <i class="fa-solid fa-bell"></i>

    <?php if($notifData['total'] > 0): ?>

        <span class="notif-count">

            <?= $notifData['total']; ?>

        </span>

    <?php endif; ?>

</a>
<a href="messages.php" class="message-icon">

    <i class="fa-solid fa-envelope"></i> 

    <?php if($unreadData['total_unread'] > 0): ?>

        <span class="message-badge">

            <?= $unreadData['total_unread']; ?>

        </span>

    <?php endif; ?>

 
</a>
    <!-- USER -->
    <div class="welcome-text">

        Welcome,
        <?= htmlspecialchars($_SESSION['username']); ?>

    </div>

    <!-- LOGOUT -->
    <a href="logout.php" class="logout-link">
        Logout
    </a>

</div>

</body>
</html>