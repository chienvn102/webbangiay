<?php
session_start();
require_once __DIR__ . '/connect.php';

// Kiểm tra đăng nhập admin/nhân viên
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || !in_array($_SESSION['user_role'], [1, 2])) {
    header('Location: login.php');
    exit;
}

// Xử lý cập nhật trạng thái đơn hàng
if(isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    try {
        $pdo->beginTransaction();

        // Lấy trạng thái hiện tại của đơn hàng
        $stmt_current_status = $pdo->prepare("SELECT dh_trangthaithanhtoan FROM dondathang WHERE dh_ma = ?");
        $stmt_current_status->execute([$order_id]);
        $current_status = $stmt_current_status->fetchColumn();

        // Chỉ hoàn kho nếu đơn hàng chuyển sang trạng thái "Hủy" (5) và trước đó chưa bị hủy
        if ($new_status == 5 && $current_status != 5) {
            // Lấy danh sách sản phẩm và số lượng trong đơn hàng
            $items_stmt = $pdo->prepare("SELECT sp_ma, sp_dh_soluong FROM sanpham_dondathang WHERE dh_ma = ?");
            $items_stmt->execute([$order_id]);
            $items_to_restock = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Cập nhật lại số lượng tồn kho
            $update_stock_stmt = $pdo->prepare("UPDATE sanpham SET sp_soluong = sp_soluong + ? WHERE sp_ma = ?");
            foreach ($items_to_restock as $item) {
                $update_stock_stmt->execute([$item['sp_dh_soluong'], $item['sp_ma']]);
            }
        }
        
        // Cập nhật trạng thái đơn hàng
        $stmt = $pdo->prepare("UPDATE dondathang SET dh_trangthaithanhtoan = ? WHERE dh_ma = ?");
        $stmt->execute([$new_status, $order_id]);

        $pdo->commit();
        $_SESSION['success_message'] = "Cập nhật trạng thái đơn hàng #$order_id thành công!";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Có lỗi xảy ra: " . $e->getMessage();
    }
    
    header('Location: admin_order.php');
    exit;
}

