<?php
if (isset($message)) { // Kiểm tra message tồn tại hay không
    foreach ($message as $message) { // Vòng lặp từng phần tử trong message
        echo '
        <div class="message">
            <span>' . $message . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<header class="header">

    <div class="header-1">
        <div class="flex">
        
            <img src="../project/images/logo.jpg" alt="">
            <p><a href="login.php">Đăng Nhập</a> | <a href="register.php">Đăng Xuất</a> </p>
        </div>
    </div>

    <div class="header-2">
        <div class="flex">
            <a href="home.php" class="logo">NEXGEN LAPTOP</a>

            <nav class="navbar">
                <a href="home.php">TRANG CHỦ</a>
                <a href="shop.php">SHOP</a>
                <a href="about.php">GIỚI THIỆU</a>
                <a href="contact.php">LIÊN HỆ</a>
                <a href="orders.php">ĐẶT HÀNG</a>
            </nav>

            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <a href="search_page.php" class="fas fa-search"></a>
                <div id="user-btn" class="fas fa-user"></div>
                <?php
                // Kiểm tra số lượng sản phẩm trong giỏ hàng
                $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                $cart_rows_number = mysqli_num_rows($select_cart_number);
                ?>
                <a href="cart.php"> <i class="fas fa-shopping-cart"></i> <span>(<?php echo $cart_rows_number; ?>)</span> </a>
            </div>

            <!-- Kiểm tra và hiển thị thông tin người dùng nếu đã đăng nhập -->
            <?php if (isset($_SESSION['user_name']) && isset($_SESSION['user_email'])): ?>
            <div class="user-box">
                <p>Username : <span><?php echo $_SESSION['user_name']; ?></span></p>
                <p>Email : <span><?php echo $_SESSION['user_email']; ?></span></p>
                <a href="logout.php" class="delete-btn">Đăng Xuất</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

</header>