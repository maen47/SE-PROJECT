<?php
session_start();
include('db/db.php');

// เปิด debug ตอนพัฒนา (คอมเมนต์ออกในโปรดักชัน)
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// ini_set('display_errors', 1); error_reporting(E_ALL);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email            = trim($_POST['email'] ?? '');
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ตรวจความครบถ้วนและความยาว
    if ($email === '' || $new_password === '' || $confirm_password === '') {
        $error = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
    } elseif ($new_password !== $confirm_password) {
        $error = 'รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน';
    } elseif (strlen($new_password) < 8) {
        $error = 'รหัสผ่านใหม่ควรมีอย่างน้อย 8 ตัวอักษร';
    } else {
        // หาอีเมลใน technicians ก่อน แล้วค่อย users
        $tables  = ['technicians', 'users'];
        $updated = false;

        foreach ($tables as $table) {
            $stmt = $conn->prepare("SELECT id FROM $table WHERE email=? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            if ($row) {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $upd  = $conn->prepare("UPDATE $table SET password=? WHERE id=?");
                $upd->bind_param("si", $hash, $row['id']);
                $upd->execute();

                // ให้ผ่านแม้ค่าจะเท่าเดิม (affected_rows อาจเป็น 0)
                $updated = true;
                $success = 'เปลี่ยนรหัสผ่านเรียบร้อย! กรุณาเข้าสู่ระบบใหม่ด้วยรหัสผ่านใหม่';
                break;
            }
        }

        if (!$updated) {
            $error = 'ไม่พบบัญชีที่ใช้อีเมลนี้ในระบบ';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>รีเซ็ตรหัสผ่าน</title>
<body>
<div class="centered-container">
  <div class="card card-wrap p-4 shadow-sm">
    <h3 class="mb-3 text-center">รีเซ็ตรหัสผ่าน</h3>
    <p class="text-muted text-center">กรอกอีเมลและรหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร)</p>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
      <a class="btn btn-primary w-100" href="index.php">กลับไปเข้าสู่ระบบ</a>
    <?php else: ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3" style="position:relative;">
          <span class="form-icon">📧</span>
          <input type="email" name="email" class="form-control with-icon" placeholder="อีเมล"
                 required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>">
        </div>

        <div class="mb-3" style="position:relative;">
          <span class="form-icon">🔒</span>
          <input type="password" id="new_password" name="new_password" class="form-control with-icon"
                 placeholder="รหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร)" minlength="8" required>
          <button type="button" class="toggle-password-btn" data-target="new_password" title="ดู/ซ่อนรหัส">👁</button>
        </div>

        <div class="mb-3" style="position:relative;">
          <span class="form-icon">✅</span>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control with-icon"
                 placeholder="ยืนยันรหัสผ่านใหม่" minlength="8" required>
          <button type="button" class="toggle-password-btn" data-target="confirm_password" title="ดู/ซ่อนรหัส">👁</button>
        </div>

        <button type="submit" class="btn btn-success w-100">บันทึกรหัสผ่านใหม่</button>
        <div class="text-center mt-3"><a href="index.php">กลับไปเข้าสู่ระบบ</a></div>
      </form>
    <?php endif; ?>
  </div>
</div>

<script>
  document.querySelectorAll('.toggle-password-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      target.type = target.type === 'password' ? 'text' : 'password';
      btn.textContent = target.type === 'password' ? '👁' : '🙈';
      target.focus({preventScroll:true});
    });
  });
</script>
</body>
</html>