// Lấy thống kê đơn hàng
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM dondathang");
$stats['total_orders'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as pending_orders FROM dondathang WHERE dh_trangthaithanhtoan IN (1,2,3)");
$stats['pending_orders'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as completed_orders FROM dondathang WHERE dh_trangthaithanhtoan = 4");
$stats['completed_orders'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as cancelled_orders FROM dondathang WHERE dh_trangthaithanhtoan = 5");
$stats['cancelled_orders'] = $stmt->fetchColumn();

// Lấy danh sách đơn hàng
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_conditions = [];
$params = [];

if($search) {
    $where_conditions[] = "(dh.dh_ma LIKE ? OR kh.kh_hoten LIKE ? OR kh.kh_email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if($status_filter !== '') {
    $where_conditions[] = "dh.dh_trangthaithanhtoan = ?";
    $params[] = $status_filter;
}

$where_clause = '';
if(!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$sql = "SELECT dh.*, kh.kh_hoten, kh.kh_email, kh.kh_sdt,
        SUM(spd.sp_dh_soluong * spd.sp_dh_dongia) as total_amount
        FROM dondathang dh 
        LEFT JOIN khachhang kh ON dh.kh_ma = kh.kh_ma 
        LEFT JOIN sanpham_dondathang spd ON dh.dh_ma = spd.dh_ma
        $where_clause
        GROUP BY dh.dh_ma 
        ORDER BY dh.dh_ngaylap DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            display: flex;
            background: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            min-width: 250px;
            background: #000;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: #ccc;
            padding: 15px 20px;
            border-bottom: 1px solid #333;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: #333;
        }
        
        .main-content {
            margin-left: 250px;
            flex-grow: 1;
            padding: 30px;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table th {
            background: #f8f9fa;
            border: none;
            padding: 15px;
            font-weight: 600;
            color: #333;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .order-id {
            font-weight: bold;
            color: #007bff;
            font-size: 1.1rem;
        }
        
        .customer-info {
            line-height: 1.4;
        }
        
        .customer-name {
            font-weight: 600;
            color: #333;
        }
        
        .customer-details {
            color: #666;
            font-size: 0.85rem;
        }
        
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .btn-action {
            margin: 2px;
            border-radius: 8px;
            font-size: 0.85rem;
        }
        .order-details-container {
            background-color: #f1f3f5;
            padding: 20px;
            border-left: 4px solid #007bff;
        }

        .order-details-scroll {
            max-height: 220px;
            overflow-y: auto;
            padding-right: 15px;
        }

        .order-details-table th {
            background: #e9ecef;
        }
        
        .search-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }

        #back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: none;
            z-index: 1050;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar p-0">
        <div class="p-3"><h4 class="text-white">ADMIN PANEL</h4></div>
        <nav class="nav flex-column">
            <a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            <a class="nav-link" href="admin_product.php"><i class="fas fa-shoe-prints me-2"></i> Quản lý sản phẩm</a>
            <a class="nav-link active" href="admin_order.php"><i class="fas fa-shopping-cart me-2"></i> Quản lý đơn hàng</a>
            <a class="nav-link" href="admin_customer.php"><i class="fas fa-users me-2"></i> Quản lý khách hàng</a>
            <?php if ($_SESSION['user_role'] == 1): ?>
                <a class="nav-link" href="admin_user.php"><i class="fas fa-user-cog me-2"></i> Quản lý người dùng</a>
            <?php endif; ?>
            <a class="nav-link" href="admin_banner.php"><i class="fas fa-images me-2"></i> Quản lý banner</a>
            <a class="nav-link" href="admin_discover.php"><i class="fas fa-newspaper me-2"></i> Quản lý Discover</a>
            <a class="nav-link" href="admin_category.php"><i class="fas fa-tags me-2"></i> Quản lý loại sản phẩm</a>
            <a class="nav-link" href="admin_brand.php"><i class="fas fa-building me-2"></i> Quản lý nhà sản xuất</a>
            <a class="nav-link" href="index.php"><i class="fas fa-home me-2"></i> Về trang chủ</a>
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
        </nav>
    </div>
            
    <!-- Main Content -->
    <div class="main-content">
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-shopping-cart me-2"></i>Quản lý đơn hàng</h2>
                <div>
                    <span class="text-muted">Xin chào, </span>
                    <strong><?php echo $_SESSION['user_name']; ?></strong>
                </div>
            </div>
            
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
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon text-primary">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stats-number text-primary"><?php echo $stats['total_orders']; ?></div>
                        <div class="stats-label">Tổng đơn hàng</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stats-number text-warning"><?php echo $stats['pending_orders']; ?></div>
                        <div class="stats-label">Đang xử lý</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stats-number text-success"><?php echo $stats['completed_orders']; ?></div>
                        <div class="stats-label">Hoàn thành</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon text-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stats-number text-danger"><?php echo $stats['cancelled_orders']; ?></div>
                        <div class="stats-label">Đã hủy</div>
                    </div>
                </div>
            </div>
            
            <!-- Search and Filter -->
            <div class="search-card">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-search me-2"></i>Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Mã đơn, tên khách hàng, email..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-filter me-2"></i>Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Đặt Hàng Thành Công</option>
                            <option value="2" <?php echo $status_filter === '2' ? 'selected' : ''; ?>>Chuyển Qua Giao Nhận</option>
                            <option value="3" <?php echo $status_filter === '3' ? 'selected' : ''; ?>>Đang Giao Hàng</option>
                            <option value="4" <?php echo $status_filter === '4' ? 'selected' : ''; ?>>Giao Hàng Thành Công</option>
                            <option value="5" <?php echo $status_filter === '5' ? 'selected' : ''; ?>>ĐÃ HỦY ĐƠN</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <a href="admin_order.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-refresh"></i> Làm mới
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Orders Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>Mã đơn</th>
                            <th><i class="fas fa-user me-2"></i>Khách hàng</th>
                            <th><i class="fas fa-calendar me-2"></i>Ngày đặt</th>
                            <th><i class="fas fa-map-marker-alt me-2"></i>Nơi giao</th>
                            <th><i class="fas fa-money-bill me-2"></i>Tổng tiền</th>
                            <th><i class="fas fa-info-circle me-2"></i>Trạng thái</th>
                            <th><i class="fas fa-cogs me-2"></i>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($orders)): ?>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td>
                                <span class="order-id">#<?php echo $order['dh_ma']; ?></span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name"><?php echo $order['kh_hoten'] ?: 'Khách'; ?></div>
                                    <div class="customer-details">
                                        <i class="fas fa-envelope me-1"></i><?php echo $order['kh_email'] ?: 'N/A'; ?><br>
                                        <i class="fas fa-phone me-1"></i><?php echo $order['kh_sdt'] ?: 'N/A'; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo date('d/m/Y H:i', strtotime($order['dh_ngaylap'])); ?>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo $order['dh_noigiao'] ?: 'N/A'; ?>
                            </td>
                            <td>
                                <strong class="text-success">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ
                                </strong>
                            </td>
                            <td>
                                <?php 
                                $status_text = '';
                                $status_class = '';
                                $status_icon = '';
                                switch($order['dh_trangthaithanhtoan']) {
                                    case 1: 
                                        $status_text = 'Đặt Hàng Thành Công';
                                        $status_class = 'bg-success';
                                        $status_icon = 'fas fa-check-circle';
                                        break;
                                    case 2: 
                                        $status_text = 'Chuyển Qua Giao Nhận';
                                        $status_class = 'bg-info';
                                        $status_icon = 'fas fa-truck';
                                        break;
                                    case 3: 
                                        $status_text = 'Đang Giao Hàng';
                                        $status_class = 'bg-primary';
                                        $status_icon = 'fas fa-shipping-fast';
                                        break;
                                    case 4: 
                                        $status_text = 'Giao Hàng Thành Công';
                                        $status_class = 'bg-success';
                                        $status_icon = 'fas fa-home';
                                        break;
                                    case 5: 
                                        $status_text = 'ĐÃ HỦY ĐƠN';
                                        $status_class = 'bg-danger';
                                        $status_icon = 'fas fa-times-circle';
                                        break;
                                    default: 
                                        $status_text = 'Không xác định';
                                        $status_class = 'bg-secondary';
                                        $status_icon = 'fas fa-question-circle';
                                }
                                ?>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <i class="<?php echo $status_icon; ?> me-1"></i>
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-action" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#orderModal<?php echo $order['dh_ma']; ?>">
                                    <i class="fas fa-edit"></i> Cập nhật
                                </button>
                                <button class="btn btn-sm btn-outline-info btn-action" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#details-<?php echo $order['dh_ma']; ?>"
                                        aria-expanded="false" 
                                        aria-controls="details-<?php echo $order['dh_ma']; ?>">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </button>
                            </td>
                        </tr>

                        <!-- Collapsible Details Row -->
                        <tr>
                            <td colspan="7" class="p-0" style="border:none;">
                                <div class="collapse" id="details-<?php echo $order['dh_ma']; ?>">
                                    <div class="order-details-container">
                                        <h6><i class="fas fa-shopping-bag me-2"></i>Sản phẩm đã đặt:</h6>
                                        <?php
                                        $stmt_items = $pdo->prepare("SELECT spd.*, sp.sp_ten, sp.sp_hinh 
                                                                     FROM sanpham_dondathang spd 
                                                                     JOIN sanpham sp ON spd.sp_ma = sp.sp_ma 
                                                                     WHERE spd.dh_ma = ?");
                                        $stmt_items->execute([$order['dh_ma']]);
                                        $order_items = $stmt_items->fetchAll();
                                        ?>
                                        <div class="order-details-scroll">
                                            <table class="table table-sm table-bordered order-details-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Sản phẩm</th>
                                                        <th>Số lượng</th>
                                                        <th>Đơn giá</th>
                                                        <th>Thành tiền</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($order_items as $item): ?>
                                                    <tr>
                                                        <td><?php echo $item['sp_ten']; ?></td>
                                                        <td><?php echo $item['sp_dh_soluong']; ?></td>
                                                        <td><?php echo number_format($item['sp_dh_dongia'], 0, ',', '.'); ?> VNĐ</td>
                                                        <td><?php echo number_format($item['sp_dh_soluong'] * $item['sp_dh_dongia'], 0, ',', '.'); ?> VNĐ</td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <hr>
                                        <div class="text-end">
                                            <strong>Tổng cộng: 
                                                <span class="text-danger"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</span>
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Update Status Modal -->
                        <div class="modal fade" id="orderModal<?php echo $order['dh_ma']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-edit me-2"></i>
                                            Cập nhật trạng thái đơn hàng #<?php echo $order['dh_ma']; ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="order_id" value="<?php echo $order['dh_ma']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Trạng thái mới:</label>
                                                <select class="form-select" name="status" required>
                                                    <option value="1" <?php echo $order['dh_trangthaithanhtoan'] == 1 ? 'selected' : ''; ?>>Đặt Hàng Thành Công</option>
                                                    <option value="2" <?php echo $order['dh_trangthaithanhtoan'] == 2 ? 'selected' : ''; ?>>Chuyển Qua Giao Nhận</option>
                                                    <option value="3" <?php echo $order['dh_trangthaithanhtoan'] == 3 ? 'selected' : ''; ?>>Đang Giao Hàng</option>
                                                    <option value="4" <?php echo $order['dh_trangthaithanhtoan'] == 4 ? 'selected' : ''; ?>>Giao Hàng Thành Công</option>
                                                    <option value="5" <?php echo $order['dh_trangthaithanhtoan'] == 5 ? 'selected' : ''; ?>>ĐÃ HỦY ĐƠN</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-1"></i> Hủy
                                            </button>
                                            <button type="submit" name="update_status" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Cập nhật
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-shopping-cart"></i>
                                    <h5>Không có đơn hàng nào</h5>
                                    <p>Chưa có đơn hàng nào được tạo hoặc không tìm thấy đơn hàng phù hợp với bộ lọc.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <a href="#" id="back-to-top" class="btn btn-lg btn-primary" role="button"><i class="fas fa-arrow-up"></i></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var backToTopButton = document.getElementById('back-to-top');

            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 200) {
                    backToTopButton.style.display = 'block';
                } else {
                    backToTopButton.style.display = 'none';
                }
            });

            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>
