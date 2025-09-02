<?php
session_start();
require_once __DIR__ . '/connect.php';

// Kiểm tra đăng nhập admin/nhân viên
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || !in_array($_SESSION['user_role'], [1, 2])) {
    header('Location: login.php');
    exit;
}

// Xử lý thêm loại sản phẩm mới
if(isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    
    $stmt = $pdo->prepare("INSERT INTO loaisanpham (lsp_ten, lsp_mota) VALUES (?, ?)");
    $stmt->execute([$name, $description]);
    
    $_SESSION['success_message'] = "Thêm loại sản phẩm thành công!";
    header('Location: admin_category.php');
    exit;
}

// Xử lý cập nhật loại sản phẩm
if(isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    
    $stmt = $pdo->prepare("UPDATE loaisanpham SET lsp_ten = ?, lsp_mota = ? WHERE lsp_ma = ?");
    $stmt->execute([$name, $description, $category_id]);
    
    $_SESSION['success_message'] = "Cập nhật loại sản phẩm thành công!";
    header('Location: admin_category.php');
    exit;
}

// Xử lý xóa loại sản phẩm
if(isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    
    // Kiểm tra xem loại sản phẩm có sản phẩm không
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sanpham WHERE lsp_ma = ?");
    $stmt->execute([$category_id]);
    $product_count = $stmt->fetchColumn();
    
    if($product_count > 0) {
        $_SESSION['error_message'] = "Không thể xóa loại sản phẩm đã có sản phẩm!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM loaisanpham WHERE lsp_ma = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success_message'] = "Xóa loại sản phẩm thành công!";
    }
    header('Location: admin_category.php');
    exit;
}

// Lấy danh sách loại sản phẩm
$stmt = $pdo->query("SELECT lsp.*, COUNT(sp.sp_ma) as product_count 
                     FROM loaisanpham lsp 
                     LEFT JOIN sanpham sp ON lsp.lsp_ma = sp.lsp_ma 
                     GROUP BY lsp.lsp_ma 
                     ORDER BY lsp.lsp_ma DESC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý loại sản phẩm - Admin</title>
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
        .card {
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
        }
        .table th {
            font-weight: 600;
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
            <a class="nav-link" href="admin_banner.php"><i class="fas fa-images me-2"></i> Quản lý banner</a>
            <a class="nav-link" href="admin_discover.php"><i class="fas fa-newspaper me-2"></i> Quản lý Discover</a>
            <a class="nav-link active" href="admin_category.php"><i class="fas fa-tags me-2"></i> Quản lý loại sản phẩm</a>
            <a class="nav-link" href="admin_brand.php"><i class="fas fa-building me-2"></i> Quản lý nhà sản xuất</a>
            <a class="nav-link" href="index.php"><i class="fas fa-home me-2"></i> Về trang chủ</a>
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tags me-2"></i>Quản lý loại sản phẩm</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fas fa-plus me-2"></i>Thêm loại</button>
                </div>

                <!-- Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên loại sản phẩm</th>
                                    <th>Mô tả</th>
                                    <th>Số lượng sản phẩm</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Chưa có loại sản phẩm nào.</td>
                                    </tr>
                                <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><strong>#<?php echo $category['lsp_ma']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($category['lsp_ten']); ?></td>
                                    <td><?php echo htmlspecialchars($category['lsp_mota']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $category['product_count']; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $category['lsp_ma']; ?>"><i class="fas fa-edit me-1"></i>Sửa</button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal<?php echo $category['lsp_ma']; ?>"><i class="fas fa-trash me-1"></i>Xóa</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm loại sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên loại sản phẩm</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Thêm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php foreach ($categories as $category): ?>
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal<?php echo $category['lsp_ma']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa loại sản phẩm #<?php echo $category['lsp_ma']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="category_id" value="<?php echo $category['lsp_ma']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Tên loại sản phẩm</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($category['lsp_ten']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($category['lsp_mota']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="update_category" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal<?php echo $category['lsp_ma']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="category_id" value="<?php echo $category['lsp_ma']; ?>">
                        <p>Bạn có chắc chắn muốn xóa loại sản phẩm "<strong><?php echo htmlspecialchars($category['lsp_ten']); ?></strong>"?</p>
                        <?php if ($category['product_count'] > 0): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Cảnh báo:</strong> Loại sản phẩm này đang có <?php echo $category['product_count']; ?> sản phẩm. Không thể xóa.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="delete_category" class="btn btn-danger" <?php if ($category['product_count'] > 0) echo 'disabled'; ?>>Xóa</button>
                    </div>
                </form>
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
