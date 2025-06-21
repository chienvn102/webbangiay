<?php
session_start();
require_once 'connect.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoten = $_POST['hoten'] ?? '';
    $email = $_POST['email'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $diachi = $_POST['diachi'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if($hoten && $email && $sdt && $password && $confirm_password) {
        if($password !== $confirm_password) {
            $error = 'Mật khẩu xác nhận không khớp!';
        } else {
            // Kiểm tra email đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT kh_ma FROM khachhang WHERE kh_email = ?");
            $stmt->execute([$email]);
            
            if($stmt->fetch()) {
                $error = 'Email đã được sử dụng!';
            } else {
                // Thêm khách hàng mới
                $stmt = $pdo->prepare("INSERT INTO khachhang (kh_hoten, kh_email, kh_sdt, kh_diachi) VALUES (?, ?, ?, ?)");
                
                if($stmt->execute([$hoten, $email, $sdt, $diachi])) {
                    $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                } else {
                    $error = 'Có lỗi xảy ra khi đăng ký!';
                }
            }
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
            color: #000;
            font-weight: bold;
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
        
        .btn-register {
            background: #000;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-weight: 600;
            width: 100%;
        }
        
        .btn-register:hover {
            background: #333;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #000;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h2>ĐĂNG KÝ TÀI KHOẢN</h2>
                <p class="text-muted">Tạo tài khoản mới để mua sắm</p>
            </div>
            
            <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="hoten" class="form-label">Họ và tên *</label>
                            <input type="text" class="form-control" id="hoten" name="hoten" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sdt" class="form-label">Số điện thoại *</label>
                            <input type="tel" class="form-control" id="sdt" name="sdt" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="diachi" class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" id="diachi" name="diachi">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="agree" required>
                    <label class="form-check-label" for="agree">
                        Tôi đồng ý với <a href="#">điều khoản sử dụng</a> và <a href="#">chính sách bảo mật</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-dark btn-register">Đăng ký</button>
            </form>
            
            <div class="login-link">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 