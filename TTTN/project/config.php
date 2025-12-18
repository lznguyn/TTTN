<?php
// Thông tin kết nối
$servername = getenv('DB_HOST') ?: 'db';
$username =  getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '123456';
$dbname = getenv('DB_NAME') ?: 'musicdb';

// Kết nối đến MySQL
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>