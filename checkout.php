<?php
session_start();
require_once 'connect.php';

// 1. Kiểm tra đăng nhập (Cho phép mọi user đã đăng nhập)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vui lòng đăng nhập để tiến hành thanh toán!";
    header('Location: login.php');
    exit;
}

// 2. Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$error = '';

// 3. Lấy thông tin khách hàng hoặc khởi tạo rỗng cho admin/nhân viên
$customer = ['kh_hoten' => '', 'kh_sdt' => '', 'kh_diachi' => '']; // Mặc định rỗng
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer') {
    $stmt = $pdo->prepare("SELECT * FROM khachhang WHERE kh_ma = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $customer_data = $stmt->fetch();
    if ($customer_data) {
        $customer = $customer_data;
    }
}

// Lấy danh sách phương thức thanh toán
$stmt_payment = $pdo->query("SELECT * FROM hinhthucthanhtoan ORDER BY httt_ma");
$payment_methods = $stmt_payment->fetchAll(PDO::FETCH_ASSOC);

// 4. Lấy thông tin giỏ hàng (để hiển thị tóm tắt)
$cart_items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $sql = "SELECT sp.* FROM sanpham sp WHERE sp.sp_ma IN ($placeholders)";
    $stmt_cart = $pdo->prepare($sql);
    $stmt_cart->execute($product_ids);
    $products = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['sp_ma']];
        $subtotal = $product['sp_gia'] * $quantity;
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
        $total += $subtotal;
    }
}
$shipping_fee = ($total >= 900000) ? 0 : 30000;
$grand_total = $total + $shipping_fee;


