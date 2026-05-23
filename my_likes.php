<?php
session_start();

require_once "navbar.php";
require_once "config.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];

$query = "
    SELECT posts.*, users.username
    FROM post_likes
    JOIN posts ON post_likes.post_id = posts.id
    JOIN users ON posts.user_id = users.id
    WHERE post_likes.user_id = :user_id
    ORDER BY posts.created_at DESC
";

$stmt = $conn->prepare($query);

$stmt->bindParam(':user_id', $user_id);

$stmt->execute();
?>

<!DOCTYPE html>
<html>

<head>

    <title>My Likes</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<h2>Posts You Liked</h2>

<?php if($stmt->rowCount() > 0): ?>

    <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

        <div class="post-card">

            <h3><?= htmlspecialchars($row['title']); ?></h3>

            <small>
                Posted by: <?= htmlspecialchars($row['username']); ?>
            </small>

            <br><br>

            <!-- IMAGE -->
            <?php if(isset($row['image']) && trim($row['image']) != ""): ?>
                <img src="<?= htmlspecialchars($row['image']); ?>">
            <?php endif; ?>

            <p><?= htmlspecialchars($row['content']); ?></p>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="post-card">
        <p>You haven't liked any posts yet.</p>
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


// LOAD SAVED MODE

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

</script>
</body>
</html>