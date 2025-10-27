<?php

include 'config.php';
// Kết nối đến tệp 'config.php' để lấy thông tin cấu hình, trong đó có kết nối cơ sở dữ liệu.

session_start();
// Bắt đầu phiên làm việc (session) để lưu và truy xuất dữ liệu phiên.

$user_id = $_SESSION['user_id'];
// Lấy `user_id` từ phiên làm việc của người dùng hiện tại (đã đăng nhập) để xác định giỏ hàng của người đó.


// Kiểm tra nếu người dùng nhấn nút 'add_to_cart'
if (isset($_POST['add_to_cart'])) {

   // Lấy thông tin sản phẩm từ biểu mẫu gửi lên
   $product_name = $_POST['product_name'];         // Tên sản phẩm
   $product_price = $_POST['product_price'];       // Giá sản phẩm
   $product_image = $_POST['product_image'];       // Hình ảnh sản phẩm
   $product_quantity = $_POST['product_quantity']; // Số lượng sản phẩm

   // Kiểm tra trong giỏ hàng xem sản phẩm này đã tồn tại hay chưa
   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   // Nếu sản phẩm đã có trong giỏ hàng của người dùng hiện tại
   if (mysqli_num_rows($check_cart_numbers) > 0) {
      // Thêm thông báo vào mảng `$message` để báo rằng sản phẩm đã có trong giỏ hàng
      $message[] = 'already added to cart!';
   } else {
      // Nếu sản phẩm chưa có trong giỏ hàng, thêm sản phẩm vào bảng `cart` của cơ sở dữ liệu
      mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
      
      // Thêm thông báo vào mảng `$message` để báo rằng sản phẩm đã được thêm thành công vào giỏ hàng
      $message[] = 'product added to cart!';
   }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>search page</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php include 'header.php'; ?>

    

    <section class="search-form">
        <form action="" method="post">
            <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." class="box">
            <input type="submit" name="submit" value="Tìm kiếm" class="btn">
        </form>
    </section>

    <section class="products" style="padding-top: 0;">

        <div class="box-container">
            <?php
      if(isset($_POST['submit'])){
         $search_item = $_POST['search'];
         $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE name LIKE '%{$search_item}%'") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
         while($fetch_product = mysqli_fetch_assoc($select_products)){
   ?>
            <form action="" method="post" class="box">
                <img src="uploaded_img/<?php echo $fetch_product['image']; ?>" alt="" class="image">
                <div class="name"><?php echo $fetch_product['name']; ?></div>
                <div class="price">VNĐ <?php echo $fetch_product['price']; ?>/-</div>
                <input type="number" class="qty" name="product_quantity" min="1" value="1">
                <input type="hidden" name="product_name" value="<?php echo $fetch_product['name']; ?>">
                <input type="hidden" name="product_price" value="<?php echo $fetch_product['price']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $fetch_product['image']; ?>">
                <input type="submit" class="btn" value="Thêm vào giỏ hàng" name="add_to_cart">
            </form>
            <?php
            }
         }else{
            echo '<p class="empty">Không tìm thấy kết quả!</p>';
         }
      }else{
         echo '<p class="empty">search something!</p>';
      }
   ?>
        </div>


    </section>









    <?php include 'footer.php'; ?>

    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>