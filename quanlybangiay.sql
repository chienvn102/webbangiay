-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 21, 2025 lúc 06:51 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanlybangiay`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banner`
--

CREATE TABLE `banner` (
  `banner_id` int(10) NOT NULL,
  `banner_img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `banner`
--

INSERT INTO `banner` (`banner_id`, `banner_img`) VALUES
(8, 'uploads/banners/banner_1750515608.jpg'),
(9, 'uploads/banners/banner_1750515639.jpeg'),
(10, 'uploads/banners/banner_1750521022.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhcho`
--

CREATE TABLE `danhcho` (
  `dc_ma` int(10) NOT NULL,
  `dc_ten` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `danhcho`
--

INSERT INTO `danhcho` (`dc_ma`, `dc_ten`) VALUES
(1, 'nam'),
(2, 'nữ');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `discover`
--

CREATE TABLE `discover` (
  `disc_id` int(10) NOT NULL,
  `disc_img` varchar(255) NOT NULL DEFAULT '0',
  `disc_tieude` varchar(255) NOT NULL DEFAULT '0',
  `disc_noidung` varchar(1000) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `discover`
--

INSERT INTO `discover` (`disc_id`, `disc_img`, `disc_tieude`, `disc_noidung`) VALUES
(14, 'uploads/1750524041_Corluray_bannerweb_desktop1920x1050 (1).jpg', 'NEW PRODUCT', 'sản phẩm mới'),
(15, 'uploads/1750524075_1750523889_catalogy-3.jpg', 'SNEAKER FEST VIETNAM VÀ SỰ KẾT HỢP', 'ádhahgjdhajsdjasghdsjhagdghja'),
(16, 'uploads/1750524097_image_1.jpg', 'URBAS CORLURAY PACK', 'gshadgagdsagvjkhgghashdgahsdv\r\nsadjadjadas');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dondathang`
--

CREATE TABLE `dondathang` (
  `dh_ma` int(10) NOT NULL,
  `dh_ngaylap` date NOT NULL,
  `dh_noigiao` varchar(255) NOT NULL,
  `dh_trangthaithanhtoan` int(10) NOT NULL,
  `httt_ma` int(10) NOT NULL,
  `kh_ma` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `dondathang`
--

INSERT INTO `dondathang` (`dh_ma`, `dh_ngaylap`, `dh_noigiao`, `dh_trangthaithanhtoan`, `httt_ma`, `kh_ma`) VALUES
(70, '2024-06-18', 'hanoi', 1, 3, 71),
(71, '2024-06-18', 'hanoi', 5, 3, 82),
(72, '2024-06-21', 'nha so 6', 4, 3, 83),
(73, '2024-07-09', '68/45 Triều Khúc', 4, 3, 84),
(74, '2024-07-09', '68/45 Triều Khúc', 1, 3, 85),
(75, '2024-07-09', '68/45 Triều Khúc', 4, 3, 86),
(76, '2024-07-10', '68/45 Triều Khúc', 3, 3, 87),
(77, '2024-07-10', '68/45 Triều Khúc', 5, 3, 88),
(78, '2024-07-10', '68/45 Triều Khúc', 4, 3, 89),
(79, '2025-06-03', '68/45 Triều Khúc', 1, 1, 90),
(80, '2025-06-03', '68/45 Triều Khúc', 2, 3, 91),
(82, '2025-06-21', 'asaasa', 4, 1, 96),
(83, '2025-06-21', 'a', 5, 1, 97),
(84, '2025-06-21', 'dxsadcasjhdhajs', 1, 2, 98),
(85, '2025-06-21', 'An Dương', 5, 3, 99),
(86, '2025-06-21', 'ádasdasd', 1, 3, 100),
(87, '2025-06-21', '68/45 Triều Khúc', 4, 3, 84);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinhsanpham`
--

CREATE TABLE `hinhsanpham` (
  `hsp_ma` int(10) NOT NULL,
  `hsp_1` varchar(255) NOT NULL,
  `hsp_2` varchar(255) NOT NULL,
  `hsp_3` varchar(255) NOT NULL,
  `sp_ma` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `hinhsanpham`
--

INSERT INTO `hinhsanpham` (`hsp_ma`, `hsp_1`, `hsp_2`, `hsp_3`, `sp_ma`) VALUES
(10, '../../uploads/Pro_AV00152_2.jpg', '../../uploads/Pro_AV00152_3.jpg', '../../uploads/Pro_AV00152_4.jpg', 1),
(12, '../../uploads/Pro_AV00150_2.jpg', '../../uploads/Pro_AV00150_3.jpg', '../../uploads/Pro_AV00150_4.jpg', 3),
(13, '../../uploads/pro_AV00142_2.jpg', '../../uploads/pro_AV00142_3.jpg', '../../uploads/pro_AV00142_4.jpg', 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinhthucthanhtoan`
--

CREATE TABLE `hinhthucthanhtoan` (
  `httt_ma` int(10) NOT NULL,
  `httt_ten` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `hinhthucthanhtoan`
--

INSERT INTO `hinhthucthanhtoan` (`httt_ma`, `httt_ten`) VALUES
(1, 'Thanh Toán Qua MoMo'),
(2, 'Chuyển Khoản Qua Ngân Hàng'),
(3, 'Thanh Toán Trực Tiếp');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `kh_ma` int(10) NOT NULL,
  `kh_hoten` varchar(50) NOT NULL DEFAULT '0',
  `kh_email` varchar(50) NOT NULL DEFAULT '0',
  `kh_sdt` varchar(50) NOT NULL DEFAULT '0',
  `kh_diachi` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`kh_ma`, `kh_hoten`, `kh_email`, `kh_sdt`, `kh_diachi`) VALUES
(71, 'Huy', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(72, 'Huy', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(73, 'da', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(74, 'da', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(75, 'Huy', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(76, 'Huy', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(77, 'Huy', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(78, 'Huy', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(79, 'Huy', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(80, 'Huy', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(81, 'Huy', 'nguyenquanghuy14052004@gmail.com', '971 198 311', 'hanoi'),
(82, 'NAm', 'nam@gmail.com', '971 198 311', 'hanoi'),
(83, 'quan', 'congquanion@gmail.com', '0867124574', 'nha so 6'),
(84, 'chienvn102', 'chienvn102@gmail.com', '0961108937', '68/45 Triều Khúc'),
(85, 'NGUYEN MANH CHIEN', 'chienvn102@gmail.com', '0961108937', '68/45 Triều Khúc'),
(86, 'NGUYEN MANH CHIEN', 'chienvn102@gmail.com', '0961108937', '68/45 Triều Khúc'),
(87, 'NGUYEN MANH CHIEN', 'chienvn102@gmail.com', '0961108937', '68/45 Triều Khúc'),
(88, 'NGUYEN MANH CHIEN', 'chienvn102@gmail.com', '0961108937', '68/45 Triều Khúc'),
(89, 'NGUYEN MANH CHIEN', 'chienvn102@gmail.com', '0961108937', '68/45 Triều Khúc'),
(90, 'NGUYEN MANH CHIEN', 'chienvn102@gmail.com', '0961108937', '68/45 Triều Khúc'),
(91, 'NGUYEN MANH CHIEN', 'chienvn102@gmail.com', '0961108937', '68/45 Triều Khúc'),
(96, 'abc', 'guest_1750516812@ananas.com', '01', 'asaasa'),
(97, 'redsctfgvhb', 'guest_1750516976@ananas.com', '456789', 'a'),
(98, 'gytasdhgahdgasd', 'guest_1750518175@ananas.com', '1235423413424', 'dxsadcasjhdhajs'),
(99, 'Nguyễn Châu Anh', 'guest_1750519280@ananas.com', '0961108937', 'An Dương'),
(100, 'gdagvsda', 'guest_1750523113@ananas.com', '12312431', 'ádasdasd');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lineslide`
--

CREATE TABLE `lineslide` (
  `ls_id` int(10) NOT NULL,
  `ls_noidung` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `lineslide`
--

INSERT INTO `lineslide` (`ls_id`, `ls_noidung`) VALUES
(13, 'HÀNG 2 TUẦN NHẬN ĐỔI - GIÀY NỬA NĂM BẢO HÀNH'),
(14, 'BUY MORE PAY LESS - ÁP DỤNG KHI MUA PHỤ KIỆN'),
(15, 'BUY 2 GET 10% OFF - ÁP DỤNG VỚI TẤT CẢ BASIC TEE'),
(16, 'HÀNG 2 TUẦN NHẬN ĐỔI - GIÀY NỬA NĂM BẢO HÀNH');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loaisanpham`
--

CREATE TABLE `loaisanpham` (
  `lsp_ma` int(10) NOT NULL,
  `lsp_ten` varchar(100) NOT NULL DEFAULT '',
  `lsp_mota` varchar(500) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `loaisanpham`
--

INSERT INTO `loaisanpham` (`lsp_ma`, `lsp_ten`, `lsp_mota`) VALUES
(59, 'Basas', 'Basas'),
(60, 'Vintas', 'Vintas'),
(61, 'Urbas', 'Urbas'),
(62, 'Pattas', 'Pattas');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhasanxuat`
--

CREATE TABLE `nhasanxuat` (
  `nsx_ma` int(10) NOT NULL,
  `nsx_ten` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `nhasanxuat`
--

INSERT INTO `nhasanxuat` (`nsx_ma`, `nsx_ten`) VALUES
(13, 'Ananas');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` int(10) NOT NULL,
  `role_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'admin'),
(2, 'nhanvien'),
(3, 'user');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `sp_ma` int(10) NOT NULL,
  `sp_ten` varchar(100) NOT NULL,
  `sp_gia` int(10) NOT NULL DEFAULT 0,
  `sp_ngaycapnhat` date NOT NULL,
  `sp_soluong` int(10) NOT NULL,
  `lsp_ma` int(10) NOT NULL,
  `nsx_ma` int(10) NOT NULL,
  `sp_hinh` varchar(200) DEFAULT NULL,
  `sp_mota` varchar(50) DEFAULT NULL,
  `dc_ma` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`sp_ma`, `sp_ten`, `sp_gia`, `sp_ngaycapnhat`, `sp_soluong`, `lsp_ma`, `nsx_ma`, `sp_hinh`, `sp_mota`, `dc_ma`) VALUES
(1, 'BASAS WORKADAY', 600000, '2024-06-13', 22, 59, 13, '../../uploads/1718681718Pro_AV00152_1.jpg', 'BASAS WORKADAY', 1),
(3, 'BASAS WORKADAY - TOP THẤP - REAL TEAL', 580000, '2024-06-21', 0, 59, 13, '../../uploads/1718936142Pro_AV00150_1.jpg', 'BASAS WORKADAY', 1),
(4, 'BASAS EVERGREEN - LOW TOP - EVERGREEN', 500000, '2025-06-21', 47, 59, 13, 'uploads/1750521165_1718770300demo2.jpeg', 'BASAS EVERGREEN ', 1),
(6, 'Giày ', 500000, '2025-06-21', 0, 59, 13, 'uploads/1750516251_1720425664demo1.jpg', 'ananas', 1),
(7, 'Dép', 400000, '2025-06-21', 100, 61, 13, 'uploads/1750521201_1720425837Pro_AV00180_1.jpeg', '', 2),
(8, 'abc', 600000, '2025-06-21', 100, 60, 13, '1750523799_demo2.jpeg', '', 2),
(9, 'giày adidas', 800000, '2025-06-21', 100, 62, 13, '1750523836_1718886717Pro_AV00180_4.jpeg', '', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham_dondathang`
--

CREATE TABLE `sanpham_dondathang` (
  `sp_ma` int(10) NOT NULL,
  `dh_ma` int(10) NOT NULL,
  `sp_dh_soluong` int(10) NOT NULL,
  `sp_dh_dongia` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham_dondathang`
--

INSERT INTO `sanpham_dondathang` (`sp_ma`, `dh_ma`, `sp_dh_soluong`, `sp_dh_dongia`) VALUES
(1, 72, 2, 600000.00),
(1, 73, 1, 600000.00),
(1, 74, 10, 600000.00),
(1, 75, 3, 600000.00),
(4, 76, 2, 500000.00),
(3, 76, 1, 580000.00),
(1, 76, 1, 600000.00),
(3, 77, 1, 580000.00),
(1, 77, 2, 600000.00),
(4, 77, 1, 500000.00),
(1, 78, 9, 600000.00),
(1, 79, 1, 600000.00),
(1, 80, 1, 600000.00),
(3, 80, 1, 580000.00),
(6, 82, 1, 500000.00),
(6, 83, 2, 500000.00),
(6, 84, 3, 500000.00),
(6, 85, 2, 500000.00),
(3, 86, 2, 580000.00),
(6, 87, 100, 500000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `trangthai`
--

CREATE TABLE `trangthai` (
  `tt_ma` int(10) NOT NULL,
  `tt_ten` varchar(50) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `trangthai`
--

INSERT INTO `trangthai` (`tt_ma`, `tt_ten`) VALUES
(1, 'Đặt Hàng Thành Công'),
(2, 'Chuyển Qua Giao Nhận'),
(3, 'Đang Giao Hàng'),
(4, 'Giao Hàng Thành Công'),
(5, 'ĐÃ HỦY ĐƠN');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role_id`, `name`) VALUES
(1, 'admin@gmail.com', '123456', 1, 'admin'),
(2, 'nv1@gmail.com', '123456', 2, 'nhan vien'),
(3, 'congquanion@gmail.com', 'Congquan123', 3, 'quan9'),
(4, 'chienvn102@gmail.com', '123456', 3, 'chienvn102');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`banner_id`) USING BTREE;

--
-- Chỉ mục cho bảng `danhcho`
--
ALTER TABLE `danhcho`
  ADD PRIMARY KEY (`dc_ma`) USING BTREE;

--
-- Chỉ mục cho bảng `discover`
--
ALTER TABLE `discover`
  ADD PRIMARY KEY (`disc_id`) USING BTREE;

--
-- Chỉ mục cho bảng `dondathang`
--
ALTER TABLE `dondathang`
  ADD PRIMARY KEY (`dh_ma`) USING BTREE,
  ADD KEY `httt_ma` (`httt_ma`) USING BTREE,
  ADD KEY `kh_ma` (`kh_ma`) USING BTREE,
  ADD KEY `dh_trangthaithanhtoan` (`dh_trangthaithanhtoan`) USING BTREE;

--
-- Chỉ mục cho bảng `hinhsanpham`
--
ALTER TABLE `hinhsanpham`
  ADD PRIMARY KEY (`hsp_ma`) USING BTREE,
  ADD KEY `sp_ma` (`sp_ma`) USING BTREE;

--
-- Chỉ mục cho bảng `hinhthucthanhtoan`
--
ALTER TABLE `hinhthucthanhtoan`
  ADD PRIMARY KEY (`httt_ma`) USING BTREE;

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`kh_ma`) USING BTREE;

--
-- Chỉ mục cho bảng `lineslide`
--
ALTER TABLE `lineslide`
  ADD PRIMARY KEY (`ls_id`) USING BTREE;

--
-- Chỉ mục cho bảng `loaisanpham`
--
ALTER TABLE `loaisanpham`
  ADD PRIMARY KEY (`lsp_ma`) USING BTREE;

--
-- Chỉ mục cho bảng `nhasanxuat`
--
ALTER TABLE `nhasanxuat`
  ADD PRIMARY KEY (`nsx_ma`) USING BTREE;

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`) USING BTREE;

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`sp_ma`),
  ADD KEY `lsp_ma` (`lsp_ma`,`nsx_ma`,`dc_ma`),
  ADD KEY `dc_ma` (`dc_ma`),
  ADD KEY `nsx_ma` (`nsx_ma`);

--
-- Chỉ mục cho bảng `sanpham_dondathang`
--
ALTER TABLE `sanpham_dondathang`
  ADD KEY `sp_ma` (`sp_ma`,`dh_ma`),
  ADD KEY `dh_ma` (`dh_ma`);

--
-- Chỉ mục cho bảng `trangthai`
--
ALTER TABLE `trangthai`
  ADD PRIMARY KEY (`tt_ma`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `banner`
--
ALTER TABLE `banner`
  MODIFY `banner_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `danhcho`
--
ALTER TABLE `danhcho`
  MODIFY `dc_ma` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `discover`
--
ALTER TABLE `discover`
  MODIFY `disc_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `dondathang`
--
ALTER TABLE `dondathang`
  MODIFY `dh_ma` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT cho bảng `hinhsanpham`
--
ALTER TABLE `hinhsanpham`
  MODIFY `hsp_ma` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `kh_ma` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT cho bảng `lineslide`
--
ALTER TABLE `lineslide`
  MODIFY `ls_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `loaisanpham`
--
ALTER TABLE `loaisanpham`
  MODIFY `lsp_ma` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT cho bảng `nhasanxuat`
--
ALTER TABLE `nhasanxuat`
  MODIFY `nsx_ma` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `sp_ma` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `trangthai`
--
ALTER TABLE `trangthai`
  MODIFY `tt_ma` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `dondathang`
--
ALTER TABLE `dondathang`
  ADD CONSTRAINT `dondathang_ibfk_1` FOREIGN KEY (`httt_ma`) REFERENCES `hinhthucthanhtoan` (`httt_ma`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dondathang_ibfk_2` FOREIGN KEY (`kh_ma`) REFERENCES `khachhang` (`kh_ma`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dondathang_ibfk_3` FOREIGN KEY (`dh_trangthaithanhtoan`) REFERENCES `trangthai` (`tt_ma`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `hinhsanpham`
--
ALTER TABLE `hinhsanpham`
  ADD CONSTRAINT `hinhsanpham_ibfk_1` FOREIGN KEY (`sp_ma`) REFERENCES `sanpham` (`sp_ma`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`dc_ma`) REFERENCES `danhcho` (`dc_ma`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sanpham_ibfk_2` FOREIGN KEY (`lsp_ma`) REFERENCES `loaisanpham` (`lsp_ma`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sanpham_ibfk_3` FOREIGN KEY (`nsx_ma`) REFERENCES `nhasanxuat` (`nsx_ma`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `sanpham_dondathang`
--
ALTER TABLE `sanpham_dondathang`
  ADD CONSTRAINT `sanpham_dondathang_ibfk_1` FOREIGN KEY (`dh_ma`) REFERENCES `dondathang` (`dh_ma`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sanpham_dondathang_ibfk_2` FOREIGN KEY (`sp_ma`) REFERENCES `sanpham` (`sp_ma`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
