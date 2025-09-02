<?php
session_start();
require_once __DIR__ . '/connect.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm cửa hàng - Ananas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .stores-hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .store-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }
        
        .store-card:hover {
            transform: translateY(-5px);
        }
        
        .store-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .store-content {
            padding: 25px;
        }
        
        .store-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .store-address {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .store-info {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .btn-direction {
            background: #000;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        
        .btn-direction:hover {
            background: #333;
            color: white;
        }
        
        .search-section {
            background: white;
            padding: 40px 0;
            margin-bottom: 40px;
        }
        
        .search-box {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <div class="stores-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">TÌM CỬA HÀNG</h1>
            <p class="lead">Khám phá các cửa hàng Ananas gần bạn nhất</p>
        </div>
    </div>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-box">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Nhập địa chỉ hoặc thành phố của bạn..." id="searchInput">
                    <button class="btn btn-dark" type="button" onclick="searchStores()">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Stores Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">CÁC CỬA HÀNG ANANAS</h2>
            
            <div class="row" id="storesContainer">
                <!-- Hà Nội -->
                <div class="col-lg-4 col-md-6">
                    <div class="store-card">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3" class="store-image" alt="Ananas Hà Nội">
                        <div class="store-content">
                            <h3 class="store-name">Ananas Hà Nội - Tràng Tiền</h3>
                            <p class="store-address">
                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                36 Tràng Tiền, Hoàn Kiếm, Hà Nội
                            </p>
                            <p class="store-info">
                                <i class="fas fa-phone"></i> 024 3933 3888
                            </p>
                            <p class="store-info">
                                <i class="fas fa-clock"></i> 9:00 - 22:00 (Thứ 2 - Chủ nhật)
                            </p>
                            <a href="https://maps.google.com/?q=36+Trang+Tien+Ha+Noi" target="_blank" class="btn-direction">
                                <i class="fas fa-directions"></i> Chỉ đường
                            </a>
                        </div>
                    </div>
                </div>

                <!-- TP.HCM -->
                <div class="col-lg-4 col-md-6">
                    <div class="store-card">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3" class="store-image" alt="Ananas TP.HCM">
                        <div class="store-content">
                            <h3 class="store-name">Ananas TP.HCM - Nguyễn Huệ</h3>
                            <p class="store-address">
                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                123 Nguyễn Huệ, Quận 1, TP.HCM
                            </p>
                            <p class="store-info">
                                <i class="fas fa-phone"></i> 028 3822 3888
                            </p>
                            <p class="store-info">
                                <i class="fas fa-clock"></i> 9:00 - 22:00 (Thứ 2 - Chủ nhật)
                            </p>
                            <a href="https://maps.google.com/?q=123+Nguyen+Hue+Ho+Chi+Minh" target="_blank" class="btn-direction">
                                <i class="fas fa-directions"></i> Chỉ đường
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Đà Nẵng -->
                <div class="col-lg-4 col-md-6">
                    <div class="store-card">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3" class="store-image" alt="Ananas Đà Nẵng">
                        <div class="store-content">
                            <h3 class="store-name">Ananas Đà Nẵng - Bạch Đằng</h3>
                            <p class="store-address">
                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                456 Bạch Đằng, Hải Châu, Đà Nẵng
                            </p>
                            <p class="store-info">
                                <i class="fas fa-phone"></i> 0236 3822 3888
                            </p>
                            <p class="store-info">
                                <i class="fas fa-clock"></i> 9:00 - 22:00 (Thứ 2 - Chủ nhật)
                            </p>
                            <a href="https://maps.google.com/?q=456+Bach+Dang+Da+Nang" target="_blank" class="btn-direction">
                                <i class="fas fa-directions"></i> Chỉ đường
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cần Thơ -->
                <div class="col-lg-4 col-md-6">
                    <div class="store-card">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3" class="store-image" alt="Ananas Cần Thơ">
                        <div class="store-content">
                            <h3 class="store-name">Ananas Cần Thơ - Nguyễn Văn Linh</h3>
                            <p class="store-address">
                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                789 Nguyễn Văn Linh, Ninh Kiều, Cần Thơ
                            </p>
                            <p class="store-info">
                                <i class="fas fa-phone"></i> 0292 3822 3888
                            </p>
                            <p class="store-info">
                                <i class="fas fa-clock"></i> 9:00 - 22:00 (Thứ 2 - Chủ nhật)
                            </p>
                            <a href="https://maps.google.com/?q=789+Nguyen+Van+Linh+Can+Tho" target="_blank" class="btn-direction">
                                <i class="fas fa-directions"></i> Chỉ đường
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Huế -->
                <div class="col-lg-4 col-md-6">
                    <div class="store-card">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3" class="store-image" alt="Ananas Huế">
                        <div class="store-content">
                            <h3 class="store-name">Ananas Huế - Trần Hưng Đạo</h3>
                            <p class="store-address">
                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                321 Trần Hưng Đạo, TP. Huế, Thừa Thiên Huế
                            </p>
                            <p class="store-info">
                                <i class="fas fa-phone"></i> 0234 3822 3888
                            </p>
                            <p class="store-info">
                                <i class="fas fa-clock"></i> 9:00 - 22:00 (Thứ 2 - Chủ nhật)
                            </p>
                            <a href="https://maps.google.com/?q=321+Tran+Hung+Dao+Hue" target="_blank" class="btn-direction">
                                <i class="fas fa-directions"></i> Chỉ đường
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Nha Trang -->
                <div class="col-lg-4 col-md-6">
                    <div class="store-card">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3" class="store-image" alt="Ananas Nha Trang">
                        <div class="store-content">
                            <h3 class="store-name">Ananas Nha Trang - Trần Phú</h3>
                            <p class="store-address">
                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                654 Trần Phú, TP. Nha Trang, Khánh Hòa
                            </p>
                            <p class="store-info">
                                <i class="fas fa-phone"></i> 0258 3822 3888
                            </p>
                            <p class="store-info">
                                <i class="fas fa-clock"></i> 9:00 - 22:00 (Thứ 2 - Chủ nhật)
                            </p>
                            <a href="https://maps.google.com/?q=654+Tran+Phu+Nha+Trang" target="_blank" class="btn-direction">
                                <i class="fas fa-directions"></i> Chỉ đường
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        function searchStores() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const storesContainer = document.getElementById('storesContainer');
            const cards = storesContainer.getElementsByClassName('col-lg-4');

            for (let i = 0; i < cards.length; i++) {
                let storeCard = cards[i];
                let storeName = storeCard.querySelector('.store-name');
                let storeAddress = storeCard.querySelector('.store-address');
                if (storeName.innerText.toUpperCase().indexOf(filter) > -1 || storeAddress.innerText.toUpperCase().indexOf(filter) > -1) {
                    storeCard.style.display = "";
                } else {
                    storeCard.style.display = "none";
                }
            }
        }
    </script>
</body>
</html> 
