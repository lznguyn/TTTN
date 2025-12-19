<?php
include 'config.php';
session_start();

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// ====== LOAD CART (để có grand_total trước) ======
$cart_items  = [];
$grand_total = 0;

$select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
if (mysqli_num_rows($select_cart) > 0) {
    while ($row = mysqli_fetch_assoc($select_cart)) {
        $row['total_price'] = $row['price'] * $row['quantity'];
        $grand_total       += $row['total_price'];
        $cart_items[]       = $row;
    }
}

// ===== Coupon apply/remove =====
$discount_amount = 0;
$applied_coupon  = $_SESSION['applied_coupon'] ?? null;

function calc_discount($grand_total, $coupon_row) {
    $type = $coupon_row['discount_type'];
    $val  = (float)$coupon_row['discount_value'];

    $d = ($type === 'percent') ? ($grand_total * ($val / 100.0)) : $val;

    // max_discount (optional)
    if (!empty($coupon_row['max_discount'])) {
        $maxD = (float)$coupon_row['max_discount'];
        if ($d > $maxD) $d = $maxD;
    }

    // never exceed total
    if ($d > $grand_total) $d = $grand_total;

    return (float)$d;
}

// APPLY
if (isset($_POST['apply_coupon'])) {
    $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
    unset($_SESSION['applied_coupon']); // reset

    if ($code !== '') {
        $stmt = $conn->prepare("
            SELECT * FROM coupons
            WHERE code = ?
              AND is_active = 1
              AND (start_at IS NULL OR start_at <= NOW())
              AND (end_at IS NULL OR end_at >= NOW())
            LIMIT 1
        ");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $res    = $stmt->get_result();
        $coupon = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$coupon) {
            $_SESSION['cart_message'] = 'Mã giảm giá không hợp lệ hoặc đã hết hạn!';
        } else if ($grand_total < (float)$coupon['min_order_total']) {
            $_SESSION['cart_message'] = 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã!';
        } else if (!empty($coupon['usage_limit']) && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
            $_SESSION['cart_message'] = 'Mã giảm giá đã hết lượt sử dụng!';
        } else {
            $_SESSION['applied_coupon'] = [
                'code' => $coupon['code'],
                'id'   => (int)$coupon['id'],
            ];
            $_SESSION['cart_message'] = 'Áp dụng mã giảm giá thành công!';
        }
    } else {
        $_SESSION['cart_message'] = 'Vui lòng nhập mã giảm giá!';
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// REMOVE
if (isset($_POST['remove_coupon'])) {
    unset($_SESSION['applied_coupon']);
    $_SESSION['cart_message'] = 'Đã gỡ mã giảm giá!';
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Load coupon detail to compute discount
$coupon_row = null;
if ($applied_coupon && !empty($applied_coupon['code'])) {
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code=? LIMIT 1");
    $stmt->bind_param('s', $applied_coupon['code']);
    $stmt->execute();
    $res = $stmt->get_result();
    $coupon_row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($coupon_row) {
        $discount_amount = calc_discount($grand_total, $coupon_row);
    } else {
        unset($_SESSION['applied_coupon']);
    }
}

$final_total = $grand_total - $discount_amount;
if ($final_total < 0) $final_total = 0;


// ===== XỬ LÝ ĐẶT HÀNG (sau khi đã có coupon_row/discount/final_total) =====
if (isset($_POST['order_btn'])) {

    $name   = mysqli_real_escape_string($conn, $_POST['name']);
    $number = $_POST['number'];
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $method = mysqli_real_escape_string($conn, $_POST['method']);

    $flat    = isset($_POST['flat']) ? $_POST['flat'] : '';
    $street  = $_POST['street'];
    $city    = $_POST['city'];
    $country = $_POST['country'];
    $pin     = isset($_POST['pin_code']) ? $_POST['pin_code'] : '';

    $address = mysqli_real_escape_string(
        $conn,
        trim($flat . ', ' . $street . ', ' . $city . ', ' . $country . ' - ' . $pin, ', ')
    );

    $placed_on = date('d-M-Y');

    // Tính tổng giỏ hàng & danh sách sản phẩm (đảm bảo đúng lúc order)
    $cart_total    = 0;
    $cart_products = [];

    $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
    if (mysqli_num_rows($cart_query) > 0) {
        while ($cart_item = mysqli_fetch_assoc($cart_query)) {
            $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ')';
            $sub_total       = $cart_item['price'] * $cart_item['quantity'];
            $cart_total     += $sub_total;
        }
    }

    $total_products = implode(', ', $cart_products);

    if ($cart_total == 0) {
        $_SESSION['cart_message'] = 'Giỏ hàng của bạn đang trống!';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    // Check đơn hàng trùng (giữ logic cũ của bạn)
    $order_query = mysqli_query(
        $conn,
        "SELECT * FROM `orders`
         WHERE user_id = '$user_id'
           AND name = '$name'
           AND number = '$number'
           AND email = '$email'
           AND method = '$method'
           AND address = '$address'
           AND total_products = '$total_products'
           AND total_price = '$cart_total'"
    ) or die('query failed');

    if (mysqli_num_rows($order_query) > 0) {
        $_SESSION['cart_message'] = 'Đơn hàng đã được đặt trước đó!';
    } else {

        $coupon_code        = $coupon_row ? $coupon_row['code'] : null;
        $discount_amount_db = (float)$discount_amount;
        $final_total_db     = (float)$final_total;

        mysqli_query(
            $conn,
            "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on, coupon_code, discount_amount, final_price)
             VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on',
                    " . ($coupon_code ? "'" . mysqli_real_escape_string($conn, $coupon_code) . "'" : "NULL") . ",
                    '$discount_amount_db', '$final_total_db')"
        ) or die('query failed');

        mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');

        $_SESSION['cart_message'] = 'Đơn hàng đã được đặt thành công!';
        unset($_SESSION['applied_coupon']);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" style="height: auto; min-height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXGEN LAPTOP - Thanh Toán</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-50" style="height: auto; min-height: 100%;">

<?php include 'header.php'; ?>

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

<section class="py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- FORM ĐẶT HÀNG (chỉ 1 form chính, không nested form) -->
        <form method="post" action="" id="checkoutForm">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">

                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
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
                                <input type="text" name="name" required
                                       placeholder="Nguyễn Văn A"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Số điện thoại <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="number" required
                                       placeholder="0912345678"
                                       pattern="\d{10}" maxlength="10"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" required
                                       placeholder="example@email.com"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-map-marker-alt text-green-600"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Địa chỉ giao hàng</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Số nhà (Nếu có)
                                </label>
                                <input type="text" name="flat"
                                       placeholder="Số 123"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Đường - Quận - Huyện <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="street" required
                                       placeholder="Đường Nguyễn Văn Cừ, Quận 1"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Thành phố <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="city" required
                                       placeholder="Thành phố Hồ Chí Minh"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Quốc gia <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="country" required
                                       placeholder="Việt Nam"
                                       value="Việt Nam"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Mã bưu điện
                                </label>
                                <input type="text" name="pin_code"
                                       placeholder="700000"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
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

                    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" required
                                   class="mt-1 w-5 h-5 text-blue-600 focus:ring-blue-500 rounded">
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

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
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
                                                <?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ × <?php echo (int)$item['quantity']; ?>
                                            </p>
                                            <p class="text-sm font-bold text-blue-600 mt-1">
                                                <?php echo number_format($item['total_price'], 0, ',', '.'); ?> VNĐ
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

                        <!-- Coupon (TÁCH FORM RIÊNG, KHÔNG NESTED FORM) -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-900">Mã giảm giá</span>
                                <?php if (!empty($coupon_row)): ?>
                                    <span class="text-sm text-green-600 font-semibold">
                                        <?php echo htmlspecialchars($coupon_row['code']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- APPLY FORM -->
                            <form method="post" class="flex gap-2">
                                <input type="text" name="coupon_code"
                                       value="<?php echo !empty($coupon_row) ? htmlspecialchars($coupon_row['code']) : ''; ?>"
                                       placeholder="Nhập mã (VD: SALE10)"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <button type="submit" name="apply_coupon"
                                        class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-black transition">
                                    Áp dụng
                                </button>
                            </form>

                            <!-- REMOVE FORM -->
                            <?php if (!empty($coupon_row)): ?>
                                <form method="post" class="mt-2">
                                    <button type="submit" name="remove_coupon"
                                            class="text-sm text-red-600 hover:text-red-800">
                                        Gỡ mã
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
                            <div class="flex justify-between items-center text-gray-600">
                                <span>Tạm tính:</span>
                                <span class="font-semibold"><?php echo number_format($grand_total, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                            <div class="flex justify-between items-center text-gray-600">
                                <span>Phí vận chuyển:</span>
                                <span class="font-semibold text-green-600">Miễn phí</span>
                            </div>
                            <div class="flex justify-between items-center text-gray-600">
                                <span>Giảm giá:</span>
                                <span class="font-semibold text-green-600">- <?php echo number_format($discount_amount, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                        </div>

                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-900">Tổng cộng:</span>
                                <span class="text-3xl font-bold text-blue-600">
                                    <?php echo number_format($final_total, 0, ',', '.'); ?> VNĐ
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">Đã bao gồm VAT (nếu có)</p>
                        </div>

                        <button type="submit" name="order_btn"
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 px-6 rounded-lg font-bold text-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg <?php echo ($grand_total <= 0 ? 'opacity-50 cursor-not-allowed' : ''); ?>"
                                <?php echo ($final_total <= 0 ? 'disabled' : ''); ?>>
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
        <span id="toastMessage">Đặt hàng thành công!</span>
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
        if (type === 'success') {
            toastContainer.className = 'bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3';
        } else {
            toastContainer.className = 'bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3';
        }

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

    // ✅ Validate ONLY when clicking "Đặt hàng ngay"
    document.addEventListener('submit', function(e) {
        const btn = e.submitter;
        if (!btn) return;
        if (btn.name !== 'order_btn') return; // không validate khi apply/remove coupon

        const phoneEl = document.querySelector('input[name="number"]');
        if (!phoneEl) return;

        const phone = phoneEl.value || '';
        if (phone.length !== 10) {
            e.preventDefault();
            showToast('Số điện thoại phải có 10 chữ số!', 'error');
            return false;
        }
    }, true);
</script>

<script src="js/script.js"></script>
</body>
</html>
