<?php

function getAllUsers_admin($conn) {
  $users = [];
  $sql = "SELECT id, username, fullname, email, role FROM users ORDER BY id ASC";
  $res = $conn->query($sql);
  if ($res) while ($row = $res->fetch_assoc()) $users[] = $row;
  return $users;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
  $username = trim($_POST['username'] ?? '');
  $fullname = trim($_POST['fullname'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role     = $_POST['role'] ?? 'user';

  if ($username !== '' && $email !== '' && $password !== '') {
    $pwHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, fullname, email, password, role) VALUES (?,?,?,?,?)");
    if ($stmt) {
      $stmt->bind_param('sssss', $username, $fullname, $email, $pwHash, $role);
      if ($stmt->execute()) {
        $_SESSION['admin_msg'] = 'Thêm tài khoản ' . htmlspecialchars($role) . ' thành công.';
      } else {
        $_SESSION['admin_err'] = 'Lỗi thêm tài khoản: ' . $stmt->error;
      }
      $stmt->close();
    } else {
      $_SESSION['admin_err'] = 'Lỗi chuẩn bị truy vấn thêm tài khoản: ' . $conn->error;
    }
  } else {
    $_SESSION['admin_err'] = 'Vui lòng nhập đủ username, email, mật khẩu.';
  }
  if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
  header('Location: Admin.php?tab=tab-users');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
  $uid = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
  $newUsername = trim($_POST['username'] ?? '');
  if ($uid > 0 && $newUsername !== '') {
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    if ($stmt) {
      $stmt->bind_param('si', $newUsername, $uid);
      if ($stmt->execute()) {
        $_SESSION['admin_msg'] = 'Cập nhật username thành công.';
      } else {
        $_SESSION['admin_err'] = 'Lỗi cập nhật username: ' . $stmt->error;
      }
      $stmt->close();
    } else {
      $_SESSION['admin_err'] = 'Lỗi chuẩn bị truy vấn cập nhật username: ' . $conn->error;
    }
  } else {
    $_SESSION['admin_err'] = 'Username hoặc ID không hợp lệ.';
  }
  if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
  header('Location: Admin.php?tab=tab-users');
  exit;
}

$users = getAllUsers_admin($conn);

$editUser = null;
if (isset($_GET['edit_user'])) {
  $euid = (int)$_GET['edit_user'];
  if ($euid > 0) {
    $ust = $conn->prepare("SELECT id, username, fullname, email, role FROM users WHERE id = ? LIMIT 1");
    if ($ust) {
      $ust->bind_param('i', $euid);
      $ust->execute();
      $ures = $ust->get_result();
      $editUser = $ures->fetch_assoc();
      $ust->close();
    }
  }
}
?>
<div id="tab-users" class="admin-tab active">
  <div class="section">
    <h2>Danh sách tài khoản</h2>

    <?php if ($editUser !== null): ?>
      <form method="POST" class="edit-user-form" action="Admin.php?tab=tab-users">
        <input type="hidden" name="update_user" value="1">
        <input type="hidden" name="user_id" value="<?php echo (int)$editUser['id']; ?>">
        <label>Chỉnh username cho ID <?php echo (int)$editUser['id']; ?></label>
        <div class="input-row">
          <input type="text" name="username" value="<?php echo htmlspecialchars($editUser['username']); ?>" class="input-flex">
          <button type="submit" class="btn btn-primary">Lưu</button>
          <a href="Admin.php?tab=tab-users" class="btn btn-secondary">Hủy</a>
        </div>
      </form>
    <?php endif; ?>

    <table>
      <thead>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Họ tên</th>
        <th>Email</th>
        <th>Role</th>
        <th>Hành động</th>
      </tr>
      </thead>
      <tbody>
      <?php if (empty($users)): ?>
        <tr><td colspan="6">Chưa có tài khoản nào.</td></tr>
      <?php else: ?>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?php echo (int)$u['id']; ?></td>
            <td><?php echo htmlspecialchars($u['username']); ?></td>
            <td><?php echo htmlspecialchars($u['fullname']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo htmlspecialchars($u['role'] ?? ''); ?></td>
            <td>
              <a class="btn btn-primary" href="Admin.php?edit_user=<?php echo (int)$u['id']; ?>&tab=tab-users">Sửa</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>

    <hr style="margin:16px 0;">

    <h3>Thêm tài khoản admin</h3>
    <form method="POST" action="Admin.php?tab=tab-users">
      <input type="hidden" name="add_user" value="1">
      <div class="input-row">
        <label>Username</label>
        <input type="text" name="username" class="input-flex" required>
      </div>
      <div class="input-row">
        <label>Họ tên</label>
        <input type="text" name="fullname" class="input-flex">
      </div>
      <div class="input-row">
        <label>Email</label>
        <input type="email" name="email" class="input-flex" required>
      </div>
      <div class="input-row">
        <label>Mật khẩu</label>
        <input type="password" name="password" class="input-flex" required>
      </div>
      <input type="hidden" name="role" value="admin">
      <div class="mt-8">
        <button type="submit" class="btn btn-primary">Thêm admin</button>
      </div>
    </form>
  </div>
</div>
