<?php
session_start();
require_once 'connect.php';

$discover_id = $_GET['id'] ?? 0;

if(!$discover_id) {
    header('Location: discover.php');
    exit;
}

// Lấy thông tin bài viết
$stmt = $pdo->prepare("SELECT * FROM discover WHERE disc_id = ?");
$stmt->execute([$discover_id]);
$discover = $stmt->fetch();

if(!$discover) {
    header('Location: discover.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($discover['disc_tieude']); ?> - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .discover-detail-hero {
            height: 400px;
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .discover-detail-hero h1 {
            font-weight: bold;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
        }
        .discover-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="discover-detail-hero" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo htmlspecialchars($discover['disc_img']); ?>');">
        <div class="container">
            <h1 class="display-4"><?php echo htmlspecialchars($discover['disc_tieude']); ?></h1>
        </div>
    </div>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4 text-muted">
                    <i class="fas fa-calendar-alt"></i> Ngày đăng: <?php echo date('d/m/Y', strtotime($discover['disc_ngaytao'] ?? 'now')); ?>
                </div>
                <div class="discover-content">
                    <?php echo nl2br(htmlspecialchars($discover['disc_noidung'])); ?>
                </div>
                <hr class="my-5">
                <a href="discover.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left"></i> Quay lại trang Discover</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 