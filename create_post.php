<?php
session_start();

require_once "navbar.php";
require_once "config.php";
require_once "Post.php";

if(!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$post = new Post($conn);

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    $imagePath = "";
    $videoPath = "";

    /* MEDIA UPLOAD */
    if(isset($_FILES['media']) && $_FILES['media']['error'] == 0) {

        $fileTmp = $_FILES['media']['tmp_name'];

        $fileName = time() . "_" . basename($_FILES['media']['name']);

        $target = "uploads/" . $fileName;

        $fileType = mime_content_type($fileTmp);

        move_uploaded_file($fileTmp, $target);

        /* IMAGE */
        if(str_starts_with($fileType, 'image/')) {

            $imagePath = $target;
        }

        /* VIDEO */
        elseif(str_starts_with($fileType, 'video/')) {

            $videoPath = $target;
        }
    }

    /* SAVE POST */
    $query = "
        INSERT INTO posts
        (user_id, title, content, image, video)
        VALUES
        (:user_id, :title, :content, :image, :video)
    ";

    $stmt = $conn->prepare($query);

    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':image', $imagePath);
    $stmt->bindParam(':video', $videoPath);

    if($stmt->execute()) {

        $message = "Post created successfully ";

    } else {

        $message = "Failed to create post.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Create Post</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="post-card">

    <h2>Create Post</h2>

    <form method="POST" enctype="multipart/form-data">

        Title:<br>

        <input 
            type="text" 
            name="title" 
            required
        >

        <br><br>

        Content:<br>

        <textarea 
            name="content"
            rows="5"
            placeholder="What's on your mind? "
        ></textarea>

        <br><br>

        Upload Photo or Video:<br>

        <input 
            type="file"
            name="media"
            accept="image/*,video/*"
        >

        <br><br>

        <button type="submit">

            Publish

        </button>

    </form>

    <?php if($message != ""): ?>

        <p style="margin-top:15px; font-weight:600;">

            <?= $message; ?>

        </p>

    <?php endif; ?>

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