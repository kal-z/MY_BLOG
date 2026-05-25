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

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $imagePath = "";

if(isset($_SESSION['profile_image'])) {
    $imagePath = $_SESSION['profile_image'];
}

    if(!empty($_FILES['profile_image']['name'])) {

        $imageName = time() . "_" . basename($_FILES['profile_image']['name']);

        $tmpName = $_FILES['profile_image']['tmp_name'];

        $imagePath = "uploads/" . $imageName;

        move_uploaded_file($tmpName, $imagePath);

        $query = "
            UPDATE users
            SET profile_image = :profile_image
            WHERE id = :id
        ";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':profile_image', $imagePath);
        $stmt->bindParam(':id', $_SESSION['user_id']);

        $stmt->execute();

        $_SESSION['profile_image'] = $imagePath;

        $message = "Profile picture updated!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Profile</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="post-card">

    <h2>Edit Profile Picture</h2>

    <?php if(!empty($_SESSION['profile_image'])): ?>
<img src="<?= htmlspecialchars($_SESSION['profile_image']); ?>"
     class="profile-pic">
        <br><br>

    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <input type="file" name="profile_image">

        <br><br>

        <button type="submit">
            Save Picture
        </button>

    </form>

    <p><?= $message ?></p>

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