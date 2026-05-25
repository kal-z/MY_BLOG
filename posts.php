<?php
session_start();

require_once "config.php";
require_once "Post.php";

$db = new Database();
$conn = $db->getConnection();

$post = new Post($conn);

$posts = $post->getPosts();
?>

<!DOCTYPE html>
<html>

<head>

    <title>All Blog Posts</title>

    <!-- FONT AWESOME -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link rel="stylesheet" href="style.css">

</head>

<body>

<?php require_once "navbar.php"; ?>

<h2>
    All Blog Posts
</h2>

<?php if($posts->rowCount() > 0): ?>

    <?php while($row = $posts->fetch(PDO::FETCH_ASSOC)): ?>

        <div class="post-card" id="post-<?= $row['id']; ?>">

            <!-- TITLE -->
            <h3>
                <?= htmlspecialchars($row['title']); ?>
            </h3>

            <!-- USER -->
           <!-- USER -->
<div class="post-user">

    <?php if(!empty($row['profile_image'])): ?>

        <img 
            src="<?= htmlspecialchars($row['profile_image']); ?>"
            class="profile-pic"
        >

    <?php else: ?>
    
        <img 
            src="uploads/default.png"
            class="profile-pic">
            
        
    <?php endif; ?>








    
    <div>

        <small>

            <a href="profile.php?id=<?= $row['user_id']; ?>">

                <?= htmlspecialchars($row['username']); ?>

            </a>

                        <?php if(
                            isset($_SESSION['user_id']) &&
                            $_SESSION['user_id'] != $row['user_id']
                        ): ?>

                            <?php

                            $followQuery = "
                                SELECT *
                                FROM followers
                                WHERE follower_id = :follower_id
                                AND following_id = :following_id
                            ";

                            $followStmt = $conn->prepare($followQuery);

                            $followStmt->bindParam(
                                ':follower_id',
                                $_SESSION['user_id']
                            );

                            $followStmt->bindParam(
                                ':following_id',
                                $row['user_id']
                            );

                            $followStmt->execute();

                            $isFollowing = $followStmt->rowCount() > 0;

                            ?>

                            <form
                                method="POST"
                                action="follow_user.php"
                                style="display:inline;"
                            >

                                <input
                                    type="hidden"
                                    name="following_id"
                                    value="<?= $row['user_id']; ?>"
                                >

                                <button type="submit">

                                    <?= $isFollowing
                                        ? "<i class='fa-solid fa-user-check'></i>"
                                        : "<i class='fa-solid fa-user-plus'></i>";
                                    ?>

                                </button>

                            </form>

                        <?php endif; ?>

                    </small>

                </div>

            </div>

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

            <!-- ACTIONS -->
            <div class="actions">

                <!-- LIKE -->
                <div class="like-section">

                    <?php

                    /* TOTAL LIKES */

                    $likeQuery = "
                        SELECT COUNT(*) AS total
                        FROM post_likes
                        WHERE post_id = :post_id
                    ";

                    $likeStmt = $conn->prepare($likeQuery);

                    $likeStmt->bindParam(':post_id', $row['id']);

                    $likeStmt->execute();

                    $likeData = $likeStmt->fetch(PDO::FETCH_ASSOC);


                    /* CHECK IF USER LIKED */

                    $isLiked = false;

                    if(isset($_SESSION['user_id'])) {

                        $likedQuery = "
                            SELECT *
                            FROM post_likes
                            WHERE post_id = :post_id
                            AND user_id = :user_id
                        ";

                        $likedStmt = $conn->prepare($likedQuery);

                        $likedStmt->bindParam(':post_id', $row['id']);

                        $likedStmt->bindParam(':user_id', $_SESSION['user_id']);

                        $likedStmt->execute();

                        $isLiked = $likedStmt->rowCount() > 0;
                    }

                    ?>

                    <a
                        href="like_post.php?post_id=<?= $row['id']; ?>&redirect=<?= urlencode('posts.php#post-' . $row['id']); ?>"
                        class="like-link"
                    >

                        <button type="button" class="like-btn">

                            <i class="<?= $isLiked
                                ? 'fa-solid fa-heart'
                                : 'fa-regular fa-heart'; ?>"></i>

                        </button>

                    </a>

                    <span class="like-count">

                        <?= $likeData['total']; ?>

                    </span>

                </div>

                <!-- COMMENT BUTTON -->
                <button
                    onclick="toggleComments(<?= $row['id']; ?>)"
                    class="comment-toggle"
                >

                    <i class="fa-regular fa-comment"></i>

                </button>

                <!-- OWNER ACTIONS -->
                <?php if(
                    isset($_SESSION['user_id']) &&
                    $_SESSION['user_id'] == $row['user_id']
                ): ?>

                    <a href="edit_post.php?id=<?= $row['id']; ?>">

                        <i class="fa-solid fa-pen-to-square"></i>

                    </a>
                    <!--
                    <a
                        href="delete_post.php?id=<?= $row['id']; ?>"
                        onclick="return confirm('Delete this post?')"
                    >

                        <i class="fa-solid fa-trash"></i>

                    </a>-->
