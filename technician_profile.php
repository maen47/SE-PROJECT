<?php
session_start();
include('db/db.php');

if (!isset($_GET['id'])) {
    echo "ไม่พบข้อมูลช่าง";
    exit;
}

$tech_id = intval($_GET['id']);

// ตรวจสิทธิ์แก้ไขโปรไฟล์ช่าง
$can_edit = isset($_SESSION['role'], $_SESSION['user_id']) &&
    $_SESSION['role'] === 'technician' &&
    $_SESSION['user_id'] == $tech_id;

$user_id = $_SESSION['user_id'] ?? 0;  // ID ผู้ใช้ที่ล็อกอิน

// อัปเดตข้อมูลช่าง (ถ้ามี POST สำหรับแก้ไข)
if ($can_edit && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_profile'])) {
    $new_name = $_POST['name'];
    $new_specialty = $_POST['specialty'];
    $new_phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE technicians SET name=?, specialty=?, phone=? WHERE id=?");
    $stmt->bind_param("sssi", $new_name, $new_specialty, $new_phone, $tech_id);
    $stmt->execute();

    header("Location: technician_profile.php?id=" . $tech_id);
    exit;
}

// ฟังก์ชันบันทึกตอบกลับรีวิว
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply']) && isset($_POST['review_id'])) {
    $reply = $_POST['reply'];
    $review_id = intval($_POST['review_id']);

    $stmt = $conn->prepare("UPDATE reviews SET reply=? WHERE id=? AND technician_id=?");
    $stmt->bind_param("sii", $reply, $review_id, $tech_id);
    $stmt->execute();

    header("Location: technician_profile.php?id=" . $tech_id);
    exit;
}

// ดึงข้อมูลช่าง
$stmt = $conn->prepare("SELECT * FROM technicians WHERE id=?");
$stmt->bind_param("i", $tech_id);
$stmt->execute();
$tech = $stmt->get_result()->fetch_assoc();

if (!$tech) {
    echo "ไม่พบข้อมูลช่าง";
    exit;
}

// ดึงรีวิวของช่าง
$stmt2 = $conn->prepare("SELECT * FROM reviews WHERE technician_id=? ORDER BY created_at DESC");
$stmt2->bind_param("i", $tech_id);
$stmt2->execute();
$reviews = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>โปรไฟล์ช่าง: <?php echo htmlspecialchars($tech['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="container mt-4 mb-5">

    <div class="card shadow rounded-4 p-4 mb-4">
        <h2><?php echo htmlspecialchars($tech['name']); ?></h2>
        <p><strong>ประเภทงานช่าง:</strong> <?php echo htmlspecialchars($tech['specialty']); ?></p>
        <p><strong>เบอร์โทร:</strong> <?php echo htmlspecialchars($tech['phone']); ?></p>
    </div>

    <h3>รีวิวจากลูกค้า</h3>
    <?php if ($reviews->num_rows == 0): ?>
        <p>ยังไม่มีรีวิวสำหรับช่างคนนี้</p>
    <?php else: ?>
        <?php while ($review = $reviews->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5>
                        <?php echo htmlspecialchars($review['user_name']); ?>
                        <small class="text-muted">- วันที่
                            <?php echo date('d/m/Y', strtotime($review['created_at'])); ?></small>
                    </h5>
                    <p>คะแนน: <?php echo intval($review['rating']); ?>/5</p>
                    <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>

                    <?php if ($review['reply']): ?>
                        <div class="alert alert-info">
                            <strong>ตอบกลับช่าง:</strong><br>
                            <?php echo nl2br(htmlspecialchars($review['reply'])); ?>
                        </div>
                    <?php else: ?>
                        <?php if ($can_edit): ?>
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <textarea name="reply" class="form-control mb-2" placeholder="ตอบกลับรีวิวนี้..." required></textarea>
                                <button type="submit" class="btn btn-sm btn-primary">ส่งคำตอบ</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <hr>

    <h3>แชทกับช่าง</h3>
    <div id="chat-box"
        style="background:#2a1e57; border-radius:10px; padding:10px; height:300px; overflow-y:auto; color:white; max-width:800px; margin-bottom:1rem;">
        กำลังโหลดข้อความ...
    </div>

    <form id="chat-form" style="max-width:800px;">
        <input type="hidden" name="technician_id" value="<?php echo $tech_id; ?>">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <textarea name="message" placeholder="พิมพ์ข้อความ..." required
            style="width:100%; height:60px; border-radius:10px; padding:10px;"></textarea>
        <button type="submit" class="btn btn-primary mt-2">ส่งข้อความ</button>
    </form>

    <a href="index.php" class="btn btn-secondary mt-4">กลับหน้าหลัก</a>

    <script>
        const chatBox = $('#chat-box');
        const techId = <?php echo $tech_id; ?>;
        const userId = <?php echo $user_id; ?>;

        function loadChat() {
            $.ajax({
                url: 'chat_load.php',
                type: 'GET',
                data: { technician_id: techId, user_id: userId },
                success: function (data) {
                    chatBox.html(data);
                    chatBox.scrollTop(chatBox[0].scrollHeight);
                }
            });
        }

        setInterval(loadChat, 3000);
        loadChat();

        $('#chat-form').submit(function (e) {
            e.preventDefault();
            const message = $(this).find('textarea[name=message]').val();
            if (!message.trim()) return;

            $.ajax({
                url: 'chat_send.php',
                type: 'POST',
                data: {
                    technician_id: techId,
                    user_id: userId,
                    sender: 'user',
                    message: message
                },
                success: function (response) {
                    $('#chat-form')[0].reset();
                    loadChat();
                }
            });
        });
    </script>

</body>

</html>