<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message bg-blue-100 text-blue-700 px-4 py-2 rounded-lg shadow mb-3 flex justify-between items-center">
            <span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times cursor-pointer" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<header class="bg-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-laptop text-white text-xl"></i>
                </div>
                <a href="home.php" class="text-2xl font-bold text-gray-800 hover:text-blue-600 transition-colors">
                    NEXGEN LAPTOP
                </a>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex space-x-8">
                <a href="home.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Trang chủ</a>
                <a href="shop.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Sản phẩm</a>
                <a href="about.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Về chúng tôi</a>
                <a href="contact.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Liên hệ</a>
                <a href="orders.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Đơn hàng</a>
            </nav>

            <!-- Icons (cart + user) -->
            <div class="flex items-center space-x-4">
                <?php
                // Đếm số lượng sản phẩm trong giỏ hàng
                $cart_rows_number = 0;
                if (isset($user_id)) {
                    $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                    $cart_rows_number = mysqli_num_rows($select_cart_number);
                }
                ?>

                <!-- Giỏ hàng -->
                <a href="cart.php" class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        <?php echo $cart_rows_number; ?>
                    </span>
                </a>

                <!-- Người dùng -->
                <?php if (isset($_SESSION['user_name']) && isset($_SESSION['user_email'])): ?>
                    <div class="relative" id="user-menu">
                        <button class="p-2 text-gray-700 hover:text-blue-600 transition-colors flex items-center gap-2" id="user-btn">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="hidden md:inline font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </button>
                        <!-- Dropdown -->
                        <div id="user-dropdown" class="absolute right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-4 hidden w-56 transition-opacity duration-300">
                            <p class="text-sm text-gray-700 mb-2">
                                <span class="font-semibold">Email:</span> <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                            </p>
                            <a href="logout.php" class="block text-center bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg transition-colors">
                                Đăng xuất
                            </a>
                        </div>
                    </div>

                    <script>
                        const userBtn = document.getElementById('user-btn');
                        const userDropdown = document.getElementById('user-dropdown');
                        const userMenu = document.getElementById('user-menu');
                        let hideTimeout;

                        userMenu.addEventListener('mouseenter', () => {
                            clearTimeout(hideTimeout);
                            userDropdown.classList.remove('hidden');
                            userDropdown.style.opacity = '1';
                        });

                        userMenu.addEventListener('mouseleave', () => {
                            hideTimeout = setTimeout(() => {
                                userDropdown.style.opacity = '0';
                                setTimeout(() => userDropdown.classList.add('hidden'), 300); // chờ hiệu ứng mờ
                            }, 5000); // 5 giây
                        });
                    </script>
                <?php else: ?>
                    <div class="flex items-center space-x-3">
                        <a href="login.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Đăng nhập</a>
                        <span>|</span>
                        <a href="register.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Đăng ký</a>
                    </div>
                <?php endif; ?>
                <!-- Nút menu (mobile) -->
                <button class="md:hidden p-2 text-gray-700 hover:text-blue-600">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>
</header>
