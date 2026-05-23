<?php

session_start();

require_once "config.php";


/* =========================================
   CHECK LOGIN
========================================= */

if(!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();
}


/* =========================================
   CHECK POST ID
========================================= */

if(!isset($_GET['post_id']) || empty($_GET['post_id'])) {

    header("Location: posts.php");
    exit();
}


/* =========================================
   DATABASE
========================================= */

$db = new Database();

$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];

$post_id = intval($_GET['post_id']);


/* =========================================
   REDIRECT URL
========================================= */

$redirect = "posts.php";

if(isset($_GET['redirect']) && !empty($_GET['redirect'])) {

    $redirect = urldecode($_GET['redirect']);
}


/* =========================================
   CHECK IF POST EXISTS
========================================= */

$postCheckQuery = "
    SELECT *
    FROM posts
    WHERE id = :post_id
";

$postCheckStmt = $conn->prepare($postCheckQuery);

$postCheckStmt->bindParam(':post_id', $post_id);

$postCheckStmt->execute();


if($postCheckStmt->rowCount() == 0) {

    header("Location: posts.php");
    exit();
}


/* =========================================
   CHECK IF ALREADY LIKED
========================================= */

$checkQuery = "
    SELECT *
    FROM post_likes
    WHERE user_id = :user_id
    AND post_id = :post_id
";

$checkStmt = $conn->prepare($checkQuery);

$checkStmt->bindParam(':user_id', $user_id);

$checkStmt->bindParam(':post_id', $post_id);

$checkStmt->execute();


/* =========================================
   LIKE POST
========================================= */

if($checkStmt->rowCount() == 0) {

    $insertQuery = "
        INSERT INTO post_likes(user_id, post_id)
        VALUES(:user_id, :post_id)
    ";

    $insertStmt = $conn->prepare($insertQuery);

    $insertStmt->bindParam(':user_id', $user_id);

    $insertStmt->bindParam(':post_id', $post_id);

    $insertStmt->execute();


    /* GET POST OWNER */

    $ownerQuery = "
        SELECT user_id
        FROM posts
        WHERE id = :post_id
    ";

    $ownerStmt = $conn->prepare($ownerQuery);

    $ownerStmt->bindParam(':post_id', $post_id);

    $ownerStmt->execute();

    $ownerData = $ownerStmt->fetch(PDO::FETCH_ASSOC);


    if($ownerData) {

        $postOwner = $ownerData['user_id'];


        /* DON'T NOTIFY YOURSELF */

        if($postOwner != $user_id) {

            $type = "like";

            $message = $_SESSION['username'] . " liked your post.";


            $notifQuery = "
                INSERT INTO notifications
                (user_id, sender_id, post_id, type, message)
                VALUES
                (:user_id, :sender_id, :post_id, :type, :message)
            ";

            $notifStmt = $conn->prepare($notifQuery);

            $notifStmt->bindParam(':user_id', $postOwner);

            $notifStmt->bindParam(':sender_id', $user_id);

            $notifStmt->bindParam(':post_id', $post_id);

            $notifStmt->bindParam(':type', $type);

            $notifStmt->bindParam(':message', $message);

            $notifStmt->execute();
        }
    }

}


/* =========================================
   UNLIKE POST
========================================= */

else {

    $deleteQuery = "
        DELETE FROM post_likes
        WHERE user_id = :user_id
        AND post_id = :post_id
    ";

    $deleteStmt = $conn->prepare($deleteQuery);

    $deleteStmt->bindParam(':user_id', $user_id);

    $deleteStmt->bindParam(':post_id', $post_id);

    $deleteStmt->execute();
}


/* =========================================
   RETURN TO SAME POST
========================================= */

header("Location: " . $redirect);

exit();

?>