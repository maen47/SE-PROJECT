<?php
session_start();
include('db/db.php');

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'technician') {
        header("Location: technician_profile.php?id=" . $_SESSION['user_id']);
        exit;
    } else {
        header("Location: dashboard.php");
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ตรวจสอบช่างก่อน
    $stmt2 = $conn->prepare("SELECT * FROM technicians WHERE email=?");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($row2 = $result2->fetch_assoc()) {
        if (password_verify($password, $row2['password'])) {
            $_SESSION['user_id'] = $row2['id'];
            $_SESSION['role'] = 'technician';
            header("Location: technician_profile.php?id=" . $row2['id']);
            exit;
        }
    }

    // ตรวจสอบผู้ใช้งานทั่วไป
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = 'user';
            header("Location: dashboard.php");
            exit;
        }
    }

    $error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง!";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ | ThunderFix</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">ThunderFix</a>
            <span class="navbar-text text-white">ค้นหาช่างทันใจ</span>
        </div>
    </nav>

    <!-- กลางหน้าจอ -->
    <div class="centered-container">
        <form method="POST" class="login-card" novalidate>
            <h1>เข้าสู่ระบบ</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <!-- อีเมล -->
            <div style="position: relative; margin-bottom: 12px;">
                <span class="form-icon">📧</span>
                <input type="email" name="email" placeholder="อีเมล" class="form-control" required>
            </div>

            <!-- รหัสผ่าน + ปุ่มดู/ซ่อน -->
            <div style="position: relative; margin-bottom: 12px;">
                <span class="form-icon">🔒</span>
                <input type="password" id="password" name="password" placeholder="รหัสผ่าน" class="form-control" required>
                <button type="button" id="togglePassword" class="toggle-password-btn" aria-label="แสดง/ซ่อนรหัสผ่าน" title="แสดง/ซ่อนรหัสผ่าน">
                    <span id="eyeIcon" aria-hidden="true">👁</span>
                </button>
            </div>

            <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>

            <div class="d-flex gap-3 justify-content-center">
                <a href="register.php">สมัครสมาชิกผู้ใช้งาน</a>
                <a href="index.php">กลับไปหน้าหลัก</a>
                <a href="forgot_password.php">ลืม</a>
            </div>
        </form>
    </div>

    <!-- สคริปต์ปุ่มดู/ซ่อนรหัสผ่าน -->
    <script>
        (function(){
            const toggleBtn = document.getElementById('togglePassword');
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            // คลิกสลับแสดง/ซ่อน
            toggleBtn.addEventListener('click', function () {
                const isHidden = pwdInput.type === 'password';
                pwdInput.type = isHidden ? 'text' : 'password';
                eyeIcon.textContent = isHidden ? '🙈' : '👁';
                pwdInput.focus({ preventScroll: true });
            });

            // รองรับกดคีย์บอร์ด (เช่น Space/Enter)
            toggleBtn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleBtn.click();
                }
            });
        })();
    </script>
</body>
</html>
