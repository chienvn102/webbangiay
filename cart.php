<?php
session_start();
require_once __DIR__ . '/connect.php';

// Khởi tạo giỏ hàng nếu chưa có
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý cập nhật giỏ hàng
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'update':
                $product_id = $_POST['product_id'];
                $quantity = (int)$_POST['quantity'];
                
                // Kiểm tra tồn kho
                $stmt = $pdo->prepare("SELECT sp_soluong, sp_ten FROM sanpham WHERE sp_ma = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if($product) {
                    if($quantity > $product['sp_soluong']) {
                        $_SESSION['error_message'] = 'Sản phẩm "' . $product['sp_ten'] . '" chỉ còn ' . $product['sp_soluong'] . ' sản phẩm trong kho';
                        // Giữ nguyên số lượng cũ
                        $quantity = min($_SESSION['cart'][$product_id] ?? 1, $product['sp_soluong']);
                    }
                    
                    if($quantity > 0) {
                        $_SESSION['cart'][$product_id] = $quantity;
                        $_SESSION['success_message'] = 'Đã cập nhật số lượng sản phẩm';
                    } else {
                        unset($_SESSION['cart'][$product_id]);
                        $_SESSION['success_message'] = 'Đã xóa sản phẩm khỏi giỏ hàng';
                    }
                } else {
                    $_SESSION['error_message'] = 'Sản phẩm không tồn tại';
                }
                break;
                
            case 'remove':
                $product_id = $_POST['product_id'];
                unset($_SESSION['cart'][$product_id]);
                $_SESSION['success_message'] = 'Đã xóa sản phẩm khỏi giỏ hàng';
                break;
                
            case 'clear':
                $_SESSION['cart'] = [];
                $_SESSION['success_message'] = 'Đã xóa tất cả sản phẩm khỏi giỏ hàng';
                break;
        }
        
        header('Location: cart.php');
        exit;
    }
}

// Lấy thông tin sản phẩm trong giỏ hàng
$cart_items = [];
$total = 0;

if(!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $sql = "SELECT sp.*, hsp.hsp_1, lsp.lsp_ten, nsx.nsx_ten 
            FROM sanpham sp 
            LEFT JOIN hinhsanpham hsp ON sp.sp_ma = hsp.sp_ma 
            LEFT JOIN loaisanpham lsp ON sp.lsp_ma = lsp.lsp_ma 
            LEFT JOIN nhasanxuat nsx ON sp.nsx_ma = nsx.nsx_ma 
            WHERE sp.sp_ma IN ($placeholders)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();
    
    foreach($products as $product) {
        $quantity = $_SESSION['cart'][$product['sp_ma']];
        $price = $product['sp_gia'];
        $subtotal = $price * $quantity;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal
        ];
        
        $total += $subtotal;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-item {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .quantity-input {
            width: 80px;
            text-align: center;
        }
        
        .stock-info {
            font-size: 0.8rem;
            color: #666;
        }
        
        .stock-warning {
            color: #dc3545;
            font-weight: bold;
        }
        
        .cart-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            position: sticky;
            top: 20px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-cart i {
            font-size: 80px;
            color: #ccc;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">GIỎ HÀNG</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if(empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h4>Giỏ hàng trống</h4>
            <p class="text-muted">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
            <a href="products.php" class="btn btn-dark">Tiếp tục mua sắm</a>
        </div>
        <?php else: ?>
        
        <div class="row">
            <!-- Cart Items -->
            <div class="col-md-8">
                <?php foreach($cart_items as $item): ?>
                <div class="cart-item">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="<?php echo $item['product']['sp_hinh'] ?: 'uploads/placeholder.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['product']['sp_ten']); ?>"
                                 onerror="this.onerror=null;this.src='uploads/placeholder.png';">
                        </div>
                        <div class="col-md-4">
                            <h6><?php echo htmlspecialchars($item['product']['sp_ten']); ?></h6>
                            <p class="text-muted"><?php echo htmlspecialchars($item['product']['lsp_ten']); ?></p>
                            <p class="text-muted"><?php echo htmlspecialchars($item['product']['nsx_ten']); ?></p>
                            <div class="stock-info">
                                <?php if ($item['product']['sp_soluong'] <= 0): ?>
                                    <span class="stock-warning">Hết hàng</span>
                                <?php elseif ($item['product']['sp_soluong'] < $item['quantity']): ?>
                                    <span class="stock-warning">Chỉ còn <?php echo $item['product']['sp_soluong']; ?> sản phẩm</span>
                                <?php else: ?>
                                    <span>Còn <?php echo $item['product']['sp_soluong']; ?> sản phẩm</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <p class="fw-bold"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</p>
                        </div>
                        <div class="col-md-2">
                            <form method="POST" class="d-flex align-items-center">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['sp_ma']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['product']['sp_soluong']; ?>" 
                                       class="form-control quantity-input" 
                                       onchange="this.form.submit()"
                                       <?php echo ($item['product']['sp_soluong'] <= 0) ? 'disabled' : ''; ?>>
                            </form>
                        </div>
                        <div class="col-md-1">
                            <p class="fw-bold"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?> VNĐ</p>
                        </div>
                        <div class="col-md-1">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['sp_ma']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="d-flex justify-content-between">
                    <a href="products.php" class="btn btn-outline-dark">
                        <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                    </a>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-danger" 
                                onclick="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm?')">
                            <i class="fas fa-trash"></i> Xóa tất cả
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-md-4">
                <div class="cart-summary">
                    <h5>Tổng đơn hàng</h5>
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển:</span>
                        <span><?php echo $total >= 900000 ? 'Miễn phí' : '30.000 VNĐ'; ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Tổng cộng:</strong>
                        <strong class="text-danger fs-5">
                            <?php 
                            $shipping = $total >= 900000 ? 0 : 30000;
                            echo number_format($total + $shipping, 0, ',', '.'); 
                            ?> VNĐ
                        </strong>
                    </div>
                    
                    <?php if($total < 900000): ?>
                    <div class="alert alert-info">
                        <small>Mua thêm <?php echo number_format(900000 - $total, 0, ',', '.'); ?> VNĐ để được miễn phí vận chuyển!</small>
                    </div>
                    <?php endif; ?>
                    
                    <a href="checkout.php" class="btn btn-dark w-100">
                        Tiến hành thanh toán
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 
