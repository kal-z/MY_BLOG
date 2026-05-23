<?php
session_start();

require_once "config.php";
require_once "navbar.php";

if(!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$query = "
    SELECT notifications.*,
           users.username,
           users.profile_image
    FROM notifications
    JOIN users
    ON notifications.sender_id = users.id
    WHERE notifications.user_id = :user_id
    ORDER BY notifications.created_at DESC
";

$stmt = $conn->prepare($query);

$stmt->bindParam(':user_id', $_SESSION['user_id']);

$stmt->execute();

$readQuery = "
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = :user_id
";

$readStmt = $conn->prepare($readQuery);

$readStmt->bindParam(':user_id', $_SESSION['user_id']);

$readStmt->execute();
?>

<!DOCTYPE html>
<html>

<head>

    <title>Notifications</title>

    <link 
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="messages-page">

    <div class="messages-header">

        <h2>

            <i class="fa-solid fa-bell"></i>

            Notifications

        </h2>

    </div>

    <?php if($stmt->rowCount() > 0): ?>

        <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

            <div class="chat-user-card">

                <?php if(!empty($row['profile_image'])): ?>

                    <img 
                        src="<?= htmlspecialchars($row['profile_image']); ?>"
                        class="chat-user-pic"
                    >

                <?php else: ?>

                    <img 
                        src="uploads/default.png"
                        class="chat-user-pic"
                    >

                <?php endif; ?>

                <div class="chat-user-info">

                    <h4>

                        <?= htmlspecialchars($row['username']); ?>

                    </h4>

                    <p>

                        <?= htmlspecialchars($row['message']); ?>

                    </p>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="post-card">

            <p>
                No notifications yet.
            </p>

        </div>

    <?php endif; ?>

</div>

</body>
</html>