<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:login.php');
    exit;
}

if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM `users` WHERE id = '$delete_id'") or die('query failed');
    header('location:admin_users.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản</title>

    <!-- font awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind giống các trang admin mới -->
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
            <h1 class="text-2xl font-bold text-gray-800">Quản lý tài khoản</h1>
            <p class="text-sm text-gray-500 mt-1">
                Danh sách tất cả tài khoản người dùng và admin trong hệ thống.
            </p>
        </div>
    </div>

    <?php
    $select_users = mysqli_query($conn, "SELECT * FROM `users` ORDER BY user_type DESC, id ASC") or die('query failed');
    ?>

    <?php if (mysqli_num_rows($select_users) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php while ($fetch_users = mysqli_fetch_assoc($select_users)): ?>
                <?php
                $isAdmin   = $fetch_users['user_type'] === 'admin';
                $badgeText = $isAdmin ? 'Admin' : 'User';
                $badgeClass = $isAdmin
                    ? 'bg-rose-50 text-rose-700'
                    : 'bg-sky-50 text-sky-700';

                $avatarLetter = strtoupper(substr($fetch_users['name'] ?: $fetch_users['email'], 0, 1));
                ?>
                <div class="box bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3">
                    <!-- Header: avatar + name + badge -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold text-sm">
                                <?php echo htmlspecialchars($avatarLetter); ?>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($fetch_users['name']); ?>
                                </p>
                                <p class="text-xs text-gray-400">
                                    ID: <?php echo (int)$fetch_users['id']; ?>
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium <?php echo $badgeClass; ?>">
                            <i class="fa-solid <?php echo $isAdmin ? 'fa-user-shield' : 'fa-user'; ?>"></i>
                            <?php echo $badgeText; ?>
                        </span>
                    </div>

                    <!-- Info -->
                    <div class="space-y-2 text-sm">
                        <p class="text-gray-500">
                            <span class="font-medium text-gray-600">Email:</span>
                            <span class="ml-1 text-gray-800">
                                <?php echo htmlspecialchars($fetch_users['email']); ?>
                            </span>
                        </p>
                        <p class="text-gray-500">
                            <span class="font-medium text-gray-600">Loại tài khoản:</span>
                            <span class="ml-1 text-gray-800">
                                <?php echo htmlspecialchars($fetch_users['user_type']); ?>
                            </span>
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="mt-3 pt-3 border-t border-dashed border-gray-200 flex justify-end">
                        <a href="admin_users.php?delete=<?php echo (int)$fetch_users['id']; ?>"
                           onclick="return confirm('Xoá tài khoản người dùng này?');"
                           class="delete-btn inline-flex items-center justify-center px-3 py-2 rounded-lg
                                  bg-rose-500 text-white text-xs font-medium shadow hover:bg-rose-600">
                            <i class="fa-solid fa-trash-can mr-1"></i>
                            Xóa tài khoản
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-sm text-gray-400 mt-10">
            Chưa có tài khoản nào trong hệ thống.
        </p>
    <?php endif; ?>

</main>

<script src="js/admin_script.js"></script>

</body>

</html>
