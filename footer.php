<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-4">
    <div class="container text-center text-md-left">
        <div class="row">
            <div class="col-md-3">
                <h5>SẢN PHẨM</h5>
                <ul class="list-unstyled">
                    <li><a href="products.php?gender=nam">Giày Nam</a></li>
                    <li><a href="products.php?gender=nu">Giày Nữ</a></li>
                    <li><a href="products.php?sale=1">Sale-off</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>VỀ CÔNG TY</h5>
                <ul class="list-unstyled">
                    <li><a href="#">Về Ananas</a></li>
                    <li><a href="#">Tuyển dụng</a></li>
                    <li><a href="#">Liên hệ</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>HỖ TRỢ</h5>
                <ul class="list-unstyled">
                    <li><a href="track-order.php">Tra cứu đơn hàng</a></li>
                    <li><a href="#">Chính sách đổi trả</a></li>
                    <li><a href="#">Hướng dẫn mua hàng</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>LIÊN HỆ</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-phone"></i> 0961108937</li>
                    <li><i class="fas fa-envelope"></i> chienvn102@gmail.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> Tìm cửa hàng</li>
                </ul>
            </div>
        </div>
        <hr class="mt-4">
        <div class="text-center">
            <p>&copy; 2025 Ananas. All rights reserved.</p>
        </div>
    </div>
</footer>

<a href="#" id="back-to-top" class="btn btn-lg btn-primary" role="button"><i class="fas fa-arrow-up"></i></a>

<style>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var backToTopButton = document.getElementById('back-to-top');

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
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
