<?php
   include 'config.php';

   session_start();

   $admin_id = $_SESSION['admin_id'];
   if(!isset($admin_id)){
    header ('Location: login.php');
   }
   if(isset($_POST['update_order'])){

    $order_update_id = $_POST['order_id'];
    $update_payment = $_POST['update_payment'];
    mysqli_query($conn, "UPDATE `orders` SET payment_status = '$update_payment' WHERE id = '$order_update_id'") or die('query failed');
    $message[] = 'Trạng thái thanh toán đã được cập nhật!';
 
 }
 
 if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed');
    header('location:admin_orders.php');
 }
 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>orders</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- custom admin css file link  -->
    <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

    <?php include 'admin_header.php'; ?>

    <section class="orders">

        <h1 class="title">Đã đặt hàng</h1>

        <div class="box-container">
            <?php
      $select_orders = mysqli_query($conn, "SELECT * FROM `orders`") or die('query failed');//
      if(mysqli_num_rows($select_orders) > 0){ //kiem tra co don khong
         while($fetch_orders = mysqli_fetch_assoc($select_orders)){ // duyet qua tung don va hien thi
      ?>
            <div class="box">
                <p> user id : <span><?php echo $fetch_orders['user_id']; ?></span> </p> <!-- Hiển thị ID của người dùng đã đặt hàng -->
                <p> Ngày đặt hàng : <span><?php echo $fetch_orders['placed_on']; ?></span> </p><!-- Hiển thị ngay dat của người dùng đã đặt hàng -->
                <p> Tên : <span><?php echo $fetch_orders['name']; ?></span> </p>
                <p> Số điện thoại : <span><?php echo $fetch_orders['number']; ?></span> </p>
                <p> Email : <span><?php echo $fetch_orders['email']; ?></span> </p>
                <p> Địa chỉ giao hàng: <span><?php echo $fetch_orders['address']; ?></span> </p>
                <p> Sản phẩm đặt hàng : <span><?php echo $fetch_orders['total_products']; ?></span> </p>
                <p> Tổng : <span>VNĐ <?php echo $fetch_orders['total_price']; ?></span> </p>
                <p> Hình thức thanh toán : <span><?php echo $fetch_orders['method']; ?></span> </p>
                <form action="" method="post">
                    <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                    <select name="update_payment">
                        <option value="" selected disabled><?php echo $fetch_orders['payment_status']; ?></option>
                        <option value="Đang duyệt">Đang duyệt</option>
                        <option value="Thành công">Thành công</option>
                    </select>
                    <input type="submit" value="Cập nhật" name="update_order" class="option-btn">
                    <a href="admin_orders.php?delete=<?php echo $fetch_orders['id']; ?>"
                        onclick="return confirm('Bạn có muốn xóa đơn đặt hàng này?');" class="delete-btn">Xóa</a>
                </form>
            </div>
            <?php
         }
      }else{
         echo '<p class="empty">Chưa có đơn hàng nào được đặt!</p>';
      }
      ?>
        </div>

    </section>










    <!-- custom admin js file link  -->
    <script src="js/admin_script.js"></script>

</body>

</html>