<!-- index.php -->
 <?php
session_start();
session_unset();       // เคลียร์ค่าทั้งหมดใน $_SESSION
session_destroy();     // ทำลาย session ปัจจุบัน
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ThunderFix | หน้าหลัก</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">ThunderFix</a>
    <span class="navbar-text text-white">
      ค้นหาช่างทันใจ
    </span>
  </div>
</nav>

<!-- Content -->
<div class="container mt-5 text-center">
  <h1 class="mb-4">ยินดีต้อนรับสู่ ThunderFix</h1>
  <p>ระบบค้นหาช่างใกล้ตัว พร้อมรีวิว</p>
  <a href="login.php" class="btn btn-primary btn-lg m-2">เข้าสู่ระบบ</a>
  <a href="register.php" class="btn btn-outline-primary btn-lg m-2">สมัครสมาชิก</a>
</div>

</body>
</html>
