<?php

include 'config.php';

session_start();

// Kiểm tra xem 'user_id' có tồn tại trong session hay không
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Chuyển hướng tới trang đăng nhập nếu chưa đăng nhập
    header('Location: login.php');
    exit();
}

if (isset($_POST['send'])) {

    //Lấy dữ liệu đầu vào của người dùng để tránh lỗi SQL Injection
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $number = $_POST['number'];
    $msg = mysqli_real_escape_string($conn, $_POST['message']);

    // Kiểm tra xem tin nhắn đã được gửi trước đó chưa
    $select_message = mysqli_query($conn, "SELECT * FROM `message` WHERE name = '$name' AND email = '$email' AND number = '$number' AND message = '$msg'") or die('query failed');

    if (mysqli_num_rows($select_message) > 0) {
        $message[] = 'Tin Nhắn Đã Được Gửi Rồi!';
    } else {
        // Thêm tin nhắn vào cơ sở dữ liệu
        mysqli_query($conn, "INSERT INTO `message`(user_id, name, email, number, message) VALUES('$user_id', '$name', '$email', '$number', '$msg')") or die('query failed');
        $message[] = 'Tin Nhắn Đã Được Gửi Thành Công!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">

  
</head>

<body>

    <?php include 'header.php'; ?>

    

    <section class="contact">

        <form action="" method="post">
            <h3>Liên hệ với chúng tôi ngay!</h3>
            <input type="text" name="name" required placeholder="Vui lòng nhập tên" class="box">
            <input type="email" name="email" required placeholder="Vui lòng nhập email" class="box">
            <input type="number" name="number" required placeholder="Vui lòng nhập số điện thoại" class="box">
            <textarea name="message" class="box" placeholder="Vui lòng nhập lời nhắn" cols="30" rows="10"></textarea>
            <input type="submit" value="Gửi tin nhắn" name="send" class="btn">
        </form>

        <?php
        if (isset($message)) {
            foreach ($message as $msg) {
                echo '<p class="message">' . $msg . '</p>';
            }
        }
        ?>

    </section>

    <?php include 'footer.php'; ?>

    <!-- Custom JS file link -->
    <script src="js/script.js"></script>

</body>

</html>
