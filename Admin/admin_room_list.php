<?php
function formatPrice_admin_list($p) { return $p ? number_format($p, 0, ',', '.') : ''; }

function getAllRooms_admin_list($conn) {
  $rooms = [];
  $sql = "SELECT * FROM room ORDER BY id ASC";
  $res = $conn->query($sql);
  if ($res) while ($row = $res->fetch_assoc()) $rooms[] = $row;
  return $rooms;
}

function getAllRoomsSearch_admin_list($conn, $search, $priceSearchMillion = null) {
  $rooms = [];
  if (($search === null || trim($search) === '') && $priceSearchMillion === null) {
    return getAllRooms_admin_list($conn);
  }
  $where = "WHERE 1=1";
  $types = '';
  $params = [];

  if ($search !== null && trim($search) !== '') {
    $s_raw = trim($search);
    $s_ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $s_raw);
    if ($s_ascii === false) $s_ascii = $s_raw;
    $s_ascii = preg_replace('/\s+/', ' ', trim($s_ascii));
    if (preg_match('/^quan\s*(\d+)$/i', $s_ascii, $m)) {
      $num = intval($m[1]);
      if ($num >= 1 && $num <= 12) {
        $districtName = 'Quận ' . $num;
        $where .= " AND district_name = ?";
        $types .= 's';
        $params[] = $districtName;
      } else {
        $s = '%' . $conn->real_escape_string($s_raw) . '%';
        $where .= " AND (title LIKE ? OR district_name LIKE ? OR id LIKE ?)";
        $types .= 'sss';
        $params[] = $s; $params[] = $s; $params[] = $s;
      }
    } else {
      $s = '%' . $conn->real_escape_string($s_raw) . '%';
      $where .= " AND (title LIKE ? OR district_name LIKE ? OR id LIKE ?)";
      $types .= 'sss';
      $params[] = $s; $params[] = $s; $params[] = $s;
    }
  }

  if ($priceSearchMillion !== null && $priceSearchMillion > 0) {
    $priceMin = intval($priceSearchMillion) * 1000000;
    $priceMax = intval($priceSearchMillion) * 1000000 + 999999;
    $where .= " AND price_vnd >= ? AND price_vnd <= ?";
    $types .= 'ii';
    $params[] = $priceMin; $params[] = $priceMax;
  }

  $sql = "SELECT * FROM room " . $where . " ORDER BY id ASC";
  if ($stmt = $conn->prepare($sql)) {
    if ($types !== '') {
      $bind_names = array($types);
      for ($i = 0; $i < count($params); $i++) $bind_names[] = &$params[$i];
      call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) while ($r = $res->fetch_assoc()) $rooms[] = $r;
    $stmt->close();
  } else {
    $q = $conn->query("SELECT * FROM room " . $where . " ORDER BY id ASC");
    if ($q) while ($r = $q->fetch_assoc()) $rooms[] = $r;
  }
  return $rooms;
}

