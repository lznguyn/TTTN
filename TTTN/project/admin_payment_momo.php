<?php
include 'config.php';
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// TODO: check admin
// if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

function get_upload_dir(): string {
  $candidates = [__DIR__ . '/uploaded_img/', __DIR__ . '/../uploaded_img/'];
  foreach ($candidates as $d) if (is_dir($d)) return $d;
  // nếu chưa có folder, tạo tại root hiện tại
  mkdir($candidates[0], 0755, true);
  return $candidates[0];
}
function get_upload_url_prefix(): string {
  // nếu có ../uploaded_img thì ưu tiên (trường hợp file trong /admin)
  if (is_dir(__DIR__ . '/../uploaded_img/')) return '../uploaded_img/';
  return 'uploaded_img/';
}

$msg = null;
$key = 'MOMO';

// Load current setting
$stmt = $conn->prepare("SELECT * FROM payment_settings WHERE payment_key=? LIMIT 1");
$stmt->bind_param("s", $key);
$stmt->execute();
$momo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$momo) {
  $stmt = $conn->prepare("INSERT INTO payment_settings(payment_key,is_enabled,bank_name) VALUES(?,1,'MoMo')");
  $stmt->bind_param("s", $key);
  $stmt->execute();
  $stmt->close();
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $is_enabled = isset($_POST['is_enabled']) ? 1 : 0;

  // mapping theo schema hiện có
  $momo_phone = trim($_POST['momo_phone'] ?? '');  // -> bank_account
  $momo_owner = trim($_POST['momo_owner'] ?? '');  // -> bank_owner
  $note_text  = trim($_POST['note_text'] ?? '');

  // upload QR
  $qr_image = $momo['qr_image'] ?? null;
  if (!empty($_FILES['qr_image']['name']) && ($_FILES['qr_image']['error'] ?? 1) === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION));
    $allow = ['png','jpg','jpeg','webp'];
    if (!in_array($ext, $allow, true)) {
      $msg = "File QR chỉ cho phép: png/jpg/jpeg/webp";
    } else {
      $newName = 'qr-momo-' . time() . '.' . $ext;
      $targetDir = get_upload_dir();
      $target = $targetDir . $newName;

      if (!move_uploaded_file($_FILES['qr_image']['tmp_name'], $target)) {
        $msg = "Upload QR thất bại!";
      } else {
        $qr_image = $newName;
      }
    }
  }

  if ($msg === null) {
    $bank_name = 'MoMo';

    $stmt = $conn->prepare("
      UPDATE payment_settings
      SET is_enabled=?, bank_name=?, bank_account=?, bank_owner=?, qr_image=?, note_text=?
      WHERE payment_key=?
    ");
    $stmt->bind_param("issssss", $is_enabled, $bank_name, $momo_phone, $momo_owner, $qr_image, $note_text, $key);
    $stmt->execute();
    $stmt->close();

    header("Location: ".$_SERVER['PHP_SELF']."?saved=1");
    exit;
  }
}

// reload
$stmt = $conn->prepare("SELECT * FROM payment_settings WHERE payment_key=? LIMIT 1");
$stmt->bind_param("s", $key);
$stmt->execute();
$momo = $stmt->get_result()->fetch_assoc();
$stmt->close();

$imgPrefix = get_upload_url_prefix();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin - Cấu hình QR MoMo</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="css/admin_style.css">
</head>
<body class="bg-gray-50">
<?php include 'admin_header.php'; ?>

<div class="max-w-3xl mx-auto p-6">
  <div class="bg-white rounded-2xl shadow p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Cấu hình QR MoMo</h1>
    <p class="text-sm text-gray-600 mb-6">Các thay đổi sẽ hiển thị ngay tại trang checkout.</p>

    <?php if (isset($_GET['saved'])): ?>
      <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700">
        Lưu thành công.
      </div>
    <?php endif; ?>

    <?php if ($msg): ?>
      <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700">
        <?php echo htmlspecialchars($msg); ?>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-5">
      <label class="flex items-center gap-3">
        <input type="checkbox" name="is_enabled" class="w-5 h-5"
          <?php echo ((int)($momo['is_enabled'] ?? 0) === 1 ? 'checked' : ''); ?> />
        <span class="font-semibold text-gray-800">Bật hiển thị QR MoMo ở checkout</span>
      </label>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">SĐT MoMo</label>
          <input name="momo_phone" value="<?php echo htmlspecialchars($momo['bank_account'] ?? '') ?>"
            class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="09xxxxxxxx" />
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700">Tên chủ ví</label>
          <input name="momo_owner" value="<?php echo htmlspecialchars($momo['bank_owner'] ?? '') ?>"
            class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="NEXGEN LAPTOP" />
        </div>

        <div class="md:col-span-2">
          <label class="text-sm font-medium text-gray-700">Ghi chú</label>
          <input name="note_text" value="<?php echo htmlspecialchars($momo['note_text'] ?? '') ?>"
            class="mt-1 w-full border rounded-lg px-3 py-2"
            placeholder="* Thanh toán xong bạn vẫn bấm..." />
        </div>

        <div class="md:col-span-2">
          <label class="text-sm font-medium text-gray-700">Ảnh QR (png/jpg/webp)</label>
          <input type="file" name="qr_image" accept=".png,.jpg,.jpeg,.webp"
            class="mt-1 w-full border rounded-lg px-3 py-2 bg-white" />

          <?php if (!empty($momo['qr_image'])): ?>
            <div class="mt-3 flex items-center gap-4">
              <img src="<?php echo $imgPrefix . htmlspecialchars($momo['qr_image']); ?>"
                   class="w-32 h-32 object-contain bg-white border rounded-lg p-2" alt="QR preview"/>
              <div class="text-sm text-gray-600">
                <div><b>File:</b> <?php echo htmlspecialchars($momo['qr_image']); ?></div>
                <div><b>Cập nhật:</b> <?php echo htmlspecialchars($momo['updated_at'] ?? ''); ?></div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <button class="w-full md:w-auto px-6 py-3 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700">
        Lưu cấu hình
      </button>
    </form>

    <!-- Hiển thị cấu hình đã lưu ở dưới -->
    <div class="mt-8 border-t pt-6">
      <h2 class="text-lg font-bold text-gray-900 mb-3">Cấu hình MoMo hiện tại (đang lưu)</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div class="p-4 rounded-xl bg-gray-50 border">
          <div class="font-semibold text-gray-700 mb-2">Trạng thái</div>
          <?php if ((int)($momo['is_enabled'] ?? 0) === 1): ?>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold">Đang bật</span>
          <?php else: ?>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 font-semibold">Đang tắt</span>
          <?php endif; ?>
          <div class="text-gray-500 mt-2">Cập nhật: <b><?php echo htmlspecialchars($momo['updated_at'] ?? ''); ?></b></div>
        </div>

        <div class="p-4 rounded-xl bg-gray-50 border">
          <div class="font-semibold text-gray-700 mb-2">Thông tin MoMo</div>
          <div><b>SĐT:</b> <?php echo htmlspecialchars($momo['bank_account'] ?? ''); ?></div>
          <div><b>Tên:</b> <?php echo htmlspecialchars($momo['bank_owner'] ?? ''); ?></div>
        </div>

        <div class="md:col-span-2 p-4 rounded-xl bg-gray-50 border">
          <div class="font-semibold text-gray-700 mb-2">Ghi chú</div>
          <div class="text-gray-700">
            <?php echo !empty($momo['note_text']) ? nl2br(htmlspecialchars($momo['note_text'])) : '<span class="text-gray-400">Không có</span>'; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
</body>
</html>
