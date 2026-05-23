<?php
session_start();

require_once "config.php";

if(!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

require_once "navbar.php";

$sender_id = $_SESSION['user_id'];

if(!isset($_GET['id'])) {

    header("Location: messages.php");
    exit();
}

$receiver_id = $_GET['id'];


/* MARK AS READ */

$readQuery = "
    UPDATE messages
    SET is_read = 1
    WHERE sender_id = :sender_id
    AND receiver_id = :receiver_id
";

$readStmt = $conn->prepare($readQuery);

$readStmt->bindParam(':sender_id', $receiver_id);
$readStmt->bindParam(':receiver_id', $sender_id);

$readStmt->execute();


/* GET USER */

$userQuery = "
    SELECT username, profile_image
    FROM users
    WHERE id = :id
";

$userStmt = $conn->prepare($userQuery);

$userStmt->bindParam(':id', $receiver_id);

$userStmt->execute();

$user = $userStmt->fetch(PDO::FETCH_ASSOC);


/* SEND MESSAGE */

if($_SERVER['REQUEST_METHOD'] == "POST") {

    $message = trim($_POST['message']);

    if(!empty($message)) {

        $insertQuery = "
            INSERT INTO messages
            (sender_id, receiver_id, message, is_read)
            VALUES
            (:sender_id, :receiver_id, :message, 0)
        ";

        $insertStmt = $conn->prepare($insertQuery);

        $insertStmt->bindParam(':sender_id', $sender_id);

        $insertStmt->bindParam(':receiver_id', $receiver_id);

        $insertStmt->bindParam(':message', $message);

        $insertStmt->execute();

        header("Location: chat.php?id=" . $receiver_id);
        exit();
    }
}


/* GET CHAT */

$chatQuery = "
    SELECT *
    FROM messages
    WHERE
    (sender_id = :sender_id AND receiver_id = :receiver_id)
    OR
    (sender_id = :receiver_id AND receiver_id = :sender_id)
    ORDER BY created_at ASC
";

$chatStmt = $conn->prepare($chatQuery);

$chatStmt->bindParam(':sender_id', $sender_id);

$chatStmt->bindParam(':receiver_id', $receiver_id);

$chatStmt->execute();
?>

<!DOCTYPE html>
<html>

<head>

    <title>Chat</title>

    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="chat-page">

    <!-- HEADER -->
    <div class="chat-header">

        <a href="messages.php" class="back-btn">

            <i class="fa-solid fa-arrow-left"></i>

        </a>

        <?php if(!empty($user['profile_image'])): ?>

            <img
                src="<?= htmlspecialchars($user['profile_image']); ?>"
                class="chat-header-pic"
            >

        <?php else: ?>

            <img
                src="uploads/default.png"
                class="chat-header-pic"
            >

        <?php endif; ?>

        <div>

            <h3>
                <?= htmlspecialchars($user['username']); ?>
            </h3>

            <small class="online-text">
                Active now
            </small>

        </div>

    </div>


    <!-- CHAT BODY -->
    <div class="chat-body" id="chatBody">

        <?php while($msg = $chatStmt->fetch(PDO::FETCH_ASSOC)): ?>

            <?php if($msg['sender_id'] == $sender_id): ?>

                <!-- MY MESSAGE -->
                <div class="message-row mine">

                    <div class="my-message">

                        <?= nl2br(htmlspecialchars($msg['message'])); ?>

                    </div>

                </div>

            <?php else: ?>

                <!-- THEIR MESSAGE -->
                <div class="message-row theirs">

                    <div class="their-message">

                        <?= nl2br(htmlspecialchars($msg['message'])); ?>

                    </div>

                </div>

            <?php endif; ?>

        <?php endwhile; ?>

    </div>


    <!-- SEND FORM -->
    <form method="POST" class="chat-form">

       <div class="chat-input-area">

    <button 
        type="button"
        class="emoji-btn"
        onclick="toggleEmojiPicker()"
    >
        😊
    </button>

    <textarea
        name="message"
        id="messageBox"
        placeholder="Type message..."
        required
    ></textarea>

</div>

<div id="emojiPicker" class="emoji-picker" style="display:none;">

    <span onclick="addEmoji('😀')">😀</span>
    <span onclick="addEmoji('😂')">😂</span>
    <span onclick="addEmoji('😍')">😍</span>
    <span onclick="addEmoji('🥰')">🥰</span>
    <span onclick="addEmoji('😭')">😭</span>
    <span onclick="addEmoji('🔥')">🔥</span>
    <span onclick="addEmoji('❤️')">❤️</span>
    <span onclick="addEmoji('👍')">👍</span>
    <span onclick="addEmoji('🎉')">🎉</span>
    <span onclick="addEmoji('😎')">😎</span>

</div>

        <button type="submit">

            <i class="fa-solid fa-paper-plane"></i>

        </button>

    </form>

</div>

<script>

let chatBody = document.getElementById("chatBody");

chatBody.scrollTop = chatBody.scrollHeight;

</script>
<script>

function toggleEmojiPicker() {

    const picker = document.getElementById("emojiPicker");

    if(picker.style.display === "none") {

        picker.style.display = "block";

    } else {

        picker.style.display = "none";
    }
}

function addEmoji(emoji) {

    const box = document.getElementById("messageBox");

    box.value += emoji;

    box.focus();
}

</script>
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