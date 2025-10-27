<?php
include 'config.php';
$messages = [];

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);
    $cpass = mysqli_real_escape_string($conn, $_POST['cpassword']);
    $user_type = $_POST['user_type'];
    $admin_code = mysqli_real_escape_string($conn, $_POST['admin_code']);

    // Kiểm tra email đã tồn tại chưa
    $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email'") or die('query failed');

    if (mysqli_num_rows($select_users) > 0) {
        $messages[] = ['type' => 'error', 'text' => 'Email đã tồn tại!'];
    } elseif ($pass != $cpass) {
        $messages[] = ['type' => 'error', 'text' => 'Mật khẩu xác nhận không trùng khớp!'];
    } elseif (strlen($pass) < 8) {
        $messages[] = ['type' => 'error', 'text' => 'Mật khẩu phải có ít nhất 8 ký tự!'];
    } elseif ($user_type == 'admin') {
        // Lấy mã hash admin từ database
        $query = mysqli_query($conn, "SELECT config_value FROM system_config WHERE config_key='admin_code'");
        $row = mysqli_fetch_assoc($query);
        $admin_hash = $row['config_value'] ?? '';

        if (!password_verify($admin_code, $admin_hash)) {
            $messages[] = ['type' => 'error', 'text' => 'Mã xác thực Admin không đúng!'];
        } else {
            // Nếu mã đúng → tiếp tục tạo tài khoản
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO `users`(name, email, password, user_type) 
                                VALUES('$name', '$email', '$hashed_pass', '$user_type')") or die('query failed');
            $messages[] = ['type' => 'success', 'text' => 'Đăng ký thành công! Chúc mừng bạn đã tạo tài khoản!'];
            echo '<meta http-equiv="refresh" content="2;url=login.php">';
        }
    } else {
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO `users`(name, email, password, user_type) 
                             VALUES('$name', '$email', '$hashed_pass', '$user_type')") or die('query failed');
        $messages[] = ['type' => 'success', 'text' => 'Đăng ký thành công! Chúc mừng bạn đã tạo tài khoản!'];

        // Chuyển hướng sau 2 giây
        echo '<meta http-equiv="refresh" content="2;url=login.php">';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center p-4">
    
    <!-- Thông báo -->
    <div id="messageContainer" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4">
        <?php if (!empty($messages)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php foreach ($messages as $msg): ?>
                        showMessage("<?= $msg['text'] ?>", "<?= $msg['type'] ?>");
                    <?php endforeach; ?>
                });
            </script>
        <?php endif; ?>
    </div>

    <!-- Form đăng ký -->
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-2xl shadow-2xl p-8 sm:p-10">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-r from-teal-500 to-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-plus text-white text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Đăng Ký Tài Khoản</h3>
                <p class="text-gray-600 text-sm">Tạo tài khoản mới để bắt đầu</p>
            </div>

            <form action="" method="post" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Họ và tên</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="name" required placeholder="Nhập họ và tên"
                               class="w-full pl-10 pr-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-3 text-gray-400"></i>
                        <input type="email" name="email" required placeholder="Nhập email"
                               class="w-full pl-10 pr-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                        <input type="password" id="password" name="password" required minlength="8"
                               oninput="checkPasswordStrength()"
                               placeholder="Nhập mật khẩu"
                               class="w-full pl-10 pr-10 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500">
                        <button type="button" onclick="togglePassword('password','passwordIcon')" 
                                class="absolute right-3 top-3 text-gray-400">
                            <i id="passwordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordStrength" class="hidden mt-2">
                        <div class="flex space-x-1">
                            <div id="strength1" class="h-1 w-1/4 bg-gray-200 rounded"></div>
                            <div id="strength2" class="h-1 w-1/4 bg-gray-200 rounded"></div>
                            <div id="strength3" class="h-1 w-1/4 bg-gray-200 rounded"></div>
                            <div id="strength4" class="h-1 w-1/4 bg-gray-200 rounded"></div>
                        </div>
                        <p id="strengthText" class="text-xs text-gray-500 mt-1">Độ mạnh mật khẩu</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Xác nhận mật khẩu</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                        <input type="password" id="cpassword" name="cpassword" required minlength="8"
                               oninput="checkPasswordMatch()" placeholder="Nhập lại mật khẩu"
                               class="w-full pl-10 pr-10 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500">
                        <button type="button" onclick="togglePassword('cpassword','cpasswordIcon')" 
                                class="absolute right-3 top-3 text-gray-400">
                            <i id="cpasswordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordMatch" class="hidden text-xs mt-1">
                        <span id="matchText"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Loại tài khoản</label>
                    <select name="user_type" onchange="toggleAdminCodeField(this)" 
                            class="w-full border rounded-lg px-3 py-3 focus:ring-2 focus:ring-teal-500">
                        <option value="user">Người dùng thường</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>

                <div id="admin-code-container" class="hidden">
                    <label class="block text-sm font-medium text-gray-700">Mã xác thực Admin</label>
                    <input type="text" name="admin_code" placeholder="Nhập mã xác thực Admin"
                           class="w-full border rounded-lg px-3 py-3 focus:ring-2 focus:ring-teal-500">
                </div>

                <div class="flex items-start space-x-3">
                    <input type="checkbox" id="terms" required class="mt-1">
                    <label for="terms" class="text-sm text-gray-700">Tôi đồng ý với 
                        <a href="#" class="text-teal-600 font-medium">Điều khoản</a> và 
                        <a href="#" class="text-teal-600 font-medium">Chính sách</a>
                    </label>
                </div>

                <button type="submit" name="submit" 
                        class="w-full bg-gradient-to-r from-teal-500 to-cyan-600 text-white py-3 rounded-lg hover:scale-[1.02] transition">
                    <i class="fas fa-user-plus mr-2"></i>Đăng Ký
                </button>
                <button type="button" onclick="window.location.reload()" 
                        class="w-full bg-gray-100 text-gray-700 py-3 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-times mr-2"></i>Hủy
                </button>

                <p class="text-center text-sm text-gray-600 pt-4">Đã có tài khoản? 
                    <a href="login.php" class="text-teal-600 font-medium">Đăng nhập</a>
                </p>
            </form>
        </div>
        <p class="text-center text-white text-sm opacity-80 mt-6">© 2024 MuTraPro. All rights reserved.</p>
    </div>

