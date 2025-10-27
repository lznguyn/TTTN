<?php 
include 'config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Số sản phẩm mỗi trang
$products_per_page = 6;

// Tính tổng số sản phẩm
$total_products_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `products`") or die('query failed');
$total_products = mysqli_fetch_assoc($total_products_query)['total'];

// Tính tổng số trang
$total_pages = ceil($total_products / $products_per_page);

// Lấy trang hiện tại từ URL (mặc định là trang 1)
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Xác định vị trí bắt đầu cho truy vấn
$start_from = ($current_page - 1) * $products_per_page;

// Lấy sản phẩm từ cơ sở dữ liệu
$select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT $start_from, $products_per_page") or die('query failed');

// Thêm sản phẩm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    if ($user_id == null) {
        header('Location: login.php'); // Chuyển đến trang đăng nhập nếu chưa đăng nhập
        exit();
    }

    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = (int)$_POST['product_quantity'];

    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    $check_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

    if (mysqli_num_rows($check_cart) > 0) {
        $_SESSION['cart_message'] = 'Product is already added to the cart!';
    } else {
        mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) 
            VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
        $_SESSION['cart_message'] = 'Product has been added to the cart successfully!';
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?page=' . $current_page);
    exit();
}

// Hiển thị thông báo
if (isset($_SESSION['cart_message'])) {
    $cart_message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS File Link -->
    <link rel="stylesheet" href="css/style.css">


</head>

<body>

    <?php include 'header.php'; ?>

    

    <section class="products">
        <h1 class="title">Sản Phẩm Mới Nhất</h1>

        <div class="box-container">
            <?php  
            if (mysqli_num_rows($select_products) > 0) {
                while ($product = mysqli_fetch_assoc($select_products)) {
            ?>
            <form action="" method="post" class="box">
                <img class="image" src="uploaded_img/<?php echo $product['image']; ?>" alt="">
                <div class="name"><?php echo $product['name']; ?></div>
                <div class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</div>
                <input type="number" min="1" name="product_quantity" value="1" class="qty">
                <input type="hidden" name="product_name" value="<?php echo $product['name']; ?>">
                <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $product['image']; ?>">
                <input type="submit" value="Add to Cart" name="add_to_cart" class="btn">
            </form>
            <?php
                }
            } else {
                echo '<p class="empty">No products found!</p>';
            }
            ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>">Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
    <?php if (isset($cart_message)): ?>
        alert("<?php echo $cart_message; ?>");
    <?php endif; ?>
    </script>
</body>
</html>
