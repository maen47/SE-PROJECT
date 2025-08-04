<?php
session_start();
include('db/db.php');

// ถ้าไม่ได้ล็อกอินให้เด้งไปหน้า index
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$tid     = $_GET['tid'];  // technician id

// ดึงข้อมูลช่าง
$stmt_t = $conn->prepare("SELECT * FROM technicians WHERE id=?");
$stmt_t->bind_param("i",$tid);
$stmt_t->execute();
$tech = $stmt_t->get_result()->fetch_assoc();

// ถ้ามีการ post รีวิว
if($_SERVER['REQUEST_METHOD']=='POST'){
    $rating  = $_POST['rating'];
    $comment = $_POST['comment'];

    $sql = $conn->prepare("INSERT INTO reviews(user_id,technician_id,rating,comment) VALUES(?,?,?,?)");
    $sql->bind_param("iiis",$user_id,$tid,$rating,$comment);
    $sql->execute();
    header("Location: reviews.php?tid=$tid");
    exit;
}

// ดึงรีวิวทั้งหมด
$stmt_r = $conn->prepare("SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id=u.id WHERE technician_id=? ORDER BY created_at DESC");
$stmt_r->bind_param("i",$tid);
$stmt_r->execute();
$reviews = $stmt_r->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>รีวิวช่าง | ThunderFix</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<div class="container mt-4">
    <a href="dashboard.php" class="btn btn-secondary mb-2">← กลับ</a>
    <div class="card mb-4">
        <div class="card-body">
            <h3><?php echo $tech['name']; ?></h3>
            <p>ประเภท: <?php echo $tech['specialty']; ?> | เบอร์: <?php echo $tech['phone']; ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>ให้คะแนน/รีวิว</h5>
            <form method="POST">
                <div class="mb-2">
                    <label>คะแนน (1-5)</label>
                    <select name="rating" class="form-control" required>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label>ความคิดเห็น</label>
                    <textarea name="comment" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">ส่งรีวิว</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>รีวิวทั้งหมด</h5>
            <?php while($row = $reviews->fetch_assoc()): ?>
                <div class="border-bottom py-2">
                    <strong><?php echo $row['name']; ?></strong> 
                    <span class="badge bg-warning text-dark"><?php echo $row['rating']; ?>/5</span>
                    <p><?php echo nl2br($row['comment']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
</body>
</html>
