<?php
include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!isset($admin_id)) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['update_order'])) {
    $order_update_id = $_POST['order_id'];
    $update_payment  = $_POST['update_payment'];

    mysqli_query(
        $conn,
        "UPDATE `orders` SET payment_status = '$update_payment' WHERE id = '$order_update_id'"
    ) or die('query failed');

    $message[] = 'Trạng thái thanh toán đã được cập nhật!';
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed');
    header('location:admin_orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng</title>

    <!-- font awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind giống các trang admin mới -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- css cũ cho header/menu nếu cần -->
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
    $select_orders = mysqli_query($conn, "SELECT * FROM `orders` ORDER BY id DESC") or die('query failed');
    ?>

    <?php if (mysqli_num_rows($select_orders) > 0): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <?php while ($fetch_orders = mysqli_fetch_assoc($select_orders)): ?>
                <?php
                $status = $fetch_orders['payment_status'];

                // badge theo trạng thái
                $badgeClass = 'bg-gray-100 text-gray-600';
                if ($status === 'Thành công' || $status === 'completed') {
                    $badgeClass = 'bg-emerald-50 text-emerald-700';
                } elseif ($status === 'Đang duyệt' || $status === 'pending') {
                    $badgeClass = 'bg-amber-50 text-amber-700';
                }
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3">
                    <!-- Header card: ID + date + status -->
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                Đơn hàng #<?php echo (int)$fetch_orders['id']; ?>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                Ngày đặt: <span class="font-medium text-gray-600">
                                    <?php echo htmlspecialchars($fetch_orders['placed_on']); ?>
                                </span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium <?php echo $badgeClass; ?>">
                            <span class="w-2 h-2 rounded-full
                                <?php
                                if ($status === 'Thành công' || $status === 'completed') {
                                    echo ' bg-emerald-500';
                                } elseif ($status === 'Đang duyệt' || $status === 'pending') {
                                    echo ' bg-amber-500';
                                } else {
                                    echo ' bg-gray-400';
                                }
                                ?>">
                            </span>
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </div>

                    <!-- Thông tin khách -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="space-y-1">
                            <p class="text-gray-500">User ID:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['user_id']); ?>
                                </span>
                            </p>
                            <p class="text-gray-500">Tên:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['name']); ?>
                                </span>
                            </p>
                            <p class="text-gray-500">Số điện thoại:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['number']); ?>
                                </span>
                            </p>
                            <p class="text-gray-500">Email:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['email']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500">Địa chỉ giao hàng:</p>
                            <p class="text-xs text-gray-700 bg-gray-50 rounded-lg px-3 py-2">
                                <?php echo nl2br(htmlspecialchars($fetch_orders['address'])); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Sản phẩm + tổng tiền -->
                    <div class="mt-1 space-y-1 text-sm">
                        <p class="text-gray-500">Sản phẩm đặt hàng:</p>
                        <p class="text-xs text-gray-700 bg-gray-50 rounded-lg px-3 py-2">
                            <?php echo htmlspecialchars($fetch_orders['total_products']); ?>
                        </p>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-gray-500">Hình thức thanh toán:
                                <span class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_orders['method']); ?>
                                </span>
                            </p>
                            <p class="text-sm font-bold text-blue-600">
                                <?php echo number_format($fetch_orders['total_price']); ?>
                                <span class="text-xs text-gray-400">VNĐ</span>
                            </p>
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
                                <select name="update_payment"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="" selected disabled>
                                        <?php echo htmlspecialchars($fetch_orders['payment_status']); ?>
                                    </option>
                                    <option value="Đang duyệt">Đang duyệt</option>
                                    <option value="Thành công">Thành công</option>
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
