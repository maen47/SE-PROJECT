<?php
session_start();
include('db/db.php');

$conversation_id = intval($_POST['conversation_id'] ?? 0);
$sender = $_POST['sender'] ?? '';
$message = trim($_POST['message'] ?? '');

if ($conversation_id == 0 || !in_array($sender, ['user', 'technician']) || $message === '') {
    http_response_code(400);
    echo "ข้อมูลไม่ครบ";
    exit;
}

$stmt = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender, message) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $conversation_id, $sender, $message);
$stmt->execute();

// อัปเดต last_message_at ใน chat_conversations
$stmt2 = $conn->prepare("UPDATE chat_conversations SET last_message_at = NOW() WHERE id=?");
$stmt2->bind_param("i", $conversation_id);
$stmt2->execute();

echo "success";
