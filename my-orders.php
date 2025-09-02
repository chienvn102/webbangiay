<?php
session_start();
require_once __DIR__ . '/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Xử lý hủy đơn hàng
if (isset($_POST['cancel_order']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $order = null;

    // Admin có thể hủy bất kỳ đơn hàng nào, khách hàng chỉ có thể hủy đơn hàng của mình
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM dondathang WHERE dh_ma = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM dondathang WHERE dh_ma = ? AND kh_ma = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();
    }
    
    // Cho phép hủy khi trạng thái là 1 (Đặt hàng thành công) hoặc 2 (Chuyển qua giao nhận) hoặc 3 (Đang giao)
    if ($order && in_array($order['dh_trangthaithanhtoan'], [1, 2, 3])) {
        try {
            $pdo->beginTransaction();
            
            // 1. Lấy danh sách sản phẩm và số lượng trong đơn hàng để hoàn kho
            $items_stmt = $pdo->prepare("SELECT sp_ma, sp_dh_soluong FROM sanpham_dondathang WHERE dh_ma = ?");
            $items_stmt->execute([$order_id]);
            $items_to_restock = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Cập nhật lại số lượng tồn kho cho từng sản phẩm
            $update_stock_stmt = $pdo->prepare("UPDATE sanpham SET sp_soluong = sp_soluong + ? WHERE sp_ma = ?");
            foreach ($items_to_restock as $item) {
                $update_stock_stmt->execute([$item['sp_dh_soluong'], $item['sp_ma']]);
            }
            
            // 3. Cập nhật trạng thái thành "Đã hủy" (mã 5)
            $update_stmt = $pdo->prepare("UPDATE dondathang SET dh_trangthaithanhtoan = 5 WHERE dh_ma = ?");
            $update_stmt->execute([$order_id]);
            
            $pdo->commit();
            $_SESSION['success_message'] = "Đã hủy đơn hàng #" . $order_id . " thành công!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Có lỗi xảy ra khi hủy đơn hàng!";
        }
    } else {
        $_SESSION['error_message'] = "Không thể hủy đơn hàng này hoặc bạn không có quyền!";
    }
    
    header('Location: my-orders.php');
    exit;
}

// Lấy danh sách đơn hàng tùy theo vai trò
$orders = [];
$is_admin_view = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin');

$sql = "
    SELECT 
        dh.dh_ma,
        dh.dh_ngaylap,
        dh.dh_noigiao,
        dh.dh_trangthaithanhtoan,
        httt.httt_ten,
        tt.tt_ten as trang_thai_ten,
        kh.kh_hoten,
        COUNT(spdh.sp_ma) as so_san_pham,
        SUM(spdh.sp_dh_soluong * spdh.sp_dh_dongia) as tong_tien
    FROM dondathang dh
    INNER JOIN khachhang kh ON dh.kh_ma = kh.kh_ma
    INNER JOIN hinhthucthanhtoan httt ON dh.httt_ma = httt.httt_ma
    INNER JOIN trangthai tt ON dh.dh_trangthaithanhtoan = tt.tt_ma
    LEFT JOIN sanpham_dondathang spdh ON dh.dh_ma = spdh.dh_ma
";

$params = [];
if (!$is_admin_view) {
    // Nếu là khách hàng, chỉ lấy đơn hàng của họ
    $sql .= " WHERE kh.kh_ma = ?";
    $params[] = $_SESSION['user_id'];
}

$sql .= " GROUP BY dh.dh_ma ORDER BY dh.dh_ngaylap DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Lấy chi tiết sản phẩm cho từng đơn hàng
$order_details = [];
foreach ($orders as $order) {
    $stmt = $pdo->prepare("
        SELECT 
            spdh.sp_dh_soluong,
            spdh.sp_dh_dongia,
            sp.sp_ten,
            sp.sp_hinh
        FROM sanpham_dondathang spdh
        INNER JOIN sanpham sp ON spdh.sp_ma = sp.sp_ma
        WHERE spdh.dh_ma = ?
    ");
    $stmt->execute([$order['dh_ma']]);
    $order_details[$order['dh_ma']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .order-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .order-body {
            padding: 15px;
        }
        .product-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-1 { background-color: #d4edda; color: #155724; }
        .status-2 { background-color: #fff3cd; color: #856404; }
        .status-3 { background-color: #cce5ff; color: #004085; }
        .status-4 { background-color: #d1ecf1; color: #0c5460; }
        .status-5 { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-shopping-bag me-2"></i>
                    <?php echo $is_admin_view ? 'Quản lý Đơn hàng' : 'Đơn hàng của tôi'; ?>
                </h2>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Bạn chưa có đơn hàng nào</h4>
                        <p class="text-muted">Hãy mua sắm để có đơn hàng đầu tiên!</p>
                        <a href="products.php" class="btn btn-dark">Mua sắm ngay</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-0">
                                            <i class="fas fa-receipt me-2"></i>
                                            Đơn hàng #<?php echo $order['dh_ma']; ?>
                                        </h6>
                                        <?php if ($is_admin_view): ?>
                                        <small class="text-info d-block mt-1">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($order['kh_hoten']); ?>
                                        </small>
                                        <?php endif; ?>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($order['dh_ngaylap'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <span class="status-badge status-<?php echo $order['dh_trangthaithanhtoan']; ?>">
                                            <?php echo $order['trang_thai_ten']; ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <strong class="text-dark">
                                            <?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> VNĐ
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="mb-3">Chi tiết sản phẩm:</h6>
                                        <?php foreach ($order_details[$order['dh_ma']] as $item): ?>
                                            <div class="product-item">
                                                <img src="<?php echo htmlspecialchars($item['sp_hinh']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['sp_ten']); ?>" 
                                                     class="product-image">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['sp_ten']); ?></h6>
                                                    <small class="text-muted">
                                                        Số lượng: <?php echo $item['sp_dh_soluong']; ?> | 
                                                        Đơn giá: <?php echo number_format($item['sp_dh_dongia'], 0, ',', '.'); ?> VNĐ
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <strong><?php echo number_format($item['sp_dh_soluong'] * $item['sp_dh_dongia'], 0, ',', '.'); ?> VNĐ</strong>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">Thông tin giao hàng</h6>
                                                <p class="card-text">
                                                    <i class="fas fa-map-marker-alt me-2"></i>
                                                    <?php echo htmlspecialchars($order['dh_noigiao']); ?>
                                                </p>
                                                <p class="card-text">
                                                    <i class="fas fa-credit-card me-2"></i>
                                                    <?php echo htmlspecialchars($order['httt_ten']); ?>
                                                </p>
                                                
                                                <?php if (in_array($order['dh_trangthaithanhtoan'], [1, 2, 3])): ?>
                                                    <form method="POST" class="mt-3" 
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['dh_ma']; ?>">
                                                        <button type="submit" name="cancel_order" class="btn btn-danger btn-sm w-100">
                                                            <i class="fas fa-times me-2"></i>
                                                            Hủy đơn hàng
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html> 
