<?php
session_start();

require_once "config.php";

$db = new Database();
$conn = $db->getConnection();

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $post_id = $_POST['post_id'];

    $comment = $_POST['comment'];

    $user_id = $_SESSION['user_id'];

    // INSERT COMMENT
    $query = "
        INSERT INTO comments(post_id, user_id, comment)
        VALUES(:post_id, :user_id, :comment)
    ";

    $stmt = $conn->prepare($query);

    $stmt->bindParam(':post_id', $post_id);

    $stmt->bindParam(':user_id', $user_id);

    $stmt->bindParam(':comment', $comment);

    $stmt->execute();


    // GET POST OWNER
    $postQuery = "
        SELECT user_id
        FROM posts
        WHERE id = :post_id
    ";

    $postStmt = $conn->prepare($postQuery);

    $postStmt->bindParam(':post_id', $post_id);

    $postStmt->execute();

    $postData = $postStmt->fetch(PDO::FETCH_ASSOC);

    $postOwner = $postData['user_id'];


    // DON'T NOTIFY YOURSELF
    if($postOwner != $user_id) {

       $message = $_SESSION['username'] . " commented: " . $comment;

        $notifQuery = "
            INSERT INTO notifications
            (user_id, sender_id, post_id, type, message)
            VALUES
            (:user_id, :sender_id, :post_id, :type, :message)
        ";

        $notifStmt = $conn->prepare($notifQuery);

        $type = "comment";

        $notifStmt->bindParam(':user_id', $postOwner);

        $notifStmt->bindParam(':sender_id', $user_id);

        $notifStmt->bindParam(':post_id', $post_id);

        $notifStmt->bindParam(':type', $type);

        $notifStmt->bindParam(':message', $message);

        $notifStmt->bindParam(':message', $message);

        $notifStmt->execute();
    }

    header("Location: posts.php");

    exit();
}
?>