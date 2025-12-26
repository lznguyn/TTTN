<?php
include 'config.php';
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// TODO: check admin
// if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$msg = null;

// Load current setting
$stmt = $conn->prepare("SELECT * FROM payment_settings WHERE payment_key='ATM' LIMIT 1");
$stmt->execute();
$atm = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$atm) {
  // auto create if missing
  $stmt = $conn->prepare("INSERT INTO payment_settings(payment_key,is_enabled) VALUES('ATM',1)");
  $stmt->execute();
  $stmt->close();
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $is_enabled   = isset($_POST['is_enabled']) ? 1 : 0;
  $bank_name    = trim($_POST['bank_name'] ?? '');
  $bank_account = trim($_POST['bank_account'] ?? '');
  $bank_owner   = trim($_POST['bank_owner'] ?? '');
  $note_text    = trim($_POST['note_text'] ?? '');

  // upload QR image (optional)
  $qr_image = $atm['qr_image'];
  if (!empty($_FILES['qr_image']['name']) && ($_FILES['qr_image']['error'] ?? 1) === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION));
    $allow = ['png','jpg','jpeg','webp'];
    if (!in_array($ext, $allow, true)) {
      $msg = "File QR chỉ cho phép: png/jpg/jpeg/webp";
    } else {
      $newName = 'qr-atm-' . time() . '.' . $ext;

      // lưu vào đúng folder đang dùng ở checkout: uploaded_img/
      $targetDir = __DIR__ . '/../uploaded_img/';
      if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

      $target = $targetDir . $newName;
      if (!move_uploaded_file($_FILES['qr_image']['tmp_name'], $target)) {
        $msg = "Upload QR thất bại!";
      } else {
        $qr_image = $newName;
      }
    }
  }

  if ($msg === null) {
    $stmt = $conn->prepare("
      UPDATE payment_settings
      SET is_enabled=?, bank_name=?, bank_account=?, bank_owner=?, qr_image=?, note_text=?
      WHERE payment_key='ATM'
    ");
    $stmt->bind_param("isssss", $is_enabled, $bank_name, $bank_account, $bank_owner, $qr_image, $note_text);
    $stmt->execute();
    $stmt->close();

    header("Location: ".$_SERVER['PHP_SELF']."?saved=1");
    exit;
  }
}

