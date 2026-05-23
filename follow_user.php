<?php

session_start();

require_once "config.php";

if(!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$follower_id = $_SESSION['user_id'];

if(!isset($_POST['following_id'])) {

    header("Location: dashboard.php");
    exit();
}

$following_id = intval($_POST['following_id']);


/* DON'T FOLLOW YOURSELF */

if($follower_id != $following_id) {

    /* CHECK IF ALREADY FOLLOWING */

    $checkQuery = "
        SELECT *
        FROM followers
        WHERE follower_id = :follower_id
        AND following_id = :following_id
    ";

    $checkStmt = $conn->prepare($checkQuery);

    $checkStmt->bindParam(':follower_id', $follower_id);

    $checkStmt->bindParam(':following_id', $following_id);

    $checkStmt->execute();


    /* FOLLOW */

    if($checkStmt->rowCount() == 0) {

        $insertQuery = "
            INSERT INTO followers(follower_id, following_id)
            VALUES(:follower_id, :following_id)
        ";

        $insertStmt = $conn->prepare($insertQuery);

        $insertStmt->bindParam(':follower_id', $follower_id);

        $insertStmt->bindParam(':following_id', $following_id);

        $insertStmt->execute();

    }

    /* UNFOLLOW */

    else {

        $deleteQuery = "
            DELETE FROM followers
            WHERE follower_id = :follower_id
            AND following_id = :following_id
        ";

        $deleteStmt = $conn->prepare($deleteQuery);

        $deleteStmt->bindParam(':follower_id', $follower_id);

        $deleteStmt->bindParam(':following_id', $following_id);

        $deleteStmt->execute();
    }
}


/* REDIRECT */

header("Location: dashboard.php");

exit();

?>