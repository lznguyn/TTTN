<?php
include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;
//Kiem tra nguoi dung co dang nhap hay chưa
if(isset($_POST['add_to_cart'])){ // kiểm tra người dùng nhấn nút thêm hàng 
   
    //nếu chưa đăng nhập ,chuyển hướng đến trang login.php
    if ($user_id == null){
        header('Location:login.php');
        exit();
    }
  //mysqli_real_escape_string xu ly chuoi du lieu dau vao an toan
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = (int)$_POST['product_quantity'];// ep kieu $product_quantity de dau vao la so

    $check_cart_number = mysqli_query($conn,"SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

    
    if (mysqli_num_rows($check_cart_number) > 0) {//kiem tra san pham da co san trong gio hang hay chua
        $_SESSION['cart_message'] = 'Sản phẩm đã được thêm vào giỏ hàng!';
    } else {
        mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');//neu chua co san pham thì them san pham vao gio
        $_SESSION['cart_message'] = 'Sản phẩm đã được thêm vào giỏ hàng!';
    }

    // chuyen huong ve trang hien tai
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXGEN LAPTOP - Trang Chủ</title>
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
<!-- Header Placeholder -->
<?php include 'header.php'?> 

    <!-- Hero Section -->
    <section id="hero" class="relative h-[600px] md:h-[700px] overflow-hidden">
        <!-- Background Image Container -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/50 z-10"></div>
        <div id="heroBackground" class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-all duration-1000 ease-in-out" style="background-image: url('https://images.unsplash.com/photo-1496181133206-80ce9b88a853?ixlib=rb-4.0.3&auto=format&fit=crop&w=2071&q=80')"></div>
        
        <!-- Hero Content -->
        <div class="relative z-20 h-full flex items-center">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 animate-slide-up">
                        LAPTOP CHÍNH HÃNG
                        <span class="block text-blue-400">CAO CẤP</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-200 mb-8 leading-relaxed animate-slide-up" style="animation-delay: 0.2s">
                        Với thiết kế hiện đại và đa dạng mẫu mã, NEXGEN LAPTOP tự tin mang đến cho bạn những lựa chọn hoàn hảo, giúp bạn tìm thấy chiếc laptop đẹp và phù hợp nhất với nhu cầu sử dụng của mình.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 animate-slide-up" style="animation-delay: 0.4s">
                        <a href="#products" class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-laptop mr-2"></i>
                            Xem Sản Phẩm
                        </a>
                        <a href="#about" class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-gray-900 transition-all duration-300">
                            Tìm Hiểu Thêm
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20">
            <div class="animate-bounce-gentle">
                <i class="fas fa-chevron-down text-white text-2xl"></i>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-16 sm:py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    SẢN PHẨM MỚI NHẤT
                </h2>
                <div class="w-24 h-1 bg-gradient-to-r from-blue-600 to-purple-600 mx-auto mb-6"></div>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Khám phá bộ sưu tập laptop cao cấp với công nghệ tiên tiến nhất
                </p>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Product Card-->
                <?php
                    $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 6") or die('query failed');
                    if (mysqli_num_rows($select_products) > 0) {
                    while ($fetch = mysqli_fetch_assoc($select_products)) {
                ?>
                <form method="post" class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
                    <div class="relative overflow-hidden">
                        <img src="uploaded_img/<?php echo $fetch['image']; ?>" 
                             alt="<?php echo htmlspecialchars($fetch['name']); ?>" 
                             class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500">

                        <!-- Tag (tùy chỉnh theo category nếu có) -->
                        <div class="absolute top-4 left-4">
                            <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold">Mới</span>
                        </div>

                        <!-- Yêu thích -->
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button type="button" class="bg-white/90 p-2 rounded-full hover:bg-white transition-colors">
                                <i class="fas fa-heart text-gray-600 hover:text-red-500"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                            <?php echo htmlspecialchars($fetch['name']); ?>
                        </h3>
                        <p class="text-gray-600 text-sm mb-4">
                            <?php echo htmlspecialchars($fetch['description'] ?? 'Sản phẩm chất lượng cao'); ?>
                        </p>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-bold text-blue-600">
                                <?php echo number_format($fetch['price'], 0, ',', '.'); ?> VNĐ
                            </span>
                            <div class="flex items-center text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mb-4">
                            <label class="text-sm font-medium text-gray-700">Số lượng:</label>
                            <input type="number" name="product_quantity" value="1" min="1"
                                   class="w-16 px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch['name']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo $fetch['price']; ?>">
                        <input type="hidden" name="product_image" value="<?php echo $fetch['image']; ?>">

                        <button type="submit" name="add_to_cart"
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Thêm vào giỏ hàng
                        </button>
                    </div>
                </form>
            <?php
                }
            } else {
                echo '<p class="text-center text-gray-500 col-span-3">Chưa có sản phẩm!</p>';
            }
            ?>                
            </div>

            <!-- View More Button -->
            <div class="text-center mt-12">
                <a href="shop.php" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-gray-800 to-gray-900 text-white font-semibold rounded-lg hover:from-gray-900 hover:to-black transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-th-large mr-2"></i>
                    Xem Tất Cả Sản Phẩm
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 sm:py-20 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Image Column -->
                <div class="relative">
                    <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                        <img id="aboutImage" 
                             src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="About Us" 
                             class="w-full h-[500px] object-cover transition-all duration-1000 ease-in-out">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>
                    <!-- Decorative Elements -->
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-blue-500 rounded-full opacity-20 animate-pulse"></div>
                    <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-purple-500 rounded-full opacity-20 animate-pulse" style="animation-delay: 1s"></div>
                </div>

                <!-- Content Column -->
                <div class="space-y-8">
                    <div>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                            VỀ CHÚNG TÔI
                        </h2>
                        <div class="w-24 h-1 bg-gradient-to-r from-blue-600 to-purple-600 mb-6"></div>
                        <p class="text-lg text-gray-600 leading-relaxed mb-6">
                            Chúng tôi đánh giá cao sự quan tâm của bạn đến những chiếc laptop của chúng tôi. Với hơn 10 năm kinh nghiệm trong ngành công nghệ, NEXGEN LAPTOP cam kết mang đến cho khách hàng những sản phẩm chất lượng cao với dịch vụ tốt nhất.
                        </p>
                        <p class="text-gray-600 leading-relaxed mb-8">
                            Nếu bạn có bất kỳ câu hỏi hoặc cần hỗ trợ, xin vui lòng liên hệ với chúng tôi. Chúng tôi mong được phục vụ bạn.
                        </p>
                    </div>

                    <!-- Features Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Bảo Hành Chính Hãng</h4>
                                <p class="text-gray-600 text-sm">Bảo hành toàn diện từ 12-36 tháng</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shipping-fast text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Giao Hàng Nhanh</h4>
                                <p class="text-gray-600 text-sm">Miễn phí giao hàng toàn quốc</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-headset text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Hỗ Trợ 24/7</h4>
                                <p class="text-gray-600 text-sm">Tư vấn và hỗ trợ kỹ thuật</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-medal text-orange-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Chất Lượng Cao</h4>
                                <p class="text-gray-600 text-sm">Sản phẩm chính hãng 100%</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="about.php" class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-info-circle mr-2"></i>
                            Tìm Hiểu Thêm
                        </a>
                        <a href="contact.php" class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-blue-600 text-blue-600 font-semibold rounded-lg hover:bg-blue-600 hover:text-white transition-all duration-300">
                            <i class="fas fa-phone mr-2"></i>
                            Liên Hệ Ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 sm:py-20 bg-gradient-to-r from-blue-600 to-purple-700 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle at 25% 25%, white 2px, transparent 2px); background-size: 50px 50px;"></div>
        </div>
        
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                    BẠN CÓ CÂU HỎI NÀO KHÔNG?
                </h2>
                <div class="w-24 h-1 bg-white mx-auto mb-8"></div>
                <p class="text-lg sm:text-xl text-blue-100 leading-relaxed mb-12 max-w-3xl mx-auto">
                    Chúng tôi rất trân trọng sự quan tâm của bạn đến những chiếc laptop của chúng tôi. Nếu bạn có bất kỳ câu hỏi nào hoặc muốn để lại đánh giá, xin vui lòng liên hệ với chúng tôi. Chúng tôi luôn sẵn sàng hỗ trợ bạn!
                </p>
                
                <!-- Contact Options -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-phone text-white text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-white mb-2">Hotline</h4>
                        <p class="text-blue-100">0911809129</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-envelope text-white text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-white mb-2">Email</h4>
                        <p class="text-blue-100">support@nexgenlaptop.com</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-white mb-2">Địa chỉ</h4>
                        <p class="text-blue-100">Mandarin Oriental Wangfujing Beijing</p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="contact.php" class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-comments mr-2"></i>
                        LIÊN HỆ NGAY
                    </a>
                    <a href="tel:19001234" class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-blue-600 transition-all duration-300">
                        <i class="fas fa-phone mr-2"></i>
                        GỌI HOTLINE
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'?>

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

    <!-- Scripts -->
    <script>
        // Background image slider for hero section
        const heroBackgroundImages = [
            'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?ixlib=rb-4.0.3&auto=format&fit=crop&w=2071&q=80',
            'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?ixlib=rb-4.0.3&auto=format&fit=crop&w=2071&q=80',
            'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?ixlib=rb-4.0.3&auto=format&fit=crop&w=2071&q=80'
        ];

        let currentHeroImageIndex = 0;
        const heroBackground = document.getElementById('heroBackground');

        function changeHeroBackground() {
            currentHeroImageIndex = (currentHeroImageIndex + 1) % heroBackgroundImages.length;
            heroBackground.style.backgroundImage = `url('${heroBackgroundImages[currentHeroImageIndex]}')`;
        }

        // Change hero background every 5 seconds
        setInterval(changeHeroBackground, 5000);

        // Background image slider for about section
        const aboutBackgroundImages = [
            'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1553877522-43269d4ea984?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
        ];

        let currentAboutImageIndex = 0;
        const aboutImage = document.getElementById('aboutImage');

        function changeAboutBackground() {
            currentAboutImageIndex = (currentAboutImageIndex + 1) % aboutBackgroundImages.length;
            aboutImage.src = aboutBackgroundImages[currentAboutImageIndex];
        }

        // Change about background every 4 seconds
        setInterval(changeAboutBackground, 4000);

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

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

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-up');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.group, .space-y-8 > div').forEach(el => {
            observer.observe(el);
        });

        // Add to cart functionality (if needed for PHP integration)
        document.querySelectorAll('form[method="post"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                // You can add client-side validation here if needed
                // The form will still submit to PHP for server-side processing
            });
        });

        // Show cart message if exists (for PHP integration)
        <?php if (isset($_SESSION['cart_message'])): ?>
        showToast("<?php echo $_SESSION['cart_message']; ?>", 'success');
        <?php unset($_SESSION['cart_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>