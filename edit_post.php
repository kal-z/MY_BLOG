<?php
session_start();

require_once "config.php";

if(!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

if(!isset($_GET['id'])) {

    header("Location: dashboard.php");
    exit();
}

$post_id = $_GET['id'];


/* GET POST */

$query = "
    SELECT *
    FROM posts
    WHERE id = :id
";

$stmt = $conn->prepare($query);

$stmt->bindParam(':id', $post_id);

$stmt->execute();

$post = $stmt->fetch(PDO::FETCH_ASSOC);


/* CHECK OWNER */

if(!$post || $post['user_id'] != $_SESSION['user_id']) {

    header("Location: dashboard.php");
    exit();
}


/* UPDATE POST */

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    $image = $post['image'];

    /* NEW IMAGE */

    if(!empty($_FILES['image']['name'])) {

        $imageName = time() . "_" .
            basename($_FILES['image']['name']);

        $tmpName = $_FILES['image']['tmp_name'];

        $imagePath = "uploads/" . $imageName;

        move_uploaded_file($tmpName, $imagePath);

        $image = $imagePath;
    }

    $updateQuery = "
        UPDATE posts
        SET title = :title,
            content = :content,
            image = :image
        WHERE id = :id
    ";

    $updateStmt = $conn->prepare($updateQuery);

    $updateStmt->bindParam(':title', $title);
    $updateStmt->bindParam(':content', $content);
    $updateStmt->bindParam(':image', $image);
    $updateStmt->bindParam(':id', $post_id);

    $updateStmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Post</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<?php require_once "navbar.php"; ?>

<div class="post-card">

    <h2>Edit Post</h2>

    <!-- CURRENT IMAGE -->

    <?php if(!empty($post['image'])): ?>

        <img
            src="<?= htmlspecialchars($post['image']); ?>"
            class="post-image"
        >

        <br><br>

    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <input
            type="text"
            name="title"
            value="<?= htmlspecialchars($post['title']); ?>"
            required
        >

        <br><br>

        <textarea
            name="content"
            rows="6"
            required
        ><?= htmlspecialchars($post['content']); ?></textarea>

        <br><br>

        <!-- CHANGE IMAGE -->

        <input
            type="file"
            name="image"
        >

        <br><br>

        <button type="submit">
            Update Post
        </button>

    </form>

</div>

</body>
</html>