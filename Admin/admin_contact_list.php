<?php
function ensureContactTableExists_admin($conn) {
  $sql = "CREATE TABLE IF NOT EXISTS `contact` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `fullname` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
  $conn->query($sql);
}

function getAllContacts_admin($conn) {
  ensureContactTableExists_admin($conn);
  $rows = [];
  $res = $conn->query("SELECT id, fullname, email, phone, message, created_at FROM contact ORDER BY created_at DESC");
  if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;
  return $rows;
}

$contacts = getAllContacts_admin($conn);
?>
<div id="tab-contacts" class="admin-tab active">
  <div class="section">
    <h2>Tin nhắn liên hệ</h2>
    <p>Danh sách các tin nhắn người dùng gửi về trang web.</p>
    <table>
      <thead>
      <tr>
        <th>#</th>
        <th>Họ Tên</th>
        <th>Email</th>
        <th>Số điện thoại</th>
        <th>Nội dung</th>
        <th>Thời gian</th>
      </tr>
      </thead>
      <tbody>
      <?php if (empty($contacts)): ?>
        <tr><td colspan="6">Chưa có tin nhắn nào.</td></tr>
      <?php else: ?>
        <?php foreach ($contacts as $c): ?>
          <tr>
            <td><?php echo (int)$c['id']; ?></td>
            <td><?php echo htmlspecialchars($c['fullname']); ?></td>
            <td><?php echo htmlspecialchars($c['email']); ?></td>
            <td><?php echo htmlspecialchars($c['phone']); ?></td>
            <td class="prewrap"><?php echo htmlspecialchars($c['message']); ?></td>
            <td><?php echo htmlspecialchars($c['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
