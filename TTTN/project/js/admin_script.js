// Lay thanh navbar va hop tai khoan tu header
let navbar = document.querySelector(".header .navbar");
let accountBox = document.querySelector(".header .account-box");

// Khi click vao nut menu (#menu-btn)
document.querySelector("#menu-btn").onclick = () => {
  navbar.classList.toggle("active"); // Hien thi hoac an thanh navbar
  accountBox.classList.remove("active"); // An hop tai khoan neu dang hien
};

// Khi click vao nut user (#user-btn)
document.querySelector("#user-btn").onclick = () => {
  accountBox.classList.toggle("active"); // Hien thi hoac an hop tai khoan
  navbar.classList.remove("active"); // An thanh navbar neu dang hien
};

// Khi keo trang (scroll)
window.onscroll = () => {
  navbar.classList.remove("active"); // An thanh navbar neu dang hien
  accountBox.classList.remove("active"); // An hop tai khoan neu dang hien
};

// Khi click vao nut close (#close-update)
document.querySelector("#close-update").onclick = () => {
  // An form sua san pham
  document.querySelector(".edit-product-form").style.display = "none";
  // Chuyen huong den trang admin_products.php
  window.location.href = "admin_products.php";
};
