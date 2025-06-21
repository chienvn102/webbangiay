<?php
session_start();
require_once 'connect.php';

// Xử lý bộ lọc
$where_conditions = ["1=1"]; // Thay vì sp.sp_trangthai = 1
$params = [];

if(isset($_GET['gender']) && $_GET['gender']) {
    $gender = $_GET['gender'];
    if($gender == 'nam') {
        $where_conditions[] = "sp.dc_ma = 1";
    } elseif($gender == 'nu') {
        $where_conditions[] = "sp.dc_ma = 2";
    }
}

if(isset($_GET['category']) && $_GET['category']) {
    $where_conditions[] = "sp.lsp_ma = ?";
    $params[] = $_GET['category'];
}

if(isset($_GET['brand']) && $_GET['brand']) {
    $where_conditions[] = "sp.nsx_ma = ?";
    $params[] = $_GET['brand'];
}

// Bỏ filter sale vì không có cột sp_giamgia
// if(isset($_GET['sale']) && $_GET['sale'] == 1) {
//     $where_conditions[] = "sp.sp_giamgia > 0";
// }

if(isset($_GET['price_min']) && $_GET['price_min']) {
    $where_conditions[] = "sp.sp_gia >= ?";
    $params[] = $_GET['price_min'];
}

if(isset($_GET['price_max']) && $_GET['price_max']) {
    $where_conditions[] = "sp.sp_gia <= ?";
    $params[] = $_GET['price_max'];
}

// Sắp xếp
$order_by = "sp.sp_ma DESC";
if(isset($_GET['sort'])) {
    switch($_GET['sort']) {
        case 'price_asc':
            $order_by = "sp.sp_gia ASC";
            break;
        case 'price_desc':
            $order_by = "sp.sp_gia DESC";
            break;
        case 'name_asc':
            $order_by = "sp.sp_ten ASC";
            break;
        case 'name_desc':
            $order_by = "sp.sp_ten DESC";
            break;
    }
}

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Đếm tổng số sản phẩm
$count_sql = "SELECT COUNT(*) FROM sanpham sp WHERE " . implode(" AND ", $where_conditions);
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_products = $stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Lấy sản phẩm
$sql = "SELECT sp.*, hsp.hsp_1, lsp.lsp_ten, nsx.nsx_ten, dc.dc_ten 
        FROM sanpham sp 
        LEFT JOIN hinhsanpham hsp ON sp.sp_ma = hsp.sp_ma 
        LEFT JOIN loaisanpham lsp ON sp.lsp_ma = lsp.lsp_ma 
        LEFT JOIN nhasanxuat nsx ON sp.nsx_ma = nsx.nsx_ma 
        LEFT JOIN danhcho dc ON sp.dc_ma = dc.dc_ma 
        WHERE " . implode(" AND ", $where_conditions) . " 
        ORDER BY $order_by 
        LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh sách loại sản phẩm và nhà sản xuất cho bộ lọc
