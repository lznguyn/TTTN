<?php
include 'config.php';
session_start();

$message = []; // Khởi tạo mảng thông báo

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email'") or die('query failed');

    if (mysqli_num_rows($select_users) > 0) {
        $row = mysqli_fetch_assoc($select_users);

        if (password_verify($pass, $row['password'])) {
            if ($row['user_type'] == 'admin') {
                $_SESSION['admin_name'] = $row['name'];
                $_SESSION['admin_email'] = $row['email'];
                $_SESSION['admin_id'] = $row['id'];
                header('Location: admin_page.php');
                exit();
            } elseif ($row['user_type'] == 'user') {
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['user_id'] = $row['id'];
                header('Location: home.php');
                exit();
            }
        } else {
            $message[] = 'Thông tin tài khoản hoặc mật khẩu không đúng!';
        }
    } else {
        $message[] = 'Thông tin tài khoản hoặc mật khẩu không đúng!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center p-4">

    <!-- Error Messages -->
    <div id="messageContainer" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4">
        <?php
        if (!empty($message)) {
            foreach ($message as $msg) {
                echo "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showMessage('$msg', 'error');
                    });
                </script>
                ";
            }
        }
        ?>
    </div>

    <!-- Login Form Container -->
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8 sm:p-10">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-r from-teal-500 to-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user text-white text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Đăng Nhập</h3>
                <p class="text-gray-600 text-sm">Chào mừng bạn trở lại!</p>
            </div>

            <!-- Login Form -->
            <form action="" method="post" class="space-y-6">
                <!-- Email -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" placeholder="Nhập email của bạn" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-gray-700 placeholder-gray-400 transition-all duration-200">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" required
                               class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-gray-700 placeholder-gray-400 transition-all duration-200">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i id="passwordIcon" class="fas fa-eye text-gray-400 hover:text-gray-600 transition-colors"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 text-sm text-gray-700">Ghi nhớ đăng nhập</label>
                    </div>
                    <a href="forgot_password.php" class="text-sm text-teal-600 hover:text-teal-800 font-medium transition-colors">Quên mật khẩu?</a>
                </div>

                <!-- Buttons -->
                <div class="space-y-3">
                    <button type="submit" name="submit"
                            class="w-full bg-gradient-to-r from-teal-500 to-cyan-600 text-white py-3 px-4 rounded-lg font-medium hover:from-teal-600 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transform hover:scale-[1.02] transition-all duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>Đăng Nhập
                    </button>

                    <button type="button" onclick="window.location.href='home.php'"
                            class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fas fa-home mr-2"></i>Trang Chủ
                    </button>
                </div>

                <!-- Register -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-gray-600 text-sm">
                        Chưa có tài khoản?
                        <a href="register.php" class="text-teal-600 hover:text-teal-800 font-medium">Đăng ký ngay</a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-white text-sm opacity-80">© 2024 Your Company. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Hiện/ẩn mật khẩu
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('passwordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Hiển thị thông báo động
        function showMessage(message, type = 'error') {
            const container = document.getElementById('messageContainer');
            const div = document.createElement('div');
            div.className = `p-4 rounded-lg shadow-lg mb-4 transition-all duration-300 transform ${
                type === 'error'
                    ? 'bg-red-50 border border-red-200 text-red-800'
                    : 'bg-green-50 border border-green-200 text-green-800'
            }`;
            div.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas ${type === 'error' ? 'fa-exclamation-circle text-red-500' : 'fa-check-circle text-green-500'} mr-2"></i>
                        <span class="text-sm font-medium">${message}</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(div);
            setTimeout(() => div.remove(), 5000);
        }
    </script>
</body>
</html>
