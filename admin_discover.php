<?php
session_start();
require_once 'connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 2])) {
    header('Location: login.php');
    exit;
}

$success_msg = '';
$error_msg = '';

// Handle form submissions for add, edit, delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new discover post
    if (isset($_POST['add_discover'])) {
        $tieude = $_POST['disc_tieude'];
        $noidung = $_POST['disc_noidung'];
        $img_path = '';

        if (isset($_FILES['disc_img']) && $_FILES['disc_img']['error'] == 0) {
            $upload_dir = 'uploads/';
            $img_name = time() . '_' . basename($_FILES['disc_img']['name']);
            $img_path = $upload_dir . $img_name;

            if (move_uploaded_file($_FILES['disc_img']['tmp_name'], $img_path)) {
                $stmt = $pdo->prepare("INSERT INTO discover (disc_tieude, disc_noidung, disc_img) VALUES (?, ?, ?)");
                if ($stmt->execute([$tieude, $noidung, $img_path])) {
                    $success_msg = "Thêm bài viết thành công!";
                } else {
                    $error_msg = "Có lỗi xảy ra khi thêm bài viết.";
                }
            } else {
                $error_msg = "Không thể tải ảnh lên.";
            }
        } else {
            $error_msg = "Vui lòng chọn ảnh cho bài viết.";
        }
    }

    // Edit discover post
    if (isset($_POST['edit_discover'])) {
        $id = $_POST['disc_id'];
        $tieude = $_POST['disc_tieude'];
        $noidung = $_POST['disc_noidung'];
        $img_path = $_POST['existing_img']; // old image path

        if (isset($_FILES['disc_img']) && $_FILES['disc_img']['error'] == 0) {
            $upload_dir = 'uploads/';
            $img_name = time() . '_' . basename($_FILES['disc_img']['name']);
            $new_img_path = $upload_dir . $img_name;

            if (move_uploaded_file($_FILES['disc_img']['tmp_name'], $new_img_path)) {
                $img_path = $new_img_path;
            } else {
                $error_msg = "Cập nhật ảnh thất bại.";
            }
        }
        
        if(empty($error_msg)) {
            $stmt = $pdo->prepare("UPDATE discover SET disc_tieude = ?, disc_noidung = ?, disc_img = ? WHERE disc_id = ?");
            if ($stmt->execute([$tieude, $noidung, $img_path, $id])) {
                $success_msg = "Cập nhật bài viết thành công!";
            } else {
                $error_msg = "Có lỗi xảy ra khi cập nhật.";
            }
        }
    }

    // Delete discover post
    if (isset($_POST['delete_discover'])) {
        $id = $_POST['disc_id'];
        $stmt = $pdo->prepare("DELETE FROM discover WHERE disc_id = ?");
        if ($stmt->execute([$id])) {
            $success_msg = "Xóa bài viết thành công!";
        } else {
            $error_msg = "Có lỗi xảy ra khi xóa.";
        }
    }
}

// Fetch all discover posts
$stmt = $pdo->query("SELECT * FROM discover ORDER BY disc_id DESC");
$discovers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Discover</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
            padding: 20px;
            z-index: 1000;
        }
        #back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            z-index: 1000;
        }
        .discover-img {
            width: 150px;
            height: 100px;
            object-fit: cover;
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
        <a class="nav-link" href="admin_customer.php"><i class="fas fa-users me-2"></i> Quản lý khách hàng</a>
        <?php if ($_SESSION['user_role'] == 1): ?>
            <a class="nav-link" href="admin_user.php"><i class="fas fa-user-cog me-2"></i> Quản lý người dùng</a>
        <?php endif; ?>
        <a class="nav-link" href="admin_banner.php"><i class="fas fa-images me-2"></i> Quản lý banner</a>
        <a class="nav-link active" href="admin_discover.php"><i class="fas fa-newspaper me-2"></i> Quản lý Discover</a>
        <a class="nav-link" href="admin_category.php"><i class="fas fa-tags me-2"></i> Quản lý loại sản phẩm</a>
        <a class="nav-link" href="admin_brand.php"><i class="fas fa-building me-2"></i> Quản lý nhà sản xuất</a>
        <a class="nav-link" href="index.php"><i class="fas fa-home me-2"></i> Về trang chủ</a>
        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
    </nav>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="my-4">Quản lý Bài viết Discover</h1>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Danh sách bài viết</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Thêm bài viết
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Nội dung</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($discovers as $discover): ?>
                                <tr>
                                    <td><?php echo $discover['disc_id']; ?></td>
                                    <td><img src="<?php echo htmlspecialchars($discover['disc_img']); ?>" alt="Discover Image" class="discover-img"></td>
                                    <td><?php echo htmlspecialchars($discover['disc_tieude']); ?></td>
                                    <td><?php echo substr(htmlspecialchars($discover['disc_noidung']), 0, 100) . '...'; ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" 
                                            data-id="<?php echo $discover['disc_id']; ?>" 
                                            data-tieude="<?php echo htmlspecialchars($discover['disc_tieude']); ?>" 
                                            data-noidung="<?php echo htmlspecialchars($discover['disc_noidung']); ?>" 
                                            data-img="<?php echo htmlspecialchars($discover['disc_img']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                            data-id="<?php echo $discover['disc_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="disc_tieude" class="form-label">Tiêu đề</label>
                        <input type="text" class="form-control" id="disc_tieude" name="disc_tieude" required>
                    </div>
                    <div class="mb-3">
                        <label for="disc_noidung" class="form-label">Nội dung</label>
                        <textarea class="form-control" id="disc_noidung" name="disc_noidung" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="disc_img" class="form-label">Ảnh</label>
                        <input type="file" class="form-control" id="disc_img" name="disc_img" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="add_discover" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_disc_id" name="disc_id">
                    <input type="hidden" id="edit_existing_img" name="existing_img">
                    <div class="mb-3">
                        <label for="edit_disc_tieude" class="form-label">Tiêu đề</label>
                        <input type="text" class="form-control" id="edit_disc_tieude" name="disc_tieude" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_disc_noidung" class="form-label">Nội dung</label>
                        <textarea class="form-control" id="edit_disc_noidung" name="disc_noidung" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_disc_img" class="form-label">Ảnh mới (tùy chọn)</label>
                        <input type="file" class="form-control" id="edit_disc_img" name="disc_img" accept="image/*">
                        <img id="preview_img" src="" alt="Current Image" class="mt-2" style="max-width: 100px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="edit_discover" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Xóa bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa bài viết này?</p>
                    <input type="hidden" id="delete_disc_id" name="disc_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="delete_discover" class="btn btn-danger">Xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<a href="#" id="back-to-top" class="btn btn-dark"><i class="fas fa-arrow-up"></i></a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Back to top button
    const backToTopBtn = document.getElementById('back-to-top');
    window.onscroll = () => {
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    };

    // Edit Modal Logic
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const tieude = button.getAttribute('data-tieude');
        const noidung = button.getAttribute('data-noidung');
        const img = button.getAttribute('data-img');

        editModal.querySelector('#edit_disc_id').value = id;
        editModal.querySelector('#edit_disc_tieude').value = tieude;
        editModal.querySelector('#edit_disc_noidung').value = noidung;
        editModal.querySelector('#edit_existing_img').value = img;
        editModal.querySelector('#preview_img').src = img;
    });

    // Delete Modal Logic
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        deleteModal.querySelector('#delete_disc_id').value = id;
    });
</script>

</body>
</html> 