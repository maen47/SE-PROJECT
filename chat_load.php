<?php
session_start();
include('db/db.php');

$conversation_id = intval($_GET['conversation_id'] ?? 0);
if ($conversation_id == 0) exit;

$stmt = $conn->prepare("SELECT sender, message, created_at FROM chat_messages WHERE conversation_id=? ORDER BY created_at ASC");
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $align = $row['sender'] === 'technician' ? 'text-start' : 'text-end';
    $bg = $row['sender'] === 'technician' ? '#4b3f99' : '#6a5acd';
    $sender_text = $row['sender'] === 'technician' ? 'ช่าง' : 'คุณ';

    echo "<div style='max-width:70%; margin-bottom:8px; background:$bg; color:white; padding:6px 12px; border-radius:12px; float:" . ($row['sender']==='technician'?'left':'right') . "; clear:both;'>";
    echo "<small><strong>$sender_text</strong></small><br>";
    echo nl2br(htmlspecialchars($row['message']));
    echo "<br><small style='font-size:10px; opacity:0.7;'>" . date('H:i d/m', strtotime($row['created_at'])) . "</small>";
    echo "</div>";
}
?>
