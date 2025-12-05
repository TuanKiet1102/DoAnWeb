<?php
require 'db.php'; // file kết nối CSDL

$err = "";
$msg = "";

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $err = "Vui lòng nhập đầy đủ Tên đăng nhập, Email và Mật khẩu.";
    } elseif (strlen($password) < 6) {
        $err = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        // Kiểm tra username hoặc email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $err = "Tên đăng nhập hoặc Email đã được sử dụng. Vui lòng chọn tên khác.";
        } else {
            // Thêm tài khoản mới
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $fullname  = "";     // nếu sau này có field họ tên thì bổ sung

            $stmt = $conn->prepare(
                "INSERT INTO users(username, password, fullname, email)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $username, $pass_hash, $fullname, $email);

            if ($stmt->execute()) {
                $msg = "Đăng ký thành công! Bạn có thể đăng nhập bằng tên đăng nhập vừa tạo.";
            } else {
                $err = "Không thể đăng ký: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng ký — WebPhong</title>
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
      <h2>Đăng ký</h2>
      <p class="auth-subtitle">Tạo tài khoản của bạn</p>

      <?php if ($err != ""): ?>
        <p class="form-error"><?php echo htmlspecialchars($err); ?></p>
      <?php endif; ?>
      <?php if ($msg != ""): ?>
        <p class="form-success"><?php echo htmlspecialchars($msg); ?></p>
      <?php endif; ?>

      <form class="auth-form" action="" method="POST">

        <div class="form-group">
          <label for="username">Tên đăng nhập</label>
          <input type="text" id="username" name="username" placeholder="Tên đăng nhập (ví dụ: kiet123)" required>
          <small class="help-text">Tên đăng nhập dùng để đăng nhập (không chứa khoảng trắng)</small>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Nhập email của bạn" required>
          <small class="help-text">Chúng tôi sẽ không bao giờ chia sẻ email của bạn</small>
        </div>

        <div class="form-group">
          <label for="password">Mật khẩu</label>
          <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
          <small class="help-text">Tối thiểu 6 ký tự</small>
        </div>

        <div class="form-checkbox">
          <input type="checkbox" id="remember" name="remember" value="yes">
          <label for="remember">Nhớ tôi</label>
        </div>

        <button type="submit" class="btn btn-primary btn-large btn-block">Đăng ký</button>

      </form>

      <div class="auth-links">
        <a href="DangNhap.php" class="link-login">Đã có tài khoản?</a>
        <span class="divider">|</span>
        <a href="QuenMK.html" class="link-back">Quên mật khẩu?</a>
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
