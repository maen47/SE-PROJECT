<?php
session_start();
include('db/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// สมมติว่ามี $_SESSION['role'] = 'user' หรือ 'technician'
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; // 'user' หรือ 'technician'

// ดึงข้อมูลโปรไฟล์ปัจจุบัน
if ($role === 'user') {
    $table = 'users';
} elseif ($role === 'technician') {
    $table = 'technicians';
} else {
    echo "สิทธิ์ไม่ถูกต้อง";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM $table WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // อัปโหลดรูปโปรไฟล์
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('profile_', true) . '.' . $ext;
            $upload_dir = __DIR__ . '/uploads/profile_images/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $upload_path = $upload_dir . $new_filename;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                // อัปเดตชื่อไฟล์ในฐานข้อมูล
                $stmt = $conn->prepare("UPDATE $table SET profile_image=? WHERE id=?");
                $stmt->bind_param("si", $new_filename, $user_id);
                if ($stmt->execute()) {
                    $success = "อัปโหลดรูปโปรไฟล์สำเร็จ";
                    $user['profile_image'] = $new_filename; // อัพเดตในตัวแปรเพื่อแสดงภาพใหม่
                } else {
                    $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                }
            } else {
                $error = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
            }
        } else {
            $error = "ไฟล์ต้องเป็น JPG, PNG หรือ GIF เท่านั้น";
        }
    } else {
        $error = "กรุณาเลือกไฟล์รูปภาพก่อนอัปโหลด";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>แก้ไขโปรไฟล์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
</head>

<body class="container mt-4">
    <h2>แก้ไขรูปโปรไฟล์</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <img src="uploads/profile_images/<?php echo htmlspecialchars($user['profile_image'] ?? 'default_profile.png'); ?>" 
             alt="รูปโปรไฟล์" class="rounded-circle" width="150" height="150">
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="profile_image" class="form-label">เลือกไฟล์รูปโปรไฟล์ (JPG, PNG, GIF)</label>
            <input type="file" name="profile_image" id="profile_image" class="form-control" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">อัปโหลด</button>
    </form>

    <a href="<?php echo $role === 'technician' ? 'technician_profile.php?id=' . $user_id : 'index.php'; ?>" class="btn btn-secondary mt-3">กลับ</a>
</body>

</html>
