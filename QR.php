<?php
require 'db.php';
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$room = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM room WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room   = $result->fetch_assoc();
    $stmt->close();
}

function formatPrice($p) {
    if (!$p) return "";
    return number_format($p, 0, ',', '.');
}

function getStatusText($status) {
    if ($status === null) return "";
    return $status ? "Còn phòng" : "Hết phòng";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Đặt phòng</title>
  <link rel="stylesheet" href="/DOANWEB/CSS/TrangChu.css">
  <link rel="stylesheet" href="./CSS/Room.css">
  <style>
    .row { display: flex; gap: 30px; }
    .col-left { flex: 1; }
    .col-right { flex: 1; background:#f9f9f9; padding:20px; border-radius:8px; }
    .form-group { margin-bottom:15px; }
    label { display:block; margin-bottom:5px; font-weight:bold; }
    input, textarea { width:100%; padding:8px; }
    button { margin-top:10px; padding:10px 15px; cursor:pointer; }
    .room-img { max-width:100%; border-radius:8px; margin-bottom:15px; }
  </style>
</head>
<body>
    <p><a class="btn btn-primary" href="TrangChu.php">Quay về</a></p>   
  <div class="container" style="max-width: 1100px; margin: 30px auto;">
    <?php if (!$room): ?>
      <h2>Không tìm thấy phòng.</h2>
    <?php else: ?>
      <div class="row">

        <div class="col-left">
          <?php
            $img = '';
            if (!empty($room['images'])) {
              $tmp = json_decode($room['images'], true);
              if (is_array($tmp) && count($tmp) > 0) {
                $img = $tmp[0];
              }
            }
            if ($img === '') $img = $room['image'] ?? 'https://via.placeholder.com/400x300?text=No+Image';
          ?>
          <p><h1>Đặt Phòng</h1></p>
          <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($room['title']); ?>" class="room-img">

          <h2><?php echo htmlspecialchars($room['title']); ?></h2>
          <p><strong>Mã phòng:</strong> <?php echo $room['id']; ?></p>
          <p><strong>Vị trí:</strong> <?php echo htmlspecialchars($room['district_name']); ?></p>
          <p><strong>Diện tích:</strong> <?php echo $room['area_m2']; ?> m²</p>
          <p><strong>Giá:</strong> <?php echo formatPrice($room['price_vnd']); ?> đ/tháng</p>
          <p><strong>Trạng thái:</strong> <?php echo getStatusText($room['status']); ?></p>
          <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($room['desc'])); ?></p>
        </div>

        <div class="col-right">
          <h3>Thông tin khách hàng</h3>
          <form action="Pay_money/xuly_thanhtoan.php" method="POST">
            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
            <input type="hidden" name="tongtien" value="<?php echo $room['price_vnd']; ?>">

            <div class="form-group">
              <label>Họ tên:</label>
              <input type="text" name="hoten" required>
            </div>
            <div class="form-group">
              <label>Số điện thoại:</label>
              <input type="text" name="sdt" required>
            </div>
            <div class="form-group">
              <label>Email:</label>
              <input type="email" name="email" required>
            </div>
            <div class="form-group">
              <label>Địa chỉ:</label>
              <textarea name="diachi" required></textarea>
            </div>
            <div class="form-group">
              <label>Ghi chú:</label>
              <textarea name="ghichu"></textarea>
            </div>

            <button type="submit" name="method" value="cod" style="background:#007bff;color:#fff;">Thanh toán COD</button>
            <button type="submit" name="method" value="momo" style="background:#A50064;color:#fff;">Thanh toán MoMo QR</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
