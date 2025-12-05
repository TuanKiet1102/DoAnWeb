<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require 'db.php';

function formatPrice($p) {
    if (!$p) return "";
    return number_format($p, 0, ',', '.');
}

$featuredRooms = [];
$sql = "SELECT * FROM room ORDER BY id DESC LIMIT 6";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $featuredRooms[] = $row;
    }
}
?>
<?php
$searchResults = [];
$isSearching = false;
$req = &$_REQUEST;
$locationRaw = isset($req['location']) && $req['location'] !== '' ? $req['location'] : '';
$locationId = is_numeric($locationRaw) ? (int)$locationRaw : 0;
$locationName = ($locationId === 0 && $locationRaw !== '') ? trim($locationRaw) : '';
$price_min_m = isset($req['price_min']) && $req['price_min'] !== '' ? floatval($req['price_min']) : null;
$price_max_m = isset($req['price_max']) && $req['price_max'] !== '' ? floatval($req['price_max']) : null;
$price_min = $price_min_m !== null ? intval($price_min_m * 1000000) : null;
$price_max = $price_max_m !== null ? intval($price_max_m * 1000000) : null;
$area = isset($req['area']) && $req['area'] !== '' ? $req['area'] : '';
$amenities = [];
if (isset($req['amenities'])) {
  if (is_array($req['amenities'])) $amenities = $req['amenities'];
  else if (strlen(trim($req['amenities']))>0) $amenities = [trim($req['amenities'])];
}

$room_type = isset($req['room_type']) ? trim($req['room_type']) : '';
$pet = isset($req['pet']) ? trim($req['pet']) : '';

