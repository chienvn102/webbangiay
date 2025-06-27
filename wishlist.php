<?php
session_start();
require_once 'connect.php';

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Xử lý xóa sản phẩm khỏi wishlist
if(isset($_POST['remove_wishlist'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    
    header('Location: wishlist.php');
    exit();
}

// Lấy danh sách sản phẩm yêu thích
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT w.*, sp.sp_ten, sp.sp_gia, sp.sp_hinh, hsp.hsp_1, lsp.lsp_ten 
                       FROM wishlist w 
                       JOIN sanpham sp ON w.product_id = sp.sp_ma 
                       LEFT JOIN hinhsanpham hsp ON sp.sp_ma = hsp.sp_ma 
                       LEFT JOIN loaisanpham lsp ON sp.lsp_ma = lsp.lsp_ma 
                       WHERE w.user_id = ? 
                       ORDER BY w.created_at DESC");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách yêu thích - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .wishlist-hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .wishlist-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }
        
        .wishlist-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        
        .product-content {
            padding: 25px;
        }
        
        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .product-price {
            color: #ff6b35;
            font-weight: bold;
            font-size: 1.3rem;
            margin-bottom: 20px;
        }
        
        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        
        .btn-remove:hover {
            background: #c82333;
            color: white;
        }
        
        .btn-view {
            background: #000;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s ease;
            margin-right: 10px;
        }
        
        .btn-view:hover {
            background: #333;
            color: white;
        }
        
        .empty-wishlist {
            text-align: center;
            padding: 80px 0;
        }
        
        .empty-wishlist i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <div class="wishlist-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">DANH SÁCH YÊU THÍCH</h1>
            <p class="lead">Những sản phẩm bạn đã thêm vào danh sách yêu thích</p>
        </div>
    </div>

    <!-- Wishlist Content -->
    <section class="py-5">
        <div class="container">
            <?php if(!empty($wishlist_items)): ?>
            <div class="row">
                <?php foreach($wishlist_items as $item): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="wishlist-card">
                        <img src="<?php echo $item['hsp_1'] ?: $item['sp_hinh'] ?: 'images/default-product.jpg'; ?>" 
                             class="product-image" alt="<?php echo $item['sp_ten']; ?>">
                        <div class="product-content">
                            <h3 class="product-title"><?php echo $item['sp_ten']; ?></h3>
                            <p class="product-category"><?php echo $item['lsp_ten']; ?></p>
                            <p class="product-price"><?php echo number_format($item['sp_gia'], 0, ',', '.'); ?> VNĐ</p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="product-detail.php?id=<?php echo $item['product_id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" name="remove_wishlist" class="btn-remove" 
                                            onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-dark btn-lg">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            </div>
            <?php else: ?>
            <div class="empty-wishlist">
                <i class="fas fa-heart"></i>
                <h3>Danh sách yêu thích trống</h3>
                <p class="text-muted">Bạn chưa có sản phẩm nào trong danh sách yêu thích.</p>
                <a href="products.php" class="btn btn-dark btn-lg">
                    <i class="fas fa-shopping-bag"></i> Khám phá sản phẩm
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html> 