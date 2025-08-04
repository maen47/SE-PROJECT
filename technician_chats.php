<?php
session_start();
include('db/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'technician') {
    die("ไม่มีสิทธิ์เข้าถึง");
}
$tech_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT cc.id, u.name AS user_name, cc.last_message_at
    FROM chat_conversations cc
    JOIN users u ON cc.user_id = u.id
    WHERE cc.technician_id = ?
    ORDER BY cc.last_message_at DESC");
$stmt->bind_param("i", $tech_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8" />
<title>รายชื่อแชท</title>
</head>
<body>
<h2>รายชื่อแชทของคุณ</h2>
<ul>
<?php while ($row = $result->fetch_assoc()): ?>
    <li>
        <a href="chat.php?conversation_id=<?php echo $row['id']; ?>">
            แชทกับ: <?php echo htmlspecialchars($row['user_name']); ?>
            (ล่าสุด: <?php echo date('d/m/Y H:i', strtotime($row['last_message_at'])); ?>)
        </a>
    </li>
<?php endwhile; ?>
</ul>
</body>
</html>
