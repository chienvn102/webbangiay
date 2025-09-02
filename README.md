# Website Bán Giày - Ananas

Dự án website bán giày được phát triển bằng PHP và MySQL sử dụng cho môn Quy trình và công cụ phát triển phần mềm, contributors chỉ thực hiện thao tác push, pull, commit,v.v. để tìm hiểu về Git
## Tính năng

- **Frontend:**
  - Trang chủ với banner và sản phẩm nổi bật
  - Danh sách sản phẩm với phân trang và bộ lọc
  - Chi tiết sản phẩm
  - Giỏ hàng và thanh toán
  - Đăng ký/đăng nhập tài khoản
  - Theo dõi đơn hàng
  - Wishlist (danh sách yêu thích)
  - Trang discover (blog/tin tức)

- **Backend (Admin):**
  - Quản lý sản phẩm
  - Quản lý danh mục và thương hiệu
  - Quản lý đơn hàng
  - Quản lý khách hàng
  - Quản lý banner
  - Quản lý nội dung discover

## Công nghệ sử dụng

- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Web Server:** Apache (XAMPP)

## Cài đặt

1. Clone repository:
```bash
git clone https://github.com/chienvn102/webbangiay.git
```

2. Copy project vào thư mục web server (ví dụ: `htdocs` của XAMPP)

3. Tạo database MySQL và import file `quanlybangiay.sql`

4. Tạo file `connect.php` với cấu hình database:
```php
<?php
$host = 'localhost';
$dbname = 'quanlybangiay';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>
```

5. Truy cập website qua `http://localhost/webbangiay`

## Cấu trúc thư mục

```
webbangiay/
├── uploads/           # Thư mục chứa hình ảnh upload
├── admin*.php         # Các trang quản trị
├── index.php          # Trang chủ
├── products.php       # Danh sách sản phẩm
├── product-detail.php # Chi tiết sản phẩm
├── cart.php           # Giỏ hàng
├── checkout.php       # Thanh toán
├── login.php          # Đăng nhập
├── register.php       # Đăng ký
├── header.php         # Header chung
├── footer.php         # Footer chung
├── connect.php        # Kết nối database (không được commit)
└── quanlybangiay.sql  # File database
```

## Tác giả

- **Developer:** chienvn102
- **GitHub:** https://github.com/chienvn102

## License

This project is open source and available under the [MIT License](LICENSE).
