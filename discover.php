<?php
session_start();
require_once __DIR__ . '/connect.php';

// Lấy tất cả bài viết discover
$stmt = $pdo->query("SELECT * FROM discover ORDER BY disc_id DESC");
$discovers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .discover-hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .discover-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }
        
        .discover-card:hover {
            transform: translateY(-10px);
        }
        
        .discover-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        
        .discover-content {
            padding: 25px;
        }
        
        .discover-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        
        .discover-text {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .discover-date {
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .btn-read-more {
            background: #000;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        
        .btn-read-more:hover {
            background: #333;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <div class="discover-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">DISCOVER</h1>
            <p class="lead">Khám phá những câu chuyện thú vị về giày và phong cách</p>
        </div>
    </div>

    <!-- Discover Content -->
    <section class="py-5">
        <div class="container">
            <?php if(!empty($discovers)): ?>
            <div class="row">
                <?php foreach($discovers as $discover): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="discover-card">
                        <img src="<?php echo $discover['disc_img']; ?>" 
                             class="discover-image" alt="<?php echo $discover['disc_tieude']; ?>">
                        <div class="discover-content">
                            <div class="discover-date">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('d/m/Y', strtotime($discover['disc_ngaytao'] ?? 'now')); ?>
                            </div>
                            <h3 class="discover-title"><?php echo $discover['disc_tieude']; ?></h3>
                            <p class="discover-text">
                                <?php echo substr($discover['disc_noidung'], 0, 200); ?>...
                            </p>
                            <a href="discover-detail.php?id=<?php echo $discover['disc_id']; ?>" class="btn-read-more">Đọc thêm</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                <h3>Chưa có bài viết nào</h3>
                <p class="text-muted">Hãy quay lại sau để xem những bài viết mới nhất!</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html> 
