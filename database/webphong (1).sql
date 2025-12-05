-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 05, 2025 at 03:22 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webphong`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `fullname`, `email`, `phone`, `message`, `created_at`, `is_read`) VALUES
(1, 'Lê Văn Đạt', 'levandat10062003@gmail.com', '0393969659', 'abcd', '2025-12-04 21:02:41', 1),
(2, 'a', 'nq2018.levandat100603@gmail.com', '0328121732', 'aaa', '2025-12-04 21:06:33', 1),
(3, 'a', 'testdk3@gmail.com', '1111111111', 'bvc', '2025-12-04 21:31:04', 1),
(4, 'a', 'testdk@gmail.com', '1111111111', 'abc', '2025-12-04 23:28:57', 1),
(5, 'a', 'testdk@gmail.com', '0393969659', 'a', '2025-12-04 23:29:25', 1),
(6, 'Quản trị viên', 'admin@webphong.com', '0393969659', 'aaaa', '2025-12-04 23:29:59', 1),
(7, 'Quản trị viên', 'admin@webphong.com', '0393969659', 'aaaaaa', '2025-12-04 23:30:51', 1),
(8, 'Quản trị viên', 'admin@webphong.com', '242452345', 'fgsdfgsdfg', '2025-12-04 23:31:07', 1),
(9, 'Quản trị viên 2', 'admin2@webphong.com', '0393969659', 'abc', '2025-12-05 01:31:50', 0),
(10, 'Quản trị viên 2', 'admin2@webphong.com', '111111111', 'abccccccccc', '2025-12-05 03:20:30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

DROP TABLE IF EXISTS `room`;
CREATE TABLE IF NOT EXISTS `room` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `district_id` int NOT NULL,
  `district_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_m2` int DEFAULT NULL,
  `price_vnd` int DEFAULT NULL,
  `amenities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `images` text COLLATE utf8mb4_unicode_ci,
  `room_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `pet` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`id`, `title`, `district_id`, `district_name`, `meta`, `area_m2`, `price_vnd`, `amenities`, `desc`, `image`, `status`, `images`, `room_type`, `pet`) VALUES
(5, 'Quận 5', 1, 'Quận 5', 'Quận 5', 32, 5000000, 'wifi', 'Quận 5', 'IMG/1764877663_93585.jpg', 0, '[\"IMG/1764877663_93585.jpg\",\"IMG/1764877663_83621.jpg\",\"IMG/1764877663_64236.jpg\",\"IMG/1764877663_81664.jpg\",\"IMG/1764877663_10269.jpg\",\"IMG/1764877663_11921.jpg\",\"IMG/1764877663_11685.jpg\",\"IMG/1764877663_17848.jpg\",\"IMG/1764877663_20625.jpg\",\"IMG/1764877663_49650.jpg\"]', '', ''),
(1, 'Quận 3', 3, 'Quận 3', 'Quận 3,TPHCM', 22, 3500000, 'Wifi,MayGiat,DieuHoa,FullNoiThat', 'bbbbbbbbbbbbbb', 'IMG/1764872723_7191.jpg', 1, '[\"IMG/1764872723_7191.jpg\",\"IMG/1764872723_2725.jpg\",\"IMG/1764872723_7572.jpg\",\"IMG/1764872723_2531.jpg\",\"IMG/1764872723_8444.jpg\",\"IMG/1764872723_8239.jpg\",\"IMG/1764872723_2443.jpg\",\"IMG/1764872723_4060.jpg\",\"IMG/1764872723_8529.jpg\",\"IMG/1764872723_8974.jpg\"]', 'Duplex', 'NuoiCho'),
(2, 'Quận 4', 4, 'Quận 4', 'Quận 4', 21, 3000000, 'MayGiat', 'bbbbbbbbbb', 'IMG/1764872395_6486.jpg', 1, '[\"IMG/1764872395_6486.jpg\",\"IMG/1764872542_1378.jpg\",\"IMG/1764872542_5556.jpg\",\"IMG/1764872542_1446.jpg\",\"IMG/1764872542_9330.jpg\",\"IMG/1764872542_6642.jpg\",\"IMG/1764872542_3361.jpg\"]', '', 'NuoiMeo'),
(3, 'Quận 7', 7, 'Quận 7', 'Quận 7', 60, 9000000, 'Wifi,MayGiat,DieuHoa,FullNoiThat', 'Quận 7', 'IMG/1764876887_7648.jpg', 1, '[\"IMG/1764876887_7648.jpg\",\"IMG/1764876887_5466.jpg\",\"IMG/1764876887_3020.jpg\",\"IMG/1764876887_4933.jpg\",\"IMG/1764876887_3417.jpg\",\"IMG/1764876887_1796.jpg\",\"IMG/1764876887_3371.jpg\"]', '', ''),
(4, 'Quận 1', 1, 'Quận 1', 'Quận 1', 50, 10000000, '', 'Quận 1', 'IMG/1764897157_16873.jpg', 1, '[\"IMG/1764897157_16873.jpg\",\"IMG/1764897157_47776.jpg\",\"IMG/1764897157_39591.jpg\",\"IMG/1764897157_13654.jpg\",\"IMG/1764897157_31724.jpg\",\"IMG/1764897157_13879.jpg\",\"IMG/1764897157_97861.jpg\",\"IMG/1764897157_69347.jpg\",\"IMG/1764897157_98381.jpg\",\"IMG/1764897157_42416.jpg\",\"IMG/1764897157_67800.jpg\",\"IMG/1764897157_60689.jpg\"]', '', ''),
(6, 'Quận 2', 1, 'Quận 2', 'Quận 2', 26, 4000000, 'Quận 2', 'Quận 2', 'IMG/1764878077_14245.jpg', 0, '[\"IMG/1764878077_14245.jpg\",\"IMG/1764878077_69477.jpg\",\"IMG/1764878077_71944.jpg\",\"IMG/1764878077_78449.jpg\",\"IMG/1764878077_63084.jpg\"]', '', ''),
(7, 'Quận 8', 8, 'Quận 8', 'Quận 8', 33, 2500000, '', 'Quận 8', 'IMG/1764895077_82674.jpg', 1, '[\"IMG/1764895077_82674.jpg\",\"IMG/1764895077_68557.jpg\",\"IMG/1764895077_28254.jpg\",\"IMG/1764895077_69169.jpg\",\"IMG/1764895077_52977.jpg\"]', '', ''),
(8, 'Quận 9', 1, 'Quận 9', 'Quận 9', 44, 8600000, 'Quận 9', 'Quận 9', 'IMG/1764878165_41156.jpg', 1, '[\"IMG/1764878165_41156.jpg\",\"IMG/1764878165_29981.jpg\",\"IMG/1764878165_21663.jpg\",\"IMG/1764878165_91998.jpg\",\"IMG/1764878165_84398.jpg\",\"IMG/1764878165_24147.jpg\",\"IMG/1764878165_92253.jpg\",\"IMG/1764878165_15890.jpg\"]', '', ''),
(9, 'Quận 2', 2, 'Quận 2', 'Quận 2', 34, 6700000, 'Wifi,MayGiat,DieuHoa,FullNoiThat', 'Quận 2', 'IMG/1764879715_12014.jpg', 1, '[\"IMG/1764879715_12014.jpg\"]', '', ''),
(10, 'Quận 2', 2, 'Quận 2', 'Quận 2', 2, 3000000, '', '', 'IMG/1764879729_42754.jpg', 1, '[\"IMG/1764879729_42754.jpg\"]', '', ''),
(11, 'Quận 2', 2, 'Quận 2', 'Quận 2', 33, 5500000, '', '', 'IMG/1764879745_18218.jpg', 1, '[\"IMG/1764879745_18218.jpg\"]', '', ''),
(12, 'Quận 2', 2, 'Quận 2', 'Quận 2', 22, 3000000, '', '', 'IMG/1764880941_41659.jpg', 1, '[\"IMG/1764880941_41659.jpg\"]', '', ''),
(13, 'Quận 2', 2, 'Quận 2', 'Quận 2', 4, 2500000, '', '', 'IMG/1764880968_52450.jpg', 1, '[\"IMG/1764880968_52450.jpg\"]', '', 'NuoiMeo'),
(14, 'Quận 9', 9, 'Quận 9', 'Quận 9', 22, 333333, 'NhaTrong', '', 'IMG/1764885729_73160.jpg', 0, '[\"IMG/1764885729_73160.jpg\"]', 'Duplex', 'NuoiMuonLoaiThu'),
(15, 'Phòng trọ giá rẻ giành cho Sinh Viên TDTU', 7, 'Quận 7', 'Quận 7 , TP.HCM', 30, 4500000, 'Wifi,MayGiat,DieuHoa,FullNoiThat', 'Phòng ngay kế Lotte Mart , 5p di chuyển đến trường TDTU', 'IMG/1764895153_80054.jpg', 1, '[\"IMG/1764895153_80054.jpg\",\"IMG/1764895153_36046.jpg\",\"IMG/1764895153_56748.jpg\",\"IMG/1764895153_78003.jpg\",\"IMG/1764895153_34464.jpg\",\"IMG/1764895153_92634.jpg\",\"IMG/1764895153_50003.jpg\",\"IMG/1764895153_55621.jpg\",\"IMG/1764895153_46981.jpg\"]', 'Duplex', 'NuoiMuonLoaiThu'),
(16, 'Quận 11', 11, 'Quận 11', 'Quận 11', 24, 4000000, 'Wifi,MayGiat,DieuHoa,FullNoiThat', 'Quận 11', 'IMG/1764895393_31867.jpg', 1, '[\"IMG/1764895393_31867.jpg\",\"IMG/1764895393_16468.jpg\",\"IMG/1764895393_12115.jpg\",\"IMG/1764895393_48063.jpg\",\"IMG/1764895393_72045.jpg\",\"IMG/1764895393_90392.jpg\",\"IMG/1764895393_34114.jpg\",\"IMG/1764895393_34074.jpg\"]', 'Studio', 'NuoiMeo');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`, `status`) VALUES
(1, 'testdk', '$2y$10$V0DXGKCAVbyKM6BlpzVh8OJjkMOtLxWDX8ACBedRjAIHwMgLINTXu', '', 'testdk@gmail.com', 'user', 'active'),
(3, 'admin@webphong.com', '$2y$10$WsfWpUArDhYJ6Jjex/O6Zu1leUytaFkwVtsSeNm4RH0xR5oAgh3W2', 'Quản trị viên', 'admin@webphong.com', 'admin', 'active'),
(4, 'test1@gmail.com', '$2y$10$fpY/oPcw0vZbEOWNOK6Kyu8kwxgzdP.DvYvn.hVQ.pvjS4JkrmUti', '', 'test1@gmail.com', 'user', 'active'),
(5, 'testdk3', '$2y$10$XrGPux6LfNdwaEY0YNbrMOtsak2OUBOVZ/WXj.U5rcqdd8aOHeNU6', '', 'testdk3@gmail.com', 'user', 'active'),
(2, 'admin2', '$2y$10$tqAjtVByYqnilcL5Y8le0eSSbA6tEKmtsqhwXPoaqqPoe.K.Z2PyO', 'Quản trị viên 2', 'admin2@webphong.com', 'admin', 'active');
(6, 'newadmin', '$2y$10$abcxyz1234567890abcdefghiABCDEFGHIJKLMNopqrstuv', 'Quản trị viên mới', 'newadmin@webphong.com', 'admin', 'active');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
