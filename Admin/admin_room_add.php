<?php
$districtList = [
  1 => 'Quận 1', 2 => 'Quận 2', 3 => 'Quận 3', 4 => 'Quận 4',
  5 => 'Quận 5', 6 => 'Quận 6', 7 => 'Quận 7', 8 => 'Quận 8',
  9 => 'Quận 9', 10 => 'Quận 10', 11 => 'Quận 11', 12 => 'Quận 12'
];

$editRoom = null;
if (isset($_GET['edit_room'])) {
  $editId = (int)$_GET['edit_room'];
  if ($editId > 0) {
    $stmt = $conn->prepare("SELECT * FROM room WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $res = $stmt->get_result();
    $editRoom = $res->fetch_assoc();
    $stmt->close();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_room'])) {
  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  if ($id === 0) {
    $res = $conn->query("SELECT MAX(id) AS maxid FROM room");
    $maxId = 0;
    if ($res) { $row = $res->fetch_assoc(); $maxId = (int)($row['maxid'] ?? 0); }
    $id = $maxId + 1;
  }

  $title       = trim($_POST['title'] ?? '');
  $district_id = (int)($_POST['district_id'] ?? 0);
  $district_name = $districtList[$district_id] ?? '';

  $meta      = trim($_POST['meta'] ?? '');
  $area_m2   = (int)($_POST['area_m2'] ?? 0);
  $price_vnd = (int)($_POST['price_vnd'] ?? 0);

  $amenities_raw = $_POST['amenities'] ?? '';
  $amenities = is_array($amenities_raw) ? implode(',', array_map('trim', $amenities_raw)) : trim($amenities_raw ?? '');
  $room_type  = trim($_POST['room_type'] ?? '');
  $pet        = trim($_POST['pet'] ?? '');
  $desc       = trim($_POST['desc'] ?? '');
  $status     = isset($_POST['status']) ? (int)$_POST['status'] : 1;

  $has_images_col = false;
  $colRes = $conn->query("SHOW COLUMNS FROM `room` LIKE 'images'");
  if ($colRes && $colRes->num_rows > 0) $has_images_col = true;

  $old_images_json = trim($_POST['old_images'] ?? '');
  $old_images = [];
  if ($old_images_json !== '' && $old_images_json !== '[]') {
    $tmp = json_decode($old_images_json, true);
    if (is_array($tmp)) $old_images = $tmp;
  }

  $new_images = [];
  $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  $max_file_size = 5 * 1024 * 1024;

  if (isset($_FILES['image_files']) && is_array($_FILES['image_files']['name']) && !empty($_FILES['image_files']['name'][0])) {
    for ($i=0; $i < count($_FILES['image_files']['name']); $i++) {
      $err = $_FILES['image_files']['error'][$i];

      if ($err === UPLOAD_ERR_NO_FILE || empty($_FILES['image_files']['name'][$i])) continue;
      if ($err !== UPLOAD_ERR_OK) {
        $_SESSION['admin_err'] = 'Lỗi upload ảnh. Mã: ' . $err;
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        header("Location: Admin.php?tab=tab-add");
        exit;
      }

      $tmpName  = $_FILES['image_files']['tmp_name'][$i];
      $origName = $_FILES['image_files']['name'][$i];
      $fileSize = $_FILES['image_files']['size'][$i];

      if ($fileSize > $max_file_size) {
        $_SESSION['admin_err'] = 'File ảnh quá lớn. Tối đa 5MB mỗi file.';
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        header("Location: Admin.php?tab=tab-add");
        exit;
      }

      $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed)) {
        $_SESSION['admin_err'] = 'Định dạng không hợp lệ. Chỉ chấp nhận: ' . implode(', ', $allowed);
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        header("Location: Admin.php?tab=tab-add");
        exit;
      }

      $imgDir = dirname(__DIR__) . '/IMG';
      if (!is_dir($imgDir)) { @mkdir($imgDir, 0777, true); }
      $timestamp = time();
      $randomNum = rand(10000, 99999);
      $newName = $imgDir . DIRECTORY_SEPARATOR . $timestamp . '_' . $randomNum . '.' . $ext;
      $relPath = 'IMG/' . $timestamp . '_' . $randomNum . '.' . $ext;

      if (move_uploaded_file($tmpName, $newName)) {
        $new_images[] = $relPath;
      } else {
        $_SESSION['admin_err'] = 'Không thể upload một file ảnh. Kiểm tra quyền thư mục.';
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        header("Location: Admin.php?tab=tab-add");
        exit;
      }
    }
  }

  if (!empty($new_images)) {
    foreach ($old_images as $of) {
      if (!$of) continue;
      if (strpos($of, 'IMG/') !== false || strpos($of, 'IMG\\') !== false) {
        if (file_exists($of)) {
          @unlink($of);
        } else {
          $try = __DIR__ . DIRECTORY_SEPARATOR . ltrim($of, './\\');
          if (file_exists($try)) @unlink($try);
        }
      }
    }
    $images_array = array_values(array_filter($new_images));
  } else {
    $images_array = array_values(array_filter($old_images));
  }

  $images_json = empty($images_array) ? '' : json_encode($images_array, JSON_UNESCAPED_SLASHES);
  $imagePath   = !empty($images_array) ? $images_array[0] : null;

  if (!$has_images_col && $images_json !== '') {
    $alter = $conn->query("ALTER TABLE `room` ADD COLUMN `images` TEXT DEFAULT NULL");
    if ($alter) $has_images_col = true;
  }

  $checkCol = $conn->query("SHOW COLUMNS FROM `room` LIKE 'room_type'");
  if (!$checkCol || $checkCol->num_rows === 0) {
    $conn->query("ALTER TABLE `room` ADD COLUMN `room_type` VARCHAR(100) DEFAULT ''");
  }
  $checkCol = $conn->query("SHOW COLUMNS FROM `room` LIKE 'pet'");
  if (!$checkCol || $checkCol->num_rows === 0) {
    $conn->query("ALTER TABLE `room` ADD COLUMN `pet` VARCHAR(100) DEFAULT ''");
  }

  $room_exists = false;
  if ($id > 0) {
    $check = $conn->query("SELECT id FROM room WHERE id=" . $id);
    if ($check && $check->num_rows > 0) $room_exists = true;
  }

  if ($room_exists) {
    if ($has_images_col) {
      $sql = "UPDATE room 
        SET title=?, district_id=?, district_name=?, meta=?, area_m2=?, price_vnd=?, image=?, images=?, amenities=?, room_type=?, pet=?, `desc`=?, status=?
        WHERE id=?";
      $stmt = $conn->prepare($sql);
      if ($stmt) {
        $types = 'sissiissssssii';
        $stmt->bind_param($types,
          $title, $district_id, $district_name, $meta, $area_m2, $price_vnd,
          $imagePath, $images_json, $amenities, $room_type, $pet, $desc, $status, $id
        );
        if (!$stmt->execute()) {
          $_SESSION['admin_err'] = 'Lỗi cập nhật phòng: ' . $stmt->error;
        } else {
          $_SESSION['admin_msg'] = $stmt->affected_rows > 0 ? 'Cập nhật phòng thành công.' : 'Không có dữ liệu nào được cập nhật.';
        }
        $stmt->close();
      } else {
        $_SESSION['admin_err'] = 'Lỗi chuẩn bị truy vấn cập nhật: ' . $conn->error;
      }
    } else {
      $sql = "UPDATE room 
        SET title=?, district_id=?, district_name=?, meta=?, area_m2=?, price_vnd=?, image=?, amenities=?, room_type=?, pet=?, `desc`=?, status=?
        WHERE id=?";
      $stmt = $conn->prepare($sql);
      if ($stmt) {
        $types = 'sissiisssssii';
        $stmt->bind_param($types,
          $title, $district_id, $district_name, $meta, $area_m2, $price_vnd,
          $imagePath, $amenities, $room_type, $pet, $desc, $status, $id
        );
        if (!$stmt->execute()) {
          $_SESSION['admin_err'] = 'Lỗi cập nhật phòng: ' . $stmt->error;
        } else {
          $_SESSION['admin_msg'] = $stmt->affected_rows > 0 ? 'Cập nhật phòng thành công.' : 'Không có dữ liệu nào được cập nhật.';
        }
        $stmt->close();
      } else {
        $_SESSION['admin_err'] = 'Lỗi chuẩn bị truy vấn cập nhật: ' . $conn->error;
      }
    }
  } else {
    $attempt = 0; $maxAttempts = 2; $inserted = false;
    while ($attempt < $maxAttempts && !$inserted) {
      if ($has_images_col) {
        $sql = "INSERT INTO room (id, title, district_id, district_name, meta, area_m2, price_vnd, image, images, amenities, room_type, pet, `desc`, status)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
          $types = 'isissiissssssi';
          $stmt->bind_param($types,
            $id, $title, $district_id, $district_name, $meta, $area_m2, $price_vnd,
            $imagePath, $images_json, $amenities, $room_type, $pet, $desc, $status
          );
          if (!$stmt->execute()) {
            $err = $stmt->error;
            if (($stmt->errno === 1062 || stripos($err, 'duplicate') !== false) && $attempt === 0) {
              $r = $conn->query("SELECT MAX(id) AS maxid FROM room");
              $mx = 0;
              if ($r) { $row = $r->fetch_assoc(); $mx = (int)($row['maxid'] ?? 0); }
              $next = $mx + 1;
              if ($next > 1) { $conn->query("ALTER TABLE `room` AUTO_INCREMENT = " . $next); }
            } else {
              $_SESSION['admin_err'] = 'Lỗi thêm phòng: ' . $err;
            }
          } else {
            $_SESSION['admin_msg'] = 'Thêm phòng mới thành công.';
            $inserted = true;
          }
          $stmt->close();
        } else {
          $_SESSION['admin_err'] = 'Lỗi chuẩn bị truy vấn thêm: ' . $conn->error;
          break;
        }
      } else {
        $sql = "INSERT INTO room (id, title, district_id, district_name, meta, area_m2, price_vnd, image, amenities, room_type, pet, `desc`, status)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
          $types = 'isissiisssssi';
          $stmt->bind_param($types,
            $id, $title, $district_id, $district_name, $meta, $area_m2, $price_vnd,
            $imagePath, $amenities, $room_type, $pet, $desc, $status
          );
          if (!$stmt->execute()) {
            $err = $stmt->error;
            if (($stmt->errno === 1062 || stripos($err, 'duplicate') !== false) && $attempt === 0) {
              $r = $conn->query("SELECT MAX(id) AS maxid FROM room");
              $mx = 0;
              if ($r) { $row = $r->fetch_assoc(); $mx = (int)($row['maxid'] ?? 0); }
              $next = $mx + 1;
              if ($next > 1) { $conn->query("ALTER TABLE `room` AUTO_INCREMENT = " . $next); }
            } else {
              $_SESSION['admin_err'] = 'Lỗi thêm phòng: ' . $err;
            }
          } else {
            $_SESSION['admin_msg'] = 'Thêm phòng mới thành công.';
            $inserted = true;
          }
          $stmt->close();
        } else {
          $_SESSION['admin_err'] = 'Lỗi chuẩn bị truy vấn thêm: ' . $conn->error;
          break;
        }
      }
      $attempt++;
    }
  }

  if (empty($district_name) && $district_id > 0) {
    $dstmt = $conn->prepare("SELECT district_name FROM room WHERE district_id = ? LIMIT 1");
    if ($dstmt) {
      $dstmt->bind_param('i', $district_id);
      $dstmt->execute();
      $dres = $dstmt->get_result();
      if ($drow = $dres->fetch_assoc()) { $district_name = $drow['district_name']; }
      $dstmt->close();
    }
  }

  if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
  header("Location: Admin.php?tab=tab-list");
  exit;
}
?>
<div id="tab-add" class="admin-tab active">
  <div class="section">
    <h2>Thêm / Cập nhật phòng trọ</h2>

    <form method="post" enctype="multipart/form-data" action="Admin.php?tab=tab-add">
      <input type="hidden" name="save_room" value="1">
      <input type="hidden" name="id" value="<?php echo $editRoom['id'] ?? 0; ?>">

      <div class="row">
        <div>
          <label>Tiêu đề</label>
          <input type="text" name="title" placeholder="Phòng trọ trung tâm Quận 1"
                 value="<?php echo htmlspecialchars($editRoom['title'] ?? ''); ?>">
        </div>

        <div>
          <label>Địa điểm (Quận)</label>
          <select name="district_id">
            <option value="0">-- Chọn quận --</option>
            <?php 
              $currentDid = isset($editRoom['district_id']) ? (int)$editRoom['district_id'] : 0;
              foreach ($districtList as $id => $name):
            ?>
              <option value="<?php echo $id; ?>" <?php echo $currentDid === $id ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label>Meta / vị trí hiển thị</label>
          <input type="text" name="meta" placeholder="Quận 1, TP.HCM"
                 value="<?php echo htmlspecialchars($editRoom['meta'] ?? ''); ?>">
        </div>

        <div>
          <label>Diện tích (m²)</label>
          <input type="number" name="area_m2" placeholder="28"
                 value="<?php echo isset($editRoom['area_m2']) ? (int)$editRoom['area_m2'] : ''; ?>">
        </div>

        <div>
          <label>Giá (VND / tháng)</label>
          <input type="number" name="price_vnd" placeholder="3000000"
                 value="<?php echo isset($editRoom['price_vnd']) ? (int)$editRoom['price_vnd'] : ''; ?>">
        </div>

        <div>
          <label>Ảnh (chọn từ máy tính) - Hỗ trợ nhiều ảnh</label>
          <input type="file" name="image_files[]" accept="image/*" multiple>
          <?php
            $curImages = [];
            if ($editRoom !== null && !empty($editRoom['images'])) {
              $tmp = json_decode($editRoom['images'], true);
              if (is_array($tmp)) $curImages = $tmp;
            }
            if (empty($curImages) && $editRoom !== null && !empty($editRoom['image'])) {
              $curImages = [ $editRoom['image'] ];
            }
          ?>
          <?php if (!empty($curImages)): ?>
            <div class="image-preview-row">
              <?php foreach ($curImages as $ci): ?>
                <div class="image-preview-item">
                  <img src="<?php echo htmlspecialchars($ci); ?>" class="thumb" alt="">
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <input type="hidden" name="old_images" value="<?php echo htmlspecialchars(json_encode($curImages)); ?>">
        </div>

        <?php
          $editAmenities = [];
          if ($editRoom !== null && !empty($editRoom['amenities'])) {
            $tmp = explode(',', $editRoom['amenities']);
            $tmp = array_map('trim', $tmp);
            $editAmenities = array_filter($tmp);
          }
          $editRoom_type = isset($editRoom['room_type']) ? $editRoom['room_type'] : '';
          $editPet = isset($editRoom['pet']) ? $editRoom['pet'] : '';
        ?>

        <div>
          <label>Tiện nghi</label>
          <div class="amenities-list">
            <label><input type="checkbox" name="amenities[]" value="Wifi" <?php echo in_array('Wifi', $editAmenities) ? 'checked' : ''; ?>> Wifi</label>
            <label><input type="checkbox" name="amenities[]" value="MayGiat" <?php echo in_array('MayGiat', $editAmenities) ? 'checked' : ''; ?>> Máy giặt</label>
            <label><input type="checkbox" name="amenities[]" value="DieuHoa" <?php echo in_array('DieuHoa', $editAmenities) ? 'checked' : ''; ?>> Điều hòa</label>
            <label><input type="checkbox" name="amenities[]" value="FullNoiThat" <?php echo in_array('FullNoiThat', $editAmenities) ? 'checked' : ''; ?>> Full nội thất</label>
            <label><input type="checkbox" name="amenities[]" value="NhaTrong" <?php echo in_array('NhaTrong', $editAmenities) ? 'checked' : ''; ?>> Nhà trống</label>
            <label><input type="checkbox" name="amenities[]" value="MatTien" <?php echo in_array('MatTien', $editAmenities) ? 'checked' : ''; ?>> Mặt tiền</label>
          </div>
        </div>

        <div>
          <label>Dạng phòng</label>
          <select name="room_type">
            <option value="" <?php echo $editRoom_type === '' ? 'selected' : ''; ?>>-- Không chọn --</option>
            <option value="Duplex" <?php echo $editRoom_type === 'Duplex' ? 'selected' : ''; ?>>Duplex</option>
            <option value="Studio" <?php echo $editRoom_type === 'Studio' ? 'selected' : ''; ?>>Studio</option>
            <option value="MatBang" <?php echo $editRoom_type === 'MatBang' ? 'selected' : ''; ?>>Mặt bằng</option>
            <option value="NhaNguyenCan" <?php echo $editRoom_type === 'NhaNguyenCan' ? 'selected' : ''; ?>>Nhà nguyên căn</option>
          </select>
        </div>

        <div>
          <label>Cho phép nuôi thú cưng</label>
          <select name="pet">
            <option value="" <?php echo $editPet === '' ? 'selected' : ''; ?>>-- Không chọn --</option>
            <option value="NuoiCho" <?php echo $editPet === 'NuoiCho' ? 'selected' : ''; ?>>Nuôi chó</option>
            <option value="NuoiMeo" <?php echo $editPet === 'NuoiMeo' ? 'selected' : ''; ?>>Nuôi mèo</option>
            <option value="NuoiMuonLoaiThu" <?php echo $editPet === 'NuoiMuonLoaiThu' ? 'selected' : ''; ?>>Nuôi mọi loài thú</option>
          </select>
        </div>

        <div>
          <label>Trạng thái phòng</label>
          <select name="status">
            <?php $st = isset($editRoom['status']) ? (int)$editRoom['status'] : 1; ?>
            <option value="1" <?php echo $st === 1 ? 'selected' : ''; ?>>Còn phòng</option>
            <option value="0" <?php echo $st === 0 ? 'selected' : ''; ?>>Hết phòng</option>
          </select>
        </div>

      </div>

      <label>Mô tả</label>
      <textarea name="desc" rows="3" placeholder="Phòng sạch, gần chợ..."><?php
        echo htmlspecialchars($editRoom['desc'] ?? '');
      ?></textarea>

      <div class="mt-8">
        <button type="submit" class="btn btn-primary">
          <?php echo isset($editRoom['id']) ? 'Cập nhật phòng' : 'Thêm phòng mới'; ?>
        </button>
        <?php if (isset($editRoom['id'])): ?>
          <button type="button" class="btn btn-secondary" onclick="window.location='Admin.php?tab=tab-add'">
            Hủy / Thêm mới
          </button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>
