<?php
session_start();
require_once __DIR__ . '/connect.php';

$order = null;
$order_items = [];
$error = '';

// Xử lý tra cứu đơn hàng
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'] ?? '';
    
    if($order_id) {
        // Tìm đơn hàng theo mã đơn hàng
        $stmt = $pdo->prepare("SELECT dh.*, kh.kh_hoten, kh.kh_email, kh.kh_sdt,
                               COALESCE(SUM(spd.sp_dh_soluong * spd.sp_dh_dongia), 0) as total_amount
                               FROM dondathang dh 
                               LEFT JOIN khachhang kh ON dh.kh_ma = kh.kh_ma 
                               LEFT JOIN sanpham_dondathang spd ON dh.dh_ma = spd.dh_ma
                               WHERE dh.dh_ma = ?
                               GROUP BY dh.dh_ma");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if($order) {
            // Lấy chi tiết sản phẩm trong đơn hàng
            $stmt = $pdo->prepare("SELECT spd.*, sp.sp_ten, sp.sp_hinh 
                                  FROM sanpham_dondathang spd 
                                  JOIN sanpham sp ON spd.sp_ma = sp.sp_ma 
                                  WHERE spd.dh_ma = ?");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
        } else {
            $error = 'Không tìm thấy đơn hàng với mã đã cung cấp!';
        }
    } else {
        $error = 'Vui lòng nhập mã đơn hàng!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đơn hàng - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .tracking-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .tracking-header {
            background: #000;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .tracking-form {
            padding: 25px;
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
        
        .btn-track {
            background: #000;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .btn-track:hover {
            background: #333;
        }
        
        .order-status {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .status-timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 30px;
        }

        .status-timeline::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 4px;
            background-color: #e0e0e0;
            transform: translateY(-50%);
            z-index: 1;
        }

        .status-timeline .progress-bar {
            position: absolute;
            top: 50%;
            left: 0;
            height: 4px;
            background-color: #28a745;
            transform: translateY(-50%);
            z-index: 2;
            transition: width 0.5s ease;
        }

        .status-step {
            position: relative;
            text-align: center;
            width: 25%;
            z-index: 3;
        }

        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 18px;
            border: 4px solid #e0e0e0;
            background-color: #fff;
            transition: all 0.3s ease;
        }

        .status-text {
            font-size: 14px;
            font-weight: 600;
            color: #6c757d;
        }

        .status-step.completed .status-icon {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .status-step.completed .status-text {
            color: #212529;
        }

        .status-cancelled {
            text-align: center;
            padding: 20px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            color: #721c24;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="tracking-container">
            <div class="tracking-header">
                <h2><i class="fas fa-search"></i> TRA CỨU ĐƠN HÀNG</h2>
                <p class="mb-0">Nhập mã đơn hàng để tra cứu</p>
            </div>
            
            <div class="tracking-form">
                <?php if($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="order_id" class="form-label">Mã đơn hàng</label>
                                <input type="text" class="form-control" id="order_id" name="order_id" 
                                       placeholder="Nhập mã đơn hàng" value="<?php echo $_POST['order_id'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-dark btn-track">
                            <i class="fas fa-search"></i> Tra cứu
                        </button>
                    </div>
                </form>
                
                <?php if($order): ?>
                <hr class="my-4">
                
                <!-- Thông tin đơn hàng -->
                <div class="order-status">
                    <h4>Thông tin đơn hàng #<?php echo $order['dh_ma']; ?></h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Khách hàng:</strong> <?php echo $order['kh_hoten']; ?></p>
                            <p><strong>Email:</strong> <?php echo $order['kh_email']; ?></p>
                            <p><strong>Số điện thoại:</strong> <?php echo $order['kh_sdt']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y', strtotime($order['dh_ngaylap'])); ?></p>
                            <p><strong>Nơi giao:</strong> <?php echo $order['dh_noigiao']; ?></p>
                            <p><strong>Tổng tiền:</strong> <span class="text-danger fw-bold"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</span></p>
                        </div>
                    </div>
                </div>
                
                <!-- Trạng thái đơn hàng -->
                <div class="order-status">
                    <h4>Trạng thái đơn hàng</h4>
                    
                    <?php 
                    $status_steps = [
                        1 => ['Đặt Hàng', 'fas fa-clipboard-check'],
                        2 => ['Đã xác nhận', 'fas fa-box-open'],
                        3 => ['Đang Giao', 'fas fa-shipping-fast'],
                        4 => ['Đã Giao', 'fas fa-home']
                    ];
                    $current_status = $order['dh_trangthaithanhtoan'];
                    $progress_width = 0;
                    if ($current_status >= 1 && $current_status <= 4) {
                        $progress_width = (($current_status - 1) / (count($status_steps) - 1)) * 100;
                    }
                    ?>

                    <?php if($current_status == 5): ?>
                        <div class="status-cancelled mt-3">
                            <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i> Đơn hàng đã bị hủy</h5>
                        </div>
                    <?php else: ?>
                        <div class="status-timeline">
                            <div class="progress-bar" style="width: <?php echo $progress_width; ?>%;"></div>
                            <?php foreach($status_steps as $step_id => $step_info): ?>
                                <div class="status-step <?php if($step_id <= $current_status) echo 'completed'; ?>">
                                    <div class="status-icon">
                                        <i class="<?php echo $step_info[1]; ?>"></i>
                                    </div>
                                    <div class="status-text"><?php echo $step_info[0]; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Chi tiết sản phẩm -->
                <div class="order-status">
                    <h4>Chi tiết sản phẩm</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Hình ảnh</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td><?php echo $item['sp_ten']; ?></td>
                                    <td>
                                        <?php if($item['sp_hinh']): ?>
                                        <img src="<?php echo $item['sp_hinh']; ?>" alt="Sản phẩm" class="product-image">
                                        <?php else: ?>
                                        <span class="text-muted">Không có hình</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $item['sp_dh_soluong']; ?></td>
                                    <td><?php echo number_format($item['sp_dh_dongia'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><?php echo number_format($item['sp_dh_soluong'] * $item['sp_dh_dongia'], 0, ',', '.'); ?> VNĐ</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Tổng cộng:</th>
                                    <th><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <!-- Hướng dẫn liên hệ -->
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Cần hỗ trợ?</h5>
                    <p class="mb-0">Nếu bạn cần hỗ trợ về đơn hàng, vui lòng liên hệ với chúng tôi:</p>
                    <ul class="mb-0 mt-2">
                        <li>Hotline: 0961108937</li>
                        <li>Email: chienvn102@gmail.com</li>
                        <li>Giờ làm việc: 8:00 - 22:00 (Thứ 2 - Chủ nhật)</li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 
