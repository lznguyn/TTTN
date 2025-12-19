<?php
// Kết nối cơ sở dữ liệu và bắt đầu phiên làm việc
include 'config.php';
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id']; // ép int cho an toàn

// Lấy tất cả đơn hàng của user (sắp xếp mới nhất)
// Nếu placed_on của bạn là dạng text (d-M-Y) thì ORDER BY placed_on có thể sai.
// Ưu tiên ORDER BY id DESC cho chắc.
$order_query = mysqli_query(
    $conn,
    "SELECT * FROM `orders` WHERE user_id = $user_id ORDER BY id DESC"
) or die('query failed');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn đặt hàng</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- CSS cũ (nếu còn dùng cho header/footer, popup...) -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Roboto', sans-serif; background: #f3f4f6; }
    </style>
</head>

<body class="bg-gray-50">
<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="bg-gray-100 mt-16 py-4">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center space-x-2 text-sm">
            <a href="home.php" class="text-gray-600 hover:text-blue-600 transition-colors">Trang chủ</a>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            <span class="text-blue-600 font-medium">Đơn đặt hàng</span>
        </nav>
    </div>
</div>

<section class="py-10 sm:py-14">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Tiêu đề -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fas fa-receipt text-blue-600"></i>
                    Đơn đặt hàng của bạn
                </h1>
                <p class="text-gray-500 mt-1 text-sm sm:text-base">
                    Danh sách tất cả đơn đặt hàng đã tạo với tài khoản hiện tại.
                </p>
            </div>
        </div>

        <?php if (mysqli_num_rows($order_query) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php while ($fetch_orders = mysqli_fetch_assoc($order_query)): ?>
                    <?php
                    // ====== TÍNH GIÁ ĐÚNG Ở ĐÂY ======
                    $baseTotal  = (float)($fetch_orders['total_price'] ?? 0);        // giá gốc
                    $discount   = (float)($fetch_orders['discount_amount'] ?? 0);   // số tiền giảm
                    $finalTotal = (float)($fetch_orders['final_price'] ?? 0);       // giá sau giảm
                    $couponCode = $fetch_orders['coupon_code'] ?? null;

                    // fallback nếu DB cũ chưa có final_price
                    if ($finalTotal <= 0 && $discount > 0) $finalTotal = max(0, $baseTotal - $discount);
                    if ($finalTotal <= 0) $finalTotal = $baseTotal;

                    $paymentStatus = $fetch_orders['payment_status'] ?? 'Đang duyệt';
                    $isPending = ($paymentStatus === 'Đang duyệt');

                    $statusClass = $isPending ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
                    $statusIcon  = $isPending ? 'fa-clock' : 'fa-check-circle';

                    // placed_on của bạn đang lưu dạng d-M-Y => strtotime vẫn parse được đa số trường hợp
                    $placedOnRaw = $fetch_orders['placed_on'] ?? '';
                    $placedOnFmt = $placedOnRaw;
                    $ts = strtotime($placedOnRaw);
                    if ($ts) $placedOnFmt = date('d/m/Y', $ts);
                    ?>

                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-200 border border-gray-100">
                        <!-- Header đơn -->
                        <div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-gray-100">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">Mã đơn</p>
                                <p class="font-semibold text-gray-800 text-sm">
                                    #<?php echo htmlspecialchars($fetch_orders['id']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-wide text-gray-400">Ngày đặt</p>
                                <p class="font-semibold text-gray-800 text-sm">
                                    <?php echo htmlspecialchars($placedOnFmt); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Nội dung đơn -->
                        <div class="px-5 py-4 space-y-2 text-sm text-gray-700">
                            <p class="flex justify-between">
                                <span class="text-gray-500">Người đặt:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($fetch_orders['name'] ?? ''); ?></span>
                            </p>

                            <p class="flex justify-between">
                                <span class="text-gray-500">Số điện thoại:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($fetch_orders['number'] ?? ''); ?></span>
                            </p>

                            <p class="flex justify-between">
                                <span class="text-gray-500">Email:</span>
                                <span class="font-medium truncate max-w-[60%] text-right">
                                    <?php echo htmlspecialchars($fetch_orders['email'] ?? ''); ?>
                                </span>
                            </p>

                            <p class="flex items-start justify-between gap-3">
                                <span class="text-gray-500 mt-[2px]">Địa chỉ:</span>
                                <span class="font-medium text-right">
                                    <?php echo htmlspecialchars($fetch_orders['address'] ?? ''); ?>
                                </span>
                            </p>

                            <p class="flex justify-between">
                                <span class="text-gray-500">Thanh toán:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($fetch_orders['method'] ?? ''); ?></span>
                            </p>

                            <p class="flex items-start justify-between gap-3">
                                <span class="text-gray-500 mt-[2px]">Sản phẩm:</span>
                                <span class="font-medium text-right text-xs sm:text-sm">
                                    <?php echo htmlspecialchars($fetch_orders['total_products'] ?? ''); ?>
                                </span>
                            </p>
                        </div>

                        <!-- Footer đơn -->
                        <div class="px-5 pb-5 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Tổng thanh toán</p>

                                <!-- Giá sau giảm -->
                                <p class="text-lg font-bold text-blue-600">
                                    <?php echo number_format($finalTotal, 0, ',', '.'); ?> VNĐ
                                </p>

                                <!-- Nếu có giảm thì show giá gốc + giảm + mã -->
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

                            <div class="text-right">
                                <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Trạng thái</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $statusClass; ?>">
                                    <i class="fas <?php echo $statusIcon; ?> mr-1"></i>
                                    <?php echo htmlspecialchars($paymentStatus); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="mt-10 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                    <i class="fas fa-box-open text-3xl text-gray-400"></i>
                </div>
                <p class="text-lg font-semibold text-gray-800 mb-1">Chưa có đơn hàng nào</p>
                <p class="text-gray-500 mb-4">Hãy khám phá sản phẩm và đặt đơn hàng đầu tiên của bạn.</p>
                <a href="shop.php"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow">
                    <i class="fas fa-shopping-bag mr-2"></i>
                    Tiếp tục mua sắm
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
