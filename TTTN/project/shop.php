<?php 
include 'config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Số sản phẩm mỗi trang
$products_per_page = 6;

// ==== LẤY FILTER TỪ QUERY STRING ====
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$price_range = isset($_GET['price_range']) ? (array)$_GET['price_range'] : [];
$brands      = isset($_GET['brand']) ? (array)$_GET['brand'] : [];
$categories  = isset($_GET['category']) ? (array)$_GET['category'] : [];

// Escape chuỗi để đưa vào SQL (PHẢI làm trước khi dùng)
$search_esc = mysqli_real_escape_string($conn, $search);

// ==== BUILD WHERE ====
$where = [];

if ($search_esc !== '') {
    $where[] = "(name LIKE '%$search_esc%' OR description LIKE '%$search_esc%')";
}

// Price range
$priceConditions = [];
foreach ($price_range as $p) {
    switch ($p) {
        case 'under-20':
            $priceConditions[] = "price < 20000000";
            break;
        case '20-30':
            $priceConditions[] = "(price >= 20000000 AND price <= 30000000)";
            break;
        case '30-50':
            $priceConditions[] = "(price >= 30000000 AND price <= 50000000)";
            break;
        case 'over-50':
            $priceConditions[] = "price > 50000000";
            break;
    }
}
if ($priceConditions) {
    $where[] = '(' . implode(' OR ', $priceConditions) . ')';
}

// Brand
if ($brands) {
    $brandEscaped = array_map(function($b) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $b) . "'";
    }, $brands);
    $where[] = 'brand IN (' . implode(',', $brandEscaped) . ')';
}

// Category
if ($categories) {
    $catEscaped = array_map(function($c) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $c) . "'";
    }, $categories);
    $where[] = 'category IN (' . implode(',', $catEscaped) . ')';
}

$whereSql = '';
if ($where) {
    $whereSql = ' WHERE ' . implode(' AND ', $where);
}
// ==== SORT / ORDER BY ====
$orderBy = ' ORDER BY id DESC'; // mặc định (hoặc created_at nếu có)

switch ($sort) {
    case 'price-asc':
        $orderBy = ' ORDER BY price ASC';
        break;
    case 'price-desc':
        $orderBy = ' ORDER BY price DESC';
        break;
    case 'name-asc':
        $orderBy = ' ORDER BY name ASC';
        break;
    case 'newest':
        // đổi created_at thành field ngày tạo thực tế của bảng
        $orderBy = ' ORDER BY created_at DESC';
        break;
}


// ==== PHÂN TRANG ====
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$start_from = ($current_page - 1) * $products_per_page;

// ==== ĐẾM TỔNG SẢN PHẨM ====
$total_products_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM `products` $whereSql"
) or die(mysqli_error($conn));

$total_products = (int)mysqli_fetch_assoc($total_products_query)['total'];
$total_pages = max(1, ceil($total_products / $products_per_page));

// ==== LẤY SẢN PHẨM ====
$select_products = mysqli_query(
    $conn,
    "SELECT * FROM `products` $whereSql $orderBy LIMIT $start_from, $products_per_page"
) or die(mysqli_error($conn));


// ==== XỬ LÝ GIỎ HÀNG ====
if (isset($_POST['add_to_cart'])) {
    if ($user_id == null) {
        header('Location: login.php'); // Chuyển đến trang đăng nhập nếu chưa đăng nhập
        exit();
    }

    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = (int)$_POST['product_quantity'];

    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    $check_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") 
        or die(mysqli_error($conn));

    if (mysqli_num_rows($check_cart) > 0) {
        $_SESSION['cart_message'] = 'Product is already added to the cart!';
    } else {
        mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) 
            VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") 
            or die(mysqli_error($conn));
        $_SESSION['cart_message'] = 'Product has been added to the cart successfully!';
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?page=' . $current_page);
    exit();
}
// Tính range đang hiển thị
$show_from = $total_products > 0 ? $start_from + 1 : 0;
$show_to   = min($start_from + $products_per_page, $total_products);

// Build base url giữ lại filter khi phân trang
$query_params = $_GET;
unset($query_params['page']);
$base_query = http_build_query($query_params);
$base_url   = $_SERVER['PHP_SELF'] . ($base_query ? ('?' . $base_query . '&') : '?');