<script>
function togglePassword(id, iconId) {
    const input = document.getElementById(id);
    const icon = document.getElementById(iconId);
    input.type = input.type === "password" ? "text" : "password";
    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");
}
function toggleAdminCodeField(select) {
    const container = document.getElementById('admin-code-container');
    select.value === 'admin' ? container.classList.remove('hidden') : container.classList.add('hidden');
}
function showMessage(message, type='error') {
    const c = document.getElementById('messageContainer');
    const d = document.createElement('div');
    d.className = `p-4 rounded-lg mb-3 shadow-lg ${type==='error'?'bg-red-50 text-red-700 border border-red-200':'bg-green-50 text-green-700 border border-green-200'}`;
    d.innerHTML = `<div class="flex items-center justify-between">
        <div><i class="fas ${type==='error'?'fa-exclamation-circle text-red-500':'fa-check-circle text-green-500'} mr-2"></i>${message}</div>
        <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
    </div>`;
    c.appendChild(d);
    setTimeout(()=>d.remove(),5000);
}
function checkPasswordStrength() {
    const pass = document.getElementById('password').value;
    const container = document.getElementById('passwordStrength');
    if (!pass) { container.classList.add('hidden'); return; }
    container.classList.remove('hidden');
    const checks = [pass.length>=8, /[a-z]/.test(pass), /[A-Z]/.test(pass), /[0-9]/.test(pass) || /[^A-Za-z0-9]/.test(pass)];
    const strength = checks.filter(Boolean).length;
    ['strength1','strength2','strength3','strength4'].forEach((id,i)=>document.getElementById(id).className=`h-1 w-1/4 rounded ${i<strength?'bg-green-500':'bg-gray-200'}`);
    document.getElementById('strengthText').textContent = ['Rất yếu','Yếu','Trung bình','Mạnh'][strength-1] || 'Rất yếu';
}
function checkPasswordMatch() {
    const p = document.getElementById('password').value;
    const cp = document.getElementById('cpassword').value;
    const m = document.getElementById('passwordMatch');
    const t = document.getElementById('matchText');
    if (!cp) { m.classList.add('hidden'); return; }
    m.classList.remove('hidden');
    if (p === cp) { t.textContent='✓ Mật khẩu khớp'; t.className='text-green-500'; }
    else { t.textContent='✗ Mật khẩu không khớp'; t.className='text-red-500'; }
}
</script>
</body>
</html>
