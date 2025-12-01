<?php
include 'config.php';

session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$messages = [];

// Xử lý cập nhật số lượng
if (isset($_POST['update_cart'])) {
    $cart_id = (int)$_POST['cart_id'];
    $cart_quantity = max(1, (int)$_POST['cart_quantity']);

    mysqli_query($conn, "UPDATE `cart` SET quantity = '$cart_quantity' WHERE id = '$cart_id' AND user_id = '$user_id'") or die('query failed');
    $messages[] = 'Số lượng giỏ hàng đã được cập nhật!';
}

// Xóa 1 sản phẩm
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$delete_id' AND user_id = '$user_id'") or die('query failed');
    header('Location: cart.php');
    exit();
}

// Xóa tất cả sản phẩm
if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
    header('Location: cart.php');
    exit();
}

// Lấy dữ liệu giỏ hàng
$grand_total = 0;
$total_items = 0;
$cart_items = [];

$select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
if (mysqli_num_rows($select_cart) > 0) {
    while ($row = mysqli_fetch_assoc($select_cart)) {
        $sub_total = $row['price'] * $row['quantity'];
        $row['sub_total'] = $sub_total;
        $cart_items[] = $row;

        $grand_total += $sub_total;
        $total_items += $row['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXGEN LAPTOP - Giỏ Hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'slide-in': 'slideIn 0.4s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">

<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="bg-gray-100 mt-16 py-4">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center space-x-2 text-sm">
            <a href="home.php" class="text-gray-600 hover:text-blue-600 transition-colors">Trang chủ</a>
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            <span class="text-blue-600 font-medium">Giỏ hàng</span>
        </nav>
    </div>
</div>

<!-- Page Header -->
<section class="py-12 bg-gradient-to-r from-blue-600 to-purple-700 text-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="text-center md:text-left mb-6 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-slide-up">Giỏ Hàng Của Bạn</h1>
                <p class="text-lg md:text-xl text-blue-100 animate-slide-up" style="animation-delay: 0.2s">
                    Xem lại và hoàn tất đơn hàng của bạn
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold">
                        <?php echo (int)$total_items; ?>
                    </div>
                    <div class="text-sm text-blue-100">Sản phẩm</div>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold">
                        <?php echo number_format($grand_total, 0, ',', '.'); ?>
                    </div>
                    <div class="text-sm text-blue-100">Tổng tiền (VNĐ)</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cart Content -->
<section class="py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-6">
                <?php if (count($cart_items) > 0): ?>

                    <?php foreach ($cart_items as $item): ?>
                        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-2xl transition-all duration-300 animate-fade-in">
                            <div class="flex flex-col sm:flex-row gap-6">
                                <!-- Product Image -->
                                <div class="relative flex-shrink-0">
                                    <img src="uploaded_img/<?php echo htmlspecialchars($item['image']); ?>"
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         class="w-full sm:w-40 h-40 object-cover rounded-lg">
                                    <button onclick="confirmDelete('cart.php?delete=<?php echo $item['id']; ?>')"
                                            class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Product Details -->
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </h3>
                                        <p class="text-gray-600 text-sm">
                                            Mã sản phẩm: #<?php echo (int)$item['id']; ?>
                                        </p>
                                        <div class="flex items-center space-x-2 mt-2">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-600 rounded text-xs font-semibold">Chính hãng</span>
                                            <span class="px-2 py-1 bg-green-100 text-green-600 rounded text-xs font-semibold">Còn hàng</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                        <!-- Quantity Controls -->
                                        <form method="post" class="flex items-center space-x-3">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                            <label class="text-sm font-medium text-gray-700">Số lượng:</label>
                                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                                <button type="button" onclick="decreaseQuantity(this)" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors">
                                                    <i class="fas fa-minus text-sm"></i>
                                                </button>
                                                <input type="number"
                                                       name="cart_quantity"
                                                       value="<?php echo (int)$item['quantity']; ?>"
                                                       min="1"
                                                       class="w-16 text-center border-x border-gray-300 py-2 focus:outline-none">
                                                <button type="button" onclick="increaseQuantity(this)" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors">
                                                    <i class="fas fa-plus text-sm"></i>
                                                </button>
                                            </div>
                                            <button type="submit" name="update_cart"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                                <i class="fas fa-sync-alt mr-1"></i>
                                                Cập nhật
                                            </button>
                                        </form>

                                        <!-- Price -->
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600">Đơn giá</p>
                                            <p class="text-xl font-bold text-blue-600">
                                                <?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Tổng:
                                                <span class="font-semibold text-gray-900">
                                                    <?php echo number_format($item['sub_total'], 0, ',', '.'); ?> VNĐ
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Clear Cart Button -->
                    <div class="flex justify-center">
                        <button onclick="confirmDeleteAll('cart.php?delete_all')"
                                class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-semibold">
                            <i class="fas fa-trash-alt mr-2"></i>
                            Xóa tất cả sản phẩm
                        </button>
                    </div>

                <?php else: ?>
                    <!-- Empty Cart -->
                    <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                        <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-shopping-cart text-gray-400 text-5xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Giỏ hàng trống</h3>
                        <p class="text-gray-600 mb-8">Bạn chưa có sản phẩm nào trong giỏ hàng. Hãy khám phá các sản phẩm của chúng tôi!</p>
                        <a href="shop.php" class="inline-flex items-center px-8 py-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Tiếp tục mua sắm
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24 animate-slide-in">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Tóm tắt đơn hàng</h3>

                    <!-- Promo Code -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mã giảm giá</label>
                        <div class="flex space-x-2">
                            <input type="text"
                                   placeholder="Nhập mã giảm giá"
                                   class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button class="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors font-semibold">
                                Áp dụng
                            </button>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="space-y-4 mb-6 pb-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Tạm tính (<?php echo (int)$total_items; ?> sản phẩm):</span>
                            <span class="font-semibold text-gray-900">
                                <?php echo number_format($grand_total, 0, ',', '.'); ?> VNĐ
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Giảm giá:</span>
                            <span class="font-semibold text-green-600">- 0 VNĐ</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Phí vận chuyển:</span>
                            <span class="font-semibold text-green-600">Miễn phí</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-gray-900">Tổng cộng:</span>
                            <span class="text-3xl font-bold text-blue-600">
                                <?php echo number_format($grand_total, 0, ',', '.'); ?> VNĐ
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Đã bao gồm VAT (nếu có)</p>
                    </div>

                    <!-- Benefits -->
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>Miễn phí vận chuyển toàn quốc</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>Bảo hành chính hãng 12-36 tháng</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>Đổi trả trong 7 ngày</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>Hỗ trợ kỹ thuật 24/7</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <a href="<?php echo ($grand_total > 0) ? 'checkout.php' : 'javascript:void(0)'; ?>"
                           class="block w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 px-6 rounded-lg font-bold text-center hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg <?php echo ($grand_total <= 0 ? 'opacity-50 cursor-not-allowed' : ''); ?>">
                            <i class="fas fa-credit-card mr-2"></i>
                            Tiến hành thanh toán
                        </a>
                        <a href="shop.php"
                           class="block w-full bg-gray-100 text-gray-700 py-4 px-6 rounded-lg font-semibold text-center hover:bg-gray-200 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Tiếp tục mua sắm
                        </a>
                    </div>

                    <!-- Security Badge -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                            <i class="fas fa-lock text-green-500"></i>
                            <span>Thanh toán an toàn & bảo mật</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Recommended Products (static demo) -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Có thể bạn quan tâm</h2>
            <p class="text-gray-600">Khám phá thêm các sản phẩm tương tự</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Demo product cards (có thể thay bằng data thật sau) -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <img src="https://images.unsplash.com/photo-1541807084-5c52b6b3adef?auto=format&fit=crop&w=400&q=80"
                     alt="HP Spectre x360" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h4 class="font-bold text-gray-900 mb-2">HP Spectre x360</h4>
                    <p class="text-blue-600 font-bold mb-3">28.990.000 VNĐ</p>
                    <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                        Thêm vào giỏ
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <img src="https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?auto=format&fit=crop&w=400&q=80"
                     alt="Lenovo ThinkPad X1" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h4 class="font-bold text-gray-900 mb-2">Lenovo ThinkPad X1</h4>
                    <p class="text-blue-600 font-bold mb-3">35.990.000 VNĐ</p>
                    <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                        Thêm vào giỏ
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <img src="https://images.unsplash.com/photo-1484788984921-03950022c9ef?auto=format&fit=crop&w=400&q=80"
                     alt="Surface Laptop 5" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h4 class="font-bold text-gray-900 mb-2">Surface Laptop 5</h4>
                    <p class="text-blue-600 font-bold mb-3">26.990.000 VNĐ</p>
                    <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                        Thêm vào giỏ
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <img src="https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=400&q=80"
                     alt="Acer Swift 3" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h4 class="font-bold text-gray-900 mb-2">Acer Swift 3</h4>
                    <p class="text-blue-600 font-bold mb-3">18.990.000 VNĐ</p>
                    <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                        Thêm vào giỏ
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<!-- Notification Toast -->
<div id="toast" class="fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300">
    <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3">
        <i class="fas fa-check-circle text-xl"></i>
        <span id="toastMessage">Cập nhật thành công!</span>
        <button onclick="hideToast()" class="ml-4 text-white hover:text-gray-200">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<script>
    // Quantity controls
    function decreaseQuantity(button) {
        const input = button.parentElement.querySelector('input[type="number"]');
        if (input.value > 1) {
            input.value = parseInt(input.value) - 1;
        }
    }

    function increaseQuantity(button) {
        const input = button.parentElement.querySelector('input[type="number"]');
        input.value = parseInt(input.value) + 1;
    }

    // Delete confirmations
    function confirmDelete(url) {
        if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            window.location.href = url;
        }
    }

    function confirmDeleteAll(url) {
        if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?')) {
            window.location.href = url;
        }
    }

    // Toast notification
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');

        toastMessage.textContent = message;

        const toastContainer = toast.querySelector('div');
        if (type === 'success') {
            toastContainer.className = 'bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3';
        } else if (type === 'error') {
            toastContainer.className = 'bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3';
        }

        toast.classList.remove('translate-x-full');

        setTimeout(() => {
            hideToast();
        }, 3000);
    }

    function hideToast() {
        const toast = document.getElementById('toast');
        toast.classList.add('translate-x-full');
    }

    // Show PHP messages (from $messages)
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            showToast("<?php echo addslashes($msg); ?>", 'success');
        <?php endforeach; ?>
    <?php endif; ?>

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-fade-in').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
</script>

<script src="js/script.js"></script>
</body>
</html>
