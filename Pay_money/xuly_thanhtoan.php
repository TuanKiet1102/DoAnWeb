<?php
session_start();
include_once(__DIR__ . '/../db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoten    = $_POST['hoten'];
    $sdt      = $_POST['sdt'];
    $email    = $_POST['email'];
    $diachi   = $_POST['diachi'];
    $ghichu   = $_POST['ghichu'];
    $tongtien = $_POST['tongtien'];
    $method   = $_POST['method'];
    $room_id  = $_POST['room_id'];
    $ngaydat  = date('Y-m-d H:i:s');

    if ($method == 'cod') {
        $sql = "INSERT INTO contact (fullname, email, phone, message, created_at, is_read) 
                VALUES ('$hoten', '$email', '$sdt', 'Đặt phòng mã: $room_id - COD - Tổng tiền: $tongtien - Ghi chú: $ghichu', '$ngaydat', 0)";
        if (mysqli_query($conn, $sql)) {
            $id_contact = mysqli_insert_id($conn);
            echo "<script>alert('Đặt phòng thành công! Mã giao dịch: #$id_contact'); window.location='../TrangChu.php';</script>";
        } else {
            echo "Lỗi SQL: " . mysqli_error($conn);
        }
    }

    elseif ($method == 'momo') {
        $_SESSION['info_khachhang'] = [
            'hoten'   => $hoten,
            'sdt'     => $sdt,
            'email'   => $email,
            'diachi'  => $diachi,
            'ghichu'  => $ghichu,
            'room_id' => $room_id
        ];

        $endpoint    = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = "MOMOONKK20251204_TEST"; 
        $accessKey   = "zdcPCXVDGnlK27re";     
        $secretKey   = "fehp2yADBY332N0PjZWf4H1UuVLO9qCJ";    

        $orderInfo   = "Thanh toán phòng #$room_id";
        $amount      = (string)intval($tongtien);
        $orderId     = time().""; 
        $requestId   = time()."";
        $extraData   = "";
        
        $redirectUrl = "http://localhost/DOANWEB/Pay_money/ketqua_momo.php";
        $ipnUrl      = "http://localhost/DOANWEB/Pay_money/ketqua_momo.php"; 
        $requestType = "captureWallet";

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
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
            'requestType' => $requestType,
            'signature'   => $signature
        ];
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($ch);
        curl_close($ch);

        $jsonResult = json_decode($result, true);
        
        if (isset($jsonResult['payUrl'])) {
            header("Location: " . $jsonResult['payUrl']);
            exit();
        } else {
            echo "Lỗi kết nối MoMo: " . ($jsonResult['message'] ?? 'Không rõ nguyên nhân');
        }
    }
}
?>