// reload after update / show
$stmt = $conn->prepare("SELECT * FROM payment_settings WHERE payment_key='ATM' LIMIT 1");
$stmt->execute();
$atm = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin - Cấu hình QR chuyển khoản</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="css/admin_style.css">
</head>
<body class="bg-gray-50">
<?php include 'admin_header.php'; ?>
  <div class="max-w-3xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-1">Cấu hình QR Chuyển khoản (ATM)</h1>
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
            <?php echo ((int)$atm['is_enabled'] === 1 ? 'checked' : ''); ?> />
          <span class="font-semibold text-gray-800">Bật hiển thị QR ATM ở checkout</span>
        </label>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-gray-700">Ngân hàng</label>
            <input name="bank_name" value="<?php echo htmlspecialchars($atm['bank_name'] ?? '') ?>"
              class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="Vietcombank" />
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Số tài khoản</label>
            <input name="bank_account" value="<?php echo htmlspecialchars($atm['bank_account'] ?? '') ?>"
              class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="0123456789" />
          </div>

          <div class="md:col-span-2">
            <label class="text-sm font-medium text-gray-700">Chủ tài khoản</label>
            <input name="bank_owner" value="<?php echo htmlspecialchars($atm['bank_owner'] ?? '') ?>"
              class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="NEXGEN LAPTOP" />
          </div>

          <div class="md:col-span-2">
            <label class="text-sm font-medium text-gray-700">Ghi chú</label>
            <input name="note_text" value="<?php echo htmlspecialchars($atm['note_text'] ?? '') ?>"
              class="mt-1 w-full border rounded-lg px-3 py-2"
              placeholder="* Sau khi chuyển khoản..." />
          </div>

          <div class="md:col-span-2">
            <label class="text-sm font-medium text-gray-700">Ảnh QR (png/jpg/webp)</label>
            <input type="file" name="qr_image" accept=".png,.jpg,.jpeg,.webp"
              class="mt-1 w-full border rounded-lg px-3 py-2 bg-white" />

            <?php if (!empty($atm['qr_image'])): ?>
              <div class="mt-3 flex items-center gap-4">
                <img src="../uploaded_img/<?php echo htmlspecialchars($atm['qr_image']); ?>"
                     class="w-32 h-32 object-contain bg-white border rounded-lg p-2" alt="QR preview"/>
                <div class="text-sm text-gray-600">
                  <div><b>File:</b> <?php echo htmlspecialchars($atm['qr_image']); ?></div>
                  <div><b>Cập nhật:</b> <?php echo htmlspecialchars($atm['updated_at']); ?></div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <button class="w-full md:w-auto px-6 py-3 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700">
          Lưu cấu hình
        </button>
      </form>
      <!-- Preview cấu hình đang lưu -->
        <div class="mt-8 border-t pt-6">
        <h2 class="text-lg font-bold text-gray-900 mb-3">Cấu hình ATM hiện tại (đang lưu)</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="p-4 rounded-xl bg-gray-50 border">
            <div class="font-semibold text-gray-700 mb-2">Trạng thái</div>
            <?php if ((int)($atm['is_enabled'] ?? 0) === 1): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold">
                Đang bật
                </span>
            <?php else: ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 font-semibold">
                Đang tắt
                </span>
            <?php endif; ?>
            <div class="text-gray-500 mt-2">
                Cập nhật: <b><?php echo htmlspecialchars($atm['updated_at'] ?? ''); ?></b>
            </div>
            </div>

            <div class="p-4 rounded-xl bg-gray-50 border">
            <div class="font-semibold text-gray-700 mb-2">Thông tin ngân hàng</div>
            <div><b>Ngân hàng:</b> <?php echo htmlspecialchars($atm['bank_name'] ?? ''); ?></div>
            <div><b>STK:</b> <?php echo htmlspecialchars($atm['bank_account'] ?? ''); ?></div>
            <div><b>Chủ TK:</b> <?php echo htmlspecialchars($atm['bank_owner'] ?? ''); ?></div>
            </div>

            <div class="md:col-span-2 p-4 rounded-xl bg-gray-50 border">
            <div class="font-semibold text-gray-700 mb-2">Ghi chú</div>
            <div class="text-gray-700">
                <?php echo !empty($atm['note_text']) ? nl2br(htmlspecialchars($atm['note_text'])) : '<span class="text-gray-400">Không có</span>'; ?>
            </div>
            </div>

            <div class="md:col-span-2 p-4 rounded-xl bg-gray-50 border">
            <div class="font-semibold text-gray-700 mb-3">QR đang dùng</div>

            <?php
                // đường dẫn hiển thị QR:
                // - nếu file admin ở root (/var/www/html/admin_payment_atm.php) và ảnh ở /uploaded_img/
                //   thì src = "uploaded_img/..."
                // - nếu file admin ở /admin/... thì src = "../uploaded_img/..."
                // Bạn đang dùng "../uploaded_img/" => giữ nguyên để đồng bộ.
                $qr = $atm['qr_image'] ?? '';
            ?>

            <?php if (!empty($qr)): ?>
                <div class="flex items-center gap-4">
                <img
                    src="../uploaded_img/<?php echo htmlspecialchars($qr); ?>"
                    class="w-40 h-40 object-contain bg-white border rounded-lg p-2"
                    alt="QR ATM"
                />
                <div class="text-gray-600">
                    <div><b>File:</b> <?php echo htmlspecialchars($qr); ?></div>
                    <div class="text-xs text-gray-500 mt-2">
                    * Nếu hình không hiện: kiểm tra đường dẫn src và quyền đọc folder uploaded_img.
                    </div>
                </div>
                </div>
            <?php else: ?>
                <div class="text-gray-500">Chưa upload QR. (Sẽ dùng ảnh mặc định ở checkout nếu bạn có set fallback)</div>
            <?php endif; ?>
            </div>
        </div>
        </div>

    </div>
  </div>
</body>
</html>
