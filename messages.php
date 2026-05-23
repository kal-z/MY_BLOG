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

$user_id = $_SESSION['user_id'];

/* SEARCH */
$search = "";

if(isset($_GET['search'])) {

    $search = trim($_GET['search']);
}


/* USERS QUERY */
if($search != "") {

    $query = "
        SELECT 
            users.id,
            users.username,
            users.profile_image,

            (
                SELECT COUNT(*)
                FROM messages
                WHERE sender_id = users.id
                AND receiver_id = :user_id
                AND is_read = 0
            ) AS unread_count,

            (
                SELECT MAX(created_at)
                FROM messages
                WHERE 
                (sender_id = users.id AND receiver_id = :user_id)
                OR
                (sender_id = :user_id AND receiver_id = users.id)
            ) AS last_message_time

        FROM users

        WHERE users.id != :user_id
        AND users.username LIKE :search

        ORDER BY unread_count DESC, last_message_time DESC
    ";

    $stmt = $conn->prepare($query);

    $searchTerm = "%" . $search . "%";

    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':search', $searchTerm);

} else {

    $query = "
        SELECT 
            users.id,
            users.username,
            users.profile_image,

            (
                SELECT COUNT(*)
                FROM messages
                WHERE sender_id = users.id
                AND receiver_id = :user_id
                AND is_read = 0
            ) AS unread_count,

            (
                SELECT MAX(created_at)
                FROM messages
                WHERE 
                (sender_id = users.id AND receiver_id = :user_id)
                OR
                (sender_id = :user_id AND receiver_id = users.id)
            ) AS last_message_time

        FROM users

        WHERE users.id != :user_id

        ORDER BY unread_count DESC, last_message_time DESC
    ";

    $stmt = $conn->prepare($query);

    $stmt->bindParam(':user_id', $user_id);
}

$stmt->execute();
?>

<!DOCTYPE html>
<html>

<head>

    <title>Messages</title>

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

            <i class="fa-regular fa-message"></i>

            Messages

        </h2>

    </div>

    <!-- SEARCH BOX -->

    <form method="GET" class="message-search-form">

        <input 
            type="text"
            name="search"
            placeholder="Search users..."
            value="<?= htmlspecialchars($search); ?>"
            class="message-search-input"
        >

        <button type="submit" class="message-search-btn">

            <i class="fa-solid fa-magnifying-glass"></i>

        </button>

    </form>

    <?php if($stmt->rowCount() > 0): ?>

        <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

            <a 
                href="chat.php?id=<?= $row['id']; ?>"
                class="chat-user-card <?= $row['unread_count'] > 0 ? 'unread-chat' : ''; ?>"
            >

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

                    <div class="chat-top-row">

                        <h4>
                            <?= htmlspecialchars($row['username']); ?>
                        </h4>

                        <?php if($row['unread_count'] > 0): ?>

                            <span class="chat-unread-badge">

                                <?= $row['unread_count']; ?>

                            </span>

                        <?php endif; ?>

                    </div>

                    <p>

                        <?php if($row['unread_count'] > 0): ?>

                            New message

                        <?php else: ?>

                            Tap to chat

                        <?php endif; ?>

                    </p>

                </div>

                <i class="fa-solid fa-chevron-right"></i>

            </a>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="post-card">

            <p style="text-align:center;">

                No users found.

            </p>

        </div>

    <?php endif; ?>

</div>

<script>

/* TOGGLE DARK MODE */
function toggleDarkMode() {

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")) {

        localStorage.setItem("darkMode", "enabled");

    } else {

        localStorage.setItem("darkMode", "disabled");
    }
}


/* LOAD SAVED MODE */
window.addEventListener("load", function() {

    if(localStorage.getItem("darkMode") === "enabled") {

        document.body.classList.add("dark-mode");
    }

});

</script>

</body>
</html>