<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

if (isset($_POST['add_product'])) {

    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    $image_size     = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder   = 'uploaded_img/' . $image;

    $select_product_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name = '$name'") or die('query failed');

    if (mysqli_num_rows($select_product_name) > 0) {
        $message[] = 'Tên sản phẩm đã tồn tại';
    } else {
        $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, price, image) VALUES('$name', '$price', '$image')") or die('query failed');

        if ($add_product_query) {
            if ($image_size > 2000000) {
                $message[] = 'Kích thước ảnh quá lớn (> 2MB)';
            } else {
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'Thêm sản phẩm thành công!';
            }
        } else {
            $message[] = 'Không thể thêm sản phẩm!';
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'") or die('query failed');
    $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
    if (!empty($fetch_delete_image['image']) && file_exists('uploaded_img/' . $fetch_delete_image['image'])) {
        unlink('uploaded_img/' . $fetch_delete_image['image']);
    }
    mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
    header('location:admin_products.php');
    exit;
}

if (isset($_POST['update_product'])) {

    $update_p_id    = $_POST['update_p_id'];
    $update_name    = $_POST['update_name'];
    $update_price   = $_POST['update_price'];

    mysqli_query($conn, "UPDATE `products` SET name = '$update_name', price = '$update_price' WHERE id = '$update_p_id'") or die('query failed');

    $update_image          = $_FILES['update_image']['name'];
    $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
    $update_image_size     = $_FILES['update_image']['size'];
    $update_folder         = 'uploaded_img/' . $update_image;
    $update_old_image      = $_POST['update_old_image'];

    if (!empty($update_image)) {
        if ($update_image_size > 2000000) {
            $message[] = 'Kích thước ảnh quá lớn (> 2MB)';
        } else {
            mysqli_query($conn, "UPDATE `products` SET image = '$update_image' WHERE id = '$update_p_id'") or die('query failed');
            move_uploaded_file($update_image_tmp_name, $update_folder);
            if (!empty($update_old_image) && file_exists('uploaded_img/' . $update_old_image)) {
                unlink('uploaded_img/' . $update_old_image);
            }
        }
    }

    header('location:admin_products.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>

    <!-- font awesome  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind giống trang admin_categories -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- css cũ (nếu header/menu đang dùng) -->
    <link rel="stylesheet" href="css/admin_style.css">
</head>

<body class="bg-gray-100 min-h-screen">

<?php include 'admin_header.php'; ?>

<main class="max-w-6xl mx-auto px-4 lg:px-0 py-8 lg:py-10">

    <!-- Tiêu đề + message -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Quản lý sản phẩm</h1>
            <p class="text-sm text-gray-500 mt-1">
                Thêm mới, cập nhật và xoá sản phẩm laptop trong hệ thống.
            </p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="space-y-2 md:max-w-xs">
                <?php foreach ($message as $m): ?>
                    <div class="px-3 py-2 rounded-lg bg-blue-50 text-blue-700 text-xs flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-info-circle"></i>
                        <span><?php echo htmlspecialchars($m); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Form thêm sản phẩm -->
    <section class="add-products bg-white shadow-sm rounded-xl p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600">
                <i class="fa-solid fa-plus"></i>
            </span>
            Thêm sản phẩm mới
        </h2>

        <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
            <div class="space-y-4">
               <!-- Hàng 1: Tên + Giá -->
               <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                           Tên sản phẩm
                        </label>
                        <input type="text" name="name"
                              class="box w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                    placeholder:text-gray-400"
                              placeholder="Nhập tên sản phẩm (VD: Asus TUF Gaming F15)" required>
                  </div>

                  <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                           Giá sản phẩm
                        </label>
                        <div class="relative">
                           <input type="number" min="0" name="price"
                                 class="box w-full rounded-lg border border-gray-300 px-3 py-2 pr-14 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                          placeholder:text-gray-400"
                                 placeholder="Nhập giá" required>
                           <span class="absolute inset-y-0 right-3 flex items-center text-xs text-gray-400">
                              VNĐ
                           </span>
                        </div>
                  </div>
               </div>

               <!-- Hàng 2: Ảnh sản phẩm -->
               <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                           Ảnh sản phẩm
                        </label>

                        <label class="flex flex-col items-center justify-center w-full px-4 py-6 border-2 border-dashed
                                    border-gray-300 rounded-lg bg-gray-50 hover:border-blue-400 hover:bg-blue-50
                                    transition-colors cursor-pointer">
                           <div class="flex items-center gap-2 text-sm text-gray-600">
                              <i class="fa-solid fa-cloud-arrow-up text-blue-500"></i>
                              <span>Chọn ảnh từ máy</span>
                           </div>
                           <p class="mt-1 text-xs text-gray-400 text-center">
                              Chấp nhận JPG, JPEG, PNG. Kích thước tối đa 2MB.
                           </p>
                           <input type="file" name="image"
                                 accept="image/jpg, image/jpeg, image/png"
                                 class="hidden" required>
                        </label>
                  </div>
               </div>

               <!-- Nút submit -->
               <div class="flex justify-end pt-2 border-t border-dashed border-gray-200 mt-2">
                  <input type="submit"
                           value="Thêm sản phẩm"
                           name="add_product"
                           class="btn inline-flex items-center justify-center px-4 py-2 rounded-lg
                                 bg-blue-600 text-white text-sm font-medium shadow hover:bg-blue-700
                                 active:scale-[0.98] transition-transform cursor-pointer">
               </div>
            </div>
        </form>
    </section>

    <!-- Danh sách sản phẩm -->
    <section class="show-products">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-laptop"></i>
                </span>
                Danh sách sản phẩm
            </h2>
            <!-- có thể thêm filter/sort ở đây nếu cần -->
        </div>

        <div class="box-container grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php
            $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
            if (mysqli_num_rows($select_products) > 0) {
                while ($fetch_products = mysqli_fetch_assoc($select_products)) {
            ?>
            <div class="box bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="bg-gray-50 flex items-center justify-center overflow-hidden aspect-[4/3]">
                    <img src="uploaded_img/<?php echo htmlspecialchars($fetch_products['image']); ?>"
                         alt=""
                         class="object-contain max-h-52">
                </div>
                <div class="p-4 flex-1 flex flex-col">
                    <div class="name text-sm font-semibold text-gray-800 mb-1 line-clamp-2">
                        <?php echo htmlspecialchars($fetch_products['name']); ?>
                    </div>
                    <div class="price text-base font-bold text-blue-600 mb-3">
                        <?php echo number_format($fetch_products['price']); ?>
                        <span class="text-xs text-gray-400">VNĐ</span>
                    </div>
                    <div class="mt-auto flex items-center gap-2">
                        <a href="admin_products.php?update=<?php echo $fetch_products['id']; ?>"
                           class="option-btn inline-flex items-center justify-center flex-1 px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-medium hover:bg-amber-600">
                            <i class="fa-solid fa-pen-to-square mr-1"></i> Cập nhật
                        </a>
                        <a href="admin_products.php?delete=<?php echo $fetch_products['id']; ?>"
                           class="delete-btn inline-flex items-center justify-center flex-1 px-3 py-2 rounded-lg bg-rose-500 text-white text-xs font-medium hover:bg-rose-600"
                           onclick="return confirm('Bạn có muốn xóa sản phẩm này?');">
                            <i class="fa-solid fa-trash-can mr-1"></i> Xóa
                        </a>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo '<p class="empty text-center text-gray-400 text-sm mt-6">Không có sản phẩm được thêm!</p>';
            }
            ?>
        </div>

    </section>

    <!-- Form cập nhật sản phẩm (popup) -->
    <section class="edit-product-form fixed inset-0 flex items-center justify-center bg-black/40 z-50">
        <?php
        if (isset($_GET['update'])) {
            $update_id = $_GET['update'];
            $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id'") or die('query failed');
            if (mysqli_num_rows($update_query) > 0) {
                while ($fetch_update = mysqli_fetch_assoc($update_query)) {
        ?>
        <form action="" method="post" enctype="multipart/form-data"
              class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 space-y-4 relative">
            <h2 class="text-lg font-semibold text-gray-800 mb-2 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-pen-to-square"></i>
                </span>
                Cập nhật sản phẩm
            </h2>

            <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['id']; ?>">
            <input type="hidden" name="update_old_image" value="<?php echo htmlspecialchars($fetch_update['image']); ?>">

            <div class="flex items-center justify-center bg-gray-50 rounded-lg py-3 mb-2">
                <img src="uploaded_img/<?php echo htmlspecialchars($fetch_update['image']); ?>" alt=""
                     class="h-32 object-contain">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên sản phẩm</label>
                <input type="text" name="update_name"
                       value="<?php echo htmlspecialchars($fetch_update['name']); ?>"
                       class="box w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       required placeholder="Nhập tên sản phẩm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Giá sản phẩm (VNĐ)</label>
                <input type="number" name="update_price"
                       value="<?php echo $fetch_update['price']; ?>"
                       min="0"
                       class="box w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       required placeholder="Nhập giá sản phẩm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh mới (tuỳ chọn)</label>
                <input type="file" class="box w-full text-sm border border-gray-300 rounded px-3 py-2 bg-white"
                       name="update_image" accept="image/jpg, image/jpeg, image/png">
                <p class="text-xs text-gray-400 mt-1">Để trống nếu không muốn thay đổi ảnh.</p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="admin_products.php"
                   class="option-btn inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-200 text-gray-700 text-xs font-medium hover:bg-gray-300">
                    Hủy
                </a>
                <input type="submit" value="Cập nhật"
                       name="update_product"
                       class="btn inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white text-xs font-medium shadow hover:bg-blue-700 cursor-pointer">
            </div>
        </form>
        <?php
                }
            }
        } else {
            // nếu không có update param thì ẩn section bằng JS để tránh flicker
            echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
        }
        ?>
    </section>

</main>

<!-- js cũ -->
<script src="js/admin_script.js"></script>

</body>

</html>
