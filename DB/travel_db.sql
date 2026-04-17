-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 14, 2026 lúc 07:05 PM
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
-- Cơ sở dữ liệu: `travel_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tour_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `num_adults` int(11) DEFAULT 1,
  `num_children` int(11) DEFAULT 0,
  `num_infants` int(11) DEFAULT 0,
  `departure_date` varchar(50) DEFAULT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed','refunded') DEFAULT 'pending',
  `momo_trans_id` varchar(100) DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `tour_id`, `customer_name`, `customer_phone`, `customer_email`, `num_adults`, `num_children`, `num_infants`, `departure_date`, `total_price`, `status`, `booking_date`) VALUES
(1, NULL, 3, 'Trần Thị Huyền', '0123456789', 'huyen@gmail.com', 1, 0, 0, NULL, 1200000.00, 'pending', '2026-02-08 06:40:35'),
(2, NULL, 5, 'Trần Thị Huyền', '0123456789', 'huyen@gmail.com', 1, 0, 0, NULL, 12000000.00, 'cancelled', '2026-02-08 06:46:23'),
(3, NULL, 6, 'Trần Thị Huyền', '0123456789', 'huyen@gmail.com', 1, 0, 0, NULL, 5000000.00, 'cancelled', '2026-02-08 06:47:54'),
(4, NULL, 4, 'Trần Thị Huyền', '0123456789', 'huyen@gmail.com', 1, 0, 0, NULL, 15000000.00, 'completed', '2026-02-08 06:55:51'),
(5, NULL, 6, 'Trần Thị Huyền', '0123456789', 'huyen@gmail.com', 1, 0, 0, NULL, 5000000.00, 'completed', '2026-02-08 07:23:55'),
(6, 8, 6, 'Phạm Trần Hoài Ly', '', 'lily05@gmail.com', 3, 4, 4, '12/04', 5000200.00, 'completed', '2026-04-10 12:50:51'),
(7, 9, 6, 'Ngô Văn Nhớ', '', 'vannho14122@gmail.com', 7, 0, 0, '12/04', 7000000.00, 'completed', '2026-04-10 12:53:46'),
(8, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 1, 2, '12/04', 3000000.00, 'completed', '2026-04-12 14:42:22'),
(9, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 0, 0, 4, '28/04', 2000000.00, 'completed', '2026-04-12 14:54:51'),
(10, 1, 1, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 2, 3, '14/06', 5600000.00, 'completed', '2026-04-12 16:38:50'),
(11, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '28/04', 4000000.00, 'completed', '2026-04-12 16:51:43'),
(12, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '14/06', 1520000.00, 'completed', '2026-04-12 17:10:01'),
(13, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '28/04', 3600000.00, 'completed', '2026-04-12 17:18:31'),
(14, 1, 4, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 2, 2, '', 9750000.00, '', '2026-04-12 17:21:36'),
(15, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 0, 0, 0, '14/06', 0.00, '', '2026-04-12 17:22:42'),
(16, 1, 3, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 1, '', 1200000.00, 'completed', '2026-04-12 17:31:09'),
(17, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 0, 3, 0, '28/04', 2700000.00, '', '2026-04-12 17:39:03'),
(18, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '28/04', 4000000.00, '', '2026-04-12 17:41:30'),
(19, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 4, 0, 0, '14/06', 3200000.00, '', '2026-04-12 17:44:11'),
(20, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 1, 1, '12/04', 2475000.00, '', '2026-04-12 17:45:54'),
(21, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '14/06', 1600000.00, '', '2026-04-12 17:57:18'),
(22, 1, 3, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 1, '', 2400000.00, '', '2026-04-12 18:02:58'),
(23, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 3, 0, 2, '28/04', 7000000.00, '', '2026-04-12 18:07:32'),
(24, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 0, 0, 3, '12/04', 750000.00, '', '2026-04-12 18:07:48'),
(25, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '12/04', 2000000.00, 'completed', '2026-04-12 18:08:41'),
(26, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 3, 0, 0, '14/06', 2400000.00, '', '2026-04-12 18:11:54'),
(27, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 2, '14/06', 2000000.00, '', '2026-04-12 18:19:07'),
(28, 11, 7, 'Phạm Trần Hoài Ly', '', 'Hoaily@gamil.com', 6, 0, 0, '14/06', 4800000.00, '', '2026-04-12 18:20:10'),
(29, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '12/04', 2000000.00, '', '2026-04-12 18:25:58'),
(30, 1, 4, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '', 30000000.00, '', '2026-04-12 18:32:05'),
(31, 1, 4, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 3, 0, 0, '', 45000000.00, '', '2026-04-12 18:33:40'),
(32, 1, 2, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 3, 0, 0, '', 30000000.00, '', '2026-04-12 18:36:02'),
(33, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 3, 0, 0, '14/06', 2400000.00, '', '2026-04-12 18:41:55'),
(34, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, '', '2026-04-12 18:43:38'),
(35, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '12/04', 2000000.00, 'completed', '2026-04-12 18:46:37'),
(36, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, '', '2026-04-12 18:48:14'),
(37, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '12/04', 2000000.00, '', '2026-04-12 18:52:42'),
(38, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '28/04', 1800000.00, '', '2026-04-12 18:56:41'),
(39, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '28/04', 2000000.00, '', '2026-04-12 18:59:11'),
(40, 1, 3, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 1, '', 1200000.00, '', '2026-04-12 19:03:00'),
(41, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '14/06', 1600000.00, '', '2026-04-12 19:05:30'),
(42, 1, 4, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '', 30000000.00, '', '2026-04-12 19:06:31'),
(43, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, '', '2026-04-12 19:19:39'),
(44, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 0, 2, 0, '14/06', 800000.00, '', '2026-04-12 19:25:47'),
(45, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, '', '2026-04-12 19:30:35'),
(46, 1, 4, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '', 15000000.00, '', '2026-04-12 19:36:40'),
(47, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '28/04', 2000000.00, '', '2026-04-12 19:40:02'),
(48, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, 'cancelled', '2026-04-12 19:41:07'),
(49, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '28/04', 2000000.00, '', '2026-04-12 19:42:09'),
(50, 1, 2, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 5, 0, 0, '', 50000000.00, '', '2026-04-12 19:51:01'),
(51, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, '', '2026-04-12 19:52:37'),
(52, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, '', '2026-04-12 19:59:54'),
(53, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '28/04', 2000000.00, '', '2026-04-12 20:02:40'),
(54, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, '', '2026-04-12 20:04:56'),
(55, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, '', '2026-04-12 20:06:20'),
(56, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '12/04', 1000000.00, 'completed', '2026-04-12 20:08:56'),
(57, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '12/04', 1000000.00, '', '2026-04-12 20:09:31'),
(58, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, '', '2026-04-12 20:13:03'),
(59, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 1, 1, '17/05', 500000.00, 'completed', '2026-04-13 02:53:05'),
(60, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 21, 0, 0, '17/05', 10500000.00, 'completed', '2026-04-14 14:30:12'),
(61, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 6, 0, 0, '17/05', 3000000.00, 'completed', '2026-04-14 14:35:21'),
(62, 1, 4, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '', 15000000.00, 'completed', '2026-04-14 14:46:06'),
(63, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '28/04', 4000000.00, '', '2026-04-14 15:31:39'),
(64, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 2, 0, 0, '17/05', 1000000.00, '', '2026-04-14 15:36:12'),
(65, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '17/05', 500000.00, '', '2026-04-14 15:38:58'),
(66, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '17/05', 500000.00, 'pending', '2026-04-14 15:52:34'),
(67, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '17/05', 500000.00, 'pending', '2026-04-14 15:55:29'),
(68, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '17/05', 500000.00, 'pending', '2026-04-14 15:55:32'),
(69, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '17/05', 500000.00, 'pending', '2026-04-14 15:57:55'),
(70, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '17/05', 500000.00, 'pending', '2026-04-14 15:57:57'),
(71, 1, 8, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '17/05', 500000.00, 'pending', '2026-04-14 15:58:00'),
(72, 1, 6, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '12/04', 1000000.00, 'pending', '2026-04-14 16:02:12'),
(73, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '28/04', 2000000.00, 'pending', '2026-04-14 16:02:57'),
(74, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '28/04', 2000000.00, 'pending', '2026-04-14 16:07:00'),
(75, 1, 5, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '28/04', 2000000.00, 'pending', '2026-04-14 16:10:07'),
(76, 1, 7, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '14/06', 800000.00, 'completed', '2026-04-14 16:10:43'),
(77, 1, 3, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '', 1200000.00, 'completed', '2026-04-14 16:13:28'),
(78, 1, 3, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '', 1200000.00, 'pending', '2026-04-14 16:15:24'),
(79, 1, 3, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '', 1200000.00, 'pending', '2026-04-14 16:16:34'),
(80, 1, 3, 'NGÔ VĂN NHỚ', '0777454550', 'vannho@travel.com', 1, 0, 0, '', 1200000.00, 'pending', '2026-04-14 16:18:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Du lịch biển đảo', 'du-lich-bien-dao'),
(2, 'Du lịch mạo hiểm', 'du-lich-mao-hiem'),
(3, 'Du lịch nghỉ dưỡng', 'du-lich-nghi-duong'),
(4, 'Du lịch quốc tế / nội địa', 'du-lich-quoc-te-noi-dia'),
(5, 'Du lịch núi - sinh thái', 'du-lich-nui-sinh-thai'),
(6, 'Du lịch lễ hội - sự kiện', 'du-lich-le-hoi-su-kien'),
(7, 'Du lịch văn hóa - lịch sử', 'du-lich-van-hoa-lich-su'),
(8, 'Du lịch thành phố ( City Tour )', 'du-lich-thanh-pho-city-tour'),
(9, 'Du lịch tâm linh', 'du-lich-tam-linh'),
(10, 'Du lịch ẩm thực', 'du-lich-am-thuc');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `summary`, `content`, `image`, `created_at`) VALUES
(1, 'Đã nẵng kích cầu du lịch trong năm 2026', 'n-ng-k-ch-c-u-du-l-ch-trong-n-m-2026', 'Đã nẵng kích cầu du lịch trong năm 2026', '<p>Đ&agrave; Nẵng l&agrave; một trong những điểm đến du lịch hấp dẫn nhất Việt Nam, nổi bật với sự kết hợp h&agrave;i h&ograve;a giữa thi&ecirc;n nhi&ecirc;n, văn h&oacute;a v&agrave; hiện đại. Th&agrave;nh phố sở hữu những b&atilde;i biển đẹp như Mỹ Kh&ecirc;, Non Nước với l&agrave;n nước trong xanh v&agrave; bờ c&aacute;t mịn. B&agrave; N&agrave; Hills g&acirc;y ấn tượng với Cầu V&agrave;ng độc đ&aacute;o v&agrave; kh&iacute; hậu m&aacute;t mẻ quanh năm. Ngo&agrave;i ra, Đ&agrave; Nẵng c&ograve;n gần c&aacute;c di sản nổi tiếng như Hội An, Huế, Mỹ Sơn. Ẩm thực phong ph&uacute;, con người th&acirc;n thiện v&agrave; hạ tầng hiện đại khiến Đ&agrave; Nẵng trở th&agrave;nh điểm đến l&yacute; tưởng cho mọi du kh&aacute;ch.</p>\r\n\r\n<p><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770531742_chuong-trinh-kich-cau-thu-hut-khach-du-lich-den-thanh-pho-da-nang-cac-thang-cuoi-nam-2025-de-da-nang-moi-trai-nghiem-moi-new-da-nang-new-experience-01-1024x576.jpg\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n', '1770531750_chuong-trinh-kich-cau-thu-hut-khach-du-lich-den-thanh-pho-da-nang-cac-thang-cuoi-nam-2025-de-da-nang-moi-trai-nghiem-moi-new-da-nang-new-experience-01-1024x576.jpg', '2026-02-08 06:22:30'),
(2, 'GIới thiệu di tích chùa bái đính', 'gi-i-thi-u-di-t-ch-ch-a-b-i-nh', 'Khu du lịch chùa bái đính - nơi tận hưởng cảm giác trong lành thu hút du khách khắp nơi', '<p><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770531879_dji202305271839200167d-1754880862074799106427.webp\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n\r\n<p>Ch&ugrave;a B&aacute;i Đ&iacute;nh nằm tại tỉnh Ninh B&igrave;nh, l&agrave; quần thể ch&ugrave;a lớn v&agrave; nổi tiếng bậc nhất Việt Nam. Nơi đ&acirc;y thu h&uacute;t du kh&aacute;ch bởi kiến tr&uacute;c đồ sộ, h&agrave;i h&ograve;a giữa n&eacute;t truyền thống v&agrave; hiện đại. Ch&ugrave;a sở hữu nhiều kỷ lục như tượng Phật bằng đồng lớn, h&agrave;nh lang La H&aacute;n d&agrave;i v&agrave; th&aacute;p chu&ocirc;ng uy nghi. Kh&ocirc;ng gian ch&ugrave;a rộng lớn, y&ecirc;n b&igrave;nh, bao quanh bởi n&uacute;i non v&agrave; thi&ecirc;n nhi&ecirc;n xanh m&aacute;t. Đến với ch&ugrave;a B&aacute;i Đ&iacute;nh, du kh&aacute;ch kh&ocirc;ng chỉ chi&ecirc;m b&aacute;i, cầu an m&agrave; c&ograve;n c&oacute; cơ hội kh&aacute;m ph&aacute; văn h&oacute;a, lịch sử v&agrave; t&igrave;m lại sự thanh tịnh trong t&acirc;m hồn.</p>\r\n\r\n<p><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770531892_chua-bai-dinh_68a0743d01fe7.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n', '1770531897_dji202305271839200167d-1754880862074799106427.webp', '2026-02-08 06:24:57'),
(3, 'Trải nghiệm vịnh hạ long mùa lễ', 'tr-i-nghi-m-v-nh-h-long-m-a-l', 'Trải nghiệm vịnh hạ long mùa lễ', '<p>Vịnh Hạ Long thuộc tỉnh Quảng Ninh, l&agrave; một trong những kỳ quan thi&ecirc;n nhi&ecirc;n nổi tiếng của Việt Nam v&agrave; thế giới. Vịnh g&acirc;y ấn tượng với h&agrave;ng ngh&igrave;n h&ograve;n đảo đ&aacute; v&ocirc;i mang h&igrave;nh d&aacute;ng độc đ&aacute;o, nổi bật tr&ecirc;n l&agrave;n nước xanh biếc. Du kh&aacute;ch đến đ&acirc;y c&oacute; thể tham quan hang động kỳ ảo, ch&egrave;o thuyền kayak, tắm biển v&agrave; nghỉ đ&ecirc;m tr&ecirc;n du thuyền. Kh&ocirc;ng chỉ c&oacute; cảnh quan h&ugrave;ng vĩ, Vịnh Hạ Long c&ograve;n gắn liền với nhiều gi&aacute; trị lịch sử v&agrave; văn h&oacute;a đặc sắc, l&agrave; điểm đến l&yacute; tưởng cho những ai y&ecirc;u thi&ecirc;n nhi&ecirc;n v&agrave; kh&aacute;m ph&aacute;.</p>\r\n\r\n<p><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770531977_kinh-nghiem-du-lich-ha-long-1_1674039271.jpg\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n', '1770531984_vviqkvkrzeohe12wxjyu.jpg', '2026-02-08 06:26:24'),
(4, 'Lễ hội pháo hoa quốc tế Đà Nẵng hè 2026', 'l-h-i-ph-o-hoa-qu-c-t-n-ng-h-2026', 'Ngày hội pháo hoa lớn nhất từ đầu năm 2026 diễn ra tại thành phố Đà Nẵng', '', '1776013005_1773297876_ngua.webp', '2026-04-12 16:56:45'),
(5, 'Bài mới ', 'b-i-m-i', '', '', '1776013359_phaohoa.jpg', '2026-04-12 17:02:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_toasted` tinyint(4) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `is_toasted`, `link`) VALUES
(1, 1, 'Nhận mã thành công', 'Mã giảm giá đã được lưu vào ví của bạn.', 'promo', 1, '2026-04-12 16:47:51', 1, NULL),
(2, NULL, 'Tin tức mới', 'Khám phá bài viết: \'Bài mới \' vừa được đăng tải.', 'news', 0, '2026-04-12 17:02:39', 1, 'news-detail.php?id=5'),
(3, NULL, 'Tour mới cực hot!', 'Hành trình \'Tour Hội An\' vừa được mở bán. Khám phá ngay!', 'tour', 1, '2026-04-12 17:05:59', 1, 'tour-detail.php?id=7'),
(4, 1, 'Đơn hàng mới #12', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:10:05', 1, 'admin/bookings.php'),
(5, 1, 'Cập nhật đơn hàng #12', 'Trạng thái tour của bạn đã được chuyển thành: confirmed', 'payment', 0, '2026-04-12 17:10:34', 1, 'profile.php?tab=tours'),
(6, 1, 'Cập nhật đơn hàng #12', 'Trạng thái tour của bạn đã được chuyển thành: completed', 'payment', 1, '2026-04-12 17:10:59', 1, 'profile.php?tab=tours'),
(7, 1, 'Cập nhật đơn hàng #11', 'Trạng thái tour của bạn đã được chuyển thành: confirmed', 'payment', 0, '2026-04-12 17:13:07', 1, 'profile.php?tab=tours'),
(8, 1, 'Cập nhật đơn hàng #11', 'Trạng thái tour của bạn đã được chuyển thành: completed', 'payment', 0, '2026-04-12 17:16:34', 1, 'profile.php?tab=tours'),
(9, 1, 'Đơn hàng mới #13', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:18:35', 1, 'admin/bookings.php'),
(10, 1, 'Cập nhật đơn hàng #13', 'Trạng thái tour của bạn đã được chuyển thành: confirmed', 'payment', 0, '2026-04-12 17:19:09', 1, 'profile.php?tab=tours'),
(11, 1, 'Cập nhật đơn hàng #13', 'Xác nhận bạn đã thanh toán toàn bộ Tour mã #13 thành công', 'payment', 0, '2026-04-12 17:19:45', 1, 'profile.php?tab=tours'),
(12, 1, 'Đơn hàng mới #14', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:21:40', 1, 'admin/bookings.php'),
(13, 1, 'Thông báo Tour mã #14', 'Xác nhận đặt cọc tour mã #14 thành công', 'payment', 0, '2026-04-12 17:22:10', 1, 'profile.php?tab=tours'),
(14, 1, 'Đơn hàng mới #15', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:22:46', 1, 'admin/bookings.php'),
(15, 1, 'Đơn hàng mới #16', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:31:13', 1, 'admin/bookings.php'),
(16, 1, 'Thông báo Tour mã #16', 'Xác nhận đặt cọc tour mã #16 thành công', 'payment', 0, '2026-04-12 17:31:31', 1, 'profile.php?tab=tours'),
(17, 1, 'Báo cáo thanh toán đủ #16', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 17:32:35', 1, 'admin/bookings.php'),
(18, 1, 'Thông báo Tour mã #16', 'Xác nhận bạn đã thanh toán toàn bộ Tour mã #16 thành công', 'payment', 0, '2026-04-12 17:32:57', 1, 'profile.php?tab=tours'),
(19, 1, 'Thông báo Tour mã #15', 'Xác nhận đặt cọc tour mã #15 thành công', 'payment', 0, '2026-04-12 17:36:44', 1, 'profile.php?tab=tours'),
(20, 1, 'Báo cáo thanh toán đủ #15', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 17:37:12', 1, 'admin/bookings.php'),
(21, 1, 'Báo cáo thanh toán đủ #14', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 17:37:32', 1, 'admin/bookings.php'),
(22, 1, 'Đơn hàng mới #17', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:39:07', 1, 'admin/bookings.php'),
(23, 1, 'Thông báo Tour mã #17', 'Xác nhận đặt cọc tour mã #17 thành công', 'payment', 0, '2026-04-12 17:39:40', 1, 'profile.php?tab=tours'),
(24, 1, 'Báo cáo thanh toán đủ #17', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 17:40:04', 1, 'admin/bookings.php'),
(25, 1, 'Đơn hàng mới #18', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:41:34', 1, 'admin/bookings.php'),
(26, 1, 'Đơn hàng mới #19', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:44:15', 1, 'admin/bookings.php'),
(27, 1, 'Thông báo Tour mã #19', 'Xác nhận đặt cọc tour mã #19 thành công', 'payment', 0, '2026-04-12 17:44:55', 1, 'profile.php?tab=tours'),
(28, 1, 'Báo cáo thanh toán đủ #19', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 17:45:09', 1, 'admin/bookings.php'),
(29, 1, 'Đơn hàng mới #20', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:45:58', 1, 'admin/bookings.php'),
(30, 1, 'Thông báo Tour mã #20', 'Xác nhận đặt cọc tour mã #20 thành công', 'payment', 0, '2026-04-12 17:50:54', 1, 'profile.php?tab=tours'),
(31, 1, 'Báo cáo thanh toán đủ #20', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 17:51:11', 1, 'admin/bookings.php'),
(32, 1, 'Thông báo Tour mã #18', 'Xác nhận đặt cọc tour mã #18 thành công', 'payment', 0, '2026-04-12 17:53:19', 1, 'profile.php?tab=tours'),
(33, 1, 'Báo cáo thanh toán đủ #18', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 17:53:31', 1, 'admin/bookings.php'),
(34, 1, 'Đơn hàng mới #21', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 17:57:22', 1, 'admin/bookings.php'),
(35, 1, 'Thông báo Tour mã #21', 'Xác nhận đặt cọc tour mã #21 thành công', 'payment', 0, '2026-04-12 17:57:34', 1, 'profile.php?tab=tours'),
(36, 1, 'Báo cáo thanh toán đủ #21', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 17:57:56', 1, 'admin/bookings.php'),
(37, 1, 'Đơn hàng mới #22', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 18:03:02', 1, 'admin/bookings.php'),
(38, 1, 'Thông báo Tour mã #22', 'Xác nhận đặt cọc tour mã #22 thành công', 'payment', 0, '2026-04-12 18:03:12', 1, 'profile.php?tab=tours'),
(39, 1, 'Báo cáo thanh toán đủ #22', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:03:24', 1, 'admin/bookings.php'),
(40, 1, 'Đơn hàng mới #23', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 18:07:36', 1, 'admin/bookings.php'),
(41, 1, 'Đơn hàng mới #24', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 18:07:52', 1, 'admin/bookings.php'),
(42, 1, 'Thông báo Tour mã #24', 'Xác nhận đặt cọc tour mã #24 thành công', 'payment', 0, '2026-04-12 18:07:59', 1, 'profile.php?tab=tours'),
(43, 1, 'Báo cáo thanh toán đủ #24', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:08:23', 1, 'admin/bookings.php'),
(44, 1, 'Đơn hàng mới #25', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 18:08:45', 1, 'admin/bookings.php'),
(45, 1, 'Thông báo Tour mã #25', 'Xác nhận đặt cọc tour mã #25 thành công', 'payment', 0, '2026-04-12 18:09:06', 1, 'profile.php?tab=tours'),
(46, 1, 'Thông báo Tour mã #25', 'Xác nhận bạn đã thanh toán toàn bộ Tour mã #25 thành công', 'payment', 0, '2026-04-12 18:09:12', 1, 'profile.php?tab=tours'),
(47, 1, 'Đơn hàng mới #26', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 18:11:58', 1, 'admin/bookings.php'),
(48, 1, 'Thông báo Tour mã #26', 'Xác nhận đặt cọc tour mã #26 thành công', 'payment', 0, '2026-04-12 18:12:04', 1, 'profile.php?tab=tours'),
(49, 1, 'Báo cáo thanh toán đủ #26', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:12:30', 1, 'admin/bookings.php'),
(50, 1, 'Thông báo Tour mã #23', 'Xác nhận đặt cọc tour mã #23 thành công', 'payment', 0, '2026-04-12 18:13:57', 1, 'profile.php?tab=tours'),
(51, 1, 'Báo cáo thanh toán đủ #23', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:14:10', 1, 'admin/bookings.php'),
(52, 1, 'Đơn hàng mới #27', 'Khách hàng NGÔ VĂN NHỚ đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 18:19:11', 1, 'admin/bookings.php'),
(53, 1, 'Thông báo Tour mã #27', 'Xác nhận đặt cọc tour mã #27 thành công', 'payment', 0, '2026-04-12 18:19:21', 1, 'profile.php?tab=tours'),
(54, 1, 'Báo cáo thanh toán đủ #27', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:19:33', 1, 'admin/bookings.php'),
(55, 1, 'Đơn hàng mới #28', 'Khách hàng Phạm Trần Hoài Ly đã báo thanh toán cọc.', 'payment', 0, '2026-04-12 18:20:14', 1, 'admin/bookings.php'),
(56, 11, 'Thông báo Tour mã #28', 'Xác nhận đặt cọc tour mã #28 thành công', 'payment', 0, '2026-04-12 18:20:26', 1, 'profile.php?tab=tours'),
(57, 1, 'Báo cáo thanh toán đủ #28', 'Khách hàng Phạm Trần Hoài Ly đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:20:44', 1, 'admin/bookings.php'),
(58, 1, 'Đơn hàng mới #29', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:26:03', 1, 'admin/bookings.php'),
(59, 1, 'Cập nhật đơn hàng #29', 'Admin đã xác nhận cọc 30% cho tour #29. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:26:10', 1, 'profile.php?tab=tours'),
(60, 1, 'Báo cáo thanh toán đủ #29', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:26:21', 1, 'admin/bookings.php'),
(61, 1, 'Đơn hàng mới #30', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:32:09', 1, 'admin/bookings.php'),
(62, 1, 'Cập nhật đơn hàng #30', 'Admin đã xác nhận cọc 30% cho tour #30. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:32:44', 1, 'profile.php?tab=tours'),
(63, 1, 'Báo cáo thanh toán đủ #30', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:33:01', 1, 'admin/bookings.php'),
(64, 1, 'Đơn hàng mới #31', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:33:44', 1, 'admin/bookings.php'),
(65, 1, 'Cập nhật đơn hàng #31', 'Admin đã xác nhận cọc 30% cho tour #31. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:33:52', 1, 'profile.php?tab=tours'),
(66, 1, 'Báo cáo thanh toán đủ #31', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:34:06', 1, 'admin/bookings.php'),
(67, 1, 'Đơn hàng mới #32', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:36:06', 1, 'admin/bookings.php'),
(68, 1, 'Cập nhật đơn hàng #32', 'Admin đã xác nhận cọc 30% cho tour #32. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:37:02', 1, 'profile.php?tab=tours'),
(69, 1, 'Báo cáo thanh toán đủ #32', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:37:16', 1, 'admin/bookings.php'),
(70, 1, 'Đơn hàng mới #33', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:41:59', 1, 'admin/bookings.php'),
(71, 1, 'Cập nhật đơn hàng #33', 'Admin đã xác nhận cọc 30% cho tour #33. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:42:12', 1, 'profile.php?tab=tours'),
(72, 1, 'Báo cáo thanh toán đủ #33', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:42:27', 1, 'admin/bookings.php'),
(73, 1, 'Đơn hàng mới #34', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:43:42', 1, 'admin/bookings.php'),
(74, 1, 'Cập nhật đơn hàng #34', 'Admin đã xác nhận cọc 30% cho tour #34. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:43:47', 1, 'profile.php?tab=tours'),
(75, 1, 'Đơn hàng mới #35', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:46:41', 1, 'admin/bookings.php'),
(76, 1, 'Cập nhật đơn hàng #35', 'Admin đã xác nhận cọc 30% cho tour #35. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:46:46', 1, 'profile.php?tab=tours'),
(77, 1, 'Báo cáo thanh toán đủ #34', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:47:11', 1, 'admin/bookings.php'),
(78, 1, 'Cập nhật đơn hàng #35', 'Lily Travel đã nhận đủ 100% tiền tour #35. Chúc bạn có chuyến đi vui vẻ! Bạn có thể tiếp tục đặt các tour khác.', 'payment', 1, '2026-04-12 18:47:21', 1, 'profile.php?tab=tours'),
(79, 1, 'Đơn hàng mới #36', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:48:18', 1, 'admin/bookings.php'),
(80, 1, 'Cập nhật đơn hàng #36', 'Admin đã xác nhận cọc 30% cho tour #36. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:48:33', 1, 'profile.php?tab=tours'),
(81, 1, 'Báo cáo thanh toán đủ #36', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:52:21', 1, 'admin/bookings.php'),
(82, 1, 'Đơn hàng mới #37', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:52:46', 1, 'admin/bookings.php'),
(83, 1, 'Cập nhật đơn hàng #37', 'Admin đã xác nhận cọc 30% cho tour #37. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:53:03', 1, 'profile.php?tab=tours'),
(84, 1, 'Báo cáo thanh toán đủ #37', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:53:19', 1, 'admin/bookings.php'),
(85, 1, 'Đơn hàng mới #38', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:56:45', 1, 'admin/bookings.php'),
(86, 1, 'Cập nhật đơn hàng #38', 'Admin đã xác nhận cọc 30% cho tour #38. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:56:52', 1, 'profile.php?tab=tours'),
(87, 1, 'Báo cáo thanh toán đủ #38', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 18:57:02', 1, 'admin/bookings.php'),
(88, 1, 'Đơn hàng mới #39', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 18:59:15', 1, 'admin/bookings.php'),
(89, 1, 'Cập nhật đơn hàng #39', 'Admin đã xác nhận cọc 30% cho tour #39. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 18:59:21', 1, 'profile.php?tab=tours'),
(90, 1, 'Báo cáo thanh toán đủ #39', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 19:00:02', 1, 'admin/bookings.php'),
(91, 1, 'Đơn hàng mới #40', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:03:04', 1, 'admin/bookings.php'),
(92, 1, 'Cập nhật đơn hàng #40', 'Admin đã xác nhận cọc 30% cho tour #40. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:03:11', 1, 'profile.php?tab=tours'),
(93, 1, 'Báo cáo thanh toán đủ #40', 'Khách hàng NGÔ VĂN NHỚ đã báo chuyển nốt 70% tiền tour.', 'payment', 0, '2026-04-12 19:03:19', 1, 'admin/bookings.php'),
(94, 1, 'Đơn hàng mới #41', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:05:34', 1, 'admin/bookings.php'),
(95, 1, 'Cập nhật đơn hàng #41', 'Admin đã xác nhận cọc 30% cho tour #41. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:05:38', 1, 'profile.php?tab=tours'),
(96, 1, 'Đơn hàng mới #42', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:06:35', 1, 'admin/bookings.php'),
(97, 1, 'Cập nhật đơn hàng #42', 'Admin đã xác nhận cọc 30% cho tour #42. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:06:39', 1, 'profile.php?tab=tours'),
(98, 1, 'Đơn hàng mới #43', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:19:43', 1, 'admin/bookings.php'),
(99, 1, 'Cập nhật đơn hàng #43', 'Admin đã xác nhận cọc 30% cho tour #43. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:19:51', 1, 'profile.php?tab=tours'),
(100, 1, 'Đơn hàng mới #44', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:25:51', 1, 'admin/bookings.php'),
(101, 1, 'Cập nhật đơn hàng #44', 'Admin đã xác nhận cọc 30% cho tour #44. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:25:55', 1, 'profile.php?tab=tours'),
(102, 1, 'Đơn hàng mới #45', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:30:39', 1, 'admin/bookings.php'),
(103, 1, 'Cập nhật đơn hàng #45', 'Admin đã xác nhận cọc 30% cho tour #45. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:30:51', 1, 'profile.php?tab=tours'),
(104, 1, 'Đơn hàng mới #46', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:36:44', 1, 'admin/bookings.php'),
(105, 1, 'Cập nhật đơn hàng #46', 'Admin đã xác nhận cọc 30% cho tour #46. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:36:49', 1, 'profile.php?tab=tours'),
(106, 1, 'Đơn hàng mới #47', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:40:06', 1, 'admin/bookings.php'),
(107, 1, 'Cập nhật đơn hàng #47', 'Admin đã xác nhận cọc 30% cho tour #47. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:40:14', 1, 'profile.php?tab=tours'),
(108, 1, 'Đơn hàng mới #48', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:41:11', 1, 'admin/bookings.php'),
(109, 1, 'Cập nhật đơn hàng #48', 'Admin đã xác nhận cọc 30% cho tour #48. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:41:16', 1, 'profile.php?tab=tours'),
(110, 1, 'Cập nhật đơn hàng #48', 'Đơn hàng #48 của bạn đã bị hủy.', 'payment', 0, '2026-04-12 19:41:30', 1, 'profile.php?tab=tours'),
(111, 1, 'Đơn hàng mới #49', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:42:13', 1, 'admin/bookings.php'),
(112, 1, 'Cập nhật đơn hàng #49', 'Admin đã xác nhận cọc 30% cho tour #49. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:42:19', 1, 'profile.php?tab=tours'),
(113, 1, 'Đơn hàng mới #50', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:51:05', 1, 'admin/bookings.php'),
(114, 1, 'Cập nhật đơn hàng #50', 'Admin đã xác nhận cọc 30% cho tour #50. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:51:12', 1, 'profile.php?tab=tours'),
(115, 1, 'Đơn hàng mới #51', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:52:41', 1, 'admin/bookings.php'),
(116, 1, 'Cập nhật đơn hàng #51', 'Admin đã xác nhận cọc 30% cho tour #51. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 19:52:46', 1, 'profile.php?tab=tours'),
(117, 1, 'Đơn hàng mới #52', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 19:59:58', 1, 'admin/bookings.php'),
(118, 1, 'Cập nhật đơn hàng #52', 'Admin đã xác nhận cọc 30% cho tour #52. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 20:00:06', 1, 'profile.php?tab=tours'),
(119, 1, 'Đơn hàng mới #53', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 20:02:44', 1, 'admin/bookings.php'),
(120, 1, 'Cập nhật đơn hàng #53', 'Admin đã xác nhận cọc 30% cho tour #53. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 20:02:50', 1, 'profile.php?tab=tours'),
(121, 1, 'Đơn hàng mới #54', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 20:05:00', 1, 'admin/bookings.php'),
(122, 1, 'Cập nhật đơn hàng #54', 'Admin đã xác nhận cọc 30% cho tour #54. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 20:05:08', 1, 'profile.php?tab=tours'),
(123, 1, 'Đơn hàng mới #55', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 20:06:24', 1, 'admin/bookings.php'),
(124, 1, 'Cập nhật đơn hàng #55', 'Admin đã xác nhận cọc 30% cho tour #55. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 20:06:32', 1, 'profile.php?tab=tours'),
(125, 1, 'Đơn hàng mới #56', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 20:09:00', 1, 'admin/bookings.php'),
(126, 1, 'Cập nhật đơn hàng #56', 'Admin đã xác nhận cọc 30% cho tour #56. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 20:09:07', 1, 'profile.php?tab=tours'),
(127, 1, 'Cập nhật đơn hàng #56', 'Lily Travel đã nhận đủ 100% tiền tour #56. Chúc bạn có chuyến đi vui vẻ! Bạn có thể tiếp tục đặt các tour khác.', 'payment', 0, '2026-04-12 20:09:14', 1, 'profile.php?tab=tours'),
(128, 1, 'Đơn hàng mới #57', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 20:09:35', 1, 'admin/bookings.php'),
(129, 1, 'Cập nhật đơn hàng #57', 'Admin đã xác nhận cọc 30% cho tour #57. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 20:09:54', 1, 'profile.php?tab=tours'),
(130, 1, 'Đơn hàng mới #58', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-12 20:13:07', 1, 'admin/bookings.php'),
(131, 1, 'Cập nhật đơn hàng #58', 'Admin đã xác nhận cọc 30% cho tour #58. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-12 20:13:13', 1, 'profile.php?tab=tours'),
(132, NULL, 'Tour mới cực hot!', 'Hành trình \'Đà nẵng -  Quy Nhơn\' vừa được mở bán. Khám phá ngay!', 'tour', 0, '2026-04-13 02:48:26', 1, 'tour-detail.php?id=8'),
(133, 1, 'Nhận mã thành công', 'Mã giảm giá đã được lưu vào ví của bạn.', 'promo', 0, '2026-04-13 02:51:12', 1, 'profile.php?tab=promos'),
(134, 1, 'Nhận mã thành công', 'Mã giảm giá đã được lưu vào ví của bạn.', 'promo', 0, '2026-04-13 02:51:23', 1, 'profile.php?tab=promos'),
(135, 1, 'Đơn hàng mới #59', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-13 02:53:09', 1, 'admin/bookings.php'),
(136, 1, 'Cập nhật đơn hàng #59', 'Admin đã xác nhận cọc 30% cho tour #59. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-13 02:53:31', 1, 'profile.php?tab=tours'),
(137, 1, 'Cập nhật đơn hàng #59', 'Lily Travel đã nhận đủ 100% tiền tour #59. Chúc bạn có chuyến đi vui vẻ! Bạn có thể tiếp tục đặt các tour khác.', 'payment', 0, '2026-04-13 02:54:33', 1, 'profile.php?tab=tours'),
(138, 1, 'Đơn hàng mới #60', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-14 14:30:16', 1, 'admin/bookings.php'),
(139, 1, 'Cập nhật đơn hàng #60', 'Admin đã xác nhận cọc 30% cho tour #60. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-14 14:30:28', 1, 'profile.php?tab=tours'),
(140, 1, 'Cập nhật đơn hàng #60', 'Lily Travel đã nhận đủ 100% tiền tour #60. Chúc bạn có chuyến đi vui vẻ! Bạn có thể tiếp tục đặt các tour khác.', 'payment', 0, '2026-04-14 14:30:32', 1, 'profile.php?tab=tours'),
(141, 1, 'Đơn hàng mới #61', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-14 14:35:25', 1, 'admin/bookings.php'),
(142, 1, 'Cập nhật đơn hàng #61', 'Admin đã xác nhận cọc 30% cho tour #61. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-14 14:35:37', 1, 'profile.php?tab=tours'),
(143, 1, 'Chúc mừng thăng hạng!', 'Bạn đã thăng hạng lên <b>Kim Cương</b> và nhận được mã ưu đãi <b>UUDAIKIMCUONG</b>!', 'promo', 0, '2026-04-14 14:35:57', 1, 'profile.php?tab=promos'),
(144, 1, 'Cập nhật đơn hàng #61', 'Lily Travel đã nhận đủ 100% tiền tour #61. Chúc bạn có chuyến đi vui vẻ! Bạn có thể tiếp tục đặt các tour khác.', 'payment', 0, '2026-04-14 14:35:57', 1, 'profile.php?tab=tours'),
(145, 1, 'Nhận mã thành công', 'Mã giảm giá đã được lưu vào ví của bạn.', 'promo', 0, '2026-04-14 14:45:46', 1, 'profile.php?tab=promos'),
(146, 1, 'Đơn hàng mới #62', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-14 14:46:10', 1, 'admin/bookings.php'),
(147, 1, 'Cập nhật đơn hàng #62', 'Admin đã xác nhận cọc 30% cho tour #62. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-14 14:46:20', 1, 'profile.php?tab=tours'),
(148, 1, 'Cập nhật đơn hàng #62', 'Lily Travel đã nhận đủ 100% tiền tour #62. Chúc bạn có chuyến đi vui vẻ! Bạn có thể tiếp tục đặt các tour khác.', 'payment', 0, '2026-04-14 14:46:23', 1, 'profile.php?tab=tours'),
(149, 1, 'Nhận mã thành công', 'Mã giảm giá đã được lưu vào ví của bạn.', 'promo', 0, '2026-04-14 15:05:30', 1, 'profile.php?tab=promos'),
(150, 1, 'Nhận mã thành công', 'Mã giảm giá đã được lưu vào ví của bạn.', 'promo', 1, '2026-04-14 15:05:33', 1, 'profile.php?tab=promos'),
(151, 1, 'Đơn hàng mới #63', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-14 15:31:43', 1, 'admin/bookings.php'),
(152, 1, 'Cập nhật đơn hàng #63', 'Admin đã xác nhận cọc 30% cho tour #63. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-14 15:32:00', 1, 'profile.php?tab=tours'),
(153, 1, 'Đơn hàng mới #64', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-14 15:36:16', 1, 'admin/bookings.php'),
(154, 1, 'Cập nhật đơn hàng #64', 'Admin đã xác nhận cọc 30% cho tour #64. Vui lòng thanh toán 70% còn lại để hoàn tất.', 'payment', 0, '2026-04-14 15:36:22', 1, 'profile.php?tab=tours'),
(155, 1, 'Đơn hàng mới #65', 'Khách hàng NGÔ VĂN NHỚ vừa đặt tour (Đang đợi bạn duyệt cọc 30%).', 'payment', 0, '2026-04-14 15:39:02', 1, 'admin/bookings.php'),
(156, 1, 'Cập nhật đơn hàng #65', 'Lily Travel đã xác nhận khoản cọc 30% của bạn cho đơn hàng #65. Chỗ của bạn đã được giữ!', 'payment', 0, '2026-04-14 15:39:07', 1, 'profile.php?tab=tours'),
(157, 1, 'Cập nhật đơn hàng #76', 'Tuyệt vời! Chúng tôi đã xác nhận thanh toán 100% cho tour #76. Chuẩn bị hành lý và lên đường thôi!', 'payment', 0, '2026-04-14 16:27:04', 1, 'profile.php?tab=tours'),
(158, 1, 'Cập nhật đơn hàng #77', 'Tuyệt vời! Chúng tôi đã xác nhận thanh toán 100% cho tour #77. Chuẩn bị hành lý và lên đường thôi!', 'payment', 0, '2026-04-14 16:27:13', 1, 'profile.php?tab=tours');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promos`
--

CREATE TABLE `promos` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `percent` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `min_rank_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `promos`
--

INSERT INTO `promos` (`id`, `code`, `percent`, `description`, `expiry_date`, `usage_limit`, `min_rank_id`) VALUES
(1, 'VIP35', 35, 'Ưu đãi đặc biệt cho khách hàng thân thiết', '2026-05-01', NULL, 1),
(2, 'LILY10', 10, 'Giảm giá tri ân khách hàng cũ', '2026-04-01', NULL, 1),
(3, 'KHVIP', 20, 'Khách hàng quay lại', '2026-04-12', NULL, 1),
(4, 'YEUTHUONG', 5, 'Ưu đãi hấp dẫn – đặt tour ngay hôm nay!', '2026-04-17', NULL, 1),
(5, '54176F1F', 10, 'Quà tặng hoàn thành tour #10', '2026-07-12', 1, 1),
(6, 'VNHO', 50, 'Dành cho bạn', '2026-05-09', 1, 1),
(7, 'SUMMERTRIP', 12, 'Số lượng có hạn – nhanh tay đặt ngay!”', '2026-04-30', NULL, 1),
(8, 'SPRINGTOUR', 15, 'Khám phá Đà Nẵng với ưu đãi cực hấp dẫn.', '2026-04-30', NULL, 1),
(9, 'CA59E07A', 10, 'Quà tặng hoàn thành tour #12', '2026-07-12', 1, 1),
(10, 'DB1A51B5', 10, 'Quà tặng hoàn thành tour #11', '2026-07-12', 1, 1),
(11, 'BF78E8EA', 10, 'Quà tặng hoàn thành tour #13', '2026-07-12', 1, 1),
(12, '2809D63A', 10, 'Quà tặng hoàn thành tour #16', '2026-07-12', 1, 1),
(13, 'E1F6E304', 10, 'Quà tặng hoàn thành tour #25', '2026-07-12', 1, 1),
(14, '229EF3B3', 10, 'Quà tặng hoàn thành tour #35', '2026-07-12', 1, 1),
(15, 'B4E49FC7', 10, 'Quà tặng hoàn thành tour #56', '2026-07-12', 1, 1),
(16, 'MUAHE', 12, 'Ưu đãi lớn', '2026-04-17', 1, 1),
(17, '72AE3691', 10, 'Quà tặng hoàn thành tour #59', '2026-07-13', 1, 1),
(18, 'A5436A08', 10, 'Quà tặng hoàn thành tour #60', '2026-07-14', 1, 1),
(19, 'UUDAIDONG', 5, 'Ưu đãi dành cho thành viên hạng Đồng khi thăng hạng', NULL, 1, 1),
(20, 'UUDAIBAC', 10, 'Ưu đãi dành cho thành viên hạng Bạc khi thăng hạng', NULL, 1, 2),
(21, 'UUDAIVANG', 15, 'Ưu đãi dành cho thành viên hạng Vàng khi thăng hạng', NULL, 1, 3),
(22, 'UUDAIKIMCUONG', 20, 'Ưu đãi dành cho thành viên hạng Kim Cương khi thăng hạng', NULL, 1, 4),
(23, '5C3C2968', 10, 'Quà tặng hoàn thành tour #61', '2026-07-14', 1, 1),
(24, '63EDEDA3', 10, 'Quà tặng hoàn thành tour #62', '2026-07-14', 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ranks`
--

CREATE TABLE `ranks` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `min_points` int(11) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `rank_up_promo_code` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ranks`
--

INSERT INTO `ranks` (`id`, `name`, `min_points`, `icon`, `color`, `rank_up_promo_code`) VALUES
(1, 'Đồng', 0, 'fa-medal', 'text-amber-700', 'UUDAIDONG'),
(2, 'Bạc', 100, 'fa-award', 'text-slate-400', 'UUDAIBAC'),
(3, 'Vàng', 200, 'fa-crown', 'text-yellow-500', 'UUDAIVANG'),
(4, 'Kim Cương', 300, 'fa-gem', 'text-blue-400', 'UUDAIKIMCUONG');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `tour_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `booking_id`, `tour_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 8, 6, 1, 5, 'tốt', '2026-04-12 14:51:30'),
(2, 9, 5, 1, 4, 'nắng', '2026-04-12 14:55:49'),
(3, 35, 6, 1, 5, 'Hấp dẫn', '2026-04-12 18:47:56'),
(4, 59, 8, 1, 3, 'Chưa hài lòng', '2026-04-13 02:55:48'),
(5, 62, 4, 1, 5, 'đẹp', '2026-04-14 15:06:25'),
(6, 61, 8, 1, 5, 'lý tưởng', '2026-04-14 15:06:33'),
(7, 60, 8, 1, 5, 'đáng sống', '2026-04-14 15:06:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price_base` decimal(15,2) NOT NULL,
  `price_child` decimal(15,2) DEFAULT 0.00,
  `price_infant` decimal(15,2) DEFAULT 0.00,
  `departure_dates` text DEFAULT NULL,
  `max_people` int(11) DEFAULT 0,
  `departure_location` varchar(255) DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `max_participants` int(11) DEFAULT 20,
  `current_participants` int(11) DEFAULT 0,
  `duration` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount_code` varchar(50) DEFAULT NULL,
  `discount_percent` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tours`
--

INSERT INTO `tours` (`id`, `category_id`, `title`, `slug`, `description`, `content`, `image`, `price_base`, `price_child`, `price_infant`, `departure_dates`, `max_people`, `departure_location`, `departure_date`, `max_participants`, `current_participants`, `duration`, `status`, `created_at`, `discount_code`, `discount_percent`) VALUES
(1, 1, 'Tour Phú Quốc', 'tour-ph-qu-c', 'Tour nghỉ dưỡng tuyệt vời tại đảo ngọc', '<p><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770530152_Hinh-2.jpg\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n', '1770530162_1499411915_1494654356_trong nước.jpg', 2000000.00, 500000.00, 200000.00, '14/06,28/06', 30, 'Phú Quốc', NULL, 20, 0, '3 ngày 2 đêm', 1, '2026-02-08 05:14:30', NULL, 0),
(2, 1, 'Du lịch phú quốc ', 'du-l-ch-ph-qu-c', 'Du lịch phú quốc 4 ngày 3 đêm hấp dẫn', '<p>Đ&acirc;y l&agrave; nội dung test</p>\r\n\r\n<p><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770530435_311791127_540022854795866_1532164650908642042_n.jpeg\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" />Ngon lu&ocirc;n</p>\r\n', '1770530449_311791127_540022854795866_1532164650908642042_n.jpeg', 10000000.00, 0.00, 0.00, NULL, 0, 'TP. Hồ Chí Minh', NULL, 20, 0, '4 ngày 3 đêm', 1, '2026-02-08 06:00:49', NULL, 0),
(3, 5, 'Tour Ninh Bình', 'tour-ninh-b-nh', '🌸𝐂𝐡𝐢𝐞̂́𝐜 𝐝𝐞𝐚𝐥 𝐧𝐚̀𝐲 𝐝𝐚̀𝐧𝐡 𝐜𝐡𝐨 𝐭𝐢́𝐧 đ𝐨̂̀ 𝐦𝐞̂ 𝐬𝐨̂́𝐧𝐠 𝐚̉𝐨', '<p><strong><img alt=\"🌸\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tc9/1/20/1f338.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" />𝐂𝐡𝐢𝐞̂́𝐜 𝐝𝐞𝐚𝐥 𝐧𝐚̀𝐲 𝐝𝐚̀𝐧𝐡 𝐜𝐡𝐨 𝐭𝐢́𝐧 đ𝐨̂̀ 𝐦𝐞̂ 𝐬𝐨̂́𝐧𝐠 𝐚̉𝐨</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770530684_ivivu-kdl-trang-an.gif\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Hiện tại Tr&agrave;ng An Travel đang c&oacute; đầy đủ lịch khởi h&agrave;nh từ Ch&acirc;u &Acirc;u - Ch&acirc;u &Aacute; - Ch&acirc;u Phi với dịch vụ chất lượng,&nbsp;<img alt=\"🌹\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t71/1/16/1f339.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /><img alt=\"🦕\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tad/1/16/1f995.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n\r\n<ul>\r\n	<li>\r\n	<p><strong>Ch&acirc;u &Acirc;u</strong>: Bắc &Acirc;u, Đ&ocirc;ng T&acirc;y, T&acirc;y &Acirc;u...</p>\r\n	</li>\r\n</ul>\r\n\r\n<ul>\r\n	<li>\r\n	<p><strong>Ch&acirc;u &Aacute;: Th&aacute;i, Singapore, H&agrave;n Quốc, Nhật Bản...</strong></p>\r\n	</li>\r\n</ul>\r\n\r\n<ul>\r\n	<li>\r\n	<p><strong>Ch&acirc;u Phi: Nam Phi...</strong></p>\r\n	</li>\r\n</ul>\r\n\r\n<p><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770530751_r-40--5.jpg\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n', '1770530786_du_lich_trang_an_ninh_binh_du_lich_thu_duc_travel.jpg', 1200000.00, 0.00, 0.00, '', 0, 'Thanh Hóa', NULL, 20, 0, 'Trong ngày', 1, '2026-02-08 06:06:26', NULL, 0),
(4, 1, 'Tour Đà Lạt', 'tour-l-t', 'Du lịch đà lạt tận hưởng không khí xuân', '<p>TOUR DU LỊCH Đ&Agrave; LẠT - CHU Đ&Aacute;O TẬN T&Igrave;NH</p>\r\n\r\n<p><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770531040_Hình-Nền-ĐL-Hè.jpg\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n\r\n<p>- Xe tham quan c&aacute;c điểm Đ&agrave; Lạt</p>\r\n\r\n<p>- Xe săn m&acirc;y đ&oacute;n b&igrave;nh minh</p>\r\n\r\n<p>- Xe đưa đ&oacute;n s&acirc;n bay Li&ecirc;n Khương - Đ&agrave; Lạt</p>\r\n\r\n<p>- Xe đưa đ&oacute;n li&ecirc;n tỉnh</p>\r\n\r\n<p><img alt=\"📌\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tac/1/16/1f4cc.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Xe 4 chỗ đến 45 chỗ sạch sẽ , đời mới</p>\r\n\r\n<p><img alt=\"📲\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/td8/1/16/1f4f2.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Li&ecirc;n hệ/ đặt xe : 0867.717.997</p>\r\n\r\n<p>&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;-</p>\r\n\r\n<p><img alt=\"✈️\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tb6/1/16/2708.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Đ&Oacute;N S&Acirc;N BAY LI&Ecirc;N KHƯƠNG - Đ&Agrave; LẠT CHỈ TỪ 220K</p>\r\n\r\n<p><img alt=\"🚗\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tec/1/16/1f697.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Xe ri&ecirc;ng &ndash; Kh&ocirc;ng gh&eacute;p kh&aacute;ch &ndash; Đ&oacute;n đ&uacute;ng giờ &ndash; Kh&ocirc;ng phụ ph&iacute;!</p>\r\n\r\n<p><img alt=\"✅\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Bảng gi&aacute; xe s&acirc;n bay (1 chiều):</p>\r\n\r\n<p>&bull; 4 chỗ: 220.000đ</p>\r\n\r\n<p>&bull; 7 chỗ: 250.000đ</p>\r\n\r\n<p>&bull; 16 chỗ: 600.000đ</p>\r\n\r\n<p><img alt=\"🗺\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tc8/1/16/1f5fa.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Tour tham quan Đ&agrave; Lạt 8 tiếng (trọn g&oacute;i):</p>\r\n\r\n<p>&bull; 4 chỗ: 900.000đ</p>\r\n\r\n<p>&bull; 7 chỗ: 1.000.000đ</p>\r\n\r\n<p>&bull; 16 chỗ: 1.500.000đ</p>\r\n\r\n<p><img alt=\"🕓\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t2b/1/16/1f553.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Tour săn m&acirc;y: Khởi h&agrave;nh 4h s&aacute;ng &ndash; kết th&uacute;c 8h30</p>\r\n\r\n<p><img alt=\"🚘\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t6d/1/16/1f698.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Xe ri&ecirc;ng:</p>\r\n\r\n<p>&bull; 4 chỗ: 600.000đ</p>\r\n\r\n<p>&bull; 7 chỗ: 700.000đ</p>\r\n\r\n<p>&bull; 16 chỗ: 1.000.000đ</p>\r\n\r\n<p>⸻</p>\r\n\r\n<p><img alt=\"✅\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> CAM KẾT DỊCH VỤ:</p>\r\n\r\n<p><img alt=\"💯\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tf1/1/16/1f4af.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Kh&ocirc;ng ph&aacute;t sinh chi ph&iacute; &ndash; Đ&atilde; bao gồm bến b&atilde;i, v&eacute; cầu đường</p>\r\n\r\n<p><img alt=\"🎁\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t84/1/16/1f381.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Tặng tư vấn lịch tr&igrave;nh &ndash; Thiết kế tour ri&ecirc;ng miễn ph&iacute;</p>\r\n\r\n<p><img alt=\"📸\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tde/1/16/1f4f8.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> T&agrave;i xế ki&ecirc;m HDV, vui vẻ &ndash; hỗ trợ quay phim &amp; chụp ảnh</p>\r\n\r\n<p><img alt=\"🧼\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tce/1/16/1f9fc.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /> Xe sạch &ndash; đời mới &ndash; chạy &ecirc;m &ndash; phục vụ tận t&igrave;nh</p>\r\n\r\n<p>&nbsp;</p>\r\n', '1775822707_da-lat.jpeg', 15000000.00, 0.00, 0.00, '', 0, 'Đà Lạt', NULL, 20, 0, '5 ngày  5 đêm', 1, '2026-02-08 06:10:50', NULL, 0),
(5, 3, 'Tour gia đình', 'tour-gia-nh', 'Tour du lịch đà nẵng cho gia đình dịp lễ ', '<p>Chương tr&igrave;nh c&oacute; sự đồng h&agrave;nh tham gia của hơn 300 đơn vị, gồm c&aacute;c Tập đo&agrave;n Sun Group, Vingroup, Mikazuki, DHC, Hoiana&hellip;; c&aacute;c bảo t&agrave;ng, di t&iacute;ch, khu điểm du lịch; c&aacute;c h&atilde;ng h&agrave;ng kh&ocirc;ng, cơ sở lưu tr&uacute; du lịch, đơn vị lữ h&agrave;nh, cơ sở ăn uống, mua sắm, hơn 200 cơ sở kinh doanh dịch vụ du lịch tr&ecirc;n địa b&agrave;n th&agrave;nh phố; c&aacute;c đơn vị cung ứng giải ph&aacute;p c&ocirc;ng nghệ VNPAY, TripC AI.</p>\r\n\r\n<p><img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770531348_Tour-du-lich-Da-Nang-_-Du-lich-Lion-Trip.png\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n', '1770531356_DA-NANG-CU-LAO-CHAM-2261148856800.png', 2000000.00, 1000000.00, 500000.00, '28/04,01/05', 15, 'Đà nẵng', NULL, 20, 0, '5 ngày 4 đêm', 1, '2026-02-08 06:15:56', NULL, 0),
(6, 8, 'Tour Đà Nẵng', 'tour-n-ng', 'Du lịch đà nẵng cho cặp đôi 2 ngày 1 đêm', '<p>Du lịch đ&agrave; nẵng cho cặp đ&ocirc;i 2 ng&agrave;y 1 đ&ecirc;m<img alt=\"\" src=\"http://localhost/travel_booking/assets/uploads/1770531458_chuong-trinh-kich-cau-thu-hut-khach-du-lich-den-thanh-pho-da-nang-cac-thang-cuoi-nam-2025-de-da-nang-moi-trai-nghiem-moi-new-da-nang-new-experience-01-1024x576.jpg\" style=\"border-radius:1rem; display:block; height:auto; margin:10px auto; max-width:100%\" /></p>\r\n', '1770531463_chuong-trinh-kich-cau-thu-hut-khach-du-lich-den-thanh-pho-da-nang-cac-thang-cuoi-nam-2025-de-da-nang-moi-trai-nghiem-moi-new-da-nang-new-experience-01-1024x576.jpg', 1000000.00, 500000.00, 250000.00, '12/04,20/04', 10, 'Đà Nẵng', NULL, 20, 0, '2 ngày 1 đêm', 1, '2026-02-08 06:17:43', NULL, 0),
(7, 7, 'Tour Hội An', 'tour-h-i-an', 'Hội An đẹp', '', '1776013589_phoco.jpg', 800000.00, 400000.00, 200000.00, '14/06,28/06', 19, 'Hội An', NULL, 20, 0, '2 ngày 1 đêm', 1, '2026-04-12 17:05:59', 'HOIAN2026', 10),
(8, 8, 'Đà nẵng -  Quy Nhơn', 'n-ng---quy-nh-n', 'Tour giá rẻ', '<p>...</p>\r\n', '1776048506_Tour.webp', 500000.00, 200000.00, 300000.00, '17/05,19/05', 10, 'Đà Nẵng', NULL, 20, 0, '2 ngày 1 đêm', 1, '2026-04-13 02:48:26', 'DANA', 15);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tour_images`
--

CREATE TABLE `tour_images` (
  `id` int(11) NOT NULL,
  `tour_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tour_images`
--

INSERT INTO `tour_images` (`id`, `tour_id`, `image_path`, `created_at`) VALUES
(1, 5, '1776179515_ex_1770531348_Tour-du-lich-Da-Nang-_-Du-lich-Lion-Trip.png', '2026-04-14 15:11:55'),
(2, 5, '1776179515_ex_1773297627_phoco.jpg', '2026-04-14 15:11:55'),
(3, 5, '1776179515_ex_1774541332_phaohoa.jpg', '2026-04-14 15:11:55'),
(4, 5, '1776179515_ex_1776013005_1773297876_ngua.webp', '2026-04-14 15:11:55'),
(5, 5, '1776179515_ex_1776013359_phaohoa.jpg', '2026-04-14 15:11:55'),
(6, 5, '1776179515_ex_1776013589_phoco.jpg', '2026-04-14 15:11:55'),
(7, 5, '1776179515_ex_banner.jpg', '2026-04-14 15:11:55'),
(8, 5, '1776179515_ex_banner.png', '2026-04-14 15:11:55'),
(9, 5, '1776179515_ex_caurong.webp', '2026-04-14 15:11:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `loyalty_points` int(11) DEFAULT 0,
  `rank_id` int(11) DEFAULT 1,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `phone`, `role`, `created_at`, `loyalty_points`, `rank_id`, `avatar`) VALUES
(1, 'admin', '$2y$10$ip5mm.m1JwP40ZKAzT3.HOvGu.35Ph5Xj/gfHQsyxHkRumZYlWWIS', 'NGÔ VĂN NHỚ', 'vannho@travel.com', '0777454550', 'admin', '2026-02-08 05:14:30', 1280, 4, '1776180205_avatar_Ngô Văn Nhớ.JPG'),
(7, 'nho', '$2y$10$TBEaJSmZ1.Ez1OX75Cv1B.adjduLueTvvI5eDWoQ/C7LYz9KuHAre', 'nho', 'nho12@gmail.com', NULL, 'user', '2026-04-08 05:26:02', 0, 1, NULL),
(8, 'lily05', '$2y$10$2e3ypUtliBMFqhUxD7Vs1eX36OC1HOypJHj5B3fQZC/UmUoKz8T6q', 'Phạm Trần Hoài Ly', 'lily05@gmail.com', NULL, 'user', '2026-04-10 12:04:00', 0, 1, NULL),
(9, 'nhobeo05', '$2y$10$I9tMjSLWB31QqpvkmRjPeObeNClHXClvEHZ6I.waLr0kBo/52a9Rq', 'Ngô Văn Nhớ', 'vannho14122@gmail.com', NULL, 'user', '2026-04-10 12:53:23', 0, 1, NULL),
(10, 'baokun', '$2y$10$py5oikU71Invp7zIz1z6CuW2W0o5RW.YcX830OrMIeFzbKMeI11fu', 'Đinh Công Bảo', 'baokun96@gmail.com', NULL, 'user', '2026-04-10 13:13:09', 0, 1, NULL),
(11, 'Ly', '$2y$10$pPBi3Im4B45oy7KaU71k6.ZQUVRb.Ml4j3WenVXUZ3IOTTFA4YrVK', 'Phạm Trần Hoài Ly', 'Hoaily@gamil.com', NULL, 'user', '2026-04-12 16:36:19', 0, 1, NULL),
(12, 'nho1', '$2y$10$WpTXyqPzvDLxBXBl5yKZKO1A6K0jlroegbeAsYeOb8dAk7qTzxMwO', 'Ngô Văn Nhớ', 'nho123@gmail.com', NULL, 'user', '2026-04-13 02:42:43', 0, 1, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_promos`
--

CREATE TABLE `user_promos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `promo_id` int(11) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `claimed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `user_promos`
--

INSERT INTO `user_promos` (`id`, `user_id`, `promo_id`, `is_used`, `claimed_at`) VALUES
(1, 1, 1, 1, '2026-04-12 16:27:03'),
(2, 1, 5, 1, '2026-04-12 16:39:19'),
(3, 1, 3, 0, '2026-04-12 16:47:51'),
(4, 1, 4, 1, '2026-04-12 16:52:00'),
(5, 1, 6, 1, '2026-04-12 16:53:09'),
(6, 1, 9, 1, '2026-04-12 17:10:59'),
(7, 1, 10, 0, '2026-04-12 17:16:34'),
(8, 1, 11, 0, '2026-04-12 17:19:45'),
(9, 1, 12, 1, '2026-04-12 17:32:57'),
(10, 1, 13, 1, '2026-04-12 18:09:12'),
(11, 1, 14, 0, '2026-04-12 18:47:21'),
(12, 1, 15, 0, '2026-04-12 20:09:14'),
(13, 1, 16, 0, '2026-04-13 02:51:12'),
(14, 1, 7, 0, '2026-04-13 02:51:23'),
(15, 1, 17, 0, '2026-04-13 02:54:33'),
(16, 1, 18, 0, '2026-04-14 14:30:32'),
(17, 1, 22, 0, '2026-04-14 14:35:57'),
(18, 1, 23, 0, '2026-04-14 14:35:57'),
(19, 1, 20, 0, '2026-04-14 14:45:46'),
(20, 1, 24, 0, '2026-04-14 14:46:23'),
(21, 1, 19, 0, '2026-04-14 15:05:30'),
(22, 1, 21, 0, '2026-04-14 15:05:33');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tour_id` (`tour_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `promos`
--
ALTER TABLE `promos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `tour_images`
--
ALTER TABLE `tour_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tour_id` (`tour_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `user_promos`
--
ALTER TABLE `user_promos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT cho bảng `promos`
--
ALTER TABLE `promos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `ranks`
--
ALTER TABLE `ranks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `tour_images`
--
ALTER TABLE `tour_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `user_promos`
--
ALTER TABLE `user_promos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `tours`
--
ALTER TABLE `tours`
  ADD CONSTRAINT `tours_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `tour_images`
--
ALTER TABLE `tour_images`
  ADD CONSTRAINT `tour_images_ibfk_1` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
