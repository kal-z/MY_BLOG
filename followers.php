<?php
session_start();

require_once "navbar.php";
require_once "config.php";

$db = new Database();
$conn = $db->getConnection();

/* CHECK USER ID */
if(!isset($_GET['id'])) {

    header("Location: posts.php");
    exit();
}

$user_id = intval($_GET['id']);


/* GET FOLLOWERS */
$query = "
    SELECT users.*
    FROM followers
    JOIN users
    ON followers.follower_id = users.id
    WHERE followers.following_id = :user_id
    ORDER BY users.username ASC
";

$stmt = $conn->prepare($query);

$stmt->bindParam(':user_id', $user_id);

$stmt->execute();
?>

<!DOCTYPE html>
<html>

<head>

    <title>Followers</title>

    <!-- FONT AWESOME -->
    <link 
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="messages-page">

    <div class="messages-header">

        <h2>

            <i class="fa-solid fa-users"></i>

            Followers

        </h2>

    </div>

    <?php if($stmt->rowCount() > 0): ?>

        <?php while($user = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

            <div class="chat-user-card">

                <!-- PROFILE -->
                <a 
                    href="profile.php?id=<?= $user['id']; ?>"
                    style="
                        display:flex;
                        align-items:center;
                        gap:15px;
                        flex:1;
                        text-decoration:none;
                    "
                >

                    <?php if(!empty($user['profile_image'])): ?>

                        <img 
                            src="<?= htmlspecialchars($user['profile_image']); ?>"
                            class="chat-user-pic"
                        >

                    <?php else: ?>

                        <img 
                            src="uploads/default.png"
                            class="chat-user-pic"
                        >

                    <?php endif; ?>

                    <div class="chat-user-info">

                        <h4>
                            <?= htmlspecialchars($user['username']); ?>
                        </h4>

                        <p>
                            Follows you
                        </p>

                    </div>

                </a>

                <!-- MESSAGE BUTTON -->
                <a 
                    href="chat.php?id=<?= $user['id']; ?>"
                    style="
                        width:45px;
                        height:45px;
                        background:#ec4899;
                        color:white;
                        border-radius:50%;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        text-decoration:none;
                        flex-shrink:0;
                    "
                >
                   <i class="fa-solid fa-paper-plane"></i>

                </a>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="post-card">

            <p style="text-align:center;">
                No followers yet.
            </p>

        </div>

    <?php endif; ?>

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


/* LOAD SAVED MODE */

window.onload = function() {

    if(localStorage.getItem("darkMode") === "enabled") {

        document.body.classList.add("dark-mode");
    }
};

</script>
</body>
</html>