if (isset($_GET['delete_room'])) {
  $delId = (int)$_GET['delete_room'];
  if ($delId > 0) {
    $stmt = $conn->prepare("SELECT image FROM room WHERE id = ?");
    $stmt->bind_param("i", $delId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
      $imgPath = $row['image'] ?? '';
      if ($imgPath && (strpos($imgPath, 'IMG/') !== false || strpos($imgPath, 'IMG\\') !== false)) {
        if (file_exists($imgPath)) {
          @unlink($imgPath);
        } else {
          $try = __DIR__ . DIRECTORY_SEPARATOR . ltrim($imgPath, './\\');
          if (file_exists($try)) @unlink($try);
        }
      }
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM room WHERE id = ?");
    $stmt->bind_param("i", $delId);
    $stmt->execute();
    $stmt->close();

    $res = $conn->query("SELECT id FROM room ORDER BY id ASC");
    if ($res && $res->num_rows > 0) {
      $newId = 1;
      while ($row = $res->fetch_assoc()) {
        $oldId = (int)$row['id'];
        if ($oldId !== $newId) {
          $conn->query("UPDATE room SET id = " . $newId . " WHERE id = " . $oldId);
        }
        $newId++;
      }
    }
    $_SESSION['admin_msg'] = 'Đã xóa phòng.';
  }
  if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
  header("Location: Admin.php?tab=tab-list");
  exit;
}

$priceSearchMillion = isset($_GET['price_search']) && $_GET['price_search'] !== '' ? floatval($_GET['price_search']) : null;
$rooms = getAllRoomsSearch_admin_list($conn, isset($_GET['room_search']) ? trim($_GET['room_search']) : '', $priceSearchMillion);
?>
<div id="tab-list" class="admin-tab active">
  <div class="section">
    <h2>Danh sách phòng trọ</h2>

    <form method="GET" class="search-form">
      <input type="hidden" name="tab" value="tab-list">
      <input type="search" name="room_search" placeholder="Tìm theo ID, tiêu đề, quận..." value="<?php echo isset($_GET['room_search']) ? htmlspecialchars($_GET['room_search']) : ''; ?>" class="search-input">
      <input type="number" name="price_search" placeholder="Giá (triệu VND)" value="<?php echo isset($_GET['price_search']) ? htmlspecialchars($_GET['price_search']) : ''; ?>" min="0" step="0.1" class="search-input--small">
      <button type="submit" class="btn btn-primary">Tìm</button>
      <a href="Admin.php?tab=tab-list" class="btn btn-secondary">Xóa</a>
    </form>

    <table>
      <thead>
      <tr>
        <th>ID</th>
        <th>Tiêu đề</th>
        <th>Quận</th>
        <th>Giá</th>
        <th>Diện tích</th>
        <th>Dạng phòng</th>
        <th>Pet</th>
        <th>Ảnh</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
      </tr>
      </thead>
      <tbody>
      <?php if (empty($rooms)): ?>
        <tr><td colspan="10">Chưa có phòng nào.</td></tr>
      <?php else: ?>
        <?php foreach ($rooms as $r): ?>
          <tr>
            <td><?php echo (int)$r['id']; ?></td>
            <td><?php echo htmlspecialchars($r['title']); ?></td>
            <td><?php echo htmlspecialchars($r['district_name']); ?></td>
            <td><?php echo formatPrice_admin_list($r['price_vnd']); ?> đ/tháng</td>
            <td><?php echo (int)$r['area_m2']; ?> m²</td>
            <td><?php echo htmlspecialchars($r['room_type'] ?? ''); ?></td>
            <td>
              <?php
                $petText = '-';
                if (!empty($r['pet'])) {
                  if ($r['pet'] === 'NuoiCho') $petText = 'Nuôi chó';
                  elseif ($r['pet'] === 'NuoiMeo') $petText = 'Nuôi mèo';
                  elseif ($r['pet'] === 'NuoiMuonLoaiThu') $petText = 'Nuôi mọi loài thú';
                  else $petText = htmlspecialchars($r['pet']);
                }
                echo $petText;
              ?>
            </td>
            <td>
              <?php
                $thumb = '';
                if (!empty($r['images'])) {
                  $tmp = json_decode($r['images'], true);
                  if (is_array($tmp) && count($tmp)>0) $thumb = $tmp[0];
                }
                if ($thumb === '' && !empty($r['image'])) $thumb = $r['image'];
              ?>
             <?php
              if ($thumb !== '') {
                $thumbPath = '../' . ltrim($thumb, './\\');
                echo '<img src="' . htmlspecialchars($thumbPath) . '" class="thumb" alt="">';
              }
            ?>
            </td>
            <td>
              <?php if ((int)$r['status'] === 1): ?>
                <span class="status-badge status-con">Còn phòng</span>
              <?php else: ?>
                <span class="status-badge status-het">Hết phòng</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="stack-col">
                <a class="btn btn-primary" href="Admin.php?edit_room=<?php echo (int)$r['id']; ?>&tab=tab-add">Sửa</a>
                <a class="btn btn-danger" href="Admin.php?delete_room=<?php echo (int)$r['id']; ?>&tab=tab-list" onclick="return confirm('Xóa phòng này?');">Xóa</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
