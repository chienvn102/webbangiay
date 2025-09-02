<?php
session_start();
require_once __DIR__ . '/connect.php';

// Kiểm tra đăng nhập admin/nhân viên
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || !in_array($_SESSION['user_role'], [1, 2])) {
    header('Location: login.php');
    exit;
}

// Xử lý thêm sản phẩm
if(isset($_POST['add_product'])) {
    $ten = $_POST['ten'];
    $gia = $_POST['gia'];
    $mota = $_POST['mota'];
    $lsp_ma = $_POST['lsp_ma'];
    $nsx_ma = $_POST['nsx_ma'];
    $dc_ma = $_POST['dc_ma'];
    $soluong = $_POST['soluong'] ?? 0;
    $hinh_anh = '';

    if(isset($_FILES['sp_hinh']) && $_FILES['sp_hinh']['error'] == 0) {
        $upload_dir = 'uploads/';
        // Đảm bảo thư mục tồn tại
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = time() . '_' . basename($_FILES['sp_hinh']['name']);
        $upload_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['sp_hinh']['tmp_name'], $upload_file)) {
            $hinh_anh = $file_name; 
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO sanpham (sp_ten, sp_gia, sp_mota, lsp_ma, nsx_ma, dc_ma, sp_soluong, sp_hinh, sp_ngaycapnhat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
    if($stmt->execute([$ten, $gia, $mota, $lsp_ma, $nsx_ma, $dc_ma, $soluong, $hinh_anh])) {
        $_SESSION['success_message'] = "Thêm sản phẩm thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi thêm sản phẩm!";
    }
    header('Location: admin_product.php');
    exit;
}

// Xử lý sửa sản phẩm
if(isset($_POST['edit_product'])) {
    $sp_ma = $_POST['sp_ma'];
    $ten = $_POST['ten'];
    $gia = $_POST['gia'];
    $mota = $_POST['mota'];
    $lsp_ma = $_POST['lsp_ma'];
    $nsx_ma = $_POST['nsx_ma'];
    $dc_ma = $_POST['dc_ma'];
    $soluong = $_POST['soluong'] ?? 0;
    $hinh_anh = $_POST['hinh_hien_tai'];

    if(isset($_FILES['sp_hinh']) && $_FILES['sp_hinh']['error'] == 0) {
        $upload_dir = 'uploads/';
        $file_name = time() . '_' . basename($_FILES['sp_hinh']['name']);
        $upload_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['sp_hinh']['tmp_name'], $upload_file)) {
            if(!empty($hinh_anh) && file_exists($upload_dir . $hinh_anh)) {
                 unlink($upload_dir . $hinh_anh);
            }
            $hinh_anh = $file_name;
        }
    }
    
    $stmt = $pdo->prepare("UPDATE sanpham SET sp_ten=?, sp_gia=?, sp_mota=?, lsp_ma=?, nsx_ma=?, dc_ma=?, sp_soluong=?, sp_hinh=?, sp_ngaycapnhat=CURDATE() WHERE sp_ma=?");
    if($stmt->execute([$ten, $gia, $mota, $lsp_ma, $nsx_ma, $dc_ma, $soluong, $hinh_anh, $sp_ma])) {
        $_SESSION['success_message'] = "Cập nhật sản phẩm thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật sản phẩm!";
    }
    header('Location: admin_product.php');
    exit;
}

// Xử lý xóa sản phẩm
if(isset($_POST['delete_product'])) {
    $sp_ma = $_POST['sp_ma'];
    // Optional: Lấy tên file hình để xóa
    $stmt = $pdo->prepare("SELECT sp_hinh FROM sanpham WHERE sp_ma = ?");
    $stmt->execute([$sp_ma]);
    $hinh_anh = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM sanpham WHERE sp_ma=?");
    if($stmt->execute([$sp_ma])) {
        // Xóa file hình ảnh
        if(!empty($hinh_anh) && file_exists('uploads/' . $hinh_anh)) {
            unlink('uploads/' . $hinh_anh);
        }
        $_SESSION['success_message'] = "Xóa sản phẩm thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi xóa sản phẩm! Sản phẩm có thể đã được đặt hàng.";
    }
    header('Location: admin_product.php');
    exit;
}

// Lấy danh sách sản phẩm
$sql = "SELECT sp.*, lsp.lsp_ten, nsx.nsx_ten, dc.dc_ten 
        FROM sanpham sp 
        LEFT JOIN loaisanpham lsp ON sp.lsp_ma = lsp.lsp_ma 
        LEFT JOIN nhasanxuat nsx ON sp.nsx_ma = nsx.nsx_ma 
        LEFT JOIN danhcho dc ON sp.dc_ma = dc.dc_ma 
        ORDER BY sp.sp_ma DESC";
$products = $pdo->query($sql)->fetchAll();

