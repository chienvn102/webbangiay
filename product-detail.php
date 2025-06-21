<?php
session_start();
require_once 'connect.php';

$product_id = $_GET['id'] ?? 0;

if(!$product_id) {
    header('Location: products.php');
    exit;
}

// Lấy thông tin sản phẩm
$stmt = $pdo->prepare("SELECT sp.*, hsp.hsp_1, hsp.hsp_2, hsp.hsp_3, lsp.lsp_ten, nsx.nsx_ten, dc.dc_ten 
                       FROM sanpham sp 
                       LEFT JOIN hinhsanpham hsp ON sp.sp_ma = hsp.sp_ma 
                       LEFT JOIN loaisanpham lsp ON sp.lsp_ma = lsp.lsp_ma 
                       LEFT JOIN nhasanxuat nsx ON sp.nsx_ma = nsx.nsx_ma 
                       LEFT JOIN danhcho dc ON sp.dc_ma = dc.dc_ma 
                       WHERE sp.sp_ma = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if(!$product) {
    header('Location: products.php');
    exit;
}

// Lấy sản phẩm liên quan
$stmt = $pdo->prepare("SELECT sp.*, hsp.hsp_1, lsp.lsp_ten 
                       FROM sanpham sp 
                       LEFT JOIN hinhsanpham hsp ON sp.sp_ma = hsp.sp_ma 
                       LEFT JOIN loaisanpham lsp ON sp.lsp_ma = lsp.lsp_ma 
                       WHERE sp.sp_ma != ? AND sp.lsp_ma = ? 
                       ORDER BY sp.sp_ma DESC LIMIT 4");
$stmt->execute([$product_id, $product['lsp_ma']]);
$related_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['sp_ten']; ?> - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-gallery {
            position: relative;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .thumbnail-images {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }
        
        .thumbnail.active {
            border-color: #000;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .product-category {
            color: #666;
            margin-bottom: 15px;
        }
        
        .product-price {
            font-size: 28px;
            font-weight: bold;
            color: #ff6b35;
            margin-bottom: 15px;
        }
        
        .product-original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 18px;
            margin-right: 10px;
        }
        
        .product-description {
            margin: 20px 0;
            line-height: 1.6;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }
        
        .quantity-input {
            width: 80px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 8px;
        }
        
        .btn-quantity {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .btn-quantity:hover {
            background: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        
        .btn-add-cart {
            background: #000;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-weight: 600;
            flex: 1;
        }
        
        .btn-add-cart:hover {
            background: #333;
            color: white;
        }
        
        .btn-wishlist {
            background: white;
            color: #000;
            border: 1px solid #000;
            padding: 15px 20px;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .btn-wishlist:hover {
            background: #000;
            color: white;
        }
        
        .product-specs {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .spec-item:last-child {
            border-bottom: none;
        }
        
        .related-products {
            margin-top: 50px;
        }
        
        .related-product-card {
            border: none;
            transition: transform 0.3s ease;
        }
        
        .related-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .related-product-image {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
                <li class="breadcrumb-item active"><?php echo $product['sp_ten']; ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Product Gallery -->
            <div class="col-md-6">
                <div class="product-gallery">
                    <img src="<?php echo $product['hsp_1'] ?: $product['sp_hinh'] ?: 'images/default-product.jpg'; ?>" 
                         class="main-image" id="mainImage" alt="<?php echo $product['sp_ten']; ?>">
                    
                    <div class="thumbnail-images">
                        <?php if($product['hsp_1']): ?>
                        <img src="<?php echo $product['hsp_1']; ?>" class="thumbnail active" 
                             onclick="changeImage(this, '<?php echo $product['hsp_1']; ?>')" alt="Hình 1">
                        <?php endif; ?>
                        
                        <?php if($product['hsp_2']): ?>
                        <img src="<?php echo $product['hsp_2']; ?>" class="thumbnail" 
                             onclick="changeImage(this, '<?php echo $product['hsp_2']; ?>')" alt="Hình 2">
                        <?php endif; ?>
                        
                        <?php if($product['hsp_3']): ?>
                        <img src="<?php echo $product['hsp_3']; ?>" class="thumbnail" 
                             onclick="changeImage(this, '<?php echo $product['hsp_3']; ?>')" alt="Hình 3">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <div class="product-info">
                    <h1 class="product-title"><?php echo $product['sp_ten']; ?></h1>
                    
                    <div class="product-category">
                        <span class="badge bg-secondary me-2"><?php echo $product['lsp_ten']; ?></span>
                        <span class="badge bg-info me-2"><?php echo $product['dc_ten']; ?></span>
                        <span class="badge bg-warning"><?php echo $product['nsx_ten']; ?></span>
                    </div>
                    
                    <div class="product-price">
                        <span><?php echo number_format($product['sp_gia'], 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    
                    <?php if($product['sp_mota']): ?>
                    <div class="product-description">
                        <h5>Mô tả sản phẩm:</h5>
                        <p><?php echo nl2br($product['sp_mota']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="quantity-selector">
                        <label>Số lượng:</label>
                        <button class="btn-quantity" onclick="changeQuantity(-1)">-</button>
                        <input type="number" class="quantity-input" id="quantity" value="1" min="1">
                        <button class="btn-quantity" onclick="changeQuantity(1)">+</button>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-add-cart" onclick="addToCart(<?php echo $product['sp_ma']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                        </button>
                        <button class="btn btn-wishlist" onclick="addToWishlist(<?php echo $product['sp_ma']; ?>)">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    
                    <div class="product-specs">
                        <h5>Thông tin sản phẩm:</h5>
                        <div class="spec-item">
                            <span>Tên sản phẩm:</span>
                            <span><?php echo $product['sp_ten']; ?></span>
                        </div>
                        <div class="spec-item">
                            <span>Danh mục:</span>
                            <span><?php echo $product['lsp_ten']; ?></span>
                        </div>
                        <div class="spec-item">
                            <span>Thương hiệu:</span>
                            <span><?php echo $product['nsx_ten']; ?></span>
                        </div>
                        <div class="spec-item">
                            <span>Giới tính:</span>
                            <span><?php echo $product['dc_ten']; ?></span>
                        </div>
                        <div class="spec-item">
                            <span>Mã sản phẩm:</span>
                            <span>#<?php echo $product['sp_ma']; ?></span>
                        </div>
                        <div class="spec-item">
                            <span>Số lượng tồn kho:</span>
                            <span><?php echo $product['sp_soluong']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if(!empty($related_products)): ?>
        <div class="related-products">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="row">
                <?php foreach($related_products as $related): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card related-product-card">
                        <img src="<?php echo $related['hsp_1'] ?: $related['sp_hinh'] ?: 'images/default-product.jpg'; ?>" 
                             class="card-img-top related-product-image" alt="<?php echo $related['sp_ten']; ?>">
                        <div class="card-body text-center">
                            <h6 class="card-title"><?php echo $related['sp_ten']; ?></h6>
                            <p class="text-muted"><?php echo $related['lsp_ten']; ?></p>
                            <p class="fw-bold text-primary"><?php echo number_format($related['sp_gia'], 0, ',', '.'); ?> VNĐ</p>
                            <a href="product-detail.php?id=<?php echo $related['sp_ma']; ?>" 
                               class="btn btn-outline-dark">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

<?php include 'footer.php'; ?>

<script>
    function changeImage(thumbnail, newSrc) {
        document.getElementById('mainImage').src = newSrc;
        
        // Remove active class from all thumbnails
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        
        // Add active class to clicked thumbnail
        thumbnail.classList.add('active');
    }
    
    const quantityInput = document.getElementById('quantity');
    const btnMinus = document.getElementById('btn-minus');
    const btnPlus = document.getElementById('btn-plus');

    btnMinus.addEventListener('click', () => {
        let currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });

    btnPlus.addEventListener('click', () => {
        let currentValue = parseInt(quantityInput.value);
        quantityInput.value = currentValue + 1;
    });
    
    function addToCart(productId) {
        const quantity = document.getElementById('quantity').value;
        
        fetch('add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Đã thêm sản phẩm vào giỏ hàng!');
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
            } else {
                alert('Có lỗi xảy ra: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi thêm vào giỏ hàng.');
        });
    }
    
    function addToWishlist(productId) {
        fetch('wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                action: 'add'
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                if(data.logged_in) {
                    // Optionally update wishlist icon/count
                } else {
                    window.location.href = 'login.php';
                }
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
</body>
</html> 