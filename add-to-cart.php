<?php
session_start();

// Xử lý GET request (từ liên kết)
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = $_GET['id'] ?? null;
    $quantity = $_GET['quantity'] ?? 1;
    
    if(!$product_id) {
        $_SESSION['error_message'] = 'Không tìm thấy sản phẩm';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'products.php'));
        exit;
    }
    
    // Kiểm tra tồn kho
    require_once 'connect.php';
    $stmt = $pdo->prepare("SELECT sp_soluong, sp_ten FROM sanpham WHERE sp_ma = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if(!$product) {
        $_SESSION['error_message'] = 'Sản phẩm không tồn tại';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'products.php'));
        exit;
    }
    
    if($product['sp_soluong'] < $quantity) {
        $_SESSION['error_message'] = 'Sản phẩm "' . $product['sp_ten'] . '" chỉ còn ' . $product['sp_soluong'] . ' sản phẩm trong kho';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'products.php'));
        exit;
    }
    
    // Khởi tạo giỏ hàng nếu chưa có
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Thêm hoặc cập nhật số lượng sản phẩm
    if(isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    $_SESSION['success_message'] = 'Đã thêm sản phẩm vào giỏ hàng';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'products.php'));
    exit;
}

// Xử lý POST request (API)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = $input['product_id'] ?? null;
    $quantity = $input['quantity'] ?? 1;

    if(!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }
    
    // Kiểm tra tồn kho
    require_once 'connect.php';
    $stmt = $pdo->prepare("SELECT sp_soluong FROM sanpham WHERE sp_ma = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if(!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }
    
    if($product['sp_soluong'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm chỉ còn ' . $product['sp_soluong'] . ' sản phẩm trong kho']);
        exit;
    }

    // Khởi tạo giỏ hàng nếu chưa có
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Thêm hoặc cập nhật số lượng sản phẩm
    if(isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
        'cart_count' => count($_SESSION['cart'])
    ]);
    exit;
}

// Nếu không phải GET hoặc POST
http_response_code(405);
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit;
?> 