// Hiển thị thông báo
if (isset($_SESSION['cart_message'])) {
    $cart_message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXGEN LAPTOP - Cửa Hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        bounceGentle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Breadcrumb -->
    <div class="bg-gray-100 py-4">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="home.php" class="text-gray-600 hover:text-blue-600 transition-colors">Trang chủ</a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <span class="text-blue-600 font-medium">Cửa hàng</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <section class="py-12 bg-gradient-to-r from-blue-600 to-purple-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-slide-up">Cửa Hàng Laptop</h1>
            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto animate-slide-up" style="animation-delay: 0.2s">
                Khám phá bộ sưu tập laptop cao cấp với công nghệ tiên tiến nhất
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:w-1/4">
                <form method="get" class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">                    
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Bộ Lọc</h3>
                    
                    <!-- Search -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm</label>
                        <div class="relative">
                            <input type="text" name="search" placeholder="Tìm sản phẩm..."
                                value="<?php echo htmlspecialchars($search); ?>"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    <!-- Price Range -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Khoảng giá</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="price_range[]" value="under-20"
                                    <?php echo in_array('under-20', $price_range) ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Dưới 20 triệu</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="price_range[]" value="20-30"
                                    <?php echo in_array('20-30', $price_range) ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">20 - 30 triệu</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="price_range[]" value="30-50"
                                    <?php echo in_array('30-50', $price_range) ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">30 - 50 triệu</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="price_range[]" value="over-50"
                                    <?php echo in_array('over-50', $price_range) ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Trên 50 triệu</span>
                            </label>
                        </div>
                    </div>


                    <!-- Brand Filter -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Thương hiệu</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="brand[]" value="Apple"
                                    <?php echo in_array('Apple', $brands) ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Apple</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="brand[]" value="Dell"
                                    <?php echo in_array('Dell', $brands) ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Dell</span>
                            </label>
                            <!-- HP, Lenovo, ASUS tương tự -->
                        </div>
                    </div>


                    <!-- Category Filter -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Danh mục</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="category[]" value="Gaming"
                                    <?php echo in_array('Gaming', $categories) ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Gaming</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="category[]" value="Văn phòng"
                                    <?php echo in_array('Văn phòng', $categories) ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Văn phòng</span>
                            </label>
                            <!-- Đồ họa, Ultrabook tương tự -->
                        </div>
                    </div>


                    <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Áp dụng bộ lọc
                    </button>
                </form> <!-- đóng form -->

            </div>
        

            <!-- Products Section -->
            <div class="lg:w-3/4">
                <!-- Sort and View Options -->
                <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col sm:flex-row justify-between gap-4">
                    <div class="text-gray-600">
                        Hiển thị
                        <span class="font-semibold">
                            <?php echo $show_from . '-' . $show_to; ?>
                        </span>
                        trong
                        <span class="font-semibold">
                            <?php echo $total_products; ?>
                        </span>
                        sản phẩm
                    </div>
                   <div class="flex items-center space-x-4">
                        <select id="sortSelect"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Sắp xếp theo</option>
                            <option value="price-asc"  <?php echo $sort === 'price-asc'  ? 'selected' : ''; ?>>Giá: Thấp đến cao</option>
                            <option value="price-desc" <?php echo $sort === 'price-desc' ? 'selected' : ''; ?>>Giá: Cao đến thấp</option>
                            <option value="name-asc"   <?php echo $sort === 'name-asc'   ? 'selected' : ''; ?>>Tên: A-Z</option>
                            <option value="newest"     <?php echo $sort === 'newest'     ? 'selected' : ''; ?>>Mới nhất</option>
                        </select>
                        <div class="flex border border-gray-300 rounded-lg overflow-hidden">
                            <button class="p-2 bg-blue-600 text-white">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button class="p-2 bg-gray-100 text-gray-600 hover:bg-gray-200">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-8">
                    <?php  
                    if (mysqli_num_rows($select_products) > 0) {
                        while ($product = mysqli_fetch_assoc($select_products)) {
                            // Tính phần trăm giảm giá (nếu có giá cũ)
                            $discount = '';
                            if (!empty($product['old_price']) && $product['old_price'] > $product['price']) {
                                $percent = round((($product['old_price'] - $product['price']) / $product['old_price']) * 100);
                                $discount = "-$percent%";
                            }
                    ?>
                    <form method="post" class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden animate-fade-in">
                        <div class="relative overflow-hidden">
                            <img src="uploaded_img/<?php echo htmlspecialchars($product['image']); ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500">
                            
                            <!-- Nhãn trạng thái (VD: Mới, Giảm giá...) -->
                            <div class="absolute top-4 left-4">
                                <?php if (!empty($discount)) { ?>
                                    <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold"><?php echo $discount; ?></span>
                                <?php } else { ?>
                                    <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-semibold">Mới</span>
                                <?php } ?>
                            </div>

                            <!-- Nút nhanh -->
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <button type="button" class="bg-white/90 p-2 rounded-full hover:bg-white transition-colors mb-2">
                                    <i class="fas fa-heart text-gray-600 hover:text-red-500"></i>
                                </button>
                                <button type="button" class="bg-white/90 p-2 rounded-full hover:bg-white transition-colors block">
                                    <i class="fas fa-eye text-gray-600 hover:text-blue-500"></i>
                                </button>
                            </div>

                            <!-- Nút Xem nhanh -->
                            <div class="absolute bottom-4 left-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <button type="button" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                                    Xem nhanh
                                </button>
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>

                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <span class="text-2xl font-bold text-blue-600">
                                        <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ
                                    </span>
                                    <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']) { ?>
                                        <span class="text-sm text-gray-500 line-through ml-2">
                                            <?php echo number_format($product['old_price'], 0, ',', '.'); ?> VNĐ
                                        </span>
                                    <?php } ?>
                                </div>
                                <?php if (!empty($discount)) { ?>
                                    <span class="bg-red-100 text-red-600 px-2 py-1 rounded-full text-xs font-semibold">
                                        <?php echo $discount; ?>
                                    </span>
                                <?php } ?>
                            </div>

                            <div class="flex items-center gap-3 mb-4">
                                <label class="text-sm font-medium text-gray-700">Số lượng:</label>
                                <input type="number" name="product_quantity" value="1" min="1" 
                                    class="w-16 px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($product['price']); ?>">
                            <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image']); ?>">

                            <!-- Add to cart -->
                            <button type="submit" name="add_to_cart" 
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Thêm vào giỏ hàng
                            </button>
                        </div>
                    </form>
                    <?php
                        } // end while
                    } else {
                        echo '<p class="col-span-3 text-center text-gray-500">Không có sản phẩm nào!</p>';
                    }
                    ?>
                </div>

                <!-- Pagination -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mt-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">

                        <!-- Phân trang -->
                        <div class="flex items-center space-x-2">
                            <!-- Nút Trước -->
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors flex items-center">
                                    <i class="fas fa-chevron-left mr-1"></i> Trước
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-50 text-gray-400 rounded-lg flex items-center cursor-not-allowed">
                                    <i class="fas fa-chevron-left mr-1"></i> Trước
                                </span>
                            <?php endif; ?>

                            <!-- Hiển thị các số trang -->
                            <?php
                            $max_links = 5; // số trang hiển thị tối đa
                            $start = max(1, $current_page - floor($max_links / 2));
                            $end = min($total_pages, $start + $max_links - 1);
                            if ($end - $start < $max_links - 1) {
                                $start = max(1, $end - $max_links + 1);
                            }

                            if ($start > 1) echo '<span class="px-2 text-gray-500">...</span>';

                            for ($i = $start; $i <= $end; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" 
                                class="px-4 py-2 rounded-lg font-semibold transition-colors 
                                        <?php echo $i == $current_page 
                                            ? 'bg-blue-600 text-white' 
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($end < $total_pages) echo '<span class="px-2 text-gray-500">...</span>'; ?>

                            <!-- Nút Sau -->
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors flex items-center">
                                    Sau <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-50 text-gray-400 rounded-lg flex items-center cursor-not-allowed">
                                    Sau <i class="fas fa-chevron-right ml-1"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
    <?php if (isset($cart_message)): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        showToast("<?php echo addslashes($cart_message); ?>", "success");
    });
    </script>
    <?php endif; ?>

    <!-- Notification Toast -->
    <div id="toast" class="fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span id="toastMessage">Sản phẩm đã được thêm vào giỏ hàng!</span>
            <button onclick="hideToast()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Xem nhanh sản phẩm</h3>
                    <button onclick="closeQuickView()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Product" class="w-full h-96 object-cover rounded-lg">
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-900 mb-4">MacBook Pro M3 14 inch</h4>
                        <p class="text-gray-600 mb-4">Chip M3 mạnh mẽ, màn hình Retina 14 inch, RAM 16GB, SSD 512GB với hiệu năng vượt trội cho công việc và giải trí.</p>
                        <div class="text-2xl font-bold text-blue-600 mb-6">45.990.000 VNĐ</div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng</label>
                                <input type="number" value="1" min="1" class="w-20 px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <button class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                                Thêm vào giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toast notification functions
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            toastMessage.textContent = message;
            
            // Change toast color based on type
            const toastContainer = toast.querySelector('div');
            if (type === 'success') {
                toastContainer.className = 'bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3';
            } else if (type === 'error') {
                toastContainer.className = 'bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3';
            }
            
            toast.classList.remove('translate-x-full');
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                hideToast();
            }, 3000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('translate-x-full');
        }

        // Quick view modal functions
        function openQuickView() {
            document.getElementById('quickViewModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeQuickView() {
            document.getElementById('quickViewModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Add event listeners for quick view buttons
        document.querySelectorAll('button:contains("Xem nhanh")').forEach(button => {
            button.addEventListener('click', openQuickView);
        });

        // Close modal when clicking outside
        document.getElementById('quickViewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuickView();
            }
        });

        // Filter functionality
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Add filter logic here
                console.log('Filter changed:', this.nextElementSibling.textContent);
            });
        });

        // Sort functionality
        // Sort functionality (dùng GET param 'sort')
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                const url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('sort', this.value);
                } else {
                    url.searchParams.delete('sort');
                }
                // khi đổi sort thì quay về page 1 cho chắc
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        }


        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, observerOptions);

        // Observe product cards for animation
        document.querySelectorAll('.animate-fade-in').forEach(el => {
            el.style.animationPlayState = 'paused';
            observer.observe(el);
        });

        // Mobile filter toggle
        function toggleMobileFilters() {
            const sidebar = document.querySelector('.lg\\:w-1\\/4');
            sidebar.classList.toggle('hidden');
        }
    </script>
</body>
</html>
