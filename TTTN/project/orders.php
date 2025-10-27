<?php
// Kết nối cơ sở dữ liệu và bắt đầu phiên làm việc
include 'config.php';
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập
    exit; // Dừng mã để không tiếp tục xử lý trang
}

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ phiên
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>

    <!-- Liên kết với Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Liên kết với CSS tùy chỉnh -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
/* Cấu hình cơ bản */
body {
    font-family: 'Roboto', sans-serif;
    color: #333;
    margin: 0;
    padding: 0;
    background: linear-gradient(to bottom, #ffffff, #f3f4f6);
}



</style>


</style>

    </style>
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="placed-orders">
        <h1 class="title">Đơn đặt hàng</h1>
        <div class="order-container">
            <?php
            // Lấy tất cả các đơn hàng của người dùng
            $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE user_id = '$user_id'") or die('query failed');
            if (mysqli_num_rows($order_query) > 0) {
                // Hiển thị từng đơn hàng
                while ($fetch_orders = mysqli_fetch_assoc($order_query)) {
            ?>
            <div class="order-box">
    <div class="order-info">
        <div class="order-details">
            <p>Ngày đặt hàng:
                <span><?php echo date('d/m/Y', strtotime($fetch_orders['placed_on'])); ?></span>
            </p>
            <p>Tên người đặt hàng: <span><?php echo $fetch_orders['name']; ?></span></p>
            <p>Số điện thoại: <span><?php echo $fetch_orders['number']; ?></span></p>
            <p>Email: <span><?php echo $fetch_orders['email']; ?></span></p>
            <p>Địa chỉ: <span><?php echo $fetch_orders['address']; ?></span></p>
            <p>Phương thức thanh toán: <span><?php echo $fetch_orders['method']; ?></span></p>
            <p>Tên sản phẩm: <span><?php echo $fetch_orders['total_products']; ?></span></p>
            <p>Tổng: <span><?php echo number_format($fetch_orders['total_price'], 0, ',', '.'); ?> VNĐ</span></p>
            <p>Tình trạng thanh toán:
                <span
                    class="payment-status <?php echo ($fetch_orders['payment_status'] == 'Đang duyệt') ? 'pending' : 'completed'; ?>">
                    <?php echo $fetch_orders['payment_status']; ?>
                </span>
            </p>
        </div>

        
        </div>
    </div>
</div>

            <?php
                }
            } else {
                echo '<p class="empty">Chưa có đơn hàng nào!</p>';
            }
            ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <!-- Liên kết với JS tùy chỉnh -->
    <script src="js/script.js"></script>
</body>

</html>
