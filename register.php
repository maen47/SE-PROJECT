<?php
session_start();
include('db/db.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $password_raw = $_POST['password'];

  if (strlen($password_raw) < 8) {
    $error = "รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร!";
  } else {
    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    // ตรวจซ้ำอีเมล
    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
      $error = "อีเมลนี้ถูกใช้งานแล้ว!";
    } else {
      // insert
      $sql = $conn->prepare("INSERT INTO users(name,email,password) VALUES(?,?,?)");
      $sql->bind_param("sss", $name, $email, $password);
      if ($sql->execute()) {
        $success = "สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ.";
      } else {
        $error = "เกิดข้อผิดพลาดในการสมัครสมาชิก";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>สมัครสมาชิก | ThunderFix</title>

  <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
</head>

<body>

  <div class="centered-container">
    <form method="POST" class="login-card">
      <h1>สมัครสมาชิกผู้ใช้ทั่วไป</h1>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
      <?php endif; ?>

      <input type="text" name="name" placeholder="ชื่อ-นามสกุล" required />
      <input type="email" name="email" placeholder="อีเมล" required />
      <input type="password" name="password" placeholder="รหัสผ่าน" required />

      <button type="submit">สมัครสมาชิก</button>

      <a href="add_technician.php">สมัครเป็นช่าง</a>
      <a href="index.php">← กลับไปหน้าหลัก</a>
    </form>
  </div>

</body>

</html>