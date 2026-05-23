<?php
session_start();

require_once "navbar.php";
require_once "config.php";

$db = new Database();
$conn = $db->getConnection();

if(!isset($_GET['id'])) {

    die("User not found.");
}

$user_id = intval($_GET['id']);


/* GET USER */
$userQuery = "
    SELECT *
    FROM users
    WHERE id = :id
";

$userStmt = $conn->prepare($userQuery);

$userStmt->bindParam(':id', $user_id);

$userStmt->execute();

$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if(!$user) {

    die("User not found.");
}


/* GET POSTS */
$postQuery = "
    SELECT *
    FROM posts
    WHERE user_id = :user_id
    ORDER BY created_at DESC
";

$postStmt = $conn->prepare($postQuery);

$postStmt->bindParam(':user_id', $user_id);

$postStmt->execute();


/* FOLLOWERS */
$followerQuery = "
    SELECT COUNT(*) AS total
    FROM followers
    WHERE following_id = :user_id
";

$followerStmt = $conn->prepare($followerQuery);

$followerStmt->bindParam(':user_id', $user_id);

$followerStmt->execute();

$followerData = $followerStmt->fetch(PDO::FETCH_ASSOC);


/* FOLLOWING */
$followingQuery = "
    SELECT COUNT(*) AS total
    FROM followers
    WHERE follower_id = :user_id
";

$followingStmt = $conn->prepare($followingQuery);

$followingStmt->bindParam(':user_id', $user_id);

$followingStmt->execute();

$followingData = $followingStmt->fetch(PDO::FETCH_ASSOC);


/* POSTS COUNT */
$postCountQuery = "
    SELECT COUNT(*) AS total
    FROM posts
    WHERE user_id = :user_id
";

$postCountStmt = $conn->prepare($postCountQuery);

$postCountStmt->bindParam(':user_id', $user_id);

$postCountStmt->execute();

$postCountData = $postCountStmt->fetch(PDO::FETCH_ASSOC);


/* CHECK FOLLOWING */

$isFollowing = false;

if(isset($_SESSION['user_id'])) {

    $checkFollow = "
        SELECT *
        FROM followers
        WHERE follower_id = :follower_id
        AND following_id = :following_id
    ";

    $checkStmt = $conn->prepare($checkFollow);

    $checkStmt->bindParam(':follower_id', $_SESSION['user_id']);

    $checkStmt->bindParam(':following_id', $user_id);

    $checkStmt->execute();

    $isFollowing = $checkStmt->rowCount() > 0;
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>
        <?= htmlspecialchars($user['username']); ?>
    </title>

    <link 
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="post-card">

    <!-- PROFILE TOP -->
    <div style="
        display:flex;
        align-items:center;
        gap:25px;
        flex-wrap:wrap;
    ">

        <!-- PROFILE IMAGE -->
        <?php if(!empty($user['profile_image'])): ?>

            <img 
                src="<?= htmlspecialchars($user['profile_image']); ?>"
                class="dashboard-profile-pic"
            >

        <?php else: ?>

            <img 
                src="uploads/default.png"
                class="dashboard-profile-pic"
            >

        <?php endif; ?>


        <!-- RIGHT SIDE -->
        <div style="flex:1;">

            <h2 style="
                margin-bottom:10px;
                text-align:left;
            ">
                <?= htmlspecialchars($user['username']); ?>
            </h2>

           <!-- STATS -->
<div class="profile-stats">

    <!-- POSTS -->
    <div class="stat-box">

        <span>
            Posts
        </span>

        <div class="stat-number">
            <?= $postCountData['total']; ?>
        </div>

    </div>


    <!-- FOLLOWERS -->
    <div class="stat-box">

        <span>

            <a 
                href="followers.php?id=<?= $user_id; ?>"
                style="
                    text-decoration:none;
                    color:inherit;
                "
            >
                Followers
            </a>

        </span>

        <div class="stat-number">
            <?= $followerData['total']; ?>
        </div>

    </div>


    <!-- FOLLOWING -->
    <div class="stat-box">

        <span>
            Following
        </span>

        <div class="stat-number">
            <?= $followingData['total']; ?>
        </div>

    </div>

</div>


            <!-- ACTIONS -->
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user_id): ?>

                <div class="actions" style="margin-top:20px;">

                    <!-- FOLLOW -->
                    <form action="follow.php" method="POST">

                        <input 
                            type="hidden"
                            name="following_id"
                            value="<?= $user_id; ?>"
                        >

                        <button type="submit">

                            <?php if($isFollowing): ?>

                                <i class="fa-solid fa-user-check"></i>
                                Following

                            <?php else: ?>

                                <i class="fa-solid fa-user-plus"></i>
                                Follow

                            <?php endif; ?>

                        </button>

                    </form>


                    <!-- MESSAGE -->
                    <a 
                        href="chat.php?id=<?= $user_id; ?>"
                        style="
                            background:#ec4899;
                            color:white;
                            padding:12px 18px;
                            border-radius:14px;
                            text-decoration:none;
                            font-weight:600;
                        "
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                        Message

                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>


<!-- POSTS -->

<h2>
    Posts
</h2>

<?php if($postStmt->rowCount() > 0): ?>

    <?php while($post = $postStmt->fetch(PDO::FETCH_ASSOC)): ?>

        <div class="post-card">

            <h3>
                <?= htmlspecialchars($post['title']); ?>
            </h3>

            <!-- IMAGE -->
            <?php if(!empty($post['image'])): ?>

                <img 
                    src="<?= htmlspecialchars($post['image']); ?>"
                    class="post-image"
                >

            <?php endif; ?>


            <!-- VIDEO -->
            <?php if(!empty($post['video'])): ?>

                <video class="post-video" controls>

                    <source 
                        src="<?= htmlspecialchars($post['video']); ?>"
                        type="video/mp4"
                    >

                </video>

            <?php endif; ?>


            <!-- CONTENT -->
            <p>
                <?= htmlspecialchars($post['content']); ?>
            </p>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="post-card">

        <p style="text-align:center;">

            No posts yet.

        </p>

    </div>

<?php endif; ?>


<script>

function toggleDarkMode() {

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")) {

        localStorage.setItem("darkMode", "enabled");

    } else {

        localStorage.setItem("darkMode", "disabled");
    }
}


/* LOAD DARK MODE */

window.onload = function() {

    if(localStorage.getItem("darkMode") === "enabled") {

        document.body.classList.add("dark-mode");
    }
};

</script>

</body>
</html>