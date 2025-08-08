<?php
session_start();
include('db/db.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $phone = $_POST['phone'];
  $specialty = $_POST['specialty'];
  $lat = $_POST['lat'];
  $lng = $_POST['lng'];
  $password_raw = $_POST['password'];

  if (strlen($password_raw) < 8) {
    $error = "รหัสผ่านต้องมีอย่างน้อย 8 ตัว";
  } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
    $error = "เบอร์โทรต้องมี 10 หลัก";
  } elseif (empty($lat) || empty($lng)) {
  } else {
    $password = password_hash($password_raw, PASSWORD_DEFAULT);



    if (empty($lat) || empty($lng)) {
      $error = "กรุณาอนุญาตให้ระบบเข้าถึงตำแหน่ง GPS ของคุณ";
    } else {
      $check = $conn->prepare("SELECT id FROM technicians WHERE email=?");
      $check->bind_param("s", $email);
      $check->execute();
      $rs = $check->get_result();
      if ($rs->num_rows > 0) {
        $error = "อีเมลนี้มีช่างใช้แล้ว!";
      } else {
        $sql = $conn->prepare("INSERT INTO technicians(name,email,password,phone,specialty,lat,lng)
                                   VALUES (?,?,?,?,?,?,?)");
        $sql->bind_param("sssssss", $name, $email, $password, $phone, $specialty, $lat, $lng);
        if ($sql->execute()) {
          $success = "สมัครเป็นช่างสำเร็จ! กรุณาเข้าสู่ระบบ.";
        } else {
          $error = "ไม่สามารถสมัครได้ ลองใหม่อีกครั้ง!";
        }
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
  <title>สมัครเป็นช่าง | ThunderFix</title>

  <!-- โหลดฟอนต์และ CSS ภายนอก -->
  <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />

  <script>
    // ขอพิกัด GPS และใส่ใน input hidden
    function getLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
          document.getElementById('lat').value = position.coords.latitude;
          document.getElementById('lng').value = position.coords.longitude;
        }, function (error) {
          alert("ไม่สามารถเข้าถึงตำแหน่ง GPS ของคุณได้ กรุณาอนุญาตการใช้งาน");
        });
      } else {
        alert("เบราว์เซอร์ไม่รองรับการระบุตำแหน่ง GPS");
      }
    }
    window.onload = getLocation;

    function validateLocation() {
      const lat = document.getElementById('lat').value;
      const lng = document.getElementById('lng').value;
      if (!lat || !lng) {
        alert("กรุณาอนุญาตให้ระบบเข้าถึงตำแหน่ง GPS ของคุณ");
        return false;
      }
      return true;
    }
  </script>
</head>

<body>
  <div class="centered-container">
    <form method="POST" onsubmit="return validateLocation();" class="login-card">
      <h1>สมัครเป็นช่าง</h1>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
      <?php endif; ?>

      <div style="position: relative;">
        <span class="form-icon">👤</span>
        <input type="text" name="name" placeholder="ชื่อ-นามสกุล" class="form-control" required>
      </div>

      <div style="position: relative;">
        <span class="form-icon">📧</span>
        <input type="email" name="email" placeholder="อีเมล" class="form-control" required>
      </div>

      <div style="position: relative;">
        <span class="form-icon">🔒</span>
        <input type="password" name="password" placeholder="รหัสผ่าน" class="form-control" required>
      </div>

      <div style="position: relative;">
        <span class="form-icon">📞</span>
        <input type="text" name="phone" placeholder="เบอร์โทร" class="form-control" required>
      </div>

      <div style="position: relative;">
        <span class="form-icon">🛠</span>
        <select name="specialty" class="form-control" required>
          <option value="ช่างไฟฟ้า">ช่างไฟฟ้า</option>
          <option value="ช่างแอร์">ช่างแอร์</option>
          <option value="ช่างประปา">ช่างประปา</option>
          <option value="ช่างซ่อมคอม">ช่างซ่อมคอม</option>
          <option value="อื่นๆ">อื่นๆ</option>
        </select>
      </div>

      <input type="hidden" name="lat" id="lat" required>
      <input type="hidden" name="lng" id="lng" required>

      <button type="submit">สมัครเป็นช่าง</button>

      <a href="index.php">← กลับหน้าเข้าสู่ระบบ</a>
    </form>
  </div>

</body>

</html>