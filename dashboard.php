<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once "config.php";
require_once "navbar.php";

$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];


/* TOTAL POSTS */
$postQuery = "
    SELECT COUNT(*) AS total_posts
    FROM posts
    WHERE user_id = :user_id
";

$postStmt = $conn->prepare($postQuery);
$postStmt->bindParam(':user_id', $user_id);
$postStmt->execute();

$postData = $postStmt->fetch(PDO::FETCH_ASSOC);


/* TOTAL LIKES */
$likeQuery = "
    SELECT COUNT(*) AS total_likes
    FROM post_likes
    JOIN posts ON post_likes.post_id = posts.id
    WHERE posts.user_id = :user_id
";

$likeStmt = $conn->prepare($likeQuery);
$likeStmt->bindParam(':user_id', $user_id);
$likeStmt->execute();

$likeData = $likeStmt->fetch(PDO::FETCH_ASSOC);


/* FOLLOWERS */
$followerQuery = "
    SELECT COUNT(*) AS total_followers
    FROM followers
    WHERE following_id = :user_id
";

$followerStmt = $conn->prepare($followerQuery);
$followerStmt->bindParam(':user_id', $user_id);
$followerStmt->execute();

$followerData = $followerStmt->fetch(PDO::FETCH_ASSOC);


/* FOLLOWING */
$followingQuery = "
    SELECT COUNT(*) AS total_following
    FROM followers
    WHERE follower_id = :user_id
";

$followingStmt = $conn->prepare($followingQuery);
$followingStmt->bindParam(':user_id', $user_id);
$followingStmt->execute();

$followingData = $followingStmt->fetch(PDO::FETCH_ASSOC);


/* USER POSTS */
$myPostsQuery = "
    SELECT *
    FROM posts
    WHERE user_id = :user_id
    ORDER BY created_at DESC
";

$myPostsStmt = $conn->prepare($myPostsQuery);
$myPostsStmt->bindParam(':user_id', $user_id);
$myPostsStmt->execute();

?>

<!DOCTYPE html>
<html>

<head>

    <title>My Profile</title>

    <link 
    rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link rel="stylesheet" href="style.css">

</head>

<body>

<!-- PROFILE CARD -->
<div class="post-card">

    <!-- PROFILE IMAGE -->
    <?php if(!empty($_SESSION['profile_image'])): ?>

        <img 
            src="<?= htmlspecialchars($_SESSION['profile_image']); ?>"
            class="dashboard-profile-pic"
        >

    <?php else: ?>

        <img 
            src="uploads/default.png"
            class="dashboard-profile-pic"
        >

    <?php endif; ?>

    <h2>
        Welcome <?= htmlspecialchars($_SESSION['username']); ?>
    </h2>

    <!-- PROFILE STATS -->
    <div class="profile-stats">

        <div class="stat-box">

            <span>Posts</span>

            <div class="stat-number">
                <?= $postData['total_posts']; ?>
            </div>

        </div>

       <div class="stat-box">

    <a 
        href="followers.php?id=<?= $user_id; ?>"
        class="stat-link"
    >

        <span>Followers</span>

        <div class="stat-number">
            <?= $followerData['total_followers']; ?>
        </div>

    </a>

</div>

        <div class="stat-box">

    <a 
       href="following.php?id=<?= $_SESSION['user_id']; ?>"
        class="stat-link"
    >

        <span>Following</span>

        <div class="stat-number">
            <?= $followingData['total_following']; ?>
        </div>

    </a>

</div>

    </div>

    <!-- ACTION BUTTONS -->
    <div class="actions dashboard-actions">

        <a href="create_post.php">
            <i class="fa-solid fa-square-plus"></i>
            Create
        </a>

        <a href="posts.php">
            <i class="fa-solid fa-house"></i>
            Posts
        </a>

        <a href="my_likes.php">
            <i class="fa-regular fa-heart"></i>
            My Likes
        </a>

        <a href="edit_profile.php">
            <i class="fa-solid fa-pen-to-square"></i>
            Edit Profile
        </a>

    </div>

</div>

<!-- MY POSTS -->
<h2>My Posts</h2>

<?php if($myPostsStmt->rowCount() > 0): ?>

    <?php while($row = $myPostsStmt->fetch(PDO::FETCH_ASSOC)): ?>

        <div class="post-card">

            <h3>
                <?= htmlspecialchars($row['title']); ?>
            </h3>

            <!-- IMAGE -->
            <?php if(isset($row['image']) && trim($row['image']) != ""): ?>

                <img 
                    src="<?= htmlspecialchars($row['image']); ?>"
                    class="post-image"
                >

            <?php endif; ?>
            <?php if(!empty($row['video'])): ?>

    <video class="post-video" controls>

        <source 
            src="<?= htmlspecialchars($row['video']); ?>"
            type="video/mp4"
        >

    </video>

