<?php
require_once "navbar.php";
class Post {
    private $conn;
    private $table_name = "posts";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create post (NOW WITH IMAGE)
    public function create($title, $content, $image, $user_id) {

        $query = "INSERT INTO posts(title, content, image, user_id)
                  VALUES(:title, :content, :image, :user_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    // Get all posts (WITH USERNAME + IMAGE)
   public function getPosts() {

    $query = "
        SELECT 
            posts.*,
            users.username,
            users.profile_image
        FROM posts
        JOIN users
        ON posts.user_id = users.id
        ORDER BY posts.created_at DESC
    ";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();

    return $stmt;
}
    public function delete($id) {

    $query = "DELETE FROM posts WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':id', $id);

    return $stmt->execute();
}
public function update($id, $title, $content) {

    $query = "UPDATE posts
              SET title = :title,
                  content = :content
              WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':id', $id);

    return $stmt->execute();
}
}
?>