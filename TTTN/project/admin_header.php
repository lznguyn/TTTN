<?php
// Hiển thị message (nếu có)
if (isset($message)) {
    // Ép về mảng cho chắc
    $messages = is_array($message) ? $message : [$message];

    foreach ($messages as $msg) {
        echo '
        <div class="message">
            <span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<header class="header">

    <div class="flex header-inner">

        <!-- Logo -->
        <a href="admin_page.php" class="logo">
            <i class="fas fa-laptop-code"></i>
            <span class="logo-main">Admin</span><span class="logo-sub">LAPTOP</span>
        </a>

        <!-- Navbar -->
        <nav class="navbar">
            <a href="admin_page.php">Trang chủ</a>
            <a href="admin_products.php">Sản phẩm</a>
            <a href="admin_orders.php">Đặt hàng</a>
            <a href="admin_users.php">Users</a>
            <a href="admin_contacts.php">Tin nhắn</a>

            <div class="dropdown">
                <button type="button" class="dropbtn" data-dropdown="config">
                    Cấu hình
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </button>

                <div class="dropdown-content" data-dropdown-content="config">
                    <a href="admin_price.php">Cấu hình giá</a>
                    <a href="admin_categories.php">Cấu hình laptop</a>
                    <a href="admin_coupou.php">Cấu hình mã giảm giá</a>
                </div>
            </div>
        </nav>

        <!-- Icons + account -->
        <div class="header-right">
            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="user-btn" class="fas fa-user"></div>
            </div>

            <div class="account-box">
                <p>Tên người dùng : <span><?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?></span></p>
                <p>Email : <span><?php echo htmlspecialchars($_SESSION['admin_email'] ?? ''); ?></span></p>
                <a href="logout.php" class="delete-btn">Đăng xuất</a>
            </div>
        </div>

    </div>

</header>
<script>
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-dropdown]');
    const anyDropdown = e.target.closest('.dropdown');

    // click đúng nút dropdown
    if (btn) {
      const key = btn.getAttribute('data-dropdown');
      const content = document.querySelector(`[data-dropdown-content="${key}"]`);
      content?.classList.toggle('show');
      return;
    }

    // click ra ngoài thì đóng hết
    if (!anyDropdown) {
      document.querySelectorAll('.dropdown-content.show').forEach(el => el.classList.remove('show'));
    }
  });
</script>
<style>
    /* ===== HEADER ===== */
.header{
  position: sticky;
  top: 0;
  z-index: 1000;
  background: #fff;
  border-bottom: 1px solid #eee;
}

.header .header-inner{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px 18px;
}

/* Logo */
.logo{
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
  text-decoration: none;
  color: #111;
  white-space: nowrap;
}
.logo i{ font-size: 20px; }
.logo-main{ font-size: 18px; }
.logo-sub{
  font-size: 18px;
  color: #0ea5e9; /* xanh nhẹ */
  margin-left: 2px;
}

/* Navbar */
.navbar{
  display: flex;
  align-items: center;
  gap: 14px;
}
.navbar a{
  text-decoration: none;
  color: #222;
  font-weight: 600;
  padding: 8px 10px;
  border-radius: 10px;
  transition: .2s;
}
.navbar a:hover{
  background: #f5f5f5;
}

/* Dropdown */
.dropdown{ position: relative; }
.dropbtn{
  cursor: pointer;
  border: none;
  background: transparent;
  font-weight: 700;
  padding: 8px 10px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #222;
  transition: .2s;
}
.dropbtn:hover{ background: #f5f5f5; }

.dropdown-content{
  display: none;
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  min-width: 220px;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 14px;
  box-shadow: 0 10px 30px rgba(0,0,0,.08);
  padding: 8px;
  z-index: 2000;
}
.dropdown-content.show{ display: block; }

.dropdown-content a{
  display: block;
  padding: 10px 10px;
  border-radius: 10px;
  font-weight: 600;
}
.dropdown-content a:hover{
  background: #f3f4f6;
}

/* Right side */
.header-right{
  position: relative;
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Icons */
.icons{
  display: flex;
  align-items: center;
  gap: 10px;
}
.icons .fas{
  font-size: 18px;
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  border-radius: 12px;
  cursor: pointer;
  background: #f3f4f6;
  transition: .2s;
}
.icons .fas:hover{ background: #e5e7eb; }

/* Account box */
.account-box{
  display: none;
  position: absolute;
  right: 0;
  top: calc(100% + 10px);
  width: 270px;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 14px;
  box-shadow: 0 10px 30px rgba(0,0,0,.08);
  padding: 12px 14px;
  z-index: 3000;
}
.account-box.active{ display: block; }

.account-box p{
  margin: 8px 0;
  font-weight: 700;
  color: #111;
}
.account-box span{
  font-weight: 600;
  color: #374151;
}
.delete-btn{
  display: inline-block;
  margin-top: 10px;
  width: 100%;
  text-align: center;
  padding: 10px 12px;
  border-radius: 12px;
  background: #ef4444;
  color: #fff;
  font-weight: 800;
  text-decoration: none;
}
.delete-btn:hover{ filter: brightness(.95); }

/* Messages */
.message{
  margin: 10px 18px;
  padding: 10px 12px;
  border-radius: 12px;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.message span{ font-weight: 600; color: #0f172a; }
.message i{ cursor: pointer; }

/* ===== RESPONSIVE ===== */
#menu-btn{ display: none; }

@media (max-width: 992px){
  #menu-btn{ display: grid; }

  .navbar{
    position: absolute;
    left: 18px;
    right: 18px;
    top: calc(100% + 10px);
    background: #fff;
    border: 1px solid #eee;
    border-radius: 14px;
    padding: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);

    display: none;
    flex-direction: column;
    align-items: stretch;
    gap: 6px;
    z-index: 2500;
  }
  .navbar.active{ display: flex; }

  .navbar a, .dropbtn{
    width: 100%;
    justify-content: space-between;
  }

  /* dropdown content trong mobile: bung ra dạng block, không bị trôi */
  .dropdown-content{
    position: static;
    box-shadow: none;
    border: 0;
    padding: 6px 0 0;
    min-width: unset;
  }
}
/* ===== EQUAL FONT SIZE IN HEADER ===== */
.header{
  font-size: 18px; /* chọn 16/17/18 tuỳ bạn */
}

/* Logo chữ */
.logo-main,
.logo-sub{
  font-size: inherit;   /* bằng nhau với header */
  line-height: 1;
}

/* Link menu */
.navbar a{
  font-size: inherit;   /* bằng nhau */
  line-height: 1;
}

/* Nút dropdown */
.dropbtn{
  font-size: inherit;   /* bằng nhau */
  line-height: 1;
  font-weight: 600;     /* giảm nhẹ để không “to cảm giác” */
}

/* Chữ trong account box */
.account-box,
.account-box p,
.account-box span{
  font-size: 16px;      /* thường account nhỏ hơn 1 chút cho đẹp */
}

/* Nếu bạn muốn account cũng bằng luôn, đổi 16px -> inherit */

</style>

