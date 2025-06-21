<?php
session_start();
require_once 'connect.php';

// Kiểm tra đăng nhập admin/nhân viên
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || !in_array($_SESSION['user_role'], [1, 2])) {
    header('Location: login.php');
    exit;
}

// Xử lý thêm banner mới
if(isset($_POST['add_banner'])) {
    // Xử lý upload hình ảnh
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'uploads/banners/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = 'banner_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image = $upload_path;
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO banner (banner_img) VALUES (?)");
    $stmt->execute([$image]);
    
    header('Location: admin_banner.php?success=1');
    exit;
}

// Xử lý cập nhật banner
if(isset($_POST['update_banner'])) {
    $banner_id = $_POST['banner_id'];
    
    $sql = "UPDATE banner SET banner_img = ?";
    $params = [];
    
    // Xử lý upload hình ảnh mới nếu có
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'uploads/banners/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = 'banner_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $params[] = $upload_path;
        }
    }
    
    $sql .= " WHERE banner_id = ?";
    $params[] = $banner_id;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    header('Location: admin_banner.php?success=2');
    exit;
}

// Xử lý xóa banner
if(isset($_POST['delete_banner'])) {
    $banner_id = $_POST['banner_id'];
    
    $stmt = $pdo->prepare("DELETE FROM banner WHERE banner_id = ?");
    $stmt->execute([$banner_id]);
    
    header('Location: admin_banner.php?success=3');
    exit;
}

// Lấy danh sách banner
$stmt = $pdo->query("SELECT * FROM banner ORDER BY banner_id DESC");
$banners = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý banner - Admin</title>
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
        .banner-thumbnail {
            width: 200px;
            height: auto;
            object-fit: cover;
            border-radius: 5px;
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
            <a class="nav-link" href="admin_order.php"><i class="fas fa-shopping-cart me-2"></i> Quản lý đơn hàng</a>
            <a class="nav-link" href="admin_customer.php"><i class="fas fa-users me-2"></i> Quản lý khách hàng</a>
            <?php if ($_SESSION['user_role'] == 1): ?>
                <a class="nav-link" href="admin_user.php"><i class="fas fa-user-cog me-2"></i> Quản lý người dùng</a>
            <?php endif; ?>
            <a class="nav-link active" href="admin_banner.php"><i class="fas fa-images me-2"></i> Quản lý banner</a>
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
                <h2><i class="fas fa-images me-2"></i>Quản lý banner</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal"><i class="fas fa-plus"></i> Thêm banner</button>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ảnh banner</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($banners as $banner): ?>
                            <tr>
                                <td><?php echo $banner['banner_id']; ?></td>
                                <td><img src="<?php echo $banner['banner_img']; ?>" class="banner-thumbnail"></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editBannerModal<?php echo $banner['banner_id']; ?>"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteBannerModal<?php echo $banner['banner_id']; ?>"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    <!-- Add Banner Modal -->
    <div class="modal fade" id="addBannerModal" tabindex="-1" aria-labelledby="addBannerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBannerModalLabel">Thêm Banner Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="admin_banner.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addImage" class="form-label">Ảnh Banner</label>
                            <input type="file" class="form-control" id="addImage" name="image" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" name="add_banner" class="btn btn-primary">Thêm Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php foreach ($banners as $banner): ?>
    <!-- Edit Banner Modal -->
    <div class="modal fade" id="editBannerModal<?php echo $banner['banner_id']; ?>" tabindex="-1" aria-labelledby="editBannerModalLabel<?php echo $banner['banner_id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBannerModalLabel<?php echo $banner['banner_id']; ?>">Sửa Banner #<?php echo $banner['banner_id']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="admin_banner.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="banner_id" value="<?php echo $banner['banner_id']; ?>">
                        <div class="mb-3">
                            <label>Ảnh hiện tại:</label><br>
                            <img src="<?php echo $banner['banner_img']; ?>" class="banner-thumbnail img-fluid">
                        </div>
                        <div class="mb-3">
                            <label for="editImage<?php echo $banner['banner_id']; ?>" class="form-label">Ảnh Banner Mới (để trống nếu không muốn thay đổi)</label>
                            <input type="file" class="form-control" id="editImage<?php echo $banner['banner_id']; ?>" name="image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" name="update_banner" class="btn btn-primary">Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Banner Modal -->
    <div class="modal fade" id="deleteBannerModal<?php echo $banner['banner_id']; ?>" tabindex="-1" aria-labelledby="deleteBannerModalLabel<?php echo $banner['banner_id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBannerModalLabel<?php echo $banner['banner_id']; ?>">Xác Nhận Xóa Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa banner #<?php echo $banner['banner_id']; ?> không?</p>
                </div>
                <div class="modal-footer">
                    <form action="admin_banner.php" method="POST">
                        <input type="hidden" name="banner_id" value="<?php echo $banner['banner_id']; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="delete_banner" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

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