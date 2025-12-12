<?php
// Hiển thị message (nếu có)
if (isset($message)) {
    // Ép về mảng cho chắc
    $messages = is_array($message) ? $message : [$message];

    foreach ($messages as $msg) {
        echo '
        <div class="message">
            <span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<header class="header">

    <div class="flex header-inner">

        <!-- Logo -->
        <a href="admin_page.php" class="logo">
            <i class="fas fa-laptop-code"></i>
            <span class="logo-main">Admin</span><span class="logo-sub">LAPTOP</span>
        </a>

        <!-- Navbar -->
        <nav class="navbar">
            <a href="admin_page.php">Trang chủ</a>
            <a href="admin_products.php">Sản phẩm</a>
            <a href="admin_orders.php">Đặt hàng</a>
            <a href="admin_users.php">Users</a>
            <a href="admin_contacts.php">Tin nhắn</a>

            <div class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">
                    Cấu hình
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <div class="dropdown-content">
                    <a href="admin_price.php">Cấu hình giá</a>
                    <a href="admin_categories.php">Cấu hình laptop</a>
                </div>
            </div>
        </nav>

        <!-- Icons + account -->
        <div class="header-right">
            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="user-btn" class="fas fa-user"></div>
            </div>

            <div class="account-box">
                <p>Tên người dùng : <span><?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?></span></p>
                <p>Email : <span><?php echo htmlspecialchars($_SESSION['admin_email'] ?? ''); ?></span></p>
                <a href="logout.php" class="delete-btn">Đăng xuất</a>
            </div>
        </div>

    </div>

</header>