$categories = $pdo->query("SELECT * FROM loaisanpham ORDER BY lsp_ten")->fetchAll();
$brands = $pdo->query("SELECT * FROM nhasanxuat ORDER BY nsx_ten")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-sidebar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        
        .product-card {
            border: none;
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            height: 100%;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 250px;
            object-fit: cover;
        }
        
        .product-price {
            color: #ff6b35;
            font-weight: bold;
            font-size: 18px;
        }
        
        .out-of-stock-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 5px 10px;
            font-size: 0.8rem;
            font-weight: bold;
            border-radius: 5px;
            z-index: 10;
        }
        
        .pagination .page-link {
            color: #000;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #000;
            border-color: #000;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar Filter -->
            <div class="col-md-3">
                <div class="filter-sidebar">
                    <h5>Bộ lọc</h5>
                    
                    <!-- Giới tính -->
                    <div class="mb-3">
                        <h6>Giới tính</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="all" value="" 
                                   <?php echo !isset($_GET['gender']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="all">Tất cả</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="nam" value="nam"
                                   <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'nam') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="nam">Nam</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="nu" value="nu"
                                   <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'nu') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="nu">Nữ</label>
                        </div>
                    </div>

                    <!-- Loại sản phẩm -->
                    <div class="mb-3">
                        <h6>Loại sản phẩm</h6>
                        <select class="form-select" name="category" id="category">
                            <option value="">Tất cả</option>
                            <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['lsp_ma']; ?>" 
                                    <?php echo (isset($_GET['category']) && $_GET['category'] == $category['lsp_ma']) ? 'selected' : ''; ?>>
                                <?php echo $category['lsp_ten']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Nhà sản xuất -->
                    <div class="mb-3">
                        <h6>Thương hiệu</h6>
                        <select class="form-select" name="brand" id="brand">
                            <option value="">Tất cả</option>
                            <?php foreach($brands as $brand): ?>
                            <option value="<?php echo $brand['nsx_ma']; ?>"
                                    <?php echo (isset($_GET['brand']) && $_GET['brand'] == $brand['nsx_ma']) ? 'selected' : ''; ?>>
                                <?php echo $brand['nsx_ten']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Khoảng giá -->
                    <div class="mb-3">
                        <h6>Khoảng giá</h6>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" class="form-control" placeholder="Từ" name="price_min" id="price_min"
                                       value="<?php echo isset($_GET['price_min']) ? $_GET['price_min'] : ''; ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" placeholder="Đến" name="price_max" id="price_max"
                                       value="<?php echo isset($_GET['price_max']) ? $_GET['price_max'] : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-dark w-100" onclick="applyFilters()">Áp dụng bộ lọc</button>
                </div>
            </div>

            <!-- Product List -->
            <div class="col-md-9">
                <!-- Sort and Results -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4>Sản phẩm (<?php echo $total_products; ?> kết quả)</h4>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="me-2">Sắp xếp:</label>
                        <select class="form-select" style="width: auto;" onchange="changeSort(this.value)">
                            <option value="">Mặc định</option>
                            <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Giá tăng dần</option>
                            <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Giá giảm dần</option>
                            <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Tên A-Z</option>
                            <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Tên Z-A</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="row">
                    <?php if (empty($products)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Không tìm thấy sản phẩm phù hợp</h4>
                        </div>
                    <?php else: ?>
                    <?php foreach($products as $product): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card product-card">
                            <?php if ($product['sp_soluong'] <= 0): ?>
                                <div class="out-of-stock-badge">HẾT HÀNG</div>
                            <?php endif; ?>
                            <a href="product-detail.php?id=<?php echo $product['sp_ma']; ?>">
                                <img src="<?php echo $product['sp_hinh'] ?: 'uploads/placeholder.png'; ?>" 
                                     class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['sp_ten']); ?>"
                                     onerror="this.onerror=null;this.src='uploads/placeholder.png';">
                            </a>
                            <div class="card-body text-center d-flex flex-column">
                                <h6 class="product-title flex-grow-1">
                                    <a href="product-detail.php?id=<?php echo $product['sp_ma']; ?>" class="text-dark text-decoration-none">
                                        <?php echo htmlspecialchars($product['sp_ten']); ?>
                                    </a>
                                </h6>
                                <p class="text-muted small mb-2"><?php echo htmlspecialchars($product['lsp_ten']); ?></p>
                                <p class="product-price mb-3"><?php echo number_format($product['sp_gia'], 0, ',', '.'); ?> VNĐ</p>
                                
                                <?php if ($product['sp_soluong'] > 0): ?>
                                <a href="add-to-cart.php?id=<?php echo $product['sp_ma']; ?>" class="btn btn-dark w-100 mt-auto">
                                    <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ
                                </a>
                                <?php else: ?>
                                <button class="btn btn-secondary w-100 mt-auto" disabled>
                                    <i class="fas fa-times-circle me-2"></i>Hết hàng
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <nav aria-label="Product pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Trước</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Sau</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    function applyFilters() {
        const gender = document.querySelector('input[name="gender"]:checked')?.value || '';
        const category = document.getElementById('category').value;
        const brand = document.getElementById('brand').value;
        const priceMin = document.getElementById('price_min').value;
        const priceMax = document.getElementById('price_max').value;
        
        const params = new URLSearchParams();
        if(gender) params.append('gender', gender);
        if(category) params.append('category', category);
        if(brand) params.append('brand', brand);
        if(priceMin) params.append('price_min', priceMin);
        if(priceMax) params.append('price_max', priceMax);
        
        params.delete('page'); // Reset to first page
        window.location.href = 'products.php?' + params.toString();
    }
    
    function changeSort(value) {
        const params = new URLSearchParams(window.location.search);
        if(value) {
            params.set('sort', value);
        } else {
            params.delete('sort');
        }
        params.delete('page'); // Reset to first page
        window.location.href = 'products.php?' + params.toString();
    }

    function addToCart(productId) {
        fetch('add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Đã thêm sản phẩm vào giỏ hàng!');
                // Update cart count in header, assuming header has an element with class .cart-count
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
            } else {
                alert('Có lỗi xảy ra: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error adding to cart:', error);
            alert('Có lỗi kết nối, vui lòng thử lại.');
        });
    }
    </script>
</body>
</html> 