<button
    class="delete-btn"
    onclick="openDeleteModal(<?= $row['id']; ?>)"
>

    <i class="fa-solid fa-trash"></i>

</button>
                <?php endif; ?>

            </div>

            <!-- COMMENTS SECTION -->
            <div
                id="comments-<?= $row['id']; ?>"
                class="comments-section"
                style="display:none;"
            >

                <?php

                $commentQuery = "
                    SELECT comments.*, users.username, users.profile_image
                    FROM comments
                    JOIN users ON comments.user_id = users.id
                    WHERE comments.post_id = :post_id
                    ORDER BY comments.created_at DESC
                ";

                $commentStmt = $conn->prepare($commentQuery);

                $commentStmt->bindParam(':post_id', $row['id']);

                $commentStmt->execute();

                ?>

                <!-- COMMENTS LIST -->
                <div class="comments-list">

                    <?php if($commentStmt->rowCount() > 0): ?>

                        <?php while($comment = $commentStmt->fetch(PDO::FETCH_ASSOC)): ?>

                            <div class="comment-box">

                                <div class="comment-user">

                                    <?php if(!empty($comment['profile_image'])): ?>

                                        <img
                                            src="<?= htmlspecialchars($comment['profile_image']); ?>"
                                            class="comment-pic"
                                        >

                                    <?php else: ?>

                                        <img
                                            src="uploads/default.png"
                                            class="comment-pic"
                                        >

                                    <?php endif; ?>

                                    <strong>
                                        <?= htmlspecialchars($comment['username']); ?>
                                    </strong>

                                </div>

                                <p>
                                    <?= htmlspecialchars($comment['comment']); ?>
                                </p>

                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <p>No comments yet.</p>

                    <?php endif; ?>

                </div>

                <!-- ADD COMMENT -->
                <div class="comment-form-side">

                    <form method="POST" action="comment_post.php">

                        <input
                            type="hidden"
                            name="post_id"
                            value="<?= $row['id']; ?>"
                        >

                        <textarea
                            name="comment"
                            placeholder="Write a comment..."
                            required
                        ></textarea>

                        <br><br>

                        <button type="submit">

                            <i class="fa-solid fa-paper-plane"></i>

                        </button>

                    </form>

                </div>

            </div>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p style="text-align:center;">
        No posts found.
    </p>

<?php endif; ?>

<script>

function toggleComments(id) {

    const box = document.getElementById("comments-" + id);

    if(box.style.display === "none") {

        box.style.display = "block";

        localStorage.setItem("openComments", id);

    } else {

        box.style.display = "none";

        localStorage.removeItem("openComments");
    }
}

</script>

<script>

window.onload = function() {

    // DARK MODE
    if(localStorage.getItem("darkMode") === "enabled") {

        document.body.classList.add("dark-mode");
    }

    // KEEP COMMENTS OPEN
    const openId = localStorage.getItem("openComments");

    if(openId) {

        const box = document.getElementById(
            "comments-" + openId
        );

        if(box) {

            box.style.display = "block";

            location.href = "#post-" + openId;
        }
    }
};



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
<script>

/* DARK MODE */
function toggleDarkMode() {

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")) {

        localStorage.setItem("darkMode", "enabled");

    } else {

        localStorage.setItem("darkMode", "disabled");
    }
}

/* LOAD MODE */
window.onload = function() {

    if(localStorage.getItem("darkMode") === "enabled") {

        document.body.classList.add("dark-mode");
    }
};

</script>
</body>
</html>