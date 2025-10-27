// Chọn phần tử `userBox` chứa thông tin của người dùng trong phần header
let userBox = document.querySelector('.header .header-2 .user-box');

// Thêm sự kiện nhấn chuột vào nút có id 'user-btn'
document.querySelector('#user-btn').onclick = () => {
   // Thêm hoặc loại bỏ lớp 'active' cho `userBox` để hiển thị hoặc ẩn thông tin người dùng
   userBox.classList.toggle('active');
   // Loại bỏ lớp 'active' khỏi `navbar` để đảm bảo thanh điều hướng được ẩn khi mở `userBox`
   navbar.classList.remove('active');
}

// Chọn phần tử `navbar` chứa menu điều hướng trong phần header
let navbar = document.querySelector('.header .header-2 .navbar');

// Thêm sự kiện nhấn chuột vào nút có id 'menu-btn'
document.querySelector('#menu-btn').onclick = () => {
   // Thêm hoặc loại bỏ lớp 'active' cho `navbar` để hiển thị hoặc ẩn menu điều hướng
   navbar.classList.toggle('active');
   // Loại bỏ lớp 'active' khỏi `userBox` để đảm bảo thông tin người dùng được ẩn khi mở `navbar`
   userBox.classList.remove('active');
}

// Thêm sự kiện khi người dùng cuộn trang
window.onscroll = () => {
   // Ẩn `userBox` khi người dùng cuộn trang
   userBox.classList.remove('active');
   // Ẩn `navbar` khi người dùng cuộn trang
   navbar.classList.remove('active');

   // Nếu người dùng cuộn xuống quá 60px
   if (window.scrollY > 60) {
      // Thêm lớp 'active' cho phần `header-2` để thay đổi kiểu hiển thị, có thể là để cố định header khi cuộn
      document.querySelector('.header .header-2').classList.add('active');
   } else {
      // Nếu cuộn lên trên 60px, loại bỏ lớp 'active' khỏi `header-2`
      document.querySelector('.header .header-2').classList.remove('active');
   }
}
