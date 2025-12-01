<?php
include 'config.php';

session_start();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXGEN LAPTOP - Về Chúng Tôi</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- CSS cũ nếu anh vẫn dùng chung cho toàn site -->
    <link rel="stylesheet" href="../project/css/style.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'slide-in-left': 'slideInLeft 0.8s ease-out',
                        'slide-in-right': 'slideInRight 0.8s ease-out',
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
                        slideInLeft: {
                            '0%': { transform: 'translateX(-50px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        slideInRight: {
                            '0%': { transform: 'translateX(50px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
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

    <?php include 'header.php'; ?>

    <!-- Breadcrumb -->
    <div class="bg-gray-100 py-4 mt-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="home.php" class="text-gray-600 hover:text-blue-600 transition-colors">Trang chủ</a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <span class="text-blue-600 font-medium">Về chúng tôi</span>
            </nav>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="relative h-[500px] md:h-[600px] overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/50 z-10"></div>
        <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&auto=format&fit=crop&w=2069&q=80" 
             alt="About Us" 
             class="absolute inset-0 w-full h-full object-cover">
        
        <div class="relative z-20 h-full flex items-center">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 animate-slide-up">
                        Về Chúng Tôi
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-200 mb-8 leading-relaxed animate-slide-up" style="animation-delay: 0.2s">
                        Chúng tôi là đối tác tin cậy của bạn trong việc cung cấp các giải pháp công nghệ laptop cao cấp với chất lượng vượt trội và dịch vụ tận tâm.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 animate-slide-up" style="animation-delay: 0.4s">
                        <a href="contact.php" class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-envelope mr-2"></i>
                            Liên Hệ Ngay
                        </a>
                        <a href="shop.php" class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-gray-900 transition-all duration-300">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Xem Sản Phẩm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Company Story Section -->
    <section class="py-16 sm:py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Image Column -->
                <div class="relative animate-slide-in-left">
                    <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Our Team" 
                             class="w-full h-[500px] object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>
                    <!-- Stats Overlay -->
                    <div class="absolute -bottom-8 -right-8 bg-white rounded-2xl shadow-2xl p-6 hidden lg:block">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-blue-600 mb-2">10+</div>
                            <div class="text-gray-600 font-medium">Năm Kinh Nghiệm</div>
                        </div>
                    </div>
                </div>

                <!-- Content Column -->
                <div class="space-y-8 animate-slide-in-right">
                    <div>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                            Chào Mừng Đến Với NEXGEN LAPTOP
                        </h2>
                        <div class="w-24 h-1 bg-gradient-to-r from-blue-600 to-purple-600 mb-6"></div>
                        <p class="text-lg text-gray-600 leading-relaxed mb-6">
                            Chúng tôi tự hào cung cấp chất lượng đặc biệt và dịch vụ vô song. Từ giá cả cạnh tranh đến hỗ trợ khách hàng tận tâm, chúng tôi nỗ lực vượt qua mong đợi ở mọi bước.
                        </p>
                        <p class="text-gray-600 leading-relaxed mb-8">
                            Với hơn 10 năm kinh nghiệm trong ngành công nghệ, chúng tôi hiểu rõ nhu cầu của khách hàng và cam kết mang đến những sản phẩm laptop chính hãng, chất lượng cao với mức giá tốt nhất thị trường. Trải nghiệm sự xuất sắc cùng chúng tôi!
                        </p>
                    </div>

                    <!-- Key Features -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-certificate text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Chính Hãng 100%</h4>
                                <p class="text-gray-600 text-sm">Sản phẩm nhập khẩu chính hãng</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Bảo Hành Dài Hạn</h4>
                                <p class="text-gray-600 text-sm">Bảo hành 12-36 tháng</p>
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
                                <i class="fas fa-truck text-orange-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Giao Hàng Nhanh</h4>
                                <p class="text-gray-600 text-sm">Miễn phí toàn quốc</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 sm:py-20 bg-gradient-to-r from-blue-600 to-purple-700 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle at 25% 25%, white 2px, transparent 2px); background-size: 50px 50px;"></div>
        </div>
        
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center animate-fade-in">
                    <div class="text-4xl md:text-5xl font-bold text-white mb-2">10+</div>
                    <div class="text-blue-100 font-medium">Năm Kinh Nghiệm</div>
                </div>
                <div class="text-center animate-fade-in" style="animation-delay: 0.1s">
                    <div class="text-4xl md:text-5xl font-bold text-white mb-2">50K+</div>
                    <div class="text-blue-100 font-medium">Khách Hàng</div>
                </div>
                <div class="text-center animate-fade-in" style="animation-delay: 0.2s">
                    <div class="text-4xl md:text-5xl font-bold text-white mb-2">500+</div>
                    <div class="text-blue-100 font-medium">Sản Phẩm</div>
                </div>
                <div class="text-center animate-fade-in" style="animation-delay: 0.3s">
                    <div class="text-4xl md:text-5xl font-bold text-white mb-2">99%</div>
                    <div class="text-blue-100 font-medium">Hài Lòng</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Reviews Section -->
    <section class="py-16 sm:py-20 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Đánh Giá Từ Khách Hàng
                </h2>
                <div class="w-24 h-1 bg-gradient-to-r from-blue-600 to-purple-600 mx-auto mb-6"></div>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Hàng nghìn khách hàng đã tin tưởng và hài lòng với sản phẩm và dịch vụ của chúng tôi
                </p>
            </div>

            <!-- Reviews Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Review Card 1 -->
                <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in">
                    <div class="flex items-center mb-6">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                             alt="Customer" 
                             class="w-16 h-16 rounded-full object-cover border-4 border-blue-100">
                        <div class="ml-4">
                            <h4 class="font-bold text-gray-900">Nguyễn Hữu Đại</h4>
                            <div class="flex items-center text-yellow-400 mt-1">
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star-half-alt text-sm"></i>
                                <span class="text-gray-600 text-sm ml-2">4.5/5</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <i class="fas fa-quote-left text-blue-600 text-2xl opacity-50"></i>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Trải nghiệm tuyệt vời khi mua hàng ở đây. Chất lượng máy tính xách tay và dịch vụ khách hàng rất tuyệt vời. Nhân viên tư vấn nhiệt tình và chuyên nghiệp.
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="far fa-clock mr-2"></i>
                        <span>2 tuần trước</span>
                    </div>
                </div>

                <!-- Review Card 2 -->
                <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in" style="animation-delay: 0.1s">
                    <div class="flex items-center mb-6">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                             alt="Customer" 
                             class="w-16 h-16 rounded-full object-cover border-4 border-blue-100">
                        <div class="ml-4">
                            <h4 class="font-bold text-gray-900">Trương Anh Tài</h4>
                            <div class="flex items-center text-yellow-400 mt-1">
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <span class="text-gray-600 text-sm ml-2">5.0/5</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <i class="fas fa-quote-left text-blue-600 text-2xl opacity-50"></i>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Giao hàng nhanh, dịch vụ khách hàng tuyệt vời và chiếc máy tính xách tay này vượt quá mong đợi của tôi. Giá cả hợp lý, chất lượng đảm bảo.
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="far fa-clock mr-2"></i>
                        <span>1 tháng trước</span>
                    </div>
                </div>

                <!-- Review Card 3 -->
                <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in" style="animation-delay: 0.2s">
                    <div class="flex items-center mb-6">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                             alt="Customer" 
                             class="w-16 h-16 rounded-full object-cover border-4 border-blue-100">
                        <div class="ml-4">
                            <h4 class="font-bold text-gray-900">Lê Thị Minh Anh</h4>
                            <div class="flex items-center text-yellow-400 mt-1">
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <span class="text-gray-600 text-sm ml-2">5.0/5</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <i class="fas fa-quote-left text-blue-600 text-2xl opacity-50"></i>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Sản phẩm chất lượng cao, giá cả phải chăng. Đội ngũ hỗ trợ rất nhiệt tình và chuyên nghiệp. Tôi rất hài lòng với lựa chọn của mình.
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="far fa-clock mr-2"></i>
                        <span>3 tuần trước</span>
                    </div>
                </div>

                <!-- Review Card 4 -->
                <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in" style="animation-delay: 0.3s">
                    <div class="flex items-center mb-6">
                        <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                             alt="Customer" 
                             class="w-16 h-16 rounded-full object-cover border-4 border-blue-100">
                        <div class="ml-4">
                            <h4 class="font-bold text-gray-900">Phạm Văn Hùng</h4>
                            <div class="flex items-center text-yellow-400 mt-1">
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="far fa-star text-sm"></i>
                                <span class="text-gray-600 text-sm ml-2">4.0/5</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <i class="fas fa-quote-left text-blue-600 text-2xl opacity-50"></i>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Laptop chạy mượt mà, cấu hình mạnh mẽ. Dịch vụ bảo hành tốt, nhân viên hỗ trợ nhanh chóng. Rất đáng để mua sắm tại đây.
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="far fa-clock mr-2"></i>
                        <span>1 tuần trước</span>
                    </div>
                </div>

                <!-- Review Card 5 -->
                <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in" style="animation-delay: 0.4s">
                    <div class="flex items-center mb-6">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                             alt="Customer" 
                             class="w-16 h-16 rounded-full object-cover border-4 border-blue-100">
                        <div class="ml-4">
                            <h4 class="font-bold text-gray-900">Võ Thị Hương</h4>
                            <div class="flex items-center text-yellow-400 mt-1">
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star-half-alt text-sm"></i>
                                <span class="text-gray-600 text-sm ml-2">4.5/5</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <i class="fas fa-quote-left text-blue-600 text-2xl opacity-50"></i>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Mua laptop cho công việc, rất hài lòng với hiệu năng và thiết kế. Giá tốt, dịch vụ chu đáo. Sẽ giới thiệu cho bạn bè.
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="far fa-clock mr-2"></i>
                        <span>4 ngày trước</span>
                    </div>
                </div>

                <!-- Review Card 6 -->
                <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in" style="animation-delay: 0.5s">
                    <div class="flex items-center mb-6">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                             alt="Customer" 
                             class="w-16 h-16 rounded-full object-cover border-4 border-blue-100">
                        <div class="ml-4">
                            <h4 class="font-bold text-gray-900">Đặng Quốc Tuấn</h4>
                            <div class="flex items-center text-yellow-400 mt-1">
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <i class="fas fa-star text-sm"></i>
                                <span class="text-gray-600 text-sm ml-2">5.0/5</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <i class="fas fa-quote-left text-blue-600 text-2xl opacity-50"></i>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Chất lượng sản phẩm tuyệt vời, giao hàng đúng hẹn. Nhân viên tư vấn nhiệt tình, giải đáp mọi thắc mắc. Rất đáng tin cậy!
                    </p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="far fa-clock mr-2"></i>
                        <span>2 tháng trước</span>
                    </div>
                </div>
            </div>

            <!-- View All Reviews Button -->
            <div class="text-center mt-12">
                <button class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-comments mr-2"></i>
                    Xem Tất Cả Đánh Giá
                </button>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 sm:py-20 bg-gradient-to-r from-blue-600 to-purple-700 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle at 25% 25%, white 2px, transparent 2px); background-size: 50px 50px;"></div>
        </div>
        
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                    Sẵn Sàng Trải Nghiệm?
                </h2>
                <p class="text-lg sm:text-xl text-blue-100 leading-relaxed mb-12 max-w-3xl mx-auto">
                    Hãy để chúng tôi giúp bạn tìm chiếc laptop hoàn hảo cho nhu cầu của bạn. Liên hệ ngay để được tư vấn miễn phí!
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="contact.php" class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-envelope mr-2"></i>
                        Liên Hệ Ngay
                    </a>
                    <a href="shop.php" class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-blue-600 transition-all duration-300">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        Xem Sản Phẩm
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <!-- Scripts -->
    <script>
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
        document.querySelectorAll('.animate-fade-in, .animate-slide-in-left, .animate-slide-in-right').forEach(el => {
            observer.observe(el);
        });

        // Counter animation for stats
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target + (element.textContent.includes('+') ? '+' : element.textContent.includes('%') ? '%' : '';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start) + (element.textContent.includes('+') ? '+' : element.textContent.includes('%') ? '%' : '';
                }
            }, 16);
        }

        // Trigger counter animation when stats section is visible
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.text-4xl');
                    counters.forEach(counter => {
                        const text = counter.textContent;
                        const target = parseInt(text.replace(/\D/g, ''));
                        animateCounter(counter, target);
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.from-blue-600.to-purple-700');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }
    </script>
</body>
</html>
