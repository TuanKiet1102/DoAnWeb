<?php
session_start();
include_once(__DIR__ . '/../db.php');

if (isset($_GET['resultCode']) && $_GET['resultCode'] == '0') {
    $info = $_SESSION['info_khachhang'] ?? [];
    $hoten   = $info['hoten'] ?? 'Khách vãng lai';
    $sdt     = $info['sdt'] ?? '';
    $email   = $info['email'] ?? '';
    $diachi  = $info['diachi'] ?? '';
    $ghichu  = $info['ghichu'] ?? '';
    $room_id = $info['room_id'] ?? 0;

    $amount     = $_GET['amount'];
    $ngaydat    = date('Y-m-d H:i:s');
    $trang_thai = 'Đã thanh toán MoMo';

    // Lưu vào bảng contact
    $sql = "INSERT INTO contact (fullname, email, phone, message, created_at, is_read) 
            VALUES ('$hoten', '$email', '$sdt', 'Đặt phòng mã: $room_id - Thanh toán MoMo - Số tiền: $amount - Ghi chú: $ghichu', '$ngaydat', 0)";
    
    if (mysqli_query($conn, $sql)) {
        $id_contact = mysqli_insert_id($conn);
        unset($_SESSION['info_khachhang']);

        echo '<div class="alert alert-success text-center m-5">
                <h2><i class="fa-solid fa-check-circle"></i> Thanh toán thành công!</h2>
                <p>Mã giao dịch: <b>#'.$id_contact.'</b></p>
                <p>Trạng thái: '.$trang_thai.'</p>
                <a href="../TrangChu.php" class="btn btn-primary">Về trang chủ</a>
              </div>';
    } else {
        echo "Lỗi SQL: " . mysqli_error($conn);
    }
} else {
    echo '<div class="alert alert-danger text-center m-5">
            <h2>Giao dịch thất bại!</h2>
            <a href="../TrangChu.php" class="btn btn-warning">Quay lại</a>
          </div>';
}
?>
