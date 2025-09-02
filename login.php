<?php
session_start();
require_once __DIR__ . '/connect.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if($email && $password) {
        // Kiểm tra trong bảng users (admin/nhân viên)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role_id'];
            $_SESSION['user_type'] = 'admin';
            
            // Chuyển hướng dựa trên role
            if($user['role_id'] == 1 || $user['role_id'] == 2) {
                // Admin hoặc Nhân viên -> chuyển đến trang quản trị
                header('Location: admin.php');
            } else {
                // Role 3 (user/khách hàng) -> chuyển đến trang chủ
                header('Location: index.php');
            }
            exit;
        } else {
            // Kiểm tra trong bảng khachhang
            $stmt = $pdo->prepare("SELECT * FROM khachhang WHERE kh_email = ?");
            $stmt->execute([$email]);
            $customer = $stmt->fetch();
            
            if($customer) {
                // Đơn giản hóa - trong thực tế nên hash password
                $_SESSION['user_id'] = $customer['kh_ma'];
                $_SESSION['user_name'] = $customer['kh_hoten'];
                $_SESSION['user_type'] = 'customer';
                $_SESSION['user_role'] = 3; // Khách hàng
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email hoặc mật khẩu không đúng!';
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
    <title>Đăng nhập - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
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
        
        .btn-login {
            background: #000;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-weight: 600;
            width: 100%;
        }
        
        .btn-login:hover {
            background: #333;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-link a {
            color: #000;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2>ĐĂNG NHẬP</h2>
                <p class="text-muted">Chào mừng bạn quay trở lại!</p>
            </div>
            
            <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                </div>
                
                <button type="submit" class="btn btn-dark btn-login">Đăng nhập</button>
            </form>
            
            <div class="register-link">
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 
