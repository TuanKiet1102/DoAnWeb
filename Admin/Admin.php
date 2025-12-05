<?php
session_start();
require './../db.php';

if (!isset($_SESSION['user']) || (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] !== 'admin')) {
  header("Location: DangNhap.php");
  exit;
}

$admin_msg = $_SESSION['admin_msg'] ?? '';
$admin_err = $_SESSION['admin_err'] ?? '';
unset($_SESSION['admin_msg'], $_SESSION['admin_err']);

$allowedTabs = ['tab-add','tab-list','tab-users','tab-contacts'];
$initialTab = 'tab-contacts';
if (isset($_GET['tab']) && in_array($_GET['tab'], $allowedTabs, true)) {
  $initialTab = $_GET['tab'];
} else if (isset($_GET['edit_room'])) {
  $initialTab = 'tab-add';
} else if (isset($_GET['edit_user'])) {
  $initialTab = 'tab-users';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Trang quản trị - WebPhong</title>
  <link rel="stylesheet" href="./../CSS/Admin.css">
  <style>
    .admin-tabs { display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap; }
    .tab-button { padding:8px 12px; background:#f3f4f6; border-radius:6px; cursor:pointer; border:1px solid #e5e7eb; font-size:14px; text-decoration:none; color:#111827; }
    .tab-button.active { background:#2563eb; color:#fff; border-color:#2563eb; }
    .flash-error { background:#fee2e2; color:#b91c1c; padding:8px; margin-bottom:10px; border-radius:6px; }
    .flash-success { background:#d1fae5; color:#065f46; padding:8px; margin-bottom:10px; border-radius:6px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
    th { background: #f9fafb; }
    .thumb { width: 72px; height: 48px; object-fit: cover; border-radius:4px; border:1px solid #eee; }
    .prewrap { white-space: pre-wrap; }
    .stack-col { display:flex; flex-direction:column; gap:6px; }
    .amenities-list label { margin-right:12px; display:inline-block; }
    .input-row { display:flex; gap:8px; align-items:center; }
    .input-flex { flex: 1; }
    .mt-8 { margin-top: 8px; }
    .status-badge { padding:2px 6px; border-radius:12px; font-size:12px; }
    .status-con { background:#dcfce7; color:#14532d; }
    .status-het { background:#fee2e2; color:#7f1d1d; }
    .search-form { display:flex; gap:8px; align-items:center; margin-bottom:12px; }
    .search-input { flex: 1; }
    .search-input--small { width: 150px; }
    .btn { padding:6px 10px; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; background:#f9fafb; text-decoration:none; display:inline-block; }
    .btn-primary { background:#2563eb; border-color:#2563eb; color:#fff; }
    .btn-secondary { background:#f3f4f6; }
    .btn-danger { background:#ef4444; border-color:#ef4444; color:#fff; }
    header .title { font-weight:600; font-size:18px; }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 16px; }
  </style>
</head>
<body>

<header>
  <div class="title">Trang quản trị WebPhong</div>
  <div>
    <?php if (isset($_SESSION['user'])): ?>
      <span class="admin-username"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
    <?php endif; ?>
    <button class="btn btn-secondary" onclick="location.href='./../TrangChu.php'">Về trang chủ</button>
  </div>
</header>

<div class="wrap">
  <?php if ($admin_err): ?><div class="flash-error"><?php echo htmlspecialchars($admin_err); ?></div><?php endif; ?>
  <?php if ($admin_msg): ?><div class="flash-success"><?php echo htmlspecialchars($admin_msg); ?></div><?php endif; ?>

  <div class="admin-tabs" role="tablist">
    <a class="tab-button <?php echo $initialTab === 'tab-add' ? 'active' : ''; ?>" href="Admin.php?tab=tab-add">Thêm phòng trọ</a>
    <a class="tab-button <?php echo $initialTab === 'tab-list' ? 'active' : ''; ?>" href="Admin.php?tab=tab-list">Danh sách phòng trọ</a>
    <a class="tab-button <?php echo $initialTab === 'tab-users' ? 'active' : ''; ?>" href="Admin.php?tab=tab-users">Danh sách tài khoản</a>
    <a class="tab-button <?php echo $initialTab === 'tab-contacts' ? 'active' : ''; ?>" href="Admin.php?tab=tab-contacts">Tin nhắn liên hệ</a>
  </div>

  <div class="admin-tab">
    <?php
      if ($initialTab === 'tab-add') {
        include 'admin_room_add.php';
      } elseif ($initialTab === 'tab-list') {
        include 'admin_room_list.php';
      } elseif ($initialTab === 'tab-users') {
        include 'admin_user_list.php';
      } elseif ($initialTab === 'tab-contacts') {
        include 'admin_contact_list.php';
      }
    ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var full = document.querySelector('input[name="amenities[]"][value="FullNoiThat"]');
  var wifi = document.querySelector('input[name="amenities[]"][value="Wifi"]');
  var may = document.querySelector('input[name="amenities[]"][value="MayGiat"]');
  var dieu = document.querySelector('input[name="amenities[]"][value="DieuHoa"]');
  function checkFull(state){
    if(!wifi || !may || !dieu) return;
    if(state){ wifi.checked = true; may.checked = true; dieu.checked = true; }
  }
  if(full){
    full.addEventListener('change', function(){ checkFull(this.checked); });
    if(full.checked) checkFull(true);
  }
  [wifi, may, dieu].forEach(function(cb){
    if(!cb) return;
    cb.addEventListener('change', function(){
      if(!this.checked && full && full.checked){ full.checked = false; }
    });
  });
});
</script>

</body>
</html>
