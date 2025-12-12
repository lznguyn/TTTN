<?php
include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null; // lấy id admin

if (!$admin_id) { // chưa đăng nhập
    header('location:login.php');
    exit;
}

if (isset($_GET['delete'])) { // yêu cầu xóa tin nhắn
    $delete_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM `message` WHERE id = '$delete_id'") or die('query failed');
    header('location:admin_contacts.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin nhắn</title>

    <!-- font awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind giống các trang admin khác -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- css cũ cho header/menu -->
    <link rel="stylesheet" href="css/admin_style.css">
</head>

<body class="bg-gray-100 min-h-screen">

<?php include 'admin_header.php'; ?>

<main class="max-w-6xl mx-auto px-4 lg:px-0 py-8 lg:py-10">

    <!-- Tiêu đề -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tin nhắn liên hệ</h1>
            <p class="text-sm text-gray-500 mt-1">
                Danh sách tin nhắn khách hàng gửi qua form liên hệ.
            </p>
        </div>
    </div>

    <?php
    $select_message = mysqli_query($conn, "SELECT * FROM `message` ORDER BY id DESC") or die('query failed');
    ?>

    <?php if (mysqli_num_rows($select_message) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php while ($fetch_message = mysqli_fetch_assoc($select_message)): ?>
                <?php
                $userId   = $fetch_message['user_id'];
                $name     = $fetch_message['name'];
                $email    = $fetch_message['email'];
                $number   = $fetch_message['number'];
                $content  = $fetch_message['message'];
                $avatarLetter = strtoupper(substr($name ?: $email ?: 'U', 0, 1));
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3">
                    <!-- Header: avatar + tên + email -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center font-semibold text-sm">
                                <?php echo htmlspecialchars($avatarLetter); ?>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($name); ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    User ID:
                                    <span class="font-medium text-gray-600">
                                        <?php echo htmlspecialchars($userId); ?>
                                    </span>
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                    <i class="fa-solid fa-envelope text-gray-400"></i>
                                    <span><?php echo htmlspecialchars($email); ?></span>
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                    <i class="fa-solid fa-phone text-gray-400"></i>
                                    <span><?php echo htmlspecialchars($number); ?></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Nội dung tin nhắn -->
                    <div class="mt-1">
                        <p class="text-xs font-medium text-gray-500 mb-1">Nội dung tin nhắn:</p>
                        <div class="bg-gray-50 border border-dashed border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 max-h-36 overflow-y-auto">
                            <?php echo nl2br(htmlspecialchars($content)); ?>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="mt-3 pt-3 border-t border-dashed border-gray-200 flex justify-end">
                        <a href="admin_contacts.php?delete=<?php echo (int)$fetch_message['id']; ?>"
                           onclick="return confirm('Xóa tin nhắn này?');"
                           class="delete-btn inline-flex items-center justify-center px-3 py-2 rounded-lg
                                  bg-rose-500 text-white text-xs font-medium shadow hover:bg-rose-600">
                            <i class="fa-solid fa-trash-can mr-1"></i>
                            Xóa tin nhắn
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-sm text-gray-400 mt-10">
            Bạn không có thông báo nào.
        </p>
    <?php endif; ?>

</main>

<script src="js/admin_script.js"></script>

</body>

</html>
