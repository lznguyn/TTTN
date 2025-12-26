<?php
// ============ PHP XỬ LÝ ĐẦU TRANG ============

include 'config.php';
session_start();

// Kiểm tra đăng nhập
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header('Location: login.php');
    exit();
}

// Xử lý gửi form
$message = [];

if (isset($_POST['send'])) {

    $name   = mysqli_real_escape_string($conn, $_POST['name']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $number = $_POST['number'];
    $msg    = mysqli_real_escape_string($conn, $_POST['message']);

    // Kiểm tra trùng tin nhắn
    $select_message = mysqli_query(
        $conn,
        "SELECT * FROM `message` 
         WHERE name = '$name' 
           AND email = '$email' 
           AND number = '$number' 
           AND message = '$msg'"
    ) or die('query failed');

    if (mysqli_num_rows($select_message) > 0) {
        $message[] = 'Tin Nhắn Đã Được Gửi Rồi!';
    } else {
        mysqli_query(
            $conn,
            "INSERT INTO `message`(user_id, name, email, number, message) 
             VALUES('$user_id', '$name', '$email', '$number', '$msg')"
        ) or die('query failed');
        $message[] = 'Tin Nhắn Đã Được Gửi Thành Công!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXGEN LAPTOP - Liên Hệ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'slide-in-left': 'slideInLeft 0.8s ease-out',
                        'slide-in-right': 'slideInRight 0.8s ease-out',
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
                        slideInLeft: {
                            '0%': { transform: 'translateX(-50px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        slideInRight: {
                            '0%': { transform: 'translateX(50px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50">
    <!-- Header (Tailwind) -->
    <?php include 'header.php'; ?>

    <!-- Breadcrumb -->
    <div class="bg-gray-100 py-4">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="home.php" class="text-gray-600 hover:text-blue-600 transition-colors">Trang chủ</a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <span class="text-blue-600 font-medium">Liên hệ</span>
            </nav>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="py-12 bg-gradient-to-r from-blue-600 to-purple-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-slide-up">Liên Hệ Với Chúng Tôi</h1>
            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto animate-slide-up" style="animation-delay: 0.2s">
                Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy để lại thông tin để được tư vấn miễn phí!
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 sm:py-20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-16">
                <!-- Contact Info Cards -->
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-phone text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Hotline</h3>
                    <p class="text-gray-600 mb-4">Liên hệ ngay với chúng tôi</p>
                    <a href="tel:0911809129" class="text-blue-600 font-semibold hover:text-blue-700 transition-colors">0911809129</a>
                    <p class="text-sm text-gray-500 mt-2">8:00 - 22:00 (Hàng ngày)</p>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-8 text-center hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in" style="animation-delay: 0.1s">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-envelope text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Email</h3>
                    <p class="text-gray-600 mb-4">Gửi email cho chúng tôi</p>
                    <a
                    href="https://mail.google.com/mail/?view=cm&fs=1&to=support@nexgenlaptop.com&su=Tu%20van%20NEXGEN%20LAPTOP&body=Chao%20NEXGEN%2C%0A%0AToi%20can%20tu%20van..."
                    target="_blank"
                    rel="noopener"
                    class="text-blue-600 font-semibold hover:text-blue-700 transition-colors"
                    >
                    support@nexgenlaptop.com
                    </a>
                    <p class="text-sm text-gray-500 mt-2">Phản hồi trong 24h</p>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-8 text-center hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in" style="animation-delay: 0.2s">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-map-marker-alt text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Địa chỉ</h3>
                    <p class="text-gray-600 mb-4">Ghé thăm showroom của chúng tôi</p>
                   <a
                    href="https://www.google.com/maps/place/Mandarin+Oriental+Wangfujing+Beijing/@39.9388838,116.3974589,10z/data=!4m12!1m2!2m1!1zS2jDoWNoIHPhuqFu!3m8!1s0x35f052cec352f5b1:0x37bce60f8282d2cf!5m2!4m1!1i2!8m2!3d39.912174!4d116.411285!16s%2Fg%2F11gnn2zsg3!5m1!1e1?entry=ttu&g_ep=EgoyMDI1MTIwOS4wIKXMDSoASAFQAw%3D%3D"
                    target="_blank"
                    rel="noopener"
                    class="text-blue-600 font-semibold hover:text-blue-700 transition-colors"
                    >Mandarin Oriental Wangfujing Beijing<br/>
                    <span class="text-sm text-gray-500 font-normal">北京王府井文华东方酒店</span>
                    </a>
                </div>
            </div>

            <!-- Contact Form + Map -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Form (gắn PHP) -->
                <div class="bg-white rounded-2xl shadow-lg p-8 sm:p-10 animate-slide-in-left">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Gửi Tin Nhắn</h2>
                        <div class="w-24 h-1 bg-gradient-to-r from-blue-600 to-purple-600 mb-4"></div>
                        <p class="text-gray-600">Điền thông tin vào form bên dưới và chúng tôi sẽ liên hệ lại với bạn trong thời gian sớm nhất.</p>
                    </div>

                    <form action="" method="post" class="space-y-6">
                        <!-- Name Input -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Họ và tên <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       required 
                                       placeholder="Nhập họ và tên của bạn" 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-700 placeholder-gray-400">
                            </div>
                        </div>

                        <!-- Email Input -->
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       required 
                                       placeholder="Nhập địa chỉ email" 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-700 placeholder-gray-400">
                            </div>
                        </div>

                        <!-- Phone Input -->
                        <div class="space-y-2">
                            <label for="number" class="block text-sm font-medium text-gray-700">Số điện thoại <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="number" 
                                       id="number" 
                                       name="number" 
                                       required 
                                       placeholder="Nhập số điện thoại" 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-700 placeholder-gray-400">
                            </div>
                        </div>

                        <!-- Message Textarea -->
                        <div class="space-y-2">
                            <label for="message" class="block text-sm font-medium text-gray-700">Nội dung tin nhắn <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <i class="fas fa-comment-dots text-gray-400"></i>
                                </div>
                                <textarea id="message" 
                                          name="message" 
                                          required 
                                          rows="6" 
                                          placeholder="Nhập nội dung tin nhắn của bạn..." 
                                          class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-700 placeholder-gray-400 resize-none"></textarea>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                name="send" 
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Gửi Tin Nhắn
                        </button>

                        <p class="text-sm text-gray-500 text-center">
                            <i class="fas fa-lock mr-1"></i>
                            Thông tin của bạn được bảo mật tuyệt đối
                        </p>
                    </form>
                </div>

                <!-- Map + Info -->
                <div class="space-y-8 animate-slide-in-right">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="h-96 bg-gray-200 relative">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d391566.3391962266!2d116.3974589!3d39.9388838!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x35f052cec352f5b1%3A0x37bce60f8282d2cf!2sMandarin%20Oriental%20Wangfujing%20Beijing!5e0!3m2!1svi!2s!4v1766741105257!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Giờ Làm Việc</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-calendar-day text-blue-600"></i>
                                    <span class="font-medium text-gray-700">Thứ 2 - Thứ 6</span>
                                </div>
                                <span class="text-gray-900 font-semibold">8:00 - 22:00</span>
                            </div>
                            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-calendar-week text-blue-600"></i>
                                    <span class="font-medium text-gray-700">Thứ 7 - Chủ Nhật</span>
                                </div>
                                <span class="text-gray-900 font-semibold">9:00 - 21:00</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-calendar-alt text-blue-600"></i>
                                    <span class="font-medium text-gray-700">Ngày lễ</span>
                                </div>
                                <span class="text-gray-900 font-semibold">9:00 - 18:00</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl shadow-lg p-8 text-white">
                        <h3 class="text-2xl font-bold mb-4">Kết Nối Với Chúng Tôi</h3>
                        <p class="text-blue-100 mb-6">Theo dõi chúng tôi trên các mạng xã hội để cập nhật tin tức mới nhất</p>
                        <div class="flex space-x-4">
                            <a href="#" class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                                <i class="fab fa-facebook-f text-xl"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                                <i class="fab fa-instagram text-xl"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                                <i class="fab fa-youtube text-xl"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                                <i class="fab fa-tiktok text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 sm:py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Câu Hỏi Thường Gặp</h2>
                <div class="w-24 h-1 bg-gradient-to-r from-blue-600 to-purple-600 mx-auto mb-6"></div>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Một số câu hỏi phổ biến từ khách hàng</p>
            </div>

            <div class="max-w-3xl mx-auto space-y-4">
                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <button class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-100 transition-colors" onclick="toggleFAQ(1)">
                        <span class="font-semibold text-gray-900">Làm thế nào để đặt hàng?</span>
                        <i id="faq-icon-1" class="fas fa-chevron-down text-blue-600 transition-transform"></i>
                    </button>
                    <div id="faq-content-1" class="hidden px-6 pb-4">
                        <p class="text-gray-600">Bạn có thể đặt hàng trực tiếp trên website, qua hotline 1900-1234, hoặc đến trực tiếp showroom của chúng tôi. Chúng tôi hỗ trợ nhiều hình thức thanh toán tiện lợi.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <button class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-100 transition-colors" onclick="toggleFAQ(2)">
                        <span class="font-semibold text-gray-900">Chính sách bảo hành như thế nào?</span>
                        <i id="faq-icon-2" class="fas fa-chevron-down text-blue-600 transition-transform"></i>
                    </button>
                    <div id="faq-content-2" class="hidden px-6 pb-4">
                        <p class="text-gray-600">Tất cả sản phẩm đều được bảo hành chính hãng từ 12-36 tháng tùy theo từng dòng máy. Chúng tôi cũng cung cấp dịch vụ bảo hành mở rộng.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <button class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-100 transition-colors" onclick="toggleFAQ(3)">
                        <span class="font-semibold text-gray-900">Có giao hàng tận nơi không?</span>
                        <i id="faq-icon-3" class="fas fa-chevron-down text-blue-600 transition-transform"></i>
                    </button>
                    <div id="faq-content-3" class="hidden px-6 pb-4">
                        <p class="text-gray-600">Có, chúng tôi giao hàng miễn phí toàn quốc cho đơn hàng trên 10 triệu đồng. Thời gian giao hàng từ 1-3 ngày tùy khu vực.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <button class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-100 transition-colors" onclick="toggleFAQ(4)">
                        <span class="font-semibold text-gray-900">Có hỗ trợ trả góp không?</span>
                        <i id="faq-icon-4" class="fas fa-chevron-down text-blue-600 transition-transform"></i>
                    </button>
                    <div id="faq-content-4" class="hidden px-6 pb-4">
                        <p class="text-gray-600">Có, chúng tôi hỗ trợ trả góp 0% lãi suất qua thẻ tín dụng và các công ty tài chính. Thủ tục đơn giản, duyệt nhanh chóng.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer (Tailwind) -->
    <?php include 'footer.php'; ?>

    <!-- Notification Toast -->
    <div id="toast" class="fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 max-w-md">
            <i class="fas fa-check-circle text-xl"></i>
            <span id="toastMessage">Tin nhắn đã được gửi thành công!</span>
            <button onclick="hideToast()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastContainer = toast.querySelector('div');

            toastMessage.textContent = message;

            if (type === 'success') {
                toastContainer.className = 'bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 max-w-md';
            } else if (type === 'error') {
                toastContainer.className = 'bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 max-w-md';
            }

            toast.classList.remove('translate-x-full');

            setTimeout(hideToast, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('translate-x-full');
        }

        function toggleFAQ(id) {
            const content = document.getElementById(`faq-content-${id}`);
            const icon = document.getElementById(`faq-icon-${id}`);
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }

        // Validation client-side
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const number = document.getElementById('number').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!name || !email || !number || !message) {
                e.preventDefault();
                showToast('Vui lòng điền đầy đủ thông tin!', 'error');
                return false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showToast('Email không hợp lệ!', 'error');
                return false;
            }
            
            if (number.length < 10) {
                e.preventDefault();
                showToast('Số điện thoại không hợp lệ!', 'error');
                return false;
            }
        });

        // Đẩy thông báo từ PHP ra toast
        <?php if (!empty($message)): ?>
            <?php foreach ($message as $msg): ?>
                showToast(<?php echo json_encode($msg); ?>, <?php echo (strpos($msg, 'Thành Công') !== false) ? json_encode('success') : json_encode('error'); ?>);
            <?php endforeach; ?>
        <?php endif; ?>

        // Smooth scrolling
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

        // Intersection Observer cho animation
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

        document.querySelectorAll('.animate-fade-in, .animate-slide-in-left, .animate-slide-in-right').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
