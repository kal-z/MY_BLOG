<?php

require_once "config.php";
require_once "Post.php";

if(isset($_GET['id'])) {

    $db = new Database();
    $conn = $db->getConnection();

    $post = new Post($conn);

    $id = $_GET['id'];

    $post->delete($id);

    header("Location: posts.php");
    exit();
}
?>