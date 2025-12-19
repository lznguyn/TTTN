<?php
include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) { // nếu chưa đăng nhập
    header('location:login.php');
    exit;
}

function sumRevenueByStatus(mysqli $conn, string $status): float
{
    $sql = "
        SELECT
            SUM(
                CASE
                    WHEN COALESCE(final_price, 0) > 0 THEN COALESCE(final_price, 0)
                    ELSE GREATEST(0, COALESCE(total_price, 0) - COALESCE(discount_amount, 0))
                END
            ) AS revenue
        FROM orders
        WHERE payment_status = ?
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return 0;

    $stmt->bind_param("s", $status);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return (float)($row['revenue'] ?? 0);
}

/**
 * Count rows helper
 */
function countRows(mysqli $conn, string $sql): int
{
    $q = mysqli_query($conn, $sql);
    if (!$q) return 0;
    return mysqli_num_rows($q);
}

// ==== TÍNH SẴN CÁC SỐ LIỆU ====

// Tổng tiền pending
$total_pendings  = sumRevenueByStatus($conn, 'pending');
$total_completed = sumRevenueByStatus($conn, 'completed');
$number_of_orders   = countRows($conn, "SELECT id FROM `orders`");
$number_of_products = countRows($conn, "SELECT id FROM `products`");

$number_of_users  = countRows($conn, "SELECT id FROM `users` WHERE user_type = 'user'");
$number_of_admins = countRows($conn, "SELECT id FROM `users` WHERE user_type = 'admin'");
$number_of_account = countRows($conn, "SELECT id FROM `users`");

$number_of_messages = countRows($conn, "SELECT id FROM `message`");
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN LAPTOP</title>

    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind giống trang admin_categories -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- nếu anh còn cần css cũ cho header/menu thì giữ -->
    <link rel="stylesheet" href="css/admin_style.css">

</head>

<body class="bg-gray-100 min-h-screen">

<?php include 'admin_header.php'; ?>

<main class="max-w-6xl mx-auto px-4 lg:px-0 py-8 lg:py-10">

    <!-- Tiêu đề + mô tả -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Bảng điều khiển</h1>
            <p class="text-sm text-gray-500 mt-1">
                Tổng quan đơn hàng, doanh thu và tài khoản trong hệ thống.
            </p>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 text-emerald-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Online
            </span>
            <span>Admin ID: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($admin_id); ?></span></span>
        </div>
    </div>

    <!-- Grid các KPI giống style card của admin_categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <!-- Tổng tiền đang xử lý -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Tiền đang xử lý</span>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-clock"></i>
                </span>
            </div>
            <div class="text-xl font-semibold text-gray-800">
                <?php echo number_format($total_pendings); ?>
                <span class="text-xs text-gray-400 ml-1">VNĐ</span>
            </div>
            <p class="text-xs text-gray-500">
                Tổng giá trị đơn hàng có trạng thái <span class="font-semibold">pending</span>.
            </p>
        </div>

        <!-- Thanh toán thành công -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Thanh toán thành công</span>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <div class="text-xl font-semibold text-gray-800">
                <?php echo number_format($total_completed); ?>
                <span class="text-xs text-gray-400 ml-1">VNĐ</span>
            </div>
            <p class="text-xs text-gray-500">
                Tổng doanh thu từ đơn hàng đã <span class="font-semibold">completed</span>.
            </p>
        </div>

        <!-- Tổng đơn hàng -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Tổng đơn hàng</span>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-50 text-blue-600">
                    <i class="fa-solid fa-receipt"></i>
                </span>
            </div>
            <div class="text-2xl font-semibold text-gray-800">
                <?php echo $number_of_orders; ?>
            </div>
            <p class="text-xs text-gray-500">
                Số lượng đơn hàng đã được tạo trong hệ thống.
            </p>
        </div>

        <!-- Tổng sản phẩm -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Tổng sản phẩm</span>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-laptop"></i>
                </span>
            </div>
            <div class="text-2xl font-semibold text-gray-800">
                <?php echo $number_of_products; ?>
            </div>
            <p class="text-xs text-gray-500">
                Số lượng laptop hiện có trong kho / catalog.
            </p>
        </div>

        <!-- Tài khoản User -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Tài khoản User</span>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-sky-50 text-sky-600">
                    <i class="fa-solid fa-user"></i>
                </span>
            </div>
            <div class="text-2xl font-semibold text-gray-800">
                <?php echo $number_of_users; ?>
            </div>
            <p class="text-xs text-gray-500">
                Người dùng thông thường đăng ký trên hệ thống.
            </p>
        </div>

        <!-- Tài khoản Admin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Tài khoản Admin</span>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-rose-50 text-rose-600">
                    <i class="fa-solid fa-user-shield"></i>
                </span>
            </div>
            <div class="text-2xl font-semibold text-gray-800">
                <?php echo $number_of_admins; ?>
            </div>
            <p class="text-xs text-gray-500">
                Quản trị viên có quyền quản lý hệ thống.
            </p>
        </div>

        <!-- Tổng tài khoản -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Tổng tài khoản</span>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-gray-50 text-gray-600">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <div class="text-2xl font-semibold text-gray-800">
                <?php echo $number_of_account; ?>
            </div>
            <p class="text-xs text-gray-500">
                Tổng số tài khoản (User + Admin).
            </p>
        </div>

        <!-- Thông báo mới -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Thông báo mới</span>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-purple-50 text-purple-600">
                    <i class="fa-solid fa-envelope"></i>
                </span>
            </div>
            <div class="text-2xl font-semibold text-gray-800">
                <?php echo $number_of_messages; ?>
            </div>
            <p class="text-xs text-gray-500">
                Số lượng tin nhắn / liên hệ chưa xử lý.
            </p>
        </div>

    </div>

</main>

<!-- nếu cần js cũ -->
<script src="js/admin_script.js"></script>

</body>
</html>
