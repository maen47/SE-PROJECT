<?php
session_start();
include('db/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

if (!isset($_GET['conversation_id'])) {
    echo "ไม่พบการสนทนา";
    exit;
}

$conversation_id = intval($_GET['conversation_id']);

// ตรวจสอบสิทธิ์เข้าถึง conversation (user ต้องเป็นเจ้าของ conversation)
$sql = "SELECT * FROM conversations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$conv = $stmt->get_result()->fetch_assoc();

if (!$conv) {
    echo "ไม่พบการสนทนา";
    exit;
}

if ($role === 'technician') {
    if ($conv['technician_id'] != $user_id) {
        echo "คุณไม่มีสิทธิ์เข้าถึงแชทนี้";
        exit;
    }
} else {
    if ($conv['user_id'] != $user_id) {
        echo "คุณไม่มีสิทธิ์เข้าถึงแชทนี้";
        exit;
    }
}

// ถ้ามีส่งข้อความใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message !== '') {
        $sender = $role === 'technician' ? 'technician' : 'user';
        $sql = "INSERT INTO messages (conversation_id, sender, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $conversation_id, $sender, $message);
        $stmt->execute();
        header("Location: chat.php?conversation_id=$conversation_id");
        exit;
    }
}

// ดึงข้อความทั้งหมดของ conversation
$sql = "SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>แชทกับช่าง | ThunderFix</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    font-family: 'Sarabun', sans-serif;
  }
  .chat-container {
    max-width: 700px;
    margin: 20px auto;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 15px;
    height: 70vh;
    display: flex;
    flex-direction: column;
  }
  .messages {
    flex: 1;
    overflow-y: auto;
    margin-bottom: 15px;
  }
  .message {
    margin-bottom: 10px;
    padding: 10px 15px;
    border-radius: 15px;
    max-width: 75%;
    word-wrap: break-word;
  }
  .message.user {
    background-color: #5f00ba;
    color: white;
    align-self: flex-end;
  }
  .message.technician {
    background-color: #c700c7;
    color: white;
    align-self: flex-start;
  }
  form {
    display: flex;
    gap: 10px;
  }
  textarea {
    flex: 1;
    resize: none;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-family: 'Sarabun', sans-serif;
  }
  button {
    background: linear-gradient(to right, #5f00ba, #c700c7);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 15px;
    cursor: pointer;
  }
  button:hover {
    background: linear-gradient(to right, #400088, #990099);
  }
</style>
</head>
<body>

<div class="chat-container">
  <div class="messages" id="messages">
    <?php while ($msg = $messages->fetch_assoc()): ?>
      <div class="message <?php echo htmlspecialchars($msg['sender']); ?>">
        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></small><br>
        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
      </div>
    <?php endwhile; ?>
  </div>

  <form method="POST">
    <textarea name="message" rows="2" placeholder="พิมพ์ข้อความ..." required></textarea>
    <button type="submit">ส่ง</button>
  </form>
</div>

<script>
// เลื่อนแชทลงล่างสุดอัตโนมัติ
const messagesDiv = document.getElementById('messages');
messagesDiv.scrollTop = messagesDiv.scrollHeight;
</script>

</body>
</html>
