<?php
session_start();
$msg = $_SESSION['msg'] ?? '';
$type = $_SESSION['type'] ?? '';
unset($_SESSION['msg'], $_SESSION['type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 { margin-bottom: 15px; color: #333; }
        label { font-weight: bold; margin-top: 10px; display: block; }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        button {
            background: #28a745;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        button:hover { background: #218838; }
        .success { color: green; font-weight: bold; margin-bottom: 10px; }
        .error { color: red; font-weight: bold; margin-bottom: 10px; }
        #message-area {
            transition: opacity 0.5s ease;
        }
    </style>
</head>
<body>

<form action="send_email.php" method="post">
    <h2>Contact Us</h2>

   
    <div id="message-area">
        <?php if ($msg): ?>
            <p class="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($msg) ?></p>
        <?php endif; ?>
    </div>

    <label for="name">Name</label>
    <input type="text" name="name" id="name" required>

    <label for="email">Email</label>
    <input type="email" name="email" id="email" required>

    <label for="subject">Subject</label>
    <input type="text" name="subject" id="subject" required>

    <label for="message">Message</label>
    <textarea name="message" id="message" rows="5" required></textarea>

    <button type="submit">Send</button>
</form>

<script>
    
    const messageArea = document.getElementById('message-area');
    if (messageArea.textContent.trim() !== "") {
        setTimeout(() => {
            messageArea.style.opacity = '0';
            setTimeout(() => { messageArea.innerHTML = ''; }, 500);
        }, 3000);
    }
</script>

</body>
</html>
