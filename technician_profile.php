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
    <div class="card shadow rounded-4 p-4 mb-4 d-flex align-items-center gap-3 text-dark"
        style="background-color: #fff;">
        <?php
        $profileImage = __DIR__ . '/uploads/profile_images/' . ($tech['profile_image'] ?? '');
        if (!empty($tech['profile_image']) && file_exists($profileImage)):
        ?>
            <img src="uploads/profile_images/<?php echo htmlspecialchars($tech['profile_image']); ?>" alt="รูปโปรไฟล์"
                style="width:120px; height:120px; object-fit:cover; border-radius:15px;">
        <?php else: ?>
            <img src="default-profile.png" alt="รูปโปรไฟล์"
                style="width:120px; height:120px; object-fit:cover; border-radius:15px;">
        <?php endif; ?>

        <div>
            <h2 class="text-dark"><?= htmlspecialchars($tech['name']); ?></h2>

            <p class="mb-1">
                <strong>ประเภทงานช่าง:</strong>
                <?= htmlspecialchars($tech['tech_type'] ?: 'ไม่ระบุ'); ?>
            </p>

            <p class="mb-3">
                <strong>เบอร์โทร:</strong>
                <?= htmlspecialchars($tech['phone']); ?>
            </p>

            <a href="edit_profile.php" class="btn btn-primary rounded-3" style="min-width:120px;">
                เปลี่ยนรูปโปรไฟล์
            </a>
        </div>

    </div>
    <?php
    // ดึงข้อมูลช่างจาก DB
    $stmt = $conn->prepare("SELECT name, profile_image, cover_image, bio, years_experience,
                        min_price, emergency_fee, phone, line_id, phone_public, line_id_public,
                        languages, latitude, longitude, service_radius_km, business_hours_json
                        FROM technicians WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $tech = $result->fetch_assoc();
    ?>

    <div class="card mb-3">
        <div class="card-header">

        </div>
        <div class="card-body">
            <!-- Bio -->
            <?php if (!empty($tech['bio'])): ?>
                <p><strong>เกี่ยวกับช่าง:</strong> <?= nl2br(htmlspecialchars($tech['bio'])); ?></p>
            <?php endif; ?>

            <!-- ประสบการณ์ -->
            <p><strong>ประสบการณ์:</strong> <?= (int)$tech['years_experience']; ?> ปี</p>

            <!-- ราคา -->
            <p><strong>ราคาเริ่มต้น:</strong> <?= number_format($tech['min_price']); ?> ฿</p>
            <p><strong>ค่าบริการด่วน:</strong> <?= number_format($tech['emergency_fee']); ?> ฿</p>

            <!-- ภาษา -->
            <?php if (!empty($tech['languages'])): ?>
                <p><strong>ภาษา:</strong> <?= htmlspecialchars($tech['languages']); ?></p>
            <?php endif; ?>

            <!-- เบอร์โทร / Line -->
            <?php if ($tech['phone_public'] && !empty($tech['phone'])): ?>
                <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($tech['phone']); ?></p>
            <?php endif; ?>

            <?php if ($tech['line_id_public'] && !empty($tech['line_id'])): ?>
                <p><strong>Line ID:</strong> <?= htmlspecialchars($tech['line_id']); ?></p>
            <?php endif; ?>

            <!-- พื้นที่บริการ -->
            <?php if (!empty($tech['service_radius_km'])): ?>
                <p><strong>พื้นที่ให้บริการ:</strong> รัศมี <?= $tech['service_radius_km']; ?> กม.</p>
            <?php endif; ?>

        </div>
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
                                <textarea name="reply" class="form-control mb-2" placeholder="ตอบกลับรีวิวนี้..."
                                    required></textarea>
                                <button type="submit" class="btn btn-sm btn-primary">ส่งคำตอบ</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary mt-4">กลับหน้าหลัก</a>
    <a href="chat.php" class="btn btn-secondary mt-4">แชท</a>


</body>

</html>