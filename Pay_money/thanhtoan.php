<?php
session_start();
include('connect.php');
include('includes/header.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$room = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM room WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room   = $result->fetch_assoc();
    $stmt->close();
}
?>

<div class="container my-5">
    <h2 class="text-center mb-4">Xác nhận đặt phòng</h2>
    
    <?php if (!$room): ?>
        <p>Không tìm thấy phòng.</p>
    <?php else: ?>
    <form action="xuly_thanhtoan.php" method="POST">
        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
        <input type="hidden" name="tongtien" value="<?php echo $room['price_vnd']; ?>">

        <div class="row">
            <div class="col-md-7">
                <div class="card p-3 shadow-sm">
                    <h4>Thông tin khách hàng</h4>
                    <div class="mb-3">
                        <label>Họ tên:</label>
                        <input type="text" name="hoten" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Số điện thoại:</label>
                        <input type="text" name="sdt" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email:</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Địa chỉ:</label>
                        <textarea name="diachi" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Ghi chú:</label>
                        <textarea name="ghichu" class="form-control"></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card p-3 shadow-sm bg-light">
                    <h4>Phòng bạn chọn</h4>
                    <p><strong><?php echo $room['title']; ?></strong></p>
                    <p>Giá: <?php echo number_format($room['price_vnd']); ?> đ/tháng</p>
                    
                    <div class="mt-4">
                        <button type="submit" name="method" value="cod" class="btn btn-primary w-100 py-2 mb-2">
                            Thanh toán COD
                        </button>
                        <button type="submit" name="method" value="momo" class="btn btn-danger w-100 py-2">
                            Thanh toán MoMo QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php include('includes/footer.php'); ?>