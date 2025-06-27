<?php
session_start();
require_once 'connect.php';

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Lấy thông tin user
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();



// Nếu là customer thì lấy thêm thông tin từ bảng khachhang
$customer = null;
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer') {
    // Lấy thông tin khách hàng dựa vào email (username là email)
    $stmt = $pdo->prepare("SELECT * FROM khachhang WHERE kh_email = ? LIMIT 1");
    $stmt->execute([$user['username']]);
    $customer = $stmt->fetch();
}



// Xử lý cập nhật thông tin
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_profile'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        
        // Kiểm tra email đã tồn tại chưa (trừ user hiện tại)
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $user_id]);
        if($stmt->fetch()) {
            $error = 'Email đã được sử dụng bởi tài khoản khác!';
        } else {
            // Cập nhật thông tin
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
            if($stmt->execute([$username, $email, $phone, $address, $user_id])) {
                $message = 'Cập nhật thông tin thành công!';
                // Cập nhật session
                $_SESSION['user_name'] = $username;
                $_SESSION['user_email'] = $email;
                // Reload user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật thông tin!';
            }
        }
    }
    
    // Xử lý đổi mật khẩu
    if(isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Kiểm tra mật khẩu hiện tại
        if(!password_verify($current_password, $user['password'])) {
            $error = 'Mật khẩu hiện tại không đúng!';
        } elseif($new_password !== $confirm_password) {
            $error = 'Mật khẩu mới không khớp!';
        } elseif(strlen($new_password) < 6) {
            $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
        } else {
            // Cập nhật mật khẩu
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            if($stmt->execute([$hashed_password, $user_id])) {
                $message = 'Đổi mật khẩu thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi đổi mật khẩu!';
            }
        }
    }
}

// Lấy lịch sử đơn hàng
$recent_orders = [];
if ($_SESSION['user_type'] === 'customer') {
    $stmt = $pdo->prepare("SELECT dh.*, COUNT(spd.sp_ma) as item_count 
                       FROM dondathang dh 
                       LEFT JOIN sanpham_dondathang spd ON dh.dh_ma = spd.dh_ma 
                       WHERE dh.kh_ma = ? 
                       GROUP BY dh.dh_ma 
                       ORDER BY dh.dh_ngaylap DESC 
                       LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_orders = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .profile-hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .profile-header {
            background: #000;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
        
        .profile-content {
            padding: 30px;
        }
        
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }
        
        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
        }
        
        .btn-update {
            background: #000;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .btn-update:hover {
            background: #333;
        }
        
        .order-item {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: box-shadow 0.3s ease;
        }
        
        .order-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <div class="profile-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">TÀI KHOẢN</h1>
            <p class="lead">Quản lý thông tin cá nhân và đơn hàng</p>
        </div>
    </div>

    <!-- Profile Content -->
    <section class="py-5">
        <div class="container">
            <?php if($message): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Thông tin cá nhân -->
                <div class="col-lg-8">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($customer['kh_hoten'] ?? $user['name'] ?? $user['username'] ?? ''); ?></h3>
                            <p class="mb-0">Thành viên từ N/A</p>
                        </div>
                        
                        <div class="profile-content">
                            <h4 class="mb-4">Thông tin cá nhân</h4>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Tên đăng nhập</label>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($customer['kh_email'] ?? $user['username'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($customer['kh_sdt'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Địa chỉ</label>
                                            <input type="text" class="form-control" id="address" name="address" 
                                                   value="<?php echo htmlspecialchars($customer['kh_diachi'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-dark btn-update">
                                    <i class="fas fa-save"></i> Cập nhật thông tin
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Đổi mật khẩu -->
                    <div class="profile-card">
                        <div class="profile-content">
                            <h4 class="mb-4">Đổi mật khẩu</h4>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                            <input type="password" class="form-control" id="current_password" 
                                                   name="current_password" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                                            <input type="password" class="form-control" id="new_password" 
                                                   name="new_password" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                            <input type="password" class="form-control" id="confirm_password" 
                                                   name="confirm_password" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-dark btn-update">
                                    <i class="fas fa-key"></i> Đổi mật khẩu
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Đơn hàng gần đây -->
                <div class="col-lg-4">
                    <div class="profile-card">
                        <div class="profile-content">
                            <h4 class="mb-4">Đơn hàng gần đây</h4>
                            
                            <?php if(!empty($recent_orders)): ?>
                                <?php foreach($recent_orders as $order): ?>
                                <div class="order-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">#<?php echo $order['dh_ma']; ?></h6>
                                        <span class="status-badge status-<?php echo $order['dh_trangthaithanhtoan'] == 1 ? 'completed' : 'pending'; ?>">
                                            <?php 
                                            switch($order['dh_trangthaithanhtoan']) {
                                                case 1: echo 'Hoàn thành'; break;
                                                case 2: echo 'Đang xử lý'; break;
                                                case 3: echo 'Đang giao'; break;
                                                case 4: echo 'Đã giao'; break;
                                                case 5: echo 'Đã hủy'; break;
                                                default: echo 'Chờ xử lý';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('d/m/Y', strtotime($order['dh_ngaylap'])); ?>
                                    </p>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-box"></i> 
                                        <?php echo $order['item_count']; ?> sản phẩm
                                    </p>
                                    <a href="order-tracking.php?order_id=<?php echo $order['dh_ma']; ?>" 
                                       class="btn btn-outline-dark btn-sm">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="text-center mt-3">
                                    <a href="orders.php" class="btn btn-dark">
                                        <i class="fas fa-list"></i> Xem tất cả đơn hàng
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-shopping-bag fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">Bạn chưa có đơn hàng nào</p>
                                    <a href="products.php" class="btn btn-dark">
                                        <i class="fas fa-shopping-cart"></i> Mua sắm ngay
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>