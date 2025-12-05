<?php
require 'db.php';

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chi tiết phòng</title>
  <link rel="stylesheet" href="/DOANWEB/CSS/TrangChu.css">
  <link rel="stylesheet" href="./CSS/Room.css">
</head>
<body>
  <header class="header">
    <div class="container header-wrapper">
      <div class="logo-section">
        <img src="./IMG/logo.jpg" alt="Logo" class="logo-img">
        <h1 class="logo-text">WebPhong</h1>
      </div>
    </div>
  </header>

  <main class="container main-min">
    <div id="content">
      <?php if (!$room): ?>
        <p>Không tìm thấy phòng.</p>
      <?php else: ?>
        <?php



            $img = '';
            $images_list = [];
            if (!empty($room['images'])) {
              $tmp = json_decode($room['images'], true);
              if (is_array($tmp) && count($tmp) > 0) {
                $images_list = $tmp;
                $img = $tmp[0];
              }
            }
            if ($img === '') {
              $img = trim($room['image'] ?? '');
            }
            if ($img === '') {

              $img = 'https://via.placeholder.com/400x300?text=No+Image';
            } else {

              if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
                // ok
              } else {
                if (preg_match('/^[A-Za-z]:\\\\|\\\\/', $img) || strpos($img, '\\') !== false) {
                  $img = 'IMG/' . basename($img);
                }
                $img = ltrim($img, './\\');
              }
            }

          $title   = htmlspecialchars($room['title'] ?? '');
          $meta    = htmlspecialchars($room['meta'] ?? ($room['district_name'] ?? ''));
          $desc    = nl2br(htmlspecialchars($room['desc'] ?? ''));
          $area_m2 = isset($room['area_m2']) ? (int)$room['area_m2'] : null;
          $price   = isset($room['price_vnd']) ? formatPrice($room['price_vnd']) . " đ/tháng" : "";
          $statusText  = getStatusText(isset($room['status']) ? (int)$room['status'] : null);
          $statusClass = (isset($room['status']) && (int)$room['status'] === 0)
                         ? "status-unavailable" : "status-available";
        ?>
        <div class="room-detail">
          <div class="img-column">
            <img id="main-room-img" src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo $title; ?>">
            <?php if (!empty($images_list)): ?>
              <div class="thumbs-row">
                <?php foreach ($images_list as $thumb): ?>
                  <img class="small-thumb" src="<?php echo htmlspecialchars($thumb); ?>" onclick="document.getElementById('main-room-img').src=this.src"/>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="room-info">
            <h2><?php echo $title; ?></h2>

            <?php if ($statusText): ?>
              <p>
                <strong>Trạng thái:</strong>
                <span class="status-badge <?php echo $statusClass; ?>">
                  <?php echo $statusText; ?>
                </span>
              </p>
            <?php endif; ?>

            <?php if ($meta !== ''): ?>
              <p><strong>Vị trí:</strong> <?php echo $meta; ?></p>
            <?php endif; ?>

            <?php if ($area_m2): ?>
              <p><strong>Diện tích:</strong> <?php echo $area_m2; ?> m²</p>
            <?php endif; ?>

            <?php if ($price !== ''): ?>
              <p><strong>Giá:</strong> <?php echo $price; ?></p>
            <?php endif; ?>

            <?php if (!empty($room['room_type'])): ?>
              <p><strong>Dạng phòng:</strong> 
                <?php 
                  $roomTypeText = $room['room_type'];
                  if ($roomTypeText === 'Duplex') $roomTypeText = 'Duplex';
                  elseif ($roomTypeText === 'Studio') $roomTypeText = 'Studio';
                  elseif ($roomTypeText === 'MatBang') $roomTypeText = 'Mặt bằng';
                  elseif ($roomTypeText === 'NhaNguyenCan') $roomTypeText = 'Nhà nguyên căn';
                  echo htmlspecialchars($roomTypeText);
                ?>
              </p>
            <?php endif; ?>

            <?php if (!empty($room['pet'])): ?>
              <p><strong>Được nuôi thú cưng:</strong> 
                <?php 
                  $petText = $room['pet'];
                  if ($petText === 'NuoiCho') $petText = 'Nuôi chó';
                  elseif ($petText === 'NuoiMeo') $petText = 'Nuôi mèo';
                  elseif ($petText === 'NuoiMuonLoaiThu') $petText = 'Nuôi mọi loài thú';
                  echo htmlspecialchars($petText);
                ?>
              </p>
            <?php endif; ?>

            <?php if (!empty($room['amenities'])): ?>
              <?php
                $items = array_map('trim', explode(',', $room['amenities']));
                $labels = [];
                foreach ($items as $it) {
                  if ($it === '') continue;
                  if ($it === 'Wifi') $labels[] = 'Wifi';
                  elseif ($it === 'MayGiat') $labels[] = 'Máy giặt';
                  elseif ($it === 'DieuHoa') $labels[] = 'Điều hòa';
                  elseif ($it === 'FullNoiThat') $labels[] = 'Full nội thất';
                  elseif ($it === 'NhaTrong') $labels[] = 'Nhà trống';
                  elseif ($it === 'MatTien') $labels[] = 'Mặt tiền';
                  else $labels[] = htmlspecialchars($it);
                }
              ?>
              <?php if (!empty($labels)): ?>
                <p><strong>Tiện nghi:</strong> <?php echo htmlspecialchars(implode(', ', $labels)); ?></p>
              <?php endif; ?>
            <?php endif; ?>

            <?php if ($desc !== ''): ?>
              <p><?php echo $desc; ?></p>
            <?php endif; ?>
            
            <p><a class="btn btn-primary" href="TrangChu.php">Quay về</a>
            <a class="btn btn-primary" href="QR.php?id=<?php echo $room['id']; ?>">Đặt phòng</a></p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <p>&copy; 2025 WebPhong</p>
    </div>
  </footer>
</body>
</html>
