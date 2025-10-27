<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (isset($_POST['order_btn'])) {//neu nguoi dung nhan nut dat hang

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $number = $_POST['number'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $method = mysqli_real_escape_string($conn, $_POST['method']);
    $address = mysqli_real_escape_string($conn, $_POST['flat'] . ', ' . $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['country'] . ' - ' . $_POST['pin_code']);
    $placed_on = date('d-M-Y'); //lay ngay hien tai luu ngay dat hang

    $cart_total = 0;
    $cart_products[] = '';

    $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   // Nếu giỏ hàng không rỗng
   if (mysqli_num_rows($cart_query) > 0) {
    // Lặp qua từng sản phẩm trong giỏ hàng
    while ($cart_item = mysqli_fetch_assoc($cart_query)) {
        // Thêm sản phẩm và số lượng vào mảng `cart_products`
        $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ') ';
        
        // Tính thành tiền cho sản phẩm này dựa trên giá và số lượng
        $sub_total = ($cart_item['price'] * $cart_item['quantity']);
        
        // Cộng thành tiền của sản phẩm vào tổng giá trị của giỏ hàng
        $cart_total += $sub_total;
    }
}

    $total_products = implode(', ', $cart_products);

    $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE name = '$name' AND number = '$number' AND email = '$email' AND method = '$method' AND address = '$address' AND total_products = '$total_products' AND total_price = '$cart_total'") or die('query failed');
// Ghép danh sách các sản phẩm trong giỏ hàng thành một chuỗi để lưu vào cơ sở dữ liệu
    $total_products = implode(', ', $cart_products);

    // Kiểm tra nếu đơn hàng đã tồn tại trong cơ sở dữ liệu (có các thông tin giống nhau)
    $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE name = '$name' AND number = '$number' AND email = '$email' AND method = '$method' AND address = '$address' AND total_products = '$total_products' AND total_price = '$cart_total'") or die('query failed');

    // Nếu giỏ hàng rỗng, đặt thông báo lỗi vào session
    if ($cart_total == 0) {
        $_SESSION['cart_message'] = 'Giỏ hàng của bạn đang trống!';
    } else {
        // Nếu đơn hàng đã tồn tại, đặt thông báo rằng đơn hàng đã được đặt trước đó
        if (mysqli_num_rows($order_query) > 0) {
            $_SESSION['cart_message'] = 'Đơn hàng đã được đặt trước đó!';
        } else {
            // Nếu đơn hàng chưa tồn tại, thêm đơn hàng mới vào cơ sở dữ liệu
            mysqli_query($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on')") or die('query failed');
            
            // Đặt thông báo thành công vào session
            $_SESSION['cart_message'] = 'Đơn hàng đã được đặt thành công!';
            
            // Xóa các sản phẩm trong giỏ hàng của người dùng sau khi đặt hàng thành công
            mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
        }
    }


    // Redirect to the same page to display message
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS File Link -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        body{
            background-color:darkgrey;
        }
    .display-order {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .cart-item {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .cart-item img {
        max-width: 100%;
        /* Ensure image fits within the container */
        height: 25rem;
    }


    .grand-total {
        font-size: 1.2em;
        font-weight: bold;
        margin-top: 20px;
        text-align: center;
        font-size: 3rem;
    }
    </style>
</head>

<body>

    <?php include 'header.php'; ?>

  

    <section class="display-order">
        <?php
    $grand_total = 0;
    $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
    if (mysqli_num_rows($select_cart) > 0) {
        while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $total_price;
    ?>
        <div class="cart-item">
            <div class="cart-image">
                <!-- Assuming the product image URL is stored in `product_image` -->
                <img src="uploaded_img/<?php echo $fetch_cart['image']; ?>" alt="">
            </div>
            <div class="cart-details">
                <p><?php echo $fetch_cart['name']; ?>
                    <span>(<?php echo number_format($fetch_cart['price'], 0, ',', '.') . ' VNĐ x ' . $fetch_cart['quantity']; ?>)</span>
                </p>
            </div>
        </div>
        <?php
        }
    } else {
        echo '<p class="empty">Giỏ hàng của bạn đang trống!</p>';
    }
    ?>
    </section>

    <!-- Grand Total outside the product items loop -->
    <div class="grand-total">Tổng: <span><?php echo number_format($grand_total, 0, ',', '.'); ?> VNĐ</span></div>




    <section class="checkout">

        <form action="" method="post">
            <h3>Địa chỉ giao hàng</h3>
            <div class="flex">
                <div class="inputBox">
                    <span>Họ và tên :</span>
                    <input type="text" name="name" required placeholder="Vui lòng nhập tên">
                </div>
                <div class="inputBox">
                    <span>Số điện thoại :</span>
                    <input type="text" name="number" required placeholder="Vui lòng nhập số điện thoại" pattern="\d{10}"
                        maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                </div>


                <div class="inputBox">
                    <span>Email :</span>
                    <input type="email" name="email" required placeholder="Vui lòng nhập email">
                </div>
                <div class="inputBox">
                    <span>Hình thức thanh toán :</span>
                    <select name="method">
                        <option value="Thanh toán khi giao hàng">Thanh toán khi giao hàng</option>
                        <option value="ATM">Chuyển khoản ngân hàng</option>
                        <option value="Momo">Thanh toán qua Momo</option>
                    </select>
                </div>
                <div class="inputBox">
                    <span>Số nhà(Nếu có):</span>
                    <input type="text" min="0" name="flat" required placeholder="">
                </div>
                <div class="inputBox">
                    <span>Đường-Quận-Huyện :</span>
                    <input type="text" name="street" required placeholder="Vui lòng nhập địa chỉ giao hàng">
                </div>
                <div class="inputBox">
                    <span>Thành phố :</span>
                    <input type="text" name="city" required placeholder="Vui lòng nhập địa chỉ giao hàng">
                </div>

                <div class="inputBox">
                    <span>Quốc gia :</span>
                    <input type="text" name="country" required placeholder="Vui lòng nhập địa chỉ giao hàng">
                </div>

            </div>
            <input type="submit" value="Đặt hàng" class="btn" name="order_btn">
        </form>

    </section>

    <?php include 'footer.php'; ?>

    <!-- Custom JS File Link -->
    <script src="js/script.js"></script>

    <!-- JavaScript to show alert messages -->
    <script>
    <?php if (isset($_SESSION['cart_message'])): ?>
    alert("<?php echo $_SESSION['cart_message']; ?>");
    <?php unset($_SESSION['cart_message']); // Clear message after displaying ?>
    <?php endif; ?>
    </script>

</body>

</html>