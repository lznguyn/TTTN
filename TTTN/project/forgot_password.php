<?php
include 'config.php';

$message = '';
$step = 1; // Biến để điều khiển giao diện (1: Quên mật khẩu, 2: Đặt lại mật khẩu)

if (isset($_POST['show_encrypted_password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Kiểm tra email trong cơ sở dữ liệu
    $query = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'") or die('Query failed');

    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        $encrypted_password = $row['password']; // Lấy mật khẩu mã hóa từ DB
        $step = 2; // Chuyển sang bước đặt lại mật khẩu
    } else {
        $message = "Email không tồn tại trong cơ sở dữ liệu.";
    }
}

if (isset($_POST['reset_password'])) {
    $encrypted_password = mysqli_real_escape_string($conn, $_POST['encrypted_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Kiểm tra mật khẩu mã hóa
    $query = mysqli_query($conn, "SELECT * FROM `users` WHERE password='$encrypted_password'") or die('Query failed');
    if (mysqli_num_rows($query) > 0) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE `users` SET password='$hashed_password' WHERE password='$encrypted_password'") or die('Query failed');
            $message = "Mật khẩu đã được đặt lại thành công.";
            $step = 1; // Quay lại bước nhập email
        } else {
            $message = "Mật khẩu mới không khớp.";
            $step = 2; // Quay lại bước đặt lại mật khẩu
        }
    } else {
        $message = "Mật khẩu mã hóa không hợp lệ.";
        $step = 2; // Quay lại bước đặt lại mật khẩu
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot & Reset Password</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .form-container h3 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .form-container input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .btn-login {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }
        .btn-login:hover {
            background-color: #218838;
        }
        .message {
            margin-top: 15px;
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if ($step == 1): ?>
            <h3>Quên Mật Khẩu</h3>
            <form action="" method="post">
                <input type="email" name="email" placeholder="Nhập Email..." required>
                <input type="submit" name="show_encrypted_password" value="Check Email">
            </form>
            <p class="message"><?php echo $message; ?></p>
        <?php elseif ($step == 2): ?>
            <h3>Reset Password</h3>
            <p>Mật Khẩu Mã Hoá Của Bạn: <strong><?php echo htmlspecialchars($encrypted_password); ?></strong></p>
            <form action="" method="post">
                <input type="text" name="encrypted_password" placeholder="" value="<?php echo htmlspecialchars($encrypted_password); ?>" readonly>
                <input type="password" name="new_password" placeholder="Nhập Mật Khẩu Mới..." required>
                <input type="password" name="confirm_password" placeholder="Nhập Lại Mật Khẩu ..." required>
                <input type="submit" name="reset_password" value="Reset Password">
            </form>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <a href="login.php" class="btn-login">Đăng Nhập</a>
    </div>
</body>
</html>
