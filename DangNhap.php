<?php
session_start();
require 'db.php';

$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $err = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {

          $avatar = $user['avatar'] ?? '';
          if ($avatar === '') {

            $rdimgDir = __DIR__ . '/RDIMG';
            $avatars = [];
            if (is_dir($rdimgDir)) {
              $files = scandir($rdimgDir);
              foreach ($files as $f) {
                if ($f !== '.' && $f !== '..' && is_file($rdimgDir . '/' . $f)) {
                  $avatars[] = 'RDIMG/' . $f;
                }
              }
            }
            if (!empty($avatars)) {
              $avatar = $avatars[array_rand($avatars)];
            }
          }

          $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'fullname' => $user['fullname'],
            'role' => $user['role'],
            'avatar' => $avatar
          ];

          if (($user['role'] ?? '') === 'admin') {
            header("Location: ./Admin/Admin.php");
          } else {
            header("Location: TrangChu.php");
          }
          exit;

        } else {
            $err = "Sai tên đăng nhập hoặc mật khẩu.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập — WebPhong</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./CSS/TrangChu.css">
  <link rel="stylesheet" href="./CSS/NhapVaTao.css">
  <link rel="stylesheet" href="./CSS/Forms.css">
</head>
<body>

  <header class="header">
    <div class="container header-wrapper">
      <div class="logo-section">
        <img src="./IMG/logo.jpg" alt="Logo WebPhong" class="logo-img">
        <h1 class="logo-text">WebPhong</h1>
      </div>

      <nav class="nav-menu">
        <a href="TrangChu.php">Trang chủ</a>
        <a href="TrangChu.php#search-section">Tìm phòng</a>
        <a href="TrangChu.php#featured">Phòng nổi bật</a>
        <a href="TrangChu.php#contact">Liên hệ</a>
      </nav>

      <div class="auth-buttons">
        <button class="btn btn-secondary" onclick="location.href='./DangNhap.php'">Đăng nhập</button>
        <button class="btn btn-primary" onclick="location.href='./DangKy.php'">Đăng ký</button>
      </div>
    </div>
  </header>

  <main class="auth-container">
    <div class="auth-card">
      <h2>Đăng nhập</h2>
      <p class="auth-subtitle">Vào tài khoản của bạn</p>

      <?php if ($err != ""): ?>
        <p class="form-error"><?php echo htmlspecialchars($err); ?></p>
      <?php endif; ?>

      <form class="auth-form" action="" method="POST">
        
        <div class="form-group">
          <label for="username">Tên đăng nhập</label>
          <input type="text" id="username" name="username"
                 placeholder="Nhập tên đăng nhập"
                 required>
          <small class="help-text">Nhập tên đăng nhập bạn đã tạo khi đăng ký</small>
        </div>

        <div class="form-group">
          <label for="password">Mật khẩu</label>
          <input type="password" id="password" name="password"
                 placeholder="Nhập mật khẩu"
                 required>
          <small class="help-text">Tối thiểu 6 ký tự</small>
        </div>

        <div class="form-checkbox">
          <input type="checkbox" id="remember" name="remember" value="yes">
          <label for="remember">Nhớ tôi</label>
        </div>
        <button type="submit" class="btn btn-primary btn-large btn-block">Đăng nhập</button>

      </form>
      <div class="auth-links">
        <a href="QuenMK.html" class="link-forgot">Quên mật khẩu?</a>
        <span class="divider">|</span>
        <a href="DangKy.php" class="link-register">Đăng ký tài khoản</a>
      </div>
    </div>
  </main>

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
            <li><a href="TrangChu.php">Trang chủ</a></li>
            <li><a href="TrangChu.php#search-section">Tìm phòng</a></li>
            <li><a href="TrangChu.php#featured">Phòng nổi bật</a></li>
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

</body>
</html>
