<?php
session_start();
require_once __DIR__ . '/connect.php';

// Kiểm tra đăng nhập và vai trò
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 2])) {
    header('Location: login.php');
    exit;
}

// Xử lý xóa khách hàng
if(isset($_POST['delete_customer'])) {
    $customer_id = $_POST['customer_id'];
    
    // Kiểm tra xem khách hàng có đơn hàng không
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM dondathang WHERE kh_ma = ?");
    $stmt->execute([$customer_id]);
    $order_count = $stmt->fetchColumn();
    
    if($order_count > 0) {
        $error = "Không thể xóa khách hàng đã có đơn hàng!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM khachhang WHERE kh_ma = ?");
        $stmt->execute([$customer_id]);
        header('Location: admin_customer.php?success=1');
        exit;
    }
}

// Lấy danh sách khách hàng
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM khachhang WHERE kh_hoten LIKE ? OR kh_email LIKE ? OR kh_sdt LIKE ? ORDER BY kh_ma DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%", "%$search%"]);
$customers = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            display: flex;
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
            background: #f8f9fa;
            min-height: 100vh;
        }
        .search-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .form-control, .btn {
            border-radius: 10px;
        }
        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table th {
            background: #f8f9fa;
        }
        .customer-details-container {
            background-color: #f1f3f5;
            padding: 20px;
            border-left: 4px solid #17a2b8;
        }
        .details-scroll {
            max-height: 300px;
            overflow-y: auto;
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
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="sidebar p-0">
        <div class="p-3">
            <h4 class="text-white">ADMIN PANEL</h4>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            <a class="nav-link" href="admin_product.php"><i class="fas fa-shoe-prints me-2"></i> Quản lý sản phẩm</a>
            <a class="nav-link" href="admin_order.php"><i class="fas fa-shopping-cart me-2"></i> Quản lý đơn hàng</a>
            <a class="nav-link active" href="admin_customer.php"><i class="fas fa-users me-2"></i> Quản lý khách hàng</a>
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
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users me-2"></i>Quản lý khách hàng</h2>
                <div>
                    <span class="text-muted">Xin chào, </span>
                    <strong><?php echo $_SESSION['user_name']; ?></strong>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Xóa khách hàng thành công!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="search-card">
                <form method="GET" class="row g-3">
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm theo tên, email, SĐT..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Tìm kiếm</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>#<?php echo $customer['kh_ma']; ?></td>
                                <td><?php echo htmlspecialchars($customer['kh_hoten']); ?></td>
                                <td><?php echo htmlspecialchars($customer['kh_email']); ?></td>
                                <td><?php echo htmlspecialchars($customer['kh_sdt']); ?></td>
                                <td><?php echo htmlspecialchars($customer['kh_diachi']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $customer['kh_ma']; ?>">
                                        <i class="fas fa-eye"></i> Xem đơn hàng
                                    </button>
                                </td>
                            </tr>
                             <!-- Collapsible Details Row -->
                             <tr>
                                <td colspan="6" class="p-0" style="border:none;">
                                    <div class="collapse" id="details-<?php echo $customer['kh_ma']; ?>">
                                        <div class="customer-details-container">
                                            <h5><i class="fas fa-history me-2"></i>Lịch sử đơn hàng của "<?php echo htmlspecialchars($customer['kh_hoten']); ?>"</h5>
                                            <?php
                                            $order_stmt = $pdo->prepare(
                                                "SELECT *, (SELECT SUM(spd.sp_dh_soluong * spd.sp_dh_dongia) FROM sanpham_dondathang spd WHERE spd.dh_ma = ddh.dh_ma) as total_amount
                                                 FROM dondathang ddh 
                                                 WHERE kh_ma = ? ORDER BY dh_ngaylap DESC"
                                            );
                                            $order_stmt->execute([$customer['kh_ma']]);
                                            $orders = $order_stmt->fetchAll();
                                            ?>
                                            <?php if ($orders): ?>
                                                <div class="details-scroll">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Mã đơn</th>
                                                                <th>Ngày đặt</th>
                                                                <th>Nơi giao</th>
                                                                <th>Tổng tiền</th>
                                                                <th>Trạng thái</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($orders as $order): ?>
                                                                <tr>
                                                                    <td>#<?php echo $order['dh_ma']; ?></td>
                                                                    <td><?php echo date('d/m/Y', strtotime($order['dh_ngaylap'])); ?></td>
                                                                    <td><?php echo htmlspecialchars($order['dh_noigiao']); ?></td>
                                                                    <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</td>
                                                                    <td>
                                                                        <?php 
                                                                            $status_map = [
                                                                                1 => ['text' => 'Đặt Hàng Thành Công', 'class' => 'bg-success'],
                                                                                2 => ['text' => 'Chuyển Giao Nhận', 'class' => 'bg-info'],
                                                                                3 => ['text' => 'Đang Giao Hàng', 'class' => 'bg-primary'],
                                                                                4 => ['text' => 'Giao Hàng Thành Công', 'class' => 'bg-success'],
                                                                                5 => ['text' => 'Đã Hủy Đơn', 'class' => 'bg-danger'],
                                                                            ];
                                                                            $status = $status_map[$order['dh_trangthaithanhtoan']] ?? ['text' => 'Không xác định', 'class' => 'bg-secondary'];
                                                                        ?>
                                                                        <span class="badge <?php echo $status['class']; ?>"><?php echo $status['text']; ?></span>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted mt-3">Khách hàng này chưa có đơn hàng nào.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
             <?php if(empty($customers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users-slash fa-3x text-muted"></i>
                    <p class="mt-3">Không tìm thấy khách hàng nào.</p>
                </div>
            <?php endif; ?>
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