if ($locationId || $locationName !== '' || $price_min !== null || $price_max !== null || $area !== '' || !empty($amenities) || $room_type !== '' || $pet !== '') {
  $isSearching = true;

  $where = " WHERE 1=1";
  $types = '';
  $params = [];

  if ($locationId > 0) {
    $where .= " AND district_id = ?";
    $types .= 'i'; $params[] = $locationId;
  } elseif ($locationName !== '') {
    if (preg_match('/^Quận\s*\d+$/iu', $locationName)) {
      $where .= " AND district_name = ?";
      $types .= 's'; $params[] = $locationName;
    } else {
      $where .= " AND district_name LIKE ?";
      $types .= 's'; $params[] = '%'. $locationName .'%';
    }
  }
  if ($price_min !== null) {
    $where .= " AND price_vnd >= ?";
    $types .= 'i'; $params[] = $price_min;
  }
  if ($price_max !== null) {
    $where .= " AND price_vnd <= ?";
    $types .= 'i'; $params[] = $price_max;
  }

  if ($area !== '') {
    if ($area == '20') {
      $where .= " AND (area_m2 IS NOT NULL AND area_m2 < 20)";
    } elseif ($area == '50') {
      $where .= " AND (area_m2 BETWEEN 20 AND 50)";
    } elseif ($area == '100') {
      $where .= " AND (area_m2 BETWEEN 50 AND 100)";
    } elseif ($area == '1000') {
      $where .= " AND (area_m2 > 100)";
    }
  }

  $use_fulltext = false;
  $idxRes = $conn->query("SHOW INDEX FROM `room` WHERE Column_name='amenities' AND Index_type='FULLTEXT'");
  if ($idxRes && $idxRes->num_rows > 0) $use_fulltext = true;

  if (!empty($amenities)) {
    if ($use_fulltext) {
      $terms = [];
      foreach ($amenities as $am) { $a = trim($am); if ($a!=='') $terms[] = '+' . $a; }
      if (!empty($terms)) {
        $where .= " AND MATCH(amenities) AGAINST (? IN BOOLEAN MODE)";
        $types .= 's'; $params[] = implode(' ', $terms);
      }
    } else {
      foreach ($amenities as $am) {
        $a = trim($am);
        if ($a === '') continue;
        $where .= " AND amenities LIKE ?";
        $types .= 's'; $params[] = '%'. $a .'%';
      }
    }
  }

  if ($room_type !== '') {
    $where .= " AND room_type = ?";
    $types .= 's'; $params[] = $room_type;
  }

  if ($pet !== '') {
    if ($pet === 'NuoiMuonLoaiThu') {
      $where .= " AND (pet = ? OR pet = ? OR pet = ?)";
      $types .= 'sss';
      $params[] = 'NuoiMuonLoaiThu';
      $params[] = 'NuoiCho';
      $params[] = 'NuoiMeo';
    } else {
      $where .= " AND pet = ?";
      $types .= 's'; $params[] = $pet;
    }
  }
    $order = 'ORDER BY id DESC';

    $selectSql = "SELECT * FROM room" . $where . " " . $order;

    if ($stmt = $conn->prepare($selectSql)) {
      if ($types !== '') {
        $bind_names = [];
        $bind_names[] = $types;
        for ($i=0;$i<count($params);$i++) $bind_names[] = &$params[$i];
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
      }
      if ($stmt->execute()) {
        $r = $stmt->get_result();
        if ($r) while ($row = $r->fetch_assoc()) $searchResults[] = $row;
      }
      $stmt->close();
    } else {
      $q = $conn->query($selectSql);
      if ($q) while ($row = $q->fetch_assoc()) $searchResults[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WebPhong — Hệ thống cho thuê phòng trọ</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./CSS/TrangChu.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
  <header class="header">
    <div class="container header-wrapper">
      <div class="logo-section">
        <img src="./IMG/logo.jpg" alt="Logo WebPhong" class="logo-img">
        <h1 class="logo-text">WebPhong</h1>
      </div>

      <nav class="nav-menu">
        <a href="TrangChu.php" onclick="return handleNavClick(event, 'home')">Trang chủ</a>
        <a href="#search-section" onclick="return handleNavClick(event, 'search-section')">Tìm phòng</a>
        <a href="#featured" onclick="return handleNavClick(event, 'featured')">Phòng nổi bật</a>
        <a href="#contact" onclick="return handleNavClick(event, 'contact')">Liên hệ</a>
      </nav>

      <div class="auth-buttons" <?php if (isset($_SESSION['user'])) echo 'data-server-user="1"'; ?>>
        <?php if (isset($_SESSION['user'])): ?>
          <div class="user-menu">
            <button class="avatar-btn" id="avatar-btn">
              <img src="<?php echo htmlspecialchars($_SESSION['user']['avatar'] ?? 'IMG/default-avatar.png'); ?>" 
                   alt="Avatar" class="avatar-img">
            </button>
            <div class="user-dropdown" id="user-dropdown">
              <div class="dropdown-header">
                <div class="dh-left">
                  <img src="<?php echo htmlspecialchars($_SESSION['user']['avatar'] ?? 'IMG/default-avatar.png'); ?>" alt="" class="dh-avatar">
                </div>
                <div class="dh-right">
                  <strong><?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? $_SESSION['user']['username']); ?></strong>
                  <div class="dh-sub">@<?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
                </div>
              </div>
              <div class="dropdown-list">
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                  <a href="./Admin/Admin.php" class="dropdown-item">
                    <span class="label">Trang Quản Trị</span>
                  </a>
                <?php endif; ?>
                <button type="button" onclick="showAvatarUpload(event)" class="dropdown-item">
                  <span class="label">Đổi ảnh đại diện</span>
                </button>
                <a href="logout.php" class="dropdown-item">
                  <span class="label">Đăng xuất</span>
                </a>
              </div>
            </div>
          </div>
          <input type="file" id="avatar-file" accept="image/*" class="d-none" style="display:none;" onchange="uploadAvatar()">
        <?php else: ?>
          <button class="btn btn-secondary" onclick="location.href='./DangNhap.php'">Đăng nhập</button>
          <button class="btn btn-primary" onclick="location.href='./DangKy.php'">Đăng ký</button>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <section id="home" class="hero">
    <div class="hero-content">
      <h2>Tìm phòng trọ nhanh chóng & dễ dàng</h2>
      <p>Kết nối bạn với hàng ngàn phòng trọ chất lượng trên toàn thành phố</p>
    </div>
  </section>

  <section id="search-section" class="search-section">
    <div class="container">
      <h3>Tìm phòng trọ của bạn</h3>
      
      <form id="search-form" class="search-form" action="#" method="GET">
        <div class="form-group">
          <label for="location">Địa điểm:</label>
          <select id="location" name="location">
            <option value="">-- Chọn quận --</option>
            <option value="Quận 1"<?php if (isset($locationRaw) && $locationRaw === 'Quận 1') echo ' selected'; ?>>Quận 1</option>
            <option value="Quận 2"<?php if (isset($locationRaw) && $locationRaw === 'Quận 2') echo ' selected'; ?>>Quận 2</option>
            <option value="Quận 3"<?php if (isset($locationRaw) && $locationRaw === 'Quận 3') echo ' selected'; ?>>Quận 3</option>
            <option value="Quận 4"<?php if (isset($locationRaw) && $locationRaw === 'Quận 4') echo ' selected'; ?>>Quận 4</option>
            <option value="Quận 5"<?php if (isset($locationRaw) && $locationRaw === 'Quận 5') echo ' selected'; ?>>Quận 5</option>
            <option value="Quận 6"<?php if (isset($locationRaw) && $locationRaw === 'Quận 6') echo ' selected'; ?>>Quận 6</option>
            <option value="Quận 7"<?php if (isset($locationRaw) && $locationRaw === 'Quận 7') echo ' selected'; ?>>Quận 7</option>
            <option value="Quận 8"<?php if (isset($locationRaw) && $locationRaw === 'Quận 8') echo ' selected'; ?>>Quận 8</option>
            <option value="Quận 9"<?php if (isset($locationRaw) && $locationRaw === 'Quận 9') echo ' selected'; ?>>Quận 9</option>
            <option value="Quận 10"<?php if (isset($locationRaw) && $locationRaw === 'Quận 10') echo ' selected'; ?>>Quận 10</option>
            <option value="Quận 11"<?php if (isset($locationRaw) && $locationRaw === 'Quận 11') echo ' selected'; ?>>Quận 11</option>
            <option value="Quận 12"<?php if (isset($locationRaw) && $locationRaw === 'Quận 12') echo ' selected'; ?>>Quận 12</option>
          </select>
        </div>

        <div class="form-group">
          <label for="price-min">Giá từ (triệu VNĐ):</label>
          <input type="number" id="price-min" name="price_min" placeholder="Ví dụ: 1" min="0" step="0.1">
        </div>

        <div class="form-group">
          <label for="price-max">Giá đến (triệu VNĐ):</label>
          <input type="number" id="price-max" name="price_max" placeholder="Ví dụ: 4" min="0" step="0.1">
        </div>

        <div class="form-group">
          <label for="area">Diện tích:</label>
          <select id="area" name="area">
            <option value="">-- Tất cả --</option>
            <option value="20">Dưới 20 m²</option>
            <option value="50">20 - 50 m²</option>
            <option value="100">50 - 100 m²</option>
            <option value="1000">Trên 100 m²</option>
          </select>
        </div>

        <div class="form-group">
          <label>Tiện nghi:</label>
            <div class="flex-row-wrap">
              <label><input type="checkbox" name="amenities[]" value="Wifi" <?php if(in_array('Wifi',$amenities)) echo 'checked'; ?>> Wifi</label>
              <label><input type="checkbox" name="amenities[]" value="MayGiat" <?php if(in_array('MayGiat',$amenities)) echo 'checked'; ?>> Máy giặt</label>
              <label><input type="checkbox" name="amenities[]" value="DieuHoa" <?php if(in_array('DieuHoa',$amenities)) echo 'checked'; ?>> Điều hòa</label>
              <label><input type="checkbox" name="amenities[]" value="FullNoiThat" <?php if(in_array('FullNoiThat',$amenities)) echo 'checked'; ?>> Full nội thất</label>
              <label><input type="checkbox" name="amenities[]" value="NhaTrong" <?php if(in_array('NhaTrong',$amenities)) echo 'checked'; ?>> Nhà trống</label>
              <label><input type="checkbox" name="amenities[]" value="MatTien" <?php if(in_array('MatTien',$amenities)) echo 'checked'; ?>> Mặt tiền</label>
            </div>
        </div>

        <div class="form-group">
          <label for="room_type">Dạng phòng:</label>
          <select id="room_type" name="room_type">
            <option value="">-- Tất cả --</option>
            <option value="Duplex" <?php if(isset($room_type) && $room_type==='Duplex') echo 'selected'; ?>>Duplex</option>
            <option value="Studio" <?php if(isset($room_type) && $room_type==='Studio') echo 'selected'; ?>>Studio</option>
            <option value="MatBang" <?php if(isset($room_type) && $room_type==='MatBang') echo 'selected'; ?>>Mặt Bằng</option>
            <option value="NhaNguyenCan" <?php if(isset($room_type) && $room_type==='NhaNguyenCan') echo 'selected'; ?>>Nhà Nguyên Căn</option>
          </select>
        </div>

        <div class="form-group">
          <label for="pet">Pet:</label>
          <select id="pet" name="pet">
            <option value="">-- Tất cả --</option>
            <option value="NuoiCho" <?php if(isset($pet) && $pet==='NuoiCho') echo 'selected'; ?>>Nuôi chó</option>
            <option value="NuoiMeo" <?php if(isset($pet) && $pet==='NuoiMeo') echo 'selected'; ?>>Nuôi mèo</option>
            <option value="NuoiMuonLoaiThu" <?php if(isset($pet) && $pet==='NuoiMuonLoaiThu') echo 'selected'; ?>>Nuôi muôn loài thú</option>
          </select>
        </div>

        <div class="mt-8">
          <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
      </form>
      
      <?php if ($isSearching): ?>
        <div id="search-results" class="mt-18">
          <h4>Kết quả tìm kiếm (<?php echo count($searchResults); ?>)</h4>
          <?php if (empty($searchResults)): ?>
            <p>Không tìm thấy phòng phù hợp.</p>
          <?php else: ?>
            <div class="rooms-grid">
              <?php foreach ($searchResults as $r): ?>
                <?php
                  $img = '';
                  if (!empty($r['images'])) {
                    $tmp = json_decode($r['images'], true);
                    if (is_array($tmp) && count($tmp) > 0) $img = $tmp[0];
                  }
                  if ($img === '') $img = trim($r['image'] ?? '');
                  if ($img === '') $img = 'IMG/no-image.png';
                  $priceText = formatPrice($r['price_vnd'] ?? 0) . ' đ/tháng';
                  $areaText  = isset($r['area_m2']) ? ((int)$r['area_m2'] . ' m²') : '';
                ?>
                <div class="room-card">
                  <div class="room-image">
                    <?php 
                      $status = isset($r['status']) ? (int)$r['status'] : 1;
                      $statusBadgeClass = $status === 1 ? 'status-badge status-badge-available' : 'status-badge status-badge-unavailable';
                      $statusText = $status === 1 ? 'Còn phòng' : 'Hết phòng';
                    ?>
                    <span class="<?php echo $statusBadgeClass; ?>"><?php echo $statusText; ?></span>
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="">
                  </div>
                  <div class="room-info">
                    <h4><?php echo htmlspecialchars($r['title']); ?></h4>
                    <p><?php echo htmlspecialchars($r['meta'] ?? $r['district_name']); ?> • <?php echo $areaText; ?></p>
                    <p class="room-price"><?php echo $priceText; ?></p>
                    
                    <div class="room-actions mt-8">
                      <button class="btn btn-secondary" onclick="viewDetail(<?php echo (int)$r['id']; ?>)">Xem chi tiết</button>
                      <button class="btn btn-primary" onclick="contact(<?php echo (int)$r['id']; ?>)">Liên hệ</button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php if (!$isSearching): ?>
  <section id="featured" class="featured-section">
    <div class="container">
      <h3 class="section-title">Phòng nổi bật</h3>
      <?php if (empty($featuredRooms)): ?>
        <p>Chưa có phòng nổi bật.</p>
      <?php else: ?>
        <div class="featured-grid">
          <?php foreach ($featuredRooms as $r): ?>
            <?php
              $img = '';
              if (!empty($r['images'])) {
                $tmp = json_decode($r['images'], true);
                if (is_array($tmp) && count($tmp) > 0) $img = $tmp[0];
              }
              if ($img === '') $img = trim($r['image'] ?? '');
              if ($img === '') $img = 'IMG/no-image.png';
              $priceText = formatPrice($r['price_vnd'] ?? 0) . ' đ/tháng';
              $areaText  = isset($r['area_m2']) ? ((int)$r['area_m2'] . ' m²') : '';
            ?>
            <article class="feature-card">
              <div class="feature-media" style="background-image:url('<?php echo htmlspecialchars($img); ?>')">
                <?php 
                  $status = isset($r['status']) ? (int)$r['status'] : 1;
                  $statusBadgeClass = $status === 1 ? 'status-badge status-con' : 'status-badge status-het';
                  $statusText = $status === 1 ? 'Còn phòng' : 'Hết phòng';
                ?>
                <span class="<?php echo $statusBadgeClass; ?>"><?php echo $statusText; ?></span>
                <button class="btn-favorite" title="Yêu thích">♡</button>
              </div>
              <div class="feature-body">
                <h4 class="feature-title"><?php echo htmlspecialchars($r['title']); ?></h4>
                <div class="feature-meta"><?php echo htmlspecialchars($r['district_name'] ?? ''); ?> • <?php echo $areaText; ?></div>
                <p class="feature-price"><?php echo $priceText; ?></p>
                <div class="feature-actions">
                  <button class="btn btn-outline" onclick="viewDetail(<?php echo (int)$r['id']; ?>)">Xem chi tiết</button>
                  <button class="btn btn-primary" onclick="contact(<?php echo (int)$r['id']; ?>)">Liên hệ</button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!$isSearching): ?>
  <section id="contact" class="contact-section">
    <div class="container contact-wrap">
      <h3 class="section-title">Liên hệ</h3>
      <div class="contact-grid">
        <div class="contact-card contact-info">
          <h4>Thông tin liên hệ</h4>
          <p><strong>Email:</strong> support@webphong.com</p>
          <p><strong>Điện thoại:</strong> 0123 456 789</p>
          <p><strong>Zalo:</strong> 0123 456 789</p>
          <p><strong>Địa chỉ:</strong> 123 Đường ABC, Quận 1, TPHCM</p>
        </div>
        <div class="contact-card contact-form">
          <h4>Gửi tin nhắn cho chúng tôi</h4>
          
          <?php if (!isset($_SESSION['user'])): ?>
            <div class="notice-login">
              <p><strong>Bạn cần <a href="DangNhap.php" class="link-emph">đăng nhập</a> để gửi tin nhắn.</strong></p>
            </div>
          <?php else: ?>
            <?php if (isset($_SESSION['contact_err'])): ?>
              <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['contact_err']); unset($_SESSION['contact_err']); ?></div>
            <?php elseif (isset($_SESSION['contact_msg'])): ?>
              <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['contact_msg']); unset($_SESSION['contact_msg']); ?></div>
            <?php endif; ?>

            <form action="contact.php" method="POST">
              <div class="form-row"><input type="text" name="name" placeholder="Họ tên đầy đủ" value="<?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? ''); ?>" required></div>
              <div class="form-row"><input type="email" name="email" placeholder="Email của bạn" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>" required></div>
              <div class="form-row"><input type="tel" name="phone" placeholder="Số điện thoại" required></div>
            <div class="form-row"><textarea name="message" rows="5" placeholder="Nội dung tin nhắn" required></textarea></div>
            <div class="form-row"><button type="submit" class="btn btn-primary">Gửi</button></div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h5>Về WebPhong</h5>
          <p>Hệ thống giới thiệu và cho thuê phòng trọ, nhà ở trực tuyến toàn diện.</p>
        </div>
        <div class="footer-col">
          <h5>Menu</h5>
          <ul>
            <li><a href="#home">Trang chủ</a></li>
            <li><a href="#search-section">Tìm phòng</a></li>
            <li><a href="#featured">Phòng nổi bật</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h5>Hỗ trợ</h5>
          <ul>
            <li><a href="#">Câu hỏi thường gặp</a></li>
            <li><a href="#">Hướng dẫn sử dụng</a></li>
            <li><a href="#">Chính sách bảo mật</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h5>Kết nối</h5>
          <ul>
            <li><a href="#">Facebook</a></li>
            <li><a href="#">Instagram</a></li>
            <li><a href="#">Zalo</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2025 WebPhong. Hệ thống dành cho sinh viên tập làm. Tất cả quyền được bảo lưu.</p>
      </div>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    (function(){
      function safeClosest(el, selector){
        if (!el) return null;
        return el.closest ? el.closest(selector) : null;
      }

      var avatarBtn = document.getElementById('avatar-btn');
      var fileInput = document.getElementById('avatar-file');

      if (avatarBtn) {
        avatarBtn.addEventListener('click', function(e){
          e.stopPropagation();
          var userMenu = avatarBtn.closest('.user-menu');
          if (userMenu) userMenu.classList.toggle('open');
        });
      }

      document.addEventListener('click', function(e){
        if (!e.target.closest || !e.target.closest('.user-menu')){
          var openMenus = document.querySelectorAll('.user-menu.open');
          openMenus.forEach(function(m){ m.classList.remove('open'); });
        }
      });

      if (!fileInput) fileInput = document.getElementById('avatar-file');
    })();

    function showAvatarUpload(e) {
      e.preventDefault();
      var fileInput = document.getElementById('avatar-file');
      if (fileInput) {
        fileInput.click();
        var userMenu = document.querySelector('.user-menu.open');
        if (userMenu) userMenu.classList.remove('open');
      }
    }

    function uploadAvatar() {
      var fileInput = document.getElementById('avatar-file');
      var file = fileInput.files[0];
      if (!file) return;

      var formData = new FormData();
      formData.append('avatar', file);

      fetch('update_avatar.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          var avatarImg = document.querySelector('.avatar-img');
          if (avatarImg) {
            avatarImg.src = data.avatar + '?' + new Date().getTime();
          }
          alert('Cập nhật ảnh đại diện thành công!');
        } else {
          alert('Lỗi: ' + (data.error || 'Không xác định'));
        }
      })
      .catch(err => {
        console.error(err);
        alert('Lỗi upload: ' + err.message);
      });
      fileInput.value = '';
    }
  </script>

  <script>
    (function() {
      if (window.jQuery && $.fn && $.fn.select2) {
        $(document).ready(function() {
          $('#location').select2({
            placeholder: '-- Chọn quận --',
            allowClear: true,
            width: '100%'
          });
        });
      }
    })();
  </script>

  <script>
    (function(){
      function setFullInteriorListeners(){

        var full = document.querySelector('input[name="amenities[]"][value="FullNoiThat"]');
        if (!full) return;

        var related = [ 'Wifi', 'MayGiat', 'DieuHoa' ];

        function markRelated(auto){
          related.forEach(function(val){
            var cb = document.querySelector('input[name="amenities[]"][value="' + val + '"]');
            if (!cb) return;
            if (auto) {
              cb.checked = true;
              cb.dataset.autoChecked = '1';
            } else {
              if (cb.dataset.autoChecked === '1') {
                cb.checked = false;
                delete cb.dataset.autoChecked;
              }
            }
          });
        }

        full.addEventListener('change', function(e){
          if (full.checked) {
            markRelated(true);
          } else {
            markRelated(false);
          }
        });
      }

      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setFullInteriorListeners);
      else setFullInteriorListeners();
    })();
  </script>

  <script>
    window.viewDetail = function(id){
      window.location.href = 'room.php?id=' + encodeURIComponent(id);
    };
    window.contact = function(id){
      var contactSection = document.getElementById('contact');
      if (contactSection) {
        contactSection.scrollIntoView({ behavior: 'smooth' });
      }
    };
    window.toggleFavorite = function(btn){
      btn.classList.toggle('liked');
      btn.textContent = btn.classList.contains('liked') ? '♥' : '♡';
    };
  </script>

  <script>
    (function () {
      var authBox = document.querySelector('.auth-buttons');
      if (!authBox) return;
      if (authBox.getAttribute('data-server-user') === '1') return;

      var role = localStorage.getItem('role');

      if (role === 'admin') {
        authBox.innerHTML = ''
          + '<button class="btn btn-secondary" onclick="location.href=\'Admin.php\'">Trang admin</button>'
          + '<button class="btn btn-primary" onclick="logout()">Đăng xuất</button>';
      } else if (role === 'user') {
        authBox.innerHTML = ''
          + '<button class="btn btn-secondary" onclick="location.href=\'DangNhap.php\'">Đổi tài khoản</button>'
          + '<button class="btn btn-primary" onclick="logout()">Đăng xuất</button>';
      }
    })();

    function logout() {
      localStorage.removeItem('role');
      localStorage.removeItem('currentUser');
      location.reload();
    }

    function handleNavClick(event, sectionId) {
      if (sectionId === 'home') {
        location.href = 'TrangChu.php';
        return false;
      }

      const isSearching = <?php echo json_encode($isSearching); ?>;
      if ((sectionId === 'featured' || sectionId === 'contact') && isSearching) {
        location.href = 'TrangChu.php#' + sectionId;
        return false;
      }

      event.preventDefault();
      const el = document.getElementById(sectionId);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
      }
      return false;
    }
  </script>
</body>
</html>