// Lấy danh sách loại sản phẩm và nhà sản xuất
$categories = $pdo->query("SELECT * FROM loaisanpham ORDER BY lsp_ten")->fetchAll();
$brands = $pdo->query("SELECT * FROM nhasanxuat ORDER BY nsx_ten")->fetchAll();
$genders = $pdo->query("SELECT * FROM danhcho")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin</title>
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
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
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
        <div class="p-3">
            <h4 class="text-white">ADMIN PANEL</h4>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            <a class="nav-link active" href="admin_product.php"><i class="fas fa-shoe-prints me-2"></i> Quản lý sản phẩm</a>
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
         <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-shoe-prints me-2"></i>Quản lý sản phẩm</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="fas fa-plus me-2"></i>Thêm sản phẩm</button>
            </div>

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
                    <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Loại</th>
                                <th>NSX</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="8" class="text-center">Chưa có sản phẩm nào.</td></tr>
                            <?php else: ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><strong>#<?php echo $product['sp_ma']; ?></strong></td>
                                <td>
                                    <img src="uploads/<?php echo htmlspecialchars($product['sp_hinh'] ?: 'placeholder.png'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['sp_ten']); ?>" class="product-image"
                                         onerror="this.onerror=null;this.src='uploads/placeholder.png';">
                                </td>
                                <td><?php echo htmlspecialchars($product['sp_ten']); ?></td>
                                <td><?php echo number_format($product['sp_gia'], 0, ',', '.'); ?> VNĐ</td>
                                <td>
                                    <?php if ($product['sp_soluong'] > 10): ?>
                                        <span class="badge bg-success"><?php echo $product['sp_soluong']; ?></span>
                                    <?php elseif ($product['sp_soluong'] > 0): ?>
                                        <span class="badge bg-warning"><?php echo $product['sp_soluong']; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Hết hàng</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['lsp_ten']); ?></td>
                                <td><?php echo htmlspecialchars($product['nsx_ten']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo $product['sp_ma']; ?>"><i class="fas fa-edit"></i> Sửa</button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteProductModal<?php echo $product['sp_ma']; ?>"><i class="fas fa-trash"></i> Xóa</button>
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

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="admin_product.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Tên sản phẩm</label>
                                    <input type="text" class="form-control" name="ten" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Giá</label>
                                    <input type="number" class="form-control" name="gia" required>
                                </div>
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Loại sản phẩm</label>
                                    <select class="form-select" name="lsp_ma" required>
                                        <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat['lsp_ma']; ?>"><?php echo htmlspecialchars($cat['lsp_ten']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nhà sản xuất</label>
                                    <select class="form-select" name="nsx_ma" required>
                                        <?php foreach($brands as $brand): ?>
                                        <option value="<?php echo $brand['nsx_ma']; ?>"><?php echo htmlspecialchars($brand['nsx_ten']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Đối tượng</label>
                                    <select class="form-select" name="dc_ma" required>
                                        <?php foreach($genders as $gender): ?>
                                        <option value="<?php echo $gender['dc_ma']; ?>"><?php echo htmlspecialchars($gender['dc_ten']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control" name="soluong" value="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="mota" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh đại diện</label>
                            <input class="form-control" type="file" name="sp_hinh">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Thêm sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php foreach ($products as $product): ?>
    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal<?php echo $product['sp_ma']; ?>" tabindex="-1">
         <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa sản phẩm #<?php echo $product['sp_ma']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="admin_product.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="sp_ma" value="<?php echo $product['sp_ma']; ?>">
                        <input type="hidden" name="hinh_hien_tai" value="<?php echo htmlspecialchars($product['sp_hinh']); ?>">
                        
                        <div class="row">
                             <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Tên sản phẩm</label>
                                    <input type="text" class="form-control" name="ten" value="<?php echo htmlspecialchars($product['sp_ten']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Giá</label>
                                    <input type="number" class="form-control" name="gia" value="<?php echo $product['sp_gia']; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Loại sản phẩm</label>
                                    <select class="form-select" name="lsp_ma" required>
                                        <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat['lsp_ma']; ?>" <?php echo $product['lsp_ma'] == $cat['lsp_ma'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['lsp_ten']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nhà sản xuất</label>
                                    <select class="form-select" name="nsx_ma" required>
                                        <?php foreach($brands as $brand): ?>
                                        <option value="<?php echo $brand['nsx_ma']; ?>" <?php echo $product['nsx_ma'] == $brand['nsx_ma'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($brand['nsx_ten']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Đối tượng</label>
                                    <select class="form-select" name="dc_ma" required>
                                        <?php foreach($genders as $gender): ?>
                                        <option value="<?php echo $gender['dc_ma']; ?>" <?php echo $product['dc_ma'] == $gender['dc_ma'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($gender['dc_ten']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                 <div class="mb-3">
                                    <label class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control" name="soluong" value="<?php echo $product['sp_soluong']; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="mota" rows="3"><?php echo htmlspecialchars($product['sp_mota']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hình ảnh đại diện</label>
                            <div class="d-flex align-items-center">
                                <img src="uploads/<?php echo htmlspecialchars($product['sp_hinh'] ?: 'placeholder.png'); ?>" class="product-image me-3" alt="Ảnh hiện tại" onerror="this.style.display='none'">
                                <input class="form-control" type="file" name="sp_hinh">
                            </div>
                            <div class="form-text">Để trống nếu không muốn thay đổi hình ảnh.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="edit_product" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div class="modal fade" id="deleteProductModal<?php echo $product['sp_ma']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                 <form action="admin_product.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="sp_ma" value="<?php echo $product['sp_ma']; ?>">
                        <p>Bạn có chắc chắn muốn xóa sản phẩm "<strong><?php echo htmlspecialchars($product['sp_ten']); ?></strong>"?</p>
                        <p class="text-danger">Hành động này không thể hoàn tác.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="delete_product" class="btn btn-danger">Xóa</button>
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
