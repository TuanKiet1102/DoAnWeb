<?php
require 'db.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST["fullname"] ?? $_POST['name'] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $phone    = trim($_POST["phone"] ?? "");
    $message  = trim($_POST["message"] ?? "");

    if ($fullname === '' || $email === '' || $phone === '' || $message === '') {
        $_SESSION['contact_err'] = 'Vui lòng nhập đầy đủ Họ Tên, Email, Số điện thoại và Nội dung.';
        header('Location: TrangChu.php#contact');
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_err'] = 'Email không hợp lệ.';
        header('Location: TrangChu.php#contact');
        exit();
    }
    $phoneClean = preg_replace('/[\s\-\.\(\)\+]/', '', $phone);
    if (!preg_match('/^[0-9]{7,15}$/', $phoneClean)) {
        $_SESSION['contact_err'] = 'Số điện thoại không hợp lệ. Vui lòng nhập 7-15 chữ số.';
        header('Location: TrangChu.php#contact');
        exit();
    }
    $createSql = "CREATE TABLE IF NOT EXISTS `contact` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `fullname` VARCHAR(255) NOT NULL,
      `email` VARCHAR(255) NOT NULL,
      `phone` VARCHAR(50) NOT NULL,
      `message` TEXT NOT NULL,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->query($createSql);
    $stmt = $conn->prepare("INSERT INTO contact (fullname, email, phone, message) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['contact_err'] = 'Lỗi hệ thống. Vui lòng thử lại sau.';
        header('Location: TrangChu.php#contact');
        exit();
    }
    $stmt->bind_param("ssss", $fullname, $email, $phone, $message);

    if ($stmt->execute()) {
        $_SESSION['contact_msg'] = 'Tin nhắn đã được gửi thành công! Cảm ơn bạn.';
        header('Location: TrangChu.php#contact');
        exit();
    } else {
        $_SESSION['contact_err'] = 'Gửi thất bại! Vui lòng thử lại.';
        header('Location: TrangChu.php#contact');
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
