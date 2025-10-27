<?php
include 'config.php';

session_start();

$user_id = isset($_POST['user_id']) ? $_SESSION['user_id'] :null;//Kiem tra nguoi dung co dang nhap hay chưa
if(isset($_POST['add_to_cart'])){ // kiểm tra người dùng nhấn nút thêm hàng 
   
    //nếu chưa đăng nhập ,chuyển hướng đến trang login.php
    if ($user_id == null){
        header('Location:login.php');
        exit();
    }
  //mysqli_real_escape_string xu ly chuoi du lieu dau vao an toan
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = (int)$_POST['product_quantity'];// ep kieu $product_quantity de dau vao la so

    $check_cart_number = mysqli_query($conn,"SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

    
    if (mysqli_num_rows($check_cart_numbers) > 0) {//kiem tra san pham da co san trong gio hang hay chua
        $_SESSION['cart_message'] = 'Sản phẩm đã được thêm vào giỏ hàng!';
    } else {
        mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');//neu chua co san pham thì them san pham vao gio
        $_SESSION['cart_message'] = 'Sản phẩm đã được thêm vào giỏ hàng!';
    }

    // chuyen huong ve trang hien tai
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
    <title>Home</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS File Link -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        .home{
            min-height: 81vh;
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('../project/images/bannerblog1.jpg') no-repeat;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-image 1s ease-in-out;
        }
        
   

   

        .products .box-container .box .image {
            height: 25rem;
            width: 25rem;
        }

        /* CSS để ẩn các sản phẩm sau sản phẩm thứ 5 */
        .products .box-container .box:nth-child(n+6) {
            display: none;
        }
    </style>
</head>

<body>



<?php include 'header.php'?> 
     <section class="home">

        <div class="content">
            <h3>LAPTOP CHÍNH HÃNG CAO CẤP</h3>
            <p>Với thiết kế hiện đại và đa dạng mẫu mã, NEXGEN LAPTOP tự tin mang đến cho bạn những lựa chọn hoàn hảo, giúp bạn tìm thấy chiếc laptop đẹp và phù hợp nhất với nhu cầu sử dụng của mình.</p>
            <a href="about.php" class="white-btn">XEM THÊM</a>
        </div>

    </section>

    <section class="products">

        <h1 class="title">SẢN PHẨM MỚI NHẤT!!</h1>

        <div class="box-container">
            <?php
            $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 6") or die('query failed');//lay 6 san pham tu bang product
            if (mysqli_num_rows($select_products) > 0) { //nếu có trên 1 san pham thì lặp qua từng sản phẩm trong kết quả
                while ($fetch_products = mysqli_fetch_assoc($select_products)) {       
            ?>
            
            <form action="" method="post" class="box">
            <img class="image" src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
                <div class="name"><?php echo $fetch_products['name']; ?></div>
                <div class="price"><?php echo number_format($fetch_products['price'], 0, ',', '.'); ?> VNĐ</div>
                <input type="number" min="1" name="product_quantity" value="1" class="qty">
                <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
                <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
                <input type="submit" value="Thêm vào giỏ hàng" name="add_to_cart" class="btn">
            </form>
            <?php
                }
            } else {
                echo '<p class="empty">Chưa có sản phẩm!</p>';
            }
            ?>
           
        </div>

        <div class="load-more" style="margin-top: 2rem; text-align:center">
            <a href="shop.php" class="option-btn">Xem thêm</a>
        </div>

    </section>

    <section class="about">

        <div class="flex">

            <div class="image">
                <img src="images/bannerblog.jpg" alt=""  >
            </div>

            <div class="content">
                <h3>VỀ CHÚNG TÔI</h3>
                <p>Chúng tôi đánh giá cao sự quan tâm của bạn đến những chiếc LapTop của chúng tôi. Nếu bạn có bất kỳ
                    câu hỏi hoặc cần hỗ trợ, xin vui lòng liên hệ với chúng tôi. Chúng tôi mong được phục vụ bạn.</p>
                <a href="about.php" class="btn">Xem thêm</a>
            </div>

        </div>

    </section>

    <section class="home-contact">

        <div class="content">
            <h3>BẠN CÓ CÂU HỎI NÀO KHÔNG?</h3>
            <p>Chúng tôi rất trân trọng sự quan tâm của bạn những chiếc LapTop của chúng tôi. Nếu bạn có bất kỳ câu
                hỏi nào hoặc muốn để lại đánh giá, xin vui lòng liên hệ với chúng tôi. Chúng tôi luôn sẵn sàng hỗ trợ
                bạn!</p>
            <a href="contact.php" class="white-btn">LIÊN HỆ</a>
        </div>

    </section>

    <?php include 'footer.php'; ?>

    <!-- Custom JS File Link -->
    <script src="js/script.js"></script>

    <!-- JavaScript to show alert messages -->
    <script>

    <?php if (isset($_SESSION['cart_message'])): ?>
    alert("<?php echo $_SESSION['cart_message']; ?>");//thong bao cho nguoi dung va trang thai gio hang
    <?php unset($_SESSION['cart_message']);  ?>//xoa thong bao khi da hien thi
    <?php endif; ?>
    </script>
     <!-- JavaScript to change background image -->
     <script>
        const backgroundImages = [
            '../project/images/bannerblog1.jpg',
            '../project/images/bannerblog2.jpg',
            '../project/images/bannerblog3.jpg'
        ];

        let currentImageIndex = 0;
        const homeSection = document.querySelector('.home');

        function changeBackgroundImage() {
            // Cập nhật chỉ số ảnh hiện tại
            currentImageIndex = (currentImageIndex + 1) % backgroundImages.length;

            // Đổi ảnh nền
            homeSection.style.backgroundImage = `linear-gradient(rgba(0, 0, 0, .7), rgba(0, 0, 0, .7)), url('${backgroundImages[currentImageIndex]}')`;
        }

        // Chuyển ảnh mỗi 2 giây
        setInterval(changeBackgroundImage, 2000);
    </script>
    <script>
        // Danh sách hình ảnh cho section .about
        const aboutBackgroundImages = [
            
            '../project/images/bannerblog4.jpg',
            '../project/images/bannerblog5.jpg'
        ];

        let currentAboutImageIndex = 0;
        const aboutSection = document.querySelector('.about .image img');

        function changeAboutBackgroundImage() {
            // Cập nhật chỉ số ảnh hiện tại
            currentAboutImageIndex = (currentAboutImageIndex + 1) % aboutBackgroundImages.length;

            // Đổi ảnh nền
            aboutSection.src = aboutBackgroundImages[currentAboutImageIndex];
        }

        // Chuyển ảnh mỗi 3 giây
        setInterval(changeAboutBackgroundImage, 2000);
    </script>


</body>

</html>