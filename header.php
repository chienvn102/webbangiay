<!-- Top Banner -->
<div class="top-banner">
    FREE SHIPPING VỚI HOÁ ĐƠN TỪ 900K ! | HÀNG 2 TUẦN NHẬN ĐỔI - GIÀY NỬA NĂM BẢO HÀNH
</div>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="index.php">ANANAS</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        SẢN PHẨM
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="products.php?gender=nam">CHO NAM</a></li>
                        <li><a class="dropdown-item" href="products.php?gender=nu">CHO NỮ</a></li>
                        <li><a class="dropdown-item" href="products.php?sale=1">OUTLET SALE</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="discover.php">DISCOVER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="stores.php">TÌM CỬA HÀNG</a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="order-tracking.php">Tra cứu đơn hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="wishlist.php">Yêu thích</a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Tài khoản (Chức năng đang lỗi)</a></li>
                            <li><a class="dropdown-item" href="my-orders.php">Đơn hàng</a></li>
                            <?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin' && in_array($_SESSION['user_role'], [1,2])): ?>
                                <li><a class="dropdown-item" href="admin.php"><i class="fas fa-cogs"></i> Quản trị</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php else: ?>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng nhập</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link cart-icon" href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav> 