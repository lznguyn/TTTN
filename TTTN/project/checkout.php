<?php
// checkout.php — FULL (prepared statements + transaction + coupon used_count atomic + FIX bind_param mismatch)

include 'config.php';
session_start();

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

$user_id = (int)$_SESSION['user_id'];

// ===== DEFAULTS =====
$coupon_row = null;
$discount_amount = 0.0;
$final_total = 0.0;

// ===== Helper =====
function calc_discount(float $total, array $coupon_row): float
{
    $type = $coupon_row['discount_type'] ?? '';
    $val  = (float)($coupon_row['discount_value'] ?? 0);

    $d = ($type === 'percent') ? ($total * ($val / 100.0)) : $val;

    // max_discount (optional)
    if (!empty($coupon_row['max_discount'])) {
        $maxD = (float)$coupon_row['max_discount'];
        if ($d > $maxD) $d = $maxD;
    }

    if ($d > $total) $d = $total;
    return (float)$d;
}

function redirect_self(): void {
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// ===== LOAD CART (để có grand_total) =====
$cart_items  = [];
$grand_total = 0.0;

try {
    $stmt = $conn->prepare("SELECT id, name, price, quantity, image FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $row['price'] = (float)$row['price'];
        $row['quantity'] = (int)$row['quantity'];
        $row['total_price'] = $row['price'] * $row['quantity'];
        $grand_total += $row['total_price'];
        $cart_items[] = $row;
    }
    $stmt->close();
} catch (Throwable $e) {
    // optional log
}

// ===== APPLY COUPON =====
if (isset($_POST['apply_coupon'])) {
    $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
    unset($_SESSION['applied_coupon']);

    if ($code === '') {
        $_SESSION['cart_message'] = 'Vui lòng nhập mã giảm giá!';
        redirect_self();
    }

    try {
        $stmt = $conn->prepare("
            SELECT *
            FROM coupons
            WHERE code = ?
              AND is_active = 1
              AND (start_at IS NULL OR start_at <= NOW())
              AND (end_at IS NULL OR end_at >= NOW())
            LIMIT 1
        ");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $coupon = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$coupon) {
            $_SESSION['cart_message'] = 'Mã giảm giá không hợp lệ hoặc đã hết hạn!';
        } elseif ($grand_total < (float)($coupon['min_order_total'] ?? 0)) {
            $_SESSION['cart_message'] = 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã!';
        } elseif (!empty($coupon['usage_limit']) && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
            $_SESSION['cart_message'] = 'Mã giảm giá đã hết lượt sử dụng!';
        } else {
            $_SESSION['applied_coupon'] = [
                'code' => $coupon['code'],
                'id'   => (int)$coupon['id'],
            ];
            $_SESSION['cart_message'] = 'Áp dụng mã giảm giá thành công!';
        }
    } catch (Throwable $e) {
        $_SESSION['cart_message'] = 'Lỗi khi áp dụng mã giảm giá!';
    }

    redirect_self();
}

// ===== REMOVE COUPON =====
if (isset($_POST['remove_coupon'])) {
    unset($_SESSION['applied_coupon']);
    $_SESSION['cart_message'] = 'Đã gỡ mã giảm giá!';
    redirect_self();
}

// ===== LOAD COUPON DETAIL (để tính discount/final_total) =====
$applied_coupon = $_SESSION['applied_coupon'] ?? null;

if ($applied_coupon && !empty($applied_coupon['code'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? LIMIT 1");
        $stmt->bind_param("s", $applied_coupon['code']);
        $stmt->execute();
        $coupon_row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($coupon_row) {
            if ((int)($coupon_row['is_active'] ?? 0) !== 1) {
                unset($_SESSION['applied_coupon']);
                $coupon_row = null;
            } else {
                $discount_amount = calc_discount($grand_total, $coupon_row);
            }
        } else {
            unset($_SESSION['applied_coupon']);
            $coupon_row = null;
        }
    } catch (Throwable $e) {
        unset($_SESSION['applied_coupon']);
        $coupon_row = null;
        $discount_amount = 0.0;
    }
}

$final_total = $grand_total - $discount_amount;
if ($final_total < 0) $final_total = 0.0;

// ===== PLACE ORDER (TRANSACTION) =====
if (isset($_POST['order_btn'])) {

    $name   = trim($_POST['name'] ?? '');
    $number = trim($_POST['number'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $method = trim($_POST['method'] ?? '');

    $flat    = trim($_POST['flat'] ?? '');
    $street  = trim($_POST['street'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $pin     = trim($_POST['pin_code'] ?? '');

    $address_raw = trim($flat . ', ' . $street . ', ' . $city . ', ' . $country . ' - ' . $pin, ", \t\n\r\0\x0B");
    $placed_on = date('Y-m-d H:i:s');

    if ($name === '' || $number === '' || $email === '' || $method === '' || $street === '' || $city === '' || $country === '') {
        $_SESSION['cart_message'] = 'Vui lòng nhập đầy đủ thông tin bắt buộc!';
        redirect_self();
    }

    try {
        $conn->begin_transaction();

        // 1) Load cart at order time
        $stmt = $conn->prepare("SELECT name, price, quantity FROM cart WHERE user_id = ? FOR UPDATE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        $cart_total = 0.0;
        $cart_products = [];

        while ($it = $res->fetch_assoc()) {
            $qty = (int)$it['quantity'];
            $price = (float)$it['price'];
            $cart_total += ($price * $qty);
            $cart_products[] = $it['name'] . ' (' . $qty . ')';
        }
        $stmt->close();

        if ($cart_total <= 0) {
            throw new Exception('Giỏ hàng của bạn đang trống!');
        }

        $total_products = implode(', ', $cart_products);

        // 2) Coupon lock + recompute
        $coupon_code_db = null;
        $discount_amount_db = 0.0;
        $final_total_db = $cart_total;
        $coupon_id = null;

        if ($coupon_row && !empty($coupon_row['id'])) {
            $coupon_id = (int)$coupon_row['id'];

            $stmt = $conn->prepare("
                SELECT id, code, usage_limit, used_count,
                       discount_type, discount_value, max_discount, min_order_total,
                       is_active
                FROM coupons
                WHERE id = ?
                FOR UPDATE
            ");
            $stmt->bind_param("i", $coupon_id);
            $stmt->execute();
            $locked = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$locked || (int)$locked['is_active'] !== 1) {
                $coupon_id = null;
            } else {
                $min_order = (float)($locked['min_order_total'] ?? 0);
                if ($cart_total < $min_order) {
                    $coupon_id = null;
                } else {
                    $usage_limit = (int)($locked['usage_limit'] ?? 0);
                    $used_count  = (int)($locked['used_count'] ?? 0);

                    if ($usage_limit > 0 && $used_count >= $usage_limit) {
                        throw new Exception('Mã giảm giá đã hết lượt sử dụng!');
                    }

                    $coupon_code_db = $locked['code'];
                    $discount_amount_db = calc_discount((float)$cart_total, $locked);
                    $final_total_db = $cart_total - $discount_amount_db;
                    if ($final_total_db < 0) $final_total_db = 0.0;
                }
            }
        }

        // đảm bảo null thật
        if (!$coupon_id) $coupon_code_db = null;

        // 3) INSERT ORDER — FIX CHUỖI TYPE + ĐÚNG SỐ BIẾN (12)
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, name, number, email, method, address,
                total_products, total_price, placed_on,
                coupon_code, discount_amount, final_price,
                payment_status
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                'pending'
            )
        ");

        // 12 biến => types dài 12: i + 6s + d + s + s + d + d
        $types = "issssssdssdd";
        $stmt->bind_param(
            $types,
            $user_id,
            $name,
            $number,
            $email,
            $method,
            $address_raw,
            $total_products,
            $cart_total,
            $placed_on,
            $coupon_code_db,
            $discount_amount_db,
            $final_total_db
        );
        $stmt->execute();
        $stmt->close();

        // 4) UPDATE used_count coupon (atomic)
        if ($coupon_id) {
            $stmt = $conn->prepare("
                UPDATE coupons
                SET used_count = used_count + 1
                WHERE id = ?
                  AND (usage_limit IS NULL OR usage_limit = 0 OR used_count < usage_limit)
            ");
            $stmt->bind_param("i", $coupon_id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected <= 0) {
                throw new Exception('Mã giảm giá đã hết lượt sử dụng!');
            }
        }

        // 5) Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        $_SESSION['cart_message'] = 'Đơn hàng đã được đặt thành công!';
        unset($_SESSION['applied_coupon']);

        redirect_self();

    } catch (Throwable $e) {
        try { $conn->rollback(); } catch (Throwable $ignored) {}
        $_SESSION['cart_message'] = $e->getMessage();
        redirect_self();
    }
}
?>
<!DOCTYPE html>
<html lang="en" style="height:auto; min-height:100%;">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEXGEN LAPTOP - Thanh Toán</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script>
        tailwind.config = {
            theme: { extend: {
                animation: {
                    'fade-in': 'fadeIn 0.5s ease-in-out',
                    'slide-up': 'slideUp 0.6s ease-out',
                    'slide-in-right': 'slideInRight 0.5s ease-out',
                },
                keyframes: {
                    fadeIn: {'0%': {opacity: '0'}, '100%': {opacity: '1'}},
                    slideUp: {'0%': {transform: 'translateY(30px)', opacity: '0'}, '100%': {transform: 'translateY(0)', opacity: '1'}},
                    slideInRight: {'0%': {transform: 'translateX(100%)', opacity: '0'}, '100%': {transform: 'translateX(0)', opacity: '1'}}
                }
            }}
        }
    </script>
</head>
<body class="bg-gray-50" style="height:auto; min-height:100%;">

<?php include 'header.php'; ?>

<!-- 2 form riêng cho coupon để tránh nested form -->
<form id="couponApplyForm" method="post" action=""></form>
<form id="couponRemoveForm" method="post" action=""></form>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-4 mt-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center space-x-2 text-sm">
            <a href="home.php" class="text-gray-600 hover:text-blue-600 transition-colors">Trang chủ</a>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            <a href="cart.php" class="text-gray-600 hover:text-blue-600 transition-colors">Giỏ hàng</a>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            <span class="text-blue-600 font-medium">Thanh toán</span>
        </nav>
    </div>
</div>

<!-- Steps -->
<section class="py-8 bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-center space-x-4 md:space-x-8">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center font-bold">
                    <i class="fas fa-check"></i>
                </div>
                <span class="ml-2 text-sm md:text-base font-medium text-gray-900">Giỏ hàng</span>
            </div>
            <div class="w-12 md:w-24 h-1 bg-blue-600"></div>
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
                <span class="ml-2 text-sm md:text-base font-medium text-gray-900">Thanh toán</span>
            </div>
            <div class="w-12 md:w-24 h-1 bg-gray-300"></div>
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-bold">3</div>
                <span class="ml-2 text-sm md:text-base font-medium text-gray-600">Hoàn tất</span>
            </div>
        </div>
    </div>
</section>

<section class="py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <form method="post" action="" id="checkoutForm">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Contact -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 animate-fade-in">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Thông tin liên hệ</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Họ và tên <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required placeholder="Nguyễn Văn A"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Số điện thoại <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="number" required placeholder="0912345678"
                                       pattern="\d{10}" maxlength="10"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" required placeholder="example@email.com"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 animate-fade-in" style="animation-delay:0.1s">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-map-marker-alt text-green-600"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Địa chỉ giao hàng</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Số nhà (Nếu có)</label>
                                <input type="text" name="flat" placeholder="Số 123"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Đường - Quận - Huyện <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="street" required placeholder="Đường Nguyễn Văn Cừ, Quận 1"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Thành phố <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="city" required placeholder="Thành phố Hồ Chí Minh"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Quốc gia <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="country" required value="Việt Nam" placeholder="Việt Nam"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mã bưu điện</label>
                                <input type="text" name="pin_code" placeholder="700000"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 animate-fade-in" style="animation-delay:0.2s">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-credit-card text-purple-600"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Phương thức thanh toán</h2>
                        </div>

                        <div class="space-y-4">
                            <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-all">
                                <input type="radio" name="method" value="Thanh toán khi giao hàng" checked
                                       class="w-5 h-5 text-blue-600 focus:ring-blue-500">
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-gray-900">Thanh toán khi giao hàng (COD)</span>
                                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Thanh toán bằng tiền mặt khi nhận hàng</p>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-all">
                                <input type="radio" name="method" value="ATM"
                                       class="w-5 h-5 text-blue-600 focus:ring-blue-500">
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-gray-900">Chuyển khoản ngân hàng</span>
                                        <i class="fas fa-university text-blue-600 text-xl"></i>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Chuyển khoản qua ATM hoặc Internet Banking</p>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-all">
                                <input type="radio" name="method" value="Momo"
                                       class="w-5 h-5 text-blue-600 focus:ring-blue-500">
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-gray-900">Ví điện tử MoMo</span>
                                        <i class="fas fa-wallet text-pink-600 text-xl"></i>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Thanh toán qua ví điện tử MoMo</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 animate-fade-in" style="animation-delay:0.3s">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" required class="mt-1 w-5 h-5 text-blue-600 focus:ring-blue-500 rounded">
                            <span class="ml-3 text-sm text-gray-700">
                                Tôi đã đọc và đồng ý với
                                <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">Điều khoản sử dụng</a>
                                và
                                <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">Chính sách bảo mật</a>
                                của NEXGEN LAPTOP
                            </span>
                        </label>
                    </div>

                </div>

                <!-- Right summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24 animate-slide-in-right">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Đơn hàng của bạn</h2>

                        <div class="space-y-4 mb-6 max-h-96 overflow-y-auto">
                            <?php if (count($cart_items) > 0): ?>
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="flex items-center space-x-4 pb-4 border-b border-gray-200">
                                        <div class="relative flex-shrink-0">
                                            <img src="uploaded_img/<?php echo htmlspecialchars($item['image']); ?>"
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                 class="w-20 h-20 object-cover rounded-lg">
                                            <span class="absolute -top-2 -right-2 w-6 h-6 bg-blue-600 text-white text-xs rounded-full flex items-center justify-center font-bold">
                                                <?php echo (int)$item['quantity']; ?>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900 text-sm mb-1">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                <?php echo number_format((float)$item['price'], 0, ',', '.'); ?> VNĐ × <?php echo (int)$item['quantity']; ?>
                                            </p>
                                            <p class="text-sm font-bold text-blue-600 mt-1">
                                                <?php echo number_format((float)$item['total_price'], 0, ',', '.'); ?> VNĐ
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-shopping-cart text-gray-300 text-5xl mb-4"></i>
                                    <p class="text-gray-600">Giỏ hàng của bạn đang trống!</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Coupon -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-900">Mã giảm giá</span>
                                <?php if (!empty($coupon_row)): ?>
                                    <span class="text-sm text-green-600 font-semibold"><?php echo htmlspecialchars($coupon_row['code']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="flex gap-2">
                                <input type="text" name="coupon_code" form="couponApplyForm"
                                       value="<?php echo !empty($coupon_row) ? htmlspecialchars($coupon_row['code']) : ''; ?>"
                                       placeholder="Nhập mã (VD: SALE10)"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <button type="submit" name="apply_coupon" form="couponApplyForm"
                                        class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-black transition">
                                    Áp dụng
                                </button>
                            </div>

                            <?php if (!empty($coupon_row)): ?>
                                <button type="submit" name="remove_coupon" form="couponRemoveForm"
                                        class="mt-2 text-sm text-red-600 hover:text-red-800">
                                    Gỡ mã
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
                            <div class="flex justify-between items-center text-gray-600">
                                <span>Tạm tính:</span>
                                <span class="font-semibold"><?php echo number_format((float)$grand_total, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                            <div class="flex justify-between items-center text-gray-600">
                                <span>Phí vận chuyển:</span>
                                <span class="font-semibold text-green-600">Miễn phí</span>
                            </div>
                            <div class="flex justify-between items-center text-gray-600">
                                <span>Giảm giá:</span>
                                <span class="font-semibold text-green-600">- <?php echo number_format((float)$discount_amount, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                        </div>

                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-900">Tổng cộng:</span>
                                <span class="text-3xl font-bold text-blue-600">
                                    <?php echo number_format((float)$final_total, 0, ',', '.'); ?> VNĐ
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">Đã bao gồm VAT (nếu có)</p>
                        </div>

                        <button type="submit" name="order_btn"
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 px-6 rounded-lg font-bold text-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg <?php echo ($grand_total <= 0 ? 'opacity-50 cursor-not-allowed' : ''); ?>"
                                <?php echo ($grand_total <= 0 ? 'disabled' : ''); ?>>
                            <i class="fas fa-check-circle mr-2"></i>
                            Đặt hàng ngay
                        </button>
                    </div>
                </div>

            </div>
        </form>

    </div>
</section>

<?php include 'footer.php'; ?>

<!-- Toast -->
<div id="toast" class="fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300">
    <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3">
        <i class="fas fa-check-circle text-xl"></i>
        <span id="toastMessage">OK</span>
        <button onclick="hideToast()" class="ml-4 text-white hover:text-gray-200">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<script>
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    toastMessage.textContent = message;

    const toastContainer = toast.querySelector('div');
    toastContainer.className =
        (type === 'success')
        ? 'bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3'
        : 'bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3';

    toast.classList.remove('translate-x-full');
    setTimeout(() => hideToast(), 3000);
}
function hideToast() {
    document.getElementById('toast').classList.add('translate-x-full');
}

<?php if (isset($_SESSION['cart_message'])): ?>
    showToast("<?php echo addslashes($_SESSION['cart_message']); ?>",
              "<?php echo (strpos($_SESSION['cart_message'], 'thành công') !== false ? 'success' : 'error'); ?>");
    <?php unset($_SESSION['cart_message']); ?>
<?php endif; ?>

document.addEventListener('submit', function(e) {
    const btn = e.submitter;
    if (!btn || btn.name !== 'order_btn') return;

    const phone = document.querySelector('input[name="number"]')?.value || '';
    if (phone.length !== 10) {
        e.preventDefault();
        showToast('Số điện thoại phải có 10 chữ số!', 'error');
    }
}, true);

const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.style.animationPlayState = 'running';
    });
}, observerOptions);

document.querySelectorAll('.animate-fade-in, .animate-slide-in-right').forEach(el => {
    el.style.animationPlayState = 'paused';
    observer.observe(el);
});
</script>

<script src="js/script.js"></script>
</body>
</html>
