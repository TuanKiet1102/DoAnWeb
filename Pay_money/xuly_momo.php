<?php
session_start();
include('connect.php');

// 1. Cấu hình MoMo Sandbox
$partnerCode = "MOMO..."; 
$accessKey   = "F8BGR...";  
$secretKey   = "uqO6Q...";  

// 2. Lấy thông tin đơn hàng
$amount   = 0;
$room_id  = $_POST['room_id'] ?? 0;   // lấy id phòng từ form
$hoten    = $_POST['hoten'] ?? '';
$sdt      = $_POST['sdt'] ?? '';
$email    = $_POST['email'] ?? '';
$diachi   = $_POST['diachi'] ?? '';
$ghichu   = $_POST['ghichu'] ?? '';

if (isset($_POST['tongtien'])) {
    $amount = $_POST['tongtien'];
} elseif (isset($_SESSION['giohang'])) {
    $id_array  = array_keys($_SESSION['giohang']);
    $id_string = implode(',', $id_array);

    $sql   = "SELECT id, price_vnd FROM room WHERE id IN ($id_string)";
    $query = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($query)) {
        $qty     = $_SESSION['giohang'][$row['id']];
        $amount += $row['price_vnd'] * $qty;
    }
} else {
    die("Không có thông tin đơn hàng");
}

// Đảm bảo tiền là số nguyên
$amount = (string)intval($amount);

// Lưu thông tin khách hàng vào session để ketqua_momo.php dùng
$_SESSION['info_khachhang'] = [
    'hoten'   => $hoten,
    'sdt'     => $sdt,
    'email'   => $email,
    'diachi'  => $diachi,
    'ghichu'  => $ghichu,
    'room_id' => $room_id
];

// 3. Cấu hình API
$orderId    = time() . ""; 
$requestId  = time() . "";
$orderInfo  = "Thanh toán phòng #$room_id qua MoMo";
$redirectUrl = "http://localhost/webphong/ketqua_momo.php";
$ipnUrl      = "http://localhost/webphong/ipn_momo.php";
$extraData   = "";

// 4. Tạo chữ ký
$rawHash = "accessKey=" . $accessKey .
           "&amount=" . $amount .
           "&extraData=" . $extraData .
           "&ipnUrl=" . $ipnUrl .
           "&orderId=" . $orderId .
           "&orderInfo=" . $orderInfo .
           "&partnerCode=" . $partnerCode .
           "&redirectUrl=" . $redirectUrl .
           "&requestId=" . $requestId .
           "&requestType=captureWallet";

$signature = hash_hmac("sha256", $rawHash, $secretKey);

// 5. Tạo dữ liệu gửi đi
$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => "Test MoMo",
    'storeId'     => "MomoTestStore",
    'requestId'   => $requestId,
    'amount'      => $amount,
    'orderId'     => $orderId,
    'orderInfo'   => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl'      => $ipnUrl,
    'lang'        => 'vi',
    'extraData'   => $extraData,
    'requestType' => 'captureWallet',
    'signature'   => $signature
);

// 6. Gửi yêu cầu sang MoMo
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($data))
));
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$result = curl_exec($ch);
curl_close($ch);

// 7. Xử lý kết quả trả về
if ($result) {
    $jsonResult = json_decode($result, true);
    if (isset($jsonResult['payUrl'])) {
        header("Location: " . $jsonResult['payUrl']);
        exit();
    } else {
        echo "Lỗi kết nối MoMo: " . ($jsonResult['message'] ?? 'Không rõ nguyên nhân');
    }
} else {
    echo "Không thể gửi yêu cầu đến MoMo.";
}
?>
