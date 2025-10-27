<?php
     include 'config.php';

     session_start();

     $admin_id = $_SESSION['admin_id'];//lay id cua admin tu bien session
     if(!isset($admin_id)){ // kiem tra dang nhap chua
        header('Location:Login.php');// chua dang nhap chyen den trang login
     };
     if(isset($_GET['delete'])){ // kiem tra neu co yeu cau xoa
        $delete_id = $_GET['delete']; // lay id can xoa
        //dung cau truy van xoa cac message
        mysqli_query($conn, "DELETE FROM `message` WHERE id = '$delete_id'") or die('query failed');
        header('location:admin_contacts.php'); // chuyen den trang admin_contracs.php
     }
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>messages</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- custom admin css file link  -->
    <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

    <?php include 'admin_header.php'; ?>

    <section class="messages">

        <h1 class="title"> Tin nhắn </h1>

        <div class="box-container">
            <?php
         $select_message = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
         if (mysqli_num_rows($select_message) > 0) { // kiem tra tin nhan co khong
            while ($fetch_message = mysqli_fetch_assoc($select_message)) { // neu co duyet qua tung tin nhan va hien thi

         ?>
            <div class="box">
                <p> User id : <span><?php echo $fetch_message['user_id']; ?></span> </p>
                <p> Tên : <span><?php echo $fetch_message['name']; ?></span> </p>
                <p> Số điện thoại : <span><?php echo $fetch_message['number']; ?></span> </p>
                <p> Email : <span><?php echo $fetch_message['email']; ?></span> </p>
                <p> Tin nhắn : <span><?php echo $fetch_message['message']; ?></span> </p>
                <a href="admin_contacts.php?delete=<?php echo $fetch_message['id']; ?>"
                    onclick="return confirm('delete this message?');" class="delete-btn">Xóa tin nhắn</a>
            </div>
            <?php
            };
         } else {
            echo '<p class="empty">Bạn không có thông báo!</p>'; // neu khong co tin nhan hien thi cau tren
         }
         ?>
        </div>

    </section>









    <!-- custom admin js file link  -->
    <script src="js/admin_script.js"></script>

</body>

</html>