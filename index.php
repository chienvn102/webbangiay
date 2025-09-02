<?php
session_start();
require_once __DIR__ . '/connect.php';

// Lấy banner
$stmt = $pdo->query("SELECT * FROM banner ORDER BY banner_id DESC LIMIT 3");
$banners = $stmt->fetchAll();

// Lấy sản phẩm nổi bật (sắp xếp theo số lượng bán chạy từ đơn hàng đã hoàn thành)
$stmt = $pdo->query("SELECT 
                        sp.sp_ma, sp.sp_ten, sp.sp_gia, sp.sp_soluong, sp.sp_hinh,
                        hsp.hsp_1, 
                        lsp.lsp_ten, 
                        nsx.nsx_ten, 
                        SUM(spd.sp_dh_soluong) as total_sold
                     FROM sanpham sp 
                     LEFT JOIN hinhsanpham hsp ON sp.sp_ma = hsp.sp_ma 
                     LEFT JOIN loaisanpham lsp ON sp.lsp_ma = lsp.lsp_ma 
                     LEFT JOIN nhasanxuat nsx ON sp.nsx_ma = nsx.nsx_ma
                     LEFT JOIN sanpham_dondathang spd ON sp.sp_ma = spd.sp_ma
                     LEFT JOIN dondathang dh ON spd.dh_ma = dh.dh_ma AND dh.dh_trangthaithanhtoan = 4
                     GROUP BY sp.sp_ma, sp.sp_ten, sp.sp_gia, sp.sp_soluong, sp.sp_hinh, hsp.hsp_1, lsp.lsp_ten, nsx.nsx_ten
                     ORDER BY total_sold DESC, sp.sp_ma DESC
                     LIMIT 8");
$featured_products = $stmt->fetchAll();

// Lấy discover content
$stmt = $pdo->query("SELECT * FROM discover ORDER BY disc_id DESC LIMIT 3");
$discovers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ananas - Website Bán Giày</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000;
            --secondary-color: #333;
            --accent-color: #ff6b35;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .top-banner {
            background: #000;
            color: white;
            text-align: center;
            padding: 10px 0;
            font-size: 14px;
        }
        
        .navbar {
            background: white !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 24px;
            color: var(--primary-color) !important;
        }
        
        .nav-link {
            color: var(--secondary-color) !important;
            font-weight: 500;
            margin: 0 10px;
        }
        
        .nav-link:hover {
            color: var(--accent-color) !important;
        }
        
        .hero-banner {
            position: relative;
            height: 600px;
            overflow: hidden;
        }
        
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        
        .hero-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .banner-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 2;
        }
        
        .product-card {
            border: none;
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 300px;
            object-fit: cover;
        }
        
        .product-title {
            font-weight: 600;
            margin: 10px 0;
        }
        
        .product-price {
            color: var(--accent-color);
            font-weight: bold;
            font-size: 18px;
        }
        
        .discover-section {
            background: #f8f9fa;
            padding: 60px 0;
        }
        
        .discover-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .discover-card:hover {
            transform: translateY(-5px);
        }
        
        .discover-image {
            height: 250px;
            object-fit: cover;
        }
        
        .footer {
            background: #000;
            color: white;
            padding: 40px 0 20px;
        }
        
        .footer h5 {
            color: var(--accent-color);
            margin-bottom: 20px;
        }
        
        .footer a {
            color: #ccc;
            text-decoration: none;
        }
        
        .footer a:hover {
            color: white;
        }
        
        .cart-icon {
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-card.out-of-stock .product-image {
            opacity: 0.5;
        }
        
        .product-card.out-of-stock .product-title {
            color: #999;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Banner -->
    <?php if(!empty($banners)): ?>
    <div id="heroCarousel" class="carousel slide hero-banner" data-bs-ride="carousel" data-bs-interval="2000">
        <div class="carousel-inner">
            <?php foreach($banners as $index => $banner): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="<?php echo $banner['banner_img']; ?>" class="d-block w-100" alt="Banner">
                <div class="banner-content">
                    <h1>MỌI NGƯỜI THƯỜNG GỌI CHÚNG TÔI LÀ DỨA !</h1>
                    <p>Khám phá bộ sưu tập giày mới nhất</p>
                    <a href="products.php" class="btn btn-dark btn-lg">MUA NGAY</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
    <?php endif; ?>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">SẢN PHẨM NỔI BẬT</h2>
            <div class="row">
                <?php foreach($featured_products as $product): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card <?php echo ($product['sp_soluong'] <= 0) ? 'out-of-stock' : ''; ?>">
                        <img src="uploads/<?php echo $product['hsp_1'] ?: $product['sp_hinh'] ?: 'placeholder.png'; ?>"
                             class="card-img-top product-image" alt="<?php echo $product['sp_ten']; ?>">
                        <div class="card-body text-center">
                            <h6 class="product-title"><?php echo $product['sp_ten']; ?></h6>
                            <p class="text-muted"><?php echo $product['lsp_ten']; ?></p>
                            <p class="product-price"><?php echo number_format($product['sp_gia'], 0, ',', '.'); ?> VNĐ</p>
                            <div class="mt-auto">
                                <?php if ($product['sp_soluong'] > 0): ?>
                                    <a href="product-detail.php?id=<?php echo $product['sp_ma']; ?>" class="btn btn-outline-dark">Xem chi tiết</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>Hết hàng</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-dark btn-lg">XEM TẤT CẢ SẢN PHẨM</a>
            </div>
        </div>
    </section>

    <!-- Discover Section -->
    <?php if(!empty($discovers)): ?>
    <section class="discover-section">
        <div class="container">
            <h2 class="text-center mb-5">DISCOVER</h2>
            <div class="row">
                <?php foreach($discovers as $discover): ?>
                <div class="col-md-4">
                    <div class="discover-card">
                        <img src="<?php echo $discover['disc_img']; ?>" 
                             class="discover-image w-100" alt="<?php echo $discover['disc_tieude']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $discover['disc_tieude']; ?></h5>
                            <p class="card-text"><?php echo substr($discover['disc_noidung'], 0, 150); ?>...</p>
                            <a href="discover-detail.php?id=<?php echo $discover['disc_id']; ?>" class="btn btn-outline-dark">Đọc thêm</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include 'footer.php'; ?>

</body>
</html> 
