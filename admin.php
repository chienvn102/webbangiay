<?php
session_start();
require_once 'connect.php';

// Kiểm tra đăng nhập admin/nhân viên
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || !in_array($_SESSION['user_role'], [1, 2])) {
    header('Location: login.php');
    exit;
}

// Lấy thông tin user hiện tại
$stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();

// Lấy thống kê
$stats = [];

// Tổng doanh thu (chỉ tính đơn hàng đã hoàn thành, trạng thái 4)
$stmt = $pdo->query("SELECT SUM(spd.sp_dh_soluong * spd.sp_dh_dongia) 
                     FROM sanpham_dondathang spd
                     JOIN dondathang dh ON spd.dh_ma = dh.dh_ma
                     WHERE dh.dh_trangthaithanhtoan = 4");
$stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

// Tổng doanh thu tất cả đơn hàng (để so sánh)
$stmt = $pdo->query("SELECT SUM(spd.sp_dh_soluong * spd.sp_dh_dongia) 
                     FROM sanpham_dondathang spd
                     JOIN dondathang dh ON spd.dh_ma = dh.dh_ma");
$stats['all_orders_revenue'] = $stmt->fetchColumn() ?: 0;

// Tổng số sản phẩm
$stmt = $pdo->query("SELECT COUNT(*) FROM sanpham");
$stats['total_products'] = $stmt->fetchColumn();

// Tổng số khách hàng
$stmt = $pdo->query("SELECT COUNT(*) FROM khachhang");
$stats['total_customers'] = $stmt->fetchColumn();

// Tổng số đơn hàng
$stmt = $pdo->query("SELECT COUNT(*) FROM dondathang");
$stats['total_orders'] = $stmt->fetchColumn();

// Doanh thu tháng này (chỉ tính đơn hàng đã hoàn thành, trạng thái 4)
$stmt = $pdo->query("SELECT SUM(spd.sp_dh_soluong * spd.sp_dh_dongia) as revenue 
                     FROM sanpham_dondathang spd 
                     JOIN dondathang dh ON spd.dh_ma = dh.dh_ma 
                     WHERE MONTH(dh.dh_ngaylap) = MONTH(CURRENT_DATE()) 
                     AND YEAR(dh.dh_ngaylap) = YEAR(CURRENT_DATE())
                     AND dh.dh_trangthaithanhtoan = 4");
$stats['monthly_revenue'] = $stmt->fetchColumn() ?: 0;

// Đơn hàng gần đây
$stmt = $pdo->query("SELECT dh.*, kh.kh_hoten,
                    (SELECT SUM(spd.sp_dh_soluong * spd.sp_dh_dongia) 
                     FROM sanpham_dondathang spd 
                     WHERE spd.dh_ma = dh.dh_ma) as total_amount
                     FROM dondathang dh 
                     LEFT JOIN khachhang kh ON dh.kh_ma = kh.kh_ma 
                     ORDER BY dh.dh_ngaylap DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();

// Sản phẩm bán chạy
$stmt = $pdo->query("SELECT sp.sp_ten, sp.sp_gia, SUM(spd.sp_dh_soluong) as total_sold 
                     FROM sanpham sp 
                     LEFT JOIN sanpham_dondathang spd ON sp.sp_ma = spd.sp_ma 
                     GROUP BY sp.sp_ma 
                     ORDER BY total_sold DESC 
                     LIMIT 5");
$top_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card .stat-icon {
            font-size: 3rem;
            opacity: 0.8;
        }
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .bg-c-blue { background: linear-gradient(45deg,#4099ff,#73b4ff); }
        .bg-c-green { background: linear-gradient(45deg,#2ed8b6,#59e0c5); }
        .bg-c-yellow { background: linear-gradient(45deg,#FFB64D,#ffcb80); }
        .bg-c-pink { background: linear-gradient(45deg,#FF5370,#ff869a); }

        .list-group-item {
            border-radius: 10px !important;
            margin-bottom: 10px;
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
        <div class="p-3">
            <h4 class="text-white">ADMIN PANEL</h4>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="admin.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            <a class="nav-link" href="admin_product.php"><i class="fas fa-shoe-prints me-2"></i> Quản lý sản phẩm</a>
            <a class="nav-link" href="admin_order.php"><i class="fas fa-shopping-cart me-2"></i> Quản lý đơn hàng</a>
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

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
            <div>
                <span class="text-muted">Xin chào, </span>
                <strong><?php echo $_SESSION['user_name']; ?></strong>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row">
            <!-- Total Revenue -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card bg-c-blue h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="text-white"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></h3>
                                <h6 class="text-white m-b-0">Tổng doanh thu</h6>
                                <small class="text-white-50">(Đơn hàng đã hoàn thành)</small>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-dollar-sign stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top border-light border-opacity-50">
                        <small class="text-white-50">
                            Tất cả đơn hàng: <?php echo number_format($stats['all_orders_revenue'], 0, ',', '.'); ?> VNĐ
                        </small>
                    </div>
                </div>
            </div>
            <!-- Total Orders -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card bg-c-green h-100">
                     <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="text-white"><?php echo $stats['total_orders']; ?></h3>
                            <h6 class="text-white m-b-0">Tổng đơn hàng</h6>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-shopping-cart stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total Products -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card bg-c-yellow h-100">
                     <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="text-white"><?php echo $stats['total_products']; ?></h3>
                            <h6 class="text-white m-b-0">Tổng sản phẩm</h6>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-box stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total Customers -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card bg-c-pink h-100">
                     <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="text-white"><?php echo $stats['total_customers']; ?></h3>
                            <h6 class="text-white m-b-0">Tổng khách hàng</h6>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Recent Orders -->
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="fas fa-history me-2"></i>Đơn hàng gần đây</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover m-b-0">
                                <thead>
                                    <tr>
                                        <th>Mã Đơn</th>
                                        <th>Khách Hàng</th>
                                        <th>Ngày Đặt</th>
                                        <th>Trạng Thái</th>
                                        <th>Tổng Tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_orders as $order): ?>
                                        <tr>
                                            <td><strong>#<?php echo $order['dh_ma']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['kh_hoten'] ?: 'Khách'); ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    <?php echo date('d/m/Y', strtotime($order['dh_ngaylap'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_map = [
                                                        1 => ['text' => 'Mới', 'class' => 'bg-success'],
                                                        2 => ['text' => 'Đang xử lý', 'class' => 'bg-info'],
                                                        3 => ['text' => 'Đang giao', 'class' => 'bg-primary'],
                                                        4 => ['text' => 'Hoàn thành', 'class' => 'bg-success'],
                                                        5 => ['text' => 'Đã hủy', 'class' => 'bg-danger'],
                                                    ];
                                                    $status = $status_map[$order['dh_trangthaithanhtoan']] ?? ['text' => 'N/A', 'class' => 'bg-secondary'];
                                                ?>
                                                <span class="badge <?php echo $status['class']; ?>"><?php echo $status['text']; ?></span>
                                            </td>
                                            <td><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Top Products -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="fas fa-star me-2"></i>Sản phẩm bán chạy</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                             <?php foreach($top_products as $product): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($product['sp_ten']); ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $product['total_sold']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
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