// 5. Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoten = $_POST['hoten'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $diachi = $_POST['diachi'] ?? '';
    $ghichu = $_POST['ghichu'] ?? '';
    $pttt = $_POST['pttt'] ?? 1; // 1: COD

    if (empty($hoten) || empty($sdt) || empty($diachi)) {
        $error = "Vui lòng điền đầy đủ thông tin giao hàng.";
    } else {
        try {
            $pdo->beginTransaction();

            // --- KIỂM TRA TỒN KHO ---
            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
            // Lấy thông tin và khóa các dòng sản phẩm để tránh xung đột
            $sql_check_stock = "SELECT sp_ma, sp_soluong FROM sanpham WHERE sp_ma IN ($placeholders) FOR UPDATE";
            $stmt_check_stock = $pdo->prepare($sql_check_stock);
            $stmt_check_stock->execute($product_ids);
            $products_in_stock = $stmt_check_stock->fetchAll(PDO::FETCH_KEY_PAIR);

            foreach ($_SESSION['cart'] as $product_id => $quantity_to_buy) {
                if (!isset($products_in_stock[$product_id])) {
                    throw new Exception("Sản phẩm có mã #$product_id không tồn tại.");
                }
                $stock_quantity = $products_in_stock[$product_id];
                if ($quantity_to_buy > $stock_quantity) {
                    $stmt_product_name = $pdo->prepare("SELECT sp_ten FROM sanpham WHERE sp_ma = ?");
                    $stmt_product_name->execute([$product_id]);
                    $product_name = $stmt_product_name->fetchColumn();
                    throw new Exception("Rất tiếc, sản phẩm \"$product_name\" chỉ còn $stock_quantity sản phẩm trong kho. Vui lòng giảm số lượng.");
                }
            }
            // --- KẾT THÚC KIỂM TRA TỒN KHO ---


            $customer_id_for_order = null;

            // Nếu là khách hàng, sử dụng ID của họ. Nếu là admin/NV, tạo KH mới.
            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer') {
                $customer_id_for_order = $_SESSION['user_id'];
                // Cập nhật thông tin nếu khách hàng có thay đổi trên form
                $update_stmt = $pdo->prepare("UPDATE khachhang SET kh_hoten = ?, kh_sdt = ?, kh_diachi = ? WHERE kh_ma = ?");
                $update_stmt->execute([$hoten, $sdt, $diachi, $customer_id_for_order]);
            } else {
                // Admin/Nhân viên đang đặt hàng -> tạo khách hàng mới
                // Tạo một email placeholder duy nhất để tránh lỗi DB
                $placeholder_email = 'guest_' . time() . '@ananas.com';
                $sql_new_customer = "INSERT INTO khachhang (kh_hoten, kh_sdt, kh_diachi, kh_email) VALUES (?, ?, ?, ?)";
                $stmt_new_customer = $pdo->prepare($sql_new_customer);
                $stmt_new_customer->execute([$hoten, $sdt, $diachi, $placeholder_email]);
                $customer_id_for_order = $pdo->lastInsertId();
            }

            // Thêm vào bảng dondathang
            $sql_order = "INSERT INTO dondathang (kh_ma, dh_ngaylap, dh_noigiao, dh_trangthaithanhtoan, httt_ma) VALUES (?, NOW(), ?, 1, ?)";
            $stmt_order = $pdo->prepare($sql_order);
            $stmt_order->execute([$customer_id_for_order, $diachi, $pttt]);
            $order_id = $pdo->lastInsertId();

            // Thêm vào bảng sanpham_dondathang VÀ CẬP NHẬT TỒN KHO
            $sql_order_detail = "INSERT INTO sanpham_dondathang (sp_ma, dh_ma, sp_dh_soluong, sp_dh_dongia) VALUES (?, ?, ?, ?)";
            $stmt_order_detail = $pdo->prepare($sql_order_detail);
            
            $sql_update_stock = "UPDATE sanpham SET sp_soluong = sp_soluong - ? WHERE sp_ma = ?";
            $stmt_update_stock = $pdo->prepare($sql_update_stock);

            foreach ($cart_items as $item) {
                // Thêm chi tiết đơn hàng
                $stmt_order_detail->execute([
                    $item['product']['sp_ma'],
                    $order_id,
                    $item['quantity'],
                    $item['product']['sp_gia']
                ]);
                
                // Trừ số lượng khỏi kho
                $stmt_update_stock->execute([$item['quantity'], $item['product']['sp_ma']]);
            }

            $pdo->commit();

            // Xóa giỏ hàng và chuyển hướng
            unset($_SESSION['cart']);
            header('Location: order-confirmation.php?order_id=' . $order_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Đã có lỗi xảy ra trong quá trình đặt hàng: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-option {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .payment-option:has(input:checked) {
            border-color: #000 !important;
            background-color: #f8f9fa;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.25);
        }
        .payment-option .form-check-label {
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container my-5">
        <h2 class="text-center mb-4">Thanh Toán</h2>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="row">
                <!-- Cột thông tin giao hàng -->
                <div class="col-md-7">
                    <h4>Thông tin giao hàng</h4>
                    <hr>
                    <div class="mb-3">
                        <label for="hoten" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" id="hoten" name="hoten" value="<?php echo htmlspecialchars($customer['kh_hoten']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="sdt" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="sdt" name="sdt" value="<?php echo htmlspecialchars($customer['kh_sdt']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="diachi" class="form-label">Địa chỉ giao hàng</label>
                        <textarea class="form-control" id="diachi" name="diachi" rows="3" required><?php echo htmlspecialchars($customer['kh_diachi']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="ghichu" class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" id="ghichu" name="ghichu" rows="2"></textarea>
                    </div>
                    
                    <h4 class="mt-4">Phương thức thanh toán</h4>
                    <hr>
                    <?php
                    $payment_icons = [
                        1 => 'fa-wallet', // MoMo
                        2 => 'fa-university', // Chuyển khoản
                        3 => 'fa-money-bill-wave' // COD
                    ];
                    ?>
                    <?php foreach ($payment_methods as $method): ?>
                    <?php $icon = $payment_icons[$method['httt_ma']] ?? 'fa-credit-card'; ?>
                    <div class="form-check border rounded p-3 mb-2 payment-option">
                        <input class="form-check-input" type="radio" name="pttt" id="pttt_<?php echo $method['httt_ma']; ?>" value="<?php echo $method['httt_ma']; ?>" <?php echo $method['httt_ma'] == 3 ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="pttt_<?php echo $method['httt_ma']; ?>">
                            <i class="fas <?php echo $icon; ?> fa-fw me-3 fa-lg"></i>
                            <strong><?php echo htmlspecialchars($method['httt_ten']); ?></strong>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cột tóm tắt đơn hàng -->
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Tóm tắt đơn hàng</h4>
                            <hr>
                            <?php foreach($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <img src="<?php echo htmlspecialchars($item['product']['sp_hinh']); ?>" width="50" class="me-2 rounded">
                                    <span><?php echo htmlspecialchars($item['product']['sp_ten']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                </div>
                                <span><?php echo number_format($item['subtotal'], 0, ',', '.'); ?> VNĐ</span>
                            </div>
                            <?php endforeach; ?>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Tạm tính:</span>
                                <span><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Phí vận chuyển:</span>
                                <span><?php echo number_format($shipping_fee, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Tổng cộng:</span>
                                <span><?php echo number_format($grand_total, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-dark btn-lg">ĐẶT HÀNG</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html> 