<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('Location: login.php');
    exit;
}

$message = [];

/**
 * Normalize payment status về 2 giá trị: pending | completed
 * Hỗ trợ cả dữ liệu cũ dùng tiếng Việt.
 */
function normalize_payment_status(string $status): string {
    $s = trim(mb_strtolower($status, 'UTF-8'));
    if ($s === 'pending' || $s === 'đang duyệt' || $s === 'dang duyet') return 'pending';
    if ($s === 'completed' || $s === 'thành công' || $s === 'thanh cong') return 'completed';
    // fallback
    return $status;
}

function status_badge_class(string $normalized): array {
    if ($normalized === 'completed') {
        return ['bg-emerald-50 text-emerald-700', 'bg-emerald-500', 'Thành công'];
    }
    if ($normalized === 'pending') {
        return ['bg-amber-50 text-amber-700', 'bg-amber-500', 'Đang duyệt'];
    }
    return ['bg-gray-100 text-gray-600', 'bg-gray-400', $normalized];
}

// ===== Update Order Status =====
if (isset($_POST['update_order'])) {
    $order_update_id = (int)($_POST['order_id'] ?? 0);
    $update_payment  = $_POST['update_payment'] ?? '';

    $allowed = ['pending', 'completed'];
    if ($order_update_id <= 0) {
        $message[] = 'Order ID không hợp lệ!';
    } elseif (!in_array($update_payment, $allowed, true)) {
        $message[] = 'Trạng thái không hợp lệ!';
    } else {
        // Lưu về dạng chuẩn pending/completed
        $stmt = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
        $stmt->bind_param("si", $update_payment, $order_update_id);
        $stmt->execute();
        $stmt->close();

        $message[] = 'Trạng thái thanh toán đã được cập nhật!';
    }
}

// ===== Delete Order =====
if (isset($_GET['delete'])) {
    $delete_id = (int)($_GET['delete'] ?? 0);
    if ($delete_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        header('location:admin_orders.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/admin_style.css">
</head>

<body class="bg-gray-100 min-h-screen">
<?php include 'admin_header.php'; ?>

<main class="max-w-6xl mx-auto px-4 lg:px-0 py-8 lg:py-10">

    <!-- Header + message -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Quản lý đơn hàng</h1>
            <p class="text-sm text-gray-500 mt-1">
                Xem chi tiết và cập nhật trạng thái thanh toán của đơn hàng.
            </p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="space-y-2 md:max-w-xs">
                <?php foreach ($message as $m): ?>
                    <div class="px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-xs flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?php echo htmlspecialchars($m); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Lấy orders. Nếu DB bạn có placed_on dạng string thì ORDER BY id DESC ok nhất.
    $select_orders = mysqli_query($conn, "SELECT * FROM `orders` ORDER BY id DESC") or die('query failed');
    ?>

    <?php if (mysqli_num_rows($select_orders) > 0): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <?php while ($fetch_orders = mysqli_fetch_assoc($select_orders)): ?>
                <?php
                // ====== GIÁ ======
                $baseTotal  = (float)($fetch_orders['total_price'] ?? 0);        // giá gốc
                $discount   = (float)($fetch_orders['discount_amount'] ?? 0);   // tiền giảm
                $finalTotal = (float)($fetch_orders['final_price'] ?? 0);       // giá sau giảm
                $couponCode = $fetch_orders['coupon_code'] ?? null;

                // fallback: đơn cũ chưa có final_price
                if ($finalTotal <= 0 && $discount > 0) $finalTotal = max(0, $baseTotal - $discount);
                if ($finalTotal <= 0) $finalTotal = $baseTotal;

                // ====== STATUS ======
                $rawStatus = (string)($fetch_orders['payment_status'] ?? '');
                $normalized = normalize_payment_status($rawStatus);
                [$badgeClass, $dotClass, $badgeText] = status_badge_class($normalized);
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3">

                    <!-- Header: ID + date + status -->
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                Đơn hàng #<?php echo (int)$fetch_orders['id']; ?>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                Ngày đặt:
                                <span class="font-medium text-gray-600">
                                    <?php echo htmlspecialchars($fetch_orders['placed_on'] ?? ''); ?>
                                </span>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium <?php echo $badgeClass; ?>">
                            <span class="w-2 h-2 rounded-full <?php echo $dotClass; ?>"></span>
                            <?php echo htmlspecialchars($badgeText); ?>
                        </span>
                    </div>

                    <!-- Thông tin khách -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="space-y-1">
                            <p class="text-gray-500">User ID:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['user_id'] ?? ''); ?>
                                </span>
                            </p>
                            <p class="text-gray-500">Tên:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['name'] ?? ''); ?>
                                </span>
                            </p>
                            <p class="text-gray-500">Số điện thoại:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['number'] ?? ''); ?>
                                </span>
                            </p>
                            <p class="text-gray-500">Email:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['email'] ?? ''); ?>
                                </span>
                            </p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-gray-500">Địa chỉ giao hàng:</p>
                            <p class="text-xs text-gray-700 bg-gray-50 rounded-lg px-3 py-2">
                                <?php echo nl2br(htmlspecialchars($fetch_orders['address'] ?? '')); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Sản phẩm + tổng tiền -->
                    <div class="mt-1 space-y-1 text-sm">
                        <p class="text-gray-500">Sản phẩm đặt hàng:</p>
                        <p class="text-xs text-gray-700 bg-gray-50 rounded-lg px-3 py-2">
                            <?php echo htmlspecialchars($fetch_orders['total_products'] ?? ''); ?>
                        </p>

                        <div class="flex items-start justify-between gap-3 mt-2">
                            <p class="text-gray-500">
                                Hình thức thanh toán:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['method'] ?? ''); ?>
                                </span>
                            </p>

                            <!-- GIÁ SAU GIẢM (đúng) -->
                            <div class="text-right">
                                <p class="text-sm font-bold text-blue-600">
                                    <?php echo number_format($finalTotal, 0, ',', '.'); ?>
                                    <span class="text-xs text-gray-400">VNĐ</span>
                                </p>

                                <!-- nếu có giảm thì show giá gốc + giảm + coupon -->
                                <?php if ($discount > 0): ?>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <span class="line-through">
                                            <?php echo number_format($baseTotal, 0, ',', '.'); ?> VNĐ
                                        </span>
                                        <span class="text-green-600 font-semibold ml-2">
                                            -<?php echo number_format($discount, 0, ',', '.'); ?> VNĐ
                                        </span>
                                        <?php if (!empty($couponCode)): ?>
                                            <span class="ml-2 text-gray-600">
                                                (Mã: <span class="font-semibold"><?php echo htmlspecialchars($couponCode); ?></span>)
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Form update -->
                    <form action="" method="post" class="mt-3 pt-3 border-t border-dashed border-gray-200">
                        <input type="hidden" name="order_id" value="<?php echo (int)$fetch_orders['id']; ?>">

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Cập nhật trạng thái thanh toán
                                </label>
                                <select name="update_payment" required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="pending"   <?php echo ($normalized === 'pending' ? 'selected' : ''); ?>>
                                        Đang duyệt
                                    </option>
                                    <option value="completed" <?php echo ($normalized === 'completed' ? 'selected' : ''); ?>>
                                        Thành công
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                <button type="submit" name="update_order"
                                        class="option-btn inline-flex items-center justify-center px-3 py-2 rounded-lg
                                               bg-blue-600 text-white text-xs font-medium shadow hover:bg-blue-700">
                                    <i class="fa-solid fa-rotate mr-1"></i> Cập nhật
                                </button>

                                <a href="admin_orders.php?delete=<?php echo (int)$fetch_orders['id']; ?>"
                                   onclick="return confirm('Bạn có muốn xóa đơn đặt hàng này?');"
                                   class="delete-btn inline-flex items-center justify-center px-3 py-2 rounded-lg
                                          bg-rose-500 text-white text-xs font-medium shadow hover:bg-rose-600">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Xóa
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-sm text-gray-400 mt-10">
            Chưa có đơn hàng nào được đặt.
        </p>
    <?php endif; ?>

</main>

<script src="js/admin_script.js"></script>
</body>
</html>