<?php endif; ?>

            <!-- CONTENT -->
            <p>
                <?= htmlspecialchars($row['content']); ?>
            </p>

            <?php

            /* TOTAL LIKES */
            $postLikeQuery = "
                SELECT COUNT(*) AS total
                FROM post_likes
                WHERE post_id = :post_id
            ";

            $postLikeStmt = $conn->prepare($postLikeQuery);

            $postLikeStmt->bindParam(':post_id', $row['id']);

            $postLikeStmt->execute();

            $postLikeData = $postLikeStmt->fetch(PDO::FETCH_ASSOC);


            /* TOTAL COMMENTS */
            $commentCountQuery = "
                SELECT COUNT(*) AS total_comments
                FROM comments
                WHERE post_id = :post_id
            ";

            $commentCountStmt = $conn->prepare($commentCountQuery);

            $commentCountStmt->bindParam(':post_id', $row['id']);

            $commentCountStmt->execute();

            $commentCountData = $commentCountStmt->fetch(PDO::FETCH_ASSOC);

            ?>

            <!-- POST META -->
            <div class="post-meta">

                <span>

                    <i class="fa-regular fa-heart"></i>

                    <?= $postLikeData['total']; ?> Likes

                </span>

                <button 
                    class="comment-toggle-btn"
                    onclick="toggleComments(<?= $row['id']; ?>)"
                >

                    <i class="fa-regular fa-comment"></i>

                    <?= $commentCountData['total_comments']; ?> Comments

                </button>

            </div>

            <!-- COMMENTS -->

            <?php

            $commentsQuery = "
                SELECT comments.*, users.username
                FROM comments
                JOIN users
                ON comments.user_id = users.id
                WHERE comments.post_id = :post_id
                ORDER BY comments.created_at ASC
            ";

            $commentsStmt = $conn->prepare($commentsQuery);

            $commentsStmt->bindParam(':post_id', $row['id']);

            $commentsStmt->execute();

            ?>

            <div 
                class="comments-wrapper"
                id="comments-<?= $row['id']; ?>"
                style="display:none;"
            >

                <div class="comments-section">

                    <div class="comments-list">

                        <?php if($commentsStmt->rowCount() > 0): ?>

                            <?php while($comment = $commentsStmt->fetch(PDO::FETCH_ASSOC)): ?>

                                <div class="comment-box">

                                    <strong>
                                        <?= htmlspecialchars($comment['username']); ?>
                                    </strong>

                                    <p>
                                        <?= htmlspecialchars($comment['comment']); ?>
                                    </p>

                                </div>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <p>No comments yet.</p>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <!-- ACTIONS -->
            <div class="actions dashboard-actions">

                <a href="edit_post.php?id=<?= $row['id']; ?>">

                    <i class="fa-solid fa-pen-to-square"></i>

                    Edit

                </a>
                <!--
//this is to be alterd
                <a 
                    href="delete_post.php?id=<?= $row['id']; ?>"
                    onclick="return confirm('Delete this post?')"
                >

                    <i class="fa-solid fa-trash"></i>

                    Delete

                </a>
                        -->
                <button
    class="delete-btn"
    onclick="openDeleteModal(<?= $row['id']; ?>)"
>

    <i class="fa-solid fa-trash"></i>

</button>
            </div>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="post-card">

        <p>
            You haven't created any posts yet.
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

window.onload = function() {

    if(localStorage.getItem("darkMode") === "enabled") {

        document.body.classList.add("dark-mode");
    }
};


/* TOGGLE COMMENTS */

function toggleComments(postId) {

    let box = document.getElementById("comments-" + postId);

    if(box.style.display === "none") {

        box.style.display = "block";

    } else {

        box.style.display = "none";
    }
}


function openDeleteModal(postId) {

    document.getElementById("deleteModal").style.display = "flex";

    document.getElementById("confirmDeleteBtn").href =
        "delete_post.php?id=" + postId;
}

function closeDeleteModal() {

    document.getElementById("deleteModal").style.display = "none";
}


</script>
<div class="delete-modal" id="deleteModal">

    <div class="delete-modal-content">

        <div class="delete-icon">

            <i class="fa-solid fa-triangle-exclamation"></i>

        </div>

        <h3>
            Delete Post?
        </h3>

        <p>
            This will permanently remove the post,
            uploaded images/videos, likes, and comments
            from the database.
        </p>

        <div class="delete-modal-actions">

            <button onclick="closeDeleteModal()">

                Cancel

            </button>

            <a id="confirmDeleteBtn">

                Delete

            </a>

        </div>

    </div>

</div>
</body>
</html>