<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('Location: Login.php');
    exit;
}

function toNullOrEmpty($v) {
    $v = trim((string)$v)
    return $v === '' ? null : $v;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $code          = strtoupper(trim($_POST['code'] ?? ''));
        $name          = trim($_POST['name'] ?? '');
        $discountType  = ($_POST['discount_type'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent';
        $discountValue = (float)($_POST['discount_value'] ?? 0);
        $minOrderTotal = (float)($_POST['min_order_total'] ?? 0);
        $maxDiscount   = toNullIfEmpty($_POST['max_discount'] ?? null);
        $startAt       = toNullIfEmpty($_POST['start_at'] ?? null);
        $endAt         = toNullIfEmpty($_POST['end_at'] ?? null);
        $usageLimit    = toNullIfEmpty($_POST['usage_limit'] ?? null);
        $perUserLimit  = toNullIfEmpty($_POST['per_user_limit'] ?? null);
        $sortOrder     = (int)($_POST['sort_order'] ?? 0);
        $isActive      = isset($_POST['is_active']) ? 1 : 0;

        if ($code !== '' && $name !== '' && $discountValue > 0) {
            $stmt = $conn->prepare("
                INSERT INTO coupons
                (code, name, discount_type, discount_value, min_order_total, max_discount, start_at, end_at, usage_limit, per_user_limit, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            // code, name, type (sss), discount_value (d), min_order_total (d), max_discount (s->nullable), start_at (s->nullable), end_at (s->nullable), usage_limit (s->nullable), per_user_limit (s->nullable), sort_order (i), is_active (i)
            $stmt->bind_param(
                'sssddssssiii',
                $code,
                $name,
                $discountType,
                $discountValue,
                $minOrderTotal,
                $maxDiscount,
                $startAt,
                $endAt,
                $usageLimit,
                $perUserLimit,
                $sortOrder,
                $isActive
            );
            $stmt->execute();
            $stmt->close();
        }

        header('Location: admin_coupon.php');
        exit;
    }

    if ($action === 'update') {
        $id            = (int)($_POST['id'] ?? 0);
        $code          = strtoupper(trim($_POST['code'] ?? ''));
        $name          = trim($_POST['name'] ?? '');
        $discountType  = ($_POST['discount_type'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent';
        $discountValue = (float)($_POST['discount_value'] ?? 0);
        $minOrderTotal = (float)($_POST['min_order_total'] ?? 0);
        $maxDiscount   = toNullIfEmpty($_POST['max_discount'] ?? null);
        $startAt       = toNullIfEmpty($_POST['start_at'] ?? null);
        $endAt         = toNullIfEmpty($_POST['end_at'] ?? null);
        $usageLimit    = toNullIfEmpty($_POST['usage_limit'] ?? null);
        $perUserLimit  = toNullIfEmpty($_POST['per_user_limit'] ?? null);
        $sortOrder     = (int)($_POST['sort_order'] ?? 0);
        $isActive      = isset($_POST['is_active']) ? 1 : 0;

        if ($id > 0 && $code !== '' && $name !== '' && $discountValue > 0) {
            $stmt = $conn->prepare("
                UPDATE coupons
                SET code=?, name=?, discount_type=?, discount_value=?, min_order_total=?, max_discount=?, start_at=?, end_at=?, usage_limit=?, per_user_limit=?, sort_order=?, is_active=?
                WHERE id=?
            ");
            $stmt->bind_param(
                'sssddssssiiiii',
                $code,
                $name,
                $discountType,
                $discountValue,
                $minOrderTotal,
                $maxDiscount,
                $startAt,
                $endAt,
                $usageLimit,
                $perUserLimit,
                $sortOrder,
                $isActive,
                $id
            );
            $stmt->execute();
            $stmt->close();
        }

        header('Location: admin_coupon.php');
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM coupons WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: admin_coupon.php');
        exit;
    }
}

$result = $conn->query("SELECT * FROM coupons ORDER BY sort_order ASC, id DESC");
$coupons = [];
if ($result) {
    $coupons = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Cấu hình mã giảm giá</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body class="bg-gray-100 min-h-screen">
<?php include 'admin_header.php'; ?>

<div class="max-w-6xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6">Cấu hình mã giảm giá</h1>

    <!-- Create -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4">Thêm mã giảm giá</h2>
        <form method="post" class="space-y-4">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã (CODE)</label>
                    <input type="text" name="code" required
                           class="w-full rounded border-gray-300 shadow-sm"
                           placeholder="VD: SALE10">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên chương trình</label>
                    <input type="text" name="name" required
                           class="w-full rounded border-gray-300 shadow-sm"
                           placeholder="VD: Giảm 10% tháng 12">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại</label>
                    <select name="discount_type" class="w-full rounded border-gray-300 shadow-sm">
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Số tiền (VNĐ)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giá trị</label>
                    <input type="number" step="0.01" name="discount_value" required
                           class="w-full rounded border-gray-300 shadow-sm"
                           placeholder="10 hoặc 50000">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Đơn tối thiểu</label>
                    <input type="number" step="0.01" name="min_order_total" value="0"
                           class="w-full rounded border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giảm tối đa</label>
                    <input type="number" step="0.01" name="max_discount"
                           class="w-full rounded border-gray-300 shadow-sm"
                           placeholder="(optional)">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bắt đầu</label>
                    <input type="datetime-local" name="start_at"
                           class="w-full rounded border-gray-300 shadow-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kết thúc</label>
                    <input type="datetime-local" name="end_at"
                           class="w-full rounded border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giới hạn dùng</label>
                    <input type="number" name="usage_limit"
                           class="w-full rounded border-gray-300 shadow-sm"
                           placeholder="(optional)">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giới hạn/user</label>
                    <input type="number" name="per_user_limit"
                           class="w-full rounded border-gray-300 shadow-sm"
                           placeholder="(optional)">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự</label>
                    <input type="number" name="sort_order" value="0"
                           class="w-full rounded border-gray-300 shadow-sm">
                </div>

                <div class="flex items-center space-x-2 mt-6">
                    <input type="checkbox" name="is_active" id="create_is_active"
                           class="rounded border-gray-300 text-blue-600" checked>
                    <label for="create_is_active" class="text-sm text-gray-700">Kích hoạt</label>
                </div>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 text-sm">
                Thêm mã
            </button>
        </form>
    </div>

    <!-- List -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Danh sách mã giảm giá</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="border-b">
                    <th class="px-3 py-2 text-left text-gray-500">ID</th>
                    <th class="px-3 py-2 text-left text-gray-500">CODE</th>
                    <th class="px-3 py-2 text-left text-gray-500">Tên</th>
                    <th class="px-3 py-2 text-left text-gray-500">Loại</th>
                    <th class="px-3 py-2 text-left text-gray-500">Giá trị</th>
                    <th class="px-3 py-2 text-left text-gray-500">Min</th>
                    <th class="px-3 py-2 text-left text-gray-500">Active</th>
                    <th class="px-3 py-2 text-right text-gray-500">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($coupons as $c): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <form method="post">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                            <td class="px-3 py-2"><?php echo (int)$c['id']; ?></td>
                            <td class="px-3 py-2">
                                <input name="code" value="<?php echo htmlspecialchars($c['code']); ?>"
                                       class="w-28 rounded border-gray-200 text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <input name="name" value="<?php echo htmlspecialchars($c['name']); ?>"
                                       class="w-56 rounded border-gray-200 text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <select name="discount_type" class="rounded border-gray-200 text-sm">
                                    <option value="percent" <?php echo $c['discount_type']==='percent'?'selected':''; ?>>%</option>
                                    <option value="fixed" <?php echo $c['discount_type']==='fixed'?'selected':''; ?>>VNĐ</option>
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" name="discount_value"
                                       value="<?php echo htmlspecialchars($c['discount_value']); ?>"
                                       class="w-24 rounded border-gray-200 text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" name="min_order_total"
                                       value="<?php echo htmlspecialchars($c['min_order_total']); ?>"
                                       class="w-24 rounded border-gray-200 text-sm">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input type="checkbox" name="is_active"
                                    <?php echo ((int)$c['is_active']===1?'checked':''); ?>>
                            </td>
                            <td class="px-3 py-2 text-right space-x-2">
                                <button type="submit"
                                        class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700">
                                    Lưu
                                </button>
                        </form>

                        <form method="post" class="inline" onsubmit="return confirm('Xóa mã này?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                            <button type="submit"
                                    class="px-3 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                Xóa
                            </button>
                        </form>
                            </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($coupons)): ?>
                    <tr>
                        <td colspan="8" class="px-3 py-4 text-center text-gray-400">
                            Chưa có mã nào.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>