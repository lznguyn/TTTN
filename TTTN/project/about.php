<?php
include 'config.php';

session_start();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../project/css/style.css">

    <style>
        :root {
            --white: #ffffff;
            --light-bg: #f7f7f7;
            --light-color: #666;
            --black: #333;
            --orange: #f90;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --border: 1px solid #ddd;
        }

        body {
            
            margin: 0;
            padding: 0;
            background-color: var(--light-bg);
            color: var(--black);
        }

      

       
    </style>
</head>

<body>

<?php include 'header.php'; ?>

    <section class="about-section">
        <img src="../project/images/back.jpg" alt="About Image">
        <div class="content">
            <h2>Welcome to Our Company</h2>
            <p>Chúng tôi tự hào cung cấp chất lượng đặc biệt và dịch vụ vô song. Từ giá cả cạnh tranh đến hỗ trợ khách hàng tận tâm, chúng tôi nỗ lực vượt qua mong đợi ở mọi bước. Trải nghiệm sự xuất sắc cùng chúng tôi!</p>
            <a href="contact.php" class="btn">Contact Us</a>
        </div>
    </section>

    <section class="reviews">
        <h2>Customer Reviews</h2>
        <div class="box-container">
            <div class="box">
                <img src="images/pic11.jpg" alt="Customer 1">
                <p>Trải nghiệm tuyệt vời khi mua hàng ở đây. Chất lượng máy tính xách tay  và dịch vụ khách hàng rất tuyệt vời.</p>
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
                <h3>Nguyễn Hữu Đại</h3>
            </div>

            <div class="box">
                <img src="images/pic12.jpg" alt="Customer 2">
                <p>Giao hàng nhanh, dịch vụ khách hàng tuyệt vời và chiếc máy tính xách tay này vượt quá mong đợi của tôi.</p>
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <h3>Trương Anh Tài</h3>
            </div>
        </div>
    </section>

  
    <?php include 'footer.php'; ?>
</body>

</html>
