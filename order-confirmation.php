<?php
session_start();
$order_id = $_GET['order_id'] ?? 0;

if (!$order_id) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .confirmation-container {
            text-align: center;
            padding: 80px 20px;
            background: #f8f9fa;
            border-radius: 15px;
            margin-top: 50px;
        }
        .confirmation-container .icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="confirmation-container">
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="display-5">Đặt hàng thành công!</h1>
            <p class="lead">Cảm ơn bạn đã mua sắm tại Ananas.</p>
            <p>Mã đơn hàng của bạn là: <strong>#<?php echo htmlspecialchars($order_id); ?></strong></p>
            <p>Bạn có thể theo dõi trạng thái đơn hàng của mình bất cứ lúc nào.</p>
            <hr class="my-4">
            <a href="products.php" class="btn btn-outline-dark mx-2">
                <i class="fas fa-shopping-cart"></i> Tiếp tục mua sắm
            </a>
            <a href="order-tracking.php?order_id=<?php echo htmlspecialchars($order_id); ?>" class="btn btn-dark mx-2">
                <i class="fas fa-eye"></i> Xem chi tiết đơn hàng
            </a>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html> 