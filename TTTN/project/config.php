<?php
// Thông tin kết nối
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'web';

// Kết nối đến MySQL
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
