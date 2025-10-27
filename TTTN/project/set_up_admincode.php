<?php
include 'config.php';

// Tạo hash mã admin
$admin_code_hash = password_hash('admin123', PASSWORD_BCRYPT);

// Kiểm tra xem đã có key 'admin_code' chưa
$check = mysqli_query($conn, "SELECT * FROM system_config WHERE config_key = 'admin_code'");

if (mysqli_num_rows($check) > 0) {
    mysqli_query($conn, "UPDATE system_config SET config_value = '$admin_code_hash' WHERE config_key = 'admin_code'");
    echo "✅ Đã cập nhật mã admin!";
} else {
    mysqli_query($conn, "INSERT INTO system_config (config_key, config_value) VALUES ('admin_code', '$admin_code_hash')");
    echo "✅ Đã thêm mới mã admin!";
}
?>
