-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2026 at 07:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Marguax_Collection`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'Male Scents', 'Perfumes and colognes for men', '2026-03-24 04:21:48'),
(2, 'Female Scents', 'Perfumes and fragrances for women', '2026-03-24 04:21:48'),
(3, 'Health Products', 'Wellness and health supplements', '2026-03-24 04:21:48'),
(4, 'Soaps & Oils', 'Personal care soaps and oils', '2026-03-24 04:21:48'),
(5, 'Packages', 'Membership starter packages', '2026-03-24 04:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT 'General Inquiry',
  `order_id` int(11) DEFAULT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `user_id`, `subject`, `order_id`, `status`, `created_at`, `updated_at`) VALUES
(16, 16, 'General Inquiry', NULL, 'open', '2026-04-23 13:39:35', '2026-04-23 13:52:13');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_type` enum('customer','admin') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `conversation_id`, `sender_type`, `sender_id`, `message`, `is_read`, `created_at`) VALUES
(18, 16, 'customer', 16, 'Hiii sirrr', 1, '2026-04-23 13:39:35'),
(19, 16, 'customer', 16, 'hows my order?', 1, '2026-04-23 13:48:16'),
(20, 16, 'admin', 1, 'DHHayDJH', 1, '2026-04-23 13:52:13');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `order_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(6, 16, 16, '✅ Order Completed!', 'Your Order #16 has been completed. Thank you for shopping with Marguax Collection!', 0, '2026-04-23 13:51:07');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `order_method` enum('pickup','shipping') DEFAULT 'pickup',
  `payment_method` enum('cash_on_pickup','cash_on_delivery','gcash','paymaya') DEFAULT 'cash_on_pickup',
  `payment_account_id` int(11) DEFAULT NULL,
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `order_status` enum('pending','processing','completed') DEFAULT 'pending',
  `queue_number` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `customer_name`, `address`, `contact_number`, `order_method`, `payment_method`, `payment_account_id`, `payment_status`, `order_status`, `queue_number`, `total_amount`, `order_date`) VALUES
(15, 16, 'Vincent Carl Atis', 'Molo', '09482841494', 'shipping', 'cash_on_delivery', NULL, '', 'pending', 101, 750.00, '2026-04-23 05:39:57'),
(16, 16, 'Vincent Carl Atis', 'molo', '09482841494', 'shipping', 'cash_on_delivery', NULL, 'paid', 'completed', 102, 1500.00, '2026-04-23 05:47:45');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(18, 15, 39, 1, 750.00),
(19, 16, 39, 1, 750.00),
(20, 16, 38, 1, 750.00);

-- --------------------------------------------------------

--
-- Table structure for table `otp_tokens`
--

CREATE TABLE `otp_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `identifier` varchar(150) NOT NULL COMMENT 'email address',
  `type` enum('login','register','reset') NOT NULL,
  `token_hash` varchar(64) NOT NULL COMMENT 'SHA-256 of the 6-digit OTP',
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_tokens`
--

INSERT INTO `otp_tokens` (`id`, `identifier`, `type`, `token_hash`, `expires_at`, `used`, `created_at`) VALUES
(10, 'atisvincentcarl1@gmail.com', 'register', '9cbc98161c84ae8ba9128b018bd1aa59dfeeaf3fadce50a8f8f4f3ee031c9d60', '2026-04-08 11:54:58', 1, '2026-04-08 17:44:58'),
(11, 'atisvincentcarl1@gmail.com', 'login', '696ee5696f25582f9f25fe75c02fcb8e3d86610936c16354a06a75635ede998f', '2026-04-08 11:52:08', 1, '2026-04-08 17:47:08'),
(15, 'admin@Marguaxworld.com', 'login', 'ab6b255f111b42cc2a88a2611d2decb8f6e5ee090804b7096396af4646a42587', '2026-04-08 12:25:42', 0, '2026-04-08 18:20:42'),
(16, 'atisvincentcarl1@gmail.com', 'login', 'af19b9ab53c4f09fca41f0f02b6745057b11010e71ff8a13294a76e165913942', '2026-04-08 12:43:34', 1, '2026-04-08 18:38:34'),
(18, 'haha@gmail.com', 'login', 'f29e1174f760d043fcf1f8ada22dd4d072e73893d67566fef9fdf74064432ef1', '2026-04-08 12:52:08', 0, '2026-04-08 18:47:08'),
(19, 'atisvincentcarl1@gmail.com', 'login', '56e4e272fb9c98f3f12398fd00db688d3b3a46c5d35a82c04edd3dd3e2eb9a97', '2026-04-08 12:52:27', 1, '2026-04-08 18:47:27'),
(21, 'rodu.fenandez.ui@phinmaed.com', 'register', 'e6ed1fc2625ed26546626c275679ddbad868d916a84e728c46a8f7ee9a3b255c', '2026-04-08 13:00:35', 1, '2026-04-08 18:50:35'),
(22, 'rodu.fenandez.ui@phinmaed.com', 'login', 'ddfc62addb613e438a0a54868a9e0675065c3a58b0c0c5f059132d6a03275c7d', '2026-04-08 12:57:36', 0, '2026-04-08 18:52:36'),
(23, 'mabo.naral.ui@phinmaed.com', 'register', '646191ff3f98407d93891ef638d21cd2b56aef9eca7e5bd0f068184033661c20', '2026-04-08 13:14:19', 1, '2026-04-08 19:04:19'),
(30, 'atisvincentcarl1@gmail.com', 'login', 'def47bfdac8c3f34ec87333f9caafaa40a2d9ffbd340e2ee69c6728eb20e6dff', '2026-04-23 07:39:49', 1, '2026-04-23 13:34:49'),
(31, 'vinc.atis.ui@phinmaed.com', 'register', '4740c958ff56e120231dc18fbceec7ed7eaef43f2e48b44eee01620f3b0b1449', '2026-04-23 07:48:16', 1, '2026-04-23 13:38:16'),
(32, 'vinc.atis.ui@phinmaed.com', 'login', 'e4d3165fdbd1e7682530eb3ef698a35ea9180e0154aa29fda761df46f98498a7', '2026-04-23 07:43:42', 1, '2026-04-23 13:38:42'),
(33, 'atisvincentcarl1@gmail.com', 'login', '22ab1abf6c1d1347a15efd2c6c4e99b5c3922df0073ab754a76665d1a88fcfbf', '2026-04-23 07:45:14', 1, '2026-04-23 13:40:14'),
(34, 'vinc.atis.ui@phinmaed.com', 'login', '96f5b03ed5094d119020b16f83df36d4b6c3f35633fb9c6841855e47cd9d10d9', '2026-04-23 07:51:20', 1, '2026-04-23 13:46:20'),
(35, 'atisvincentcarl1@gmail.com', 'login', '2f1df3b103edff62e81c3948296a2bb761b424f8fc3b708bec61b7cc6b3bc621', '2026-04-23 07:54:37', 1, '2026-04-23 13:49:37'),
(36, 'vinc.atis.ui@phinmaed.com', '', 'c98f8faa9df5e49dcf4c6b48cf34dae78f43bf698e9405850e540dbdc47782c7', '2026-04-29 09:37:21', 0, '2026-04-29 15:27:21'),
(37, 'vinc.atis.ui@phinmaed.com', '', '1e0d454f967eecf5901f27cdb8cbfe5e66a55b05ccdb278f33b495cff800afe1', '2026-04-29 09:37:56', 0, '2026-04-29 15:27:56'),
(38, 'vinc.atis.ui@phinmaed.com', '', 'c5ff9506c101b58bdcda8ce8ecf9f19d21bb010b876ea3907c82948e6dc20ba4', '2026-04-29 09:52:19', 0, '2026-04-29 15:42:19'),
(39, 'vinc.atis.ui@phinmaed.com', '', '52d6f294b3d8e9e583b1482d57bf80e42e4cce0f81ecdb3af05019d91d828b5b', '2026-04-29 09:52:27', 0, '2026-04-29 15:42:27'),
(40, 'vinc.atis.ui@phinmaed.com', '', '1dcb3ad0cc427da1749043faaa37a52fde1d83faf2b8eba5464c45be0cbdd4bf', '2026-04-29 09:53:03', 0, '2026-04-29 15:43:03'),
(41, 'vinc.atis.ui@phinmaed.com', '', '884e4179e7a5b6dad70a4b85c724a18f84985d044f3592e9e3abfb13a1553812', '2026-04-29 09:53:25', 0, '2026-04-29 15:43:25'),
(42, 'vinc.atis.ui@phinmaed.com', '', '63de62e84726a193c246a93ab8f5eaee4788cd2a0dd112353e5b11d7a865df18', '2026-04-29 09:53:46', 0, '2026-04-29 15:43:46'),
(43, 'vinc.atis.ui@phinmaed.com', '', '685b21c73d8be97fed10e034f0ad340a363473b2739ab07d7043398c2858519a', '2026-04-29 09:53:48', 0, '2026-04-29 15:43:48'),
(44, 'vinc.atis.ui@phinmaed.com', '', '241750206d510635e87f30067055817a30134f7f4ac1c52da3daa2343cf969a0', '2026-04-29 09:53:48', 0, '2026-04-29 15:43:48'),
(45, 'atisvincentcarl@gmail.com', '', '0721a5d562d3508e57404a2bd19010f9e3b3422832f2cd630d087f4e96cd208b', '2026-04-29 10:03:40', 0, '2026-04-29 15:53:40'),
(46, 'atisvincentcarl@gmail.com', '', 'f4032579217ada0f9bd1ba70c6775187539bffc17451207161e3cb9f73ee2c0a', '2026-04-29 10:08:06', 0, '2026-04-29 15:58:06'),
(47, 'atisvincentcarl@gmail.com', '', '48bbd4a7547d785641de41461175eea41b7054242bd90d1ccc6b115ed8f6abf4', '2026-04-29 10:08:18', 0, '2026-04-29 15:58:18'),
(48, 'vinc.atis.ui@phinmaed.com', '', 'e402bee12716de89765a1c3040e3b23c6dc90294358998b57d70b0702e298ba2', '2026-04-29 10:10:28', 0, '2026-04-29 16:00:28'),
(52, 'atisvincentcarl1@gmail.com', 'login', 'cc5e740e9f5f3781747c9344c21480fbe00f375f2ddc55c707d90e67d0a682f7', '2026-04-29 10:30:59', 1, '2026-04-29 16:25:59'),
(53, 'vinc.atis.ui@phinmaed.com', '', 'a40b7b56e8bccb9b1cc5b9deb70b40a7ca4aa88caa3cd9414b53e479fb482bb5', '2026-04-29 10:38:12', 0, '2026-04-29 16:28:12'),
(54, 'vinc.atis.ui@phinmaed.com', '', '188148ee8f27e99b90520ded2cb9ce529789acb2b65f014a89804f5771ce96cf', '2026-04-29 10:41:48', 0, '2026-04-29 16:31:48'),
(57, 'atisvincentcarl1@gmail.com', 'login', '25f893cad8388ee35d74537eaf8cc25cbceb7b157dea4c92ca3478092ec2a356', '2026-04-30 08:25:38', 1, '2026-04-30 14:20:38'),
(58, 'vinc.atis.ui@phinmaed.com', 'reset', '404777929198d3087af49b0d2ee5ed6aba09708f570fbfc75e86eaed7a27888c', '2026-04-30 08:32:01', 1, '2026-04-30 14:22:01'),
(59, 'vinc.atis.ui@phinmaed.com', 'login', '1d48bb78a8f8e33155a3d175451f1024b37042a62f0f0b81b692342ab0dfc8e9', '2026-04-30 08:32:27', 1, '2026-04-30 14:27:27'),
(63, 'atisvincentcarl1@gmail.com', 'login', 'a45004f9fb71d0fc5aedabef7776b8b841cf90552f0141cd9d351d6d0089c402', '2026-05-05 05:20:13', 1, '2026-05-05 11:15:13'),
(64, 'vinc.atis.ui@phinmaed.com', 'reset', 'bab15da5bdbf49e64e221313b4b046620f53b730951f7c7bb9a58d24f8ff8cbb', '2026-05-05 05:27:49', 1, '2026-05-05 11:17:49'),
(65, 'vinc.atis.ui@phinmaed.com', 'login', '971f2ce482eda11c4dc9f452c07ec95d69d06feebd9fcb00034b9e3cea0993e9', '2026-05-05 05:23:55', 1, '2026-05-05 11:18:55'),
(67, 'vinc.atis.ui@phinmaed.com', 'login', 'd2aae45bfd94c90dbad28556a848932b35db99b358ede5c6e5ac07119173b519', '2026-05-05 08:10:38', 1, '2026-05-05 14:05:38'),
(68, 'vinc.atis.ui@phinmaed.com', 'login', '1bb9bafb60102eb30870d4f397713654dcebea090dc15c098401ade03adc5424', '2026-05-05 08:16:07', 1, '2026-05-05 14:11:07'),
(69, 'atisvincentcarl1@gmail.com', 'login', '18b24896abb39dde318673e94372d2fb8f91a6ac5731f8d520f0235fe3824371', '2026-05-17 06:14:01', 0, '2026-05-17 12:09:01');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `product_type` enum('loose','member','package') DEFAULT 'loose',
  `image` varchar(255) DEFAULT 'images/product-placeholder.jpg',
  `stock` int(11) DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `product_type`, `image`, `stock`, `created_at`) VALUES
(1, 1, 'M1 - CHAMP', 'Inspired by BVLGARI EXTREME by Bvlgari. A bold and intense fragrance for the modern man.', 498.00, 'loose', 'images/products/c8c3f66614c15aa317188f1e3424b188.jpg', 100, '2026-03-24 03:15:28'),
(2, 1, 'M2 - FLASH', 'Inspired by POLOSPORT by Ralph Lauren. A fresh sporty scent perfect for active lifestyles.', 498.00, 'loose', 'images/products/158b1c32ca5c08c40d6a75ba0c6af978.jpg', 100, '2026-03-24 03:15:28'),
(3, 1, 'M3 - BRAVE', 'Inspired by HUGO BOSS RED by Hugo Boss. A daring and powerful fragrance for confident men.', 498.00, 'loose', 'images/products/e8fb1449f58788175231ce1ada3fc544.jpg', 100, '2026-03-24 03:15:28'),
(4, 1, 'M4 - ADMIRAL', 'Inspired by SWISS ARMY by Victorinox. A classic and sophisticated scent for distinguished gentlemen.', 498.00, 'loose', 'images/products/cdac1022334be9836769006a6bf9a2aa.jpg', 100, '2026-03-24 03:15:28'),
(5, 1, 'M5 - FIERCE', 'Inspired by LACOSTE RED by Lacoste Red. A vibrant and energetic fragrance for dynamic men.', 498.00, 'loose', 'images/products/59a302bc3ec62e946b51fd4eed11fb1b.jpg', 100, '2026-03-24 03:15:28'),
(6, 1, 'M6 - PRADA', 'Inspired by LUNA ROSSA CARBON by Prada. A sleek and sophisticated fragrance with carbon freshness.', 498.00, 'loose', 'images/products/2dd71f78275b57dc931b61e75c9975ee.jpg', 100, '2026-03-24 03:15:28'),
(7, 1, 'M7 - BLISS', 'Inspired by CLINIQUE HAPPY (MEN) by Clinique. A cheerful and uplifting scent for everyday wear.', 498.00, 'loose', 'images/products/1e5e763cddf7c910b7a21321b24635a3.jpg', 100, '2026-03-24 03:15:28'),
(8, 1, 'M8 - DECENT', 'Inspired by FERRARI BLACK by Ferrari. A sleek and timeless fragrance for refined men.', 498.00, 'loose', 'images/products/0d659c4c08ba0cc9d05a6ebe90699d8f.jpg', 100, '2026-03-24 03:15:28'),
(9, 1, 'M9 - GENTLEMAN', 'Inspired by 1 MILLION by Paco Rabanne. A luxurious golden scent for the man who has everything.', 498.00, 'loose', 'images/products/0883c1caa081bb41f355b7f07302a153.jpg', 100, '2026-03-24 03:15:28'),
(10, 1, 'M10 - HUNK', 'Inspired by LACOSTE BLACK by Lacoste. A dark and mysterious fragrance for the modern alpha.', 498.00, 'loose', 'images/products/c8c36075be7e27dd3f655d0eec01ad51.jpg', 100, '2026-03-24 03:15:28'),
(11, 1, 'M11 - REY', 'Inspired by INVICTUS by Paco Rabanne. An invincible and powerful scent for victorious men.', 498.00, 'loose', 'images/products/757689a350dd1e8b2c78ae94e6496f7d.jpg', 99, '2026-03-24 03:15:28'),
(12, 1, 'M12 - DAVE', 'Inspired by BLUE DE CHANEL by Chanel. A timeless and refined fragrance of unparalleled elegance.', 498.00, 'loose', 'images/products/cd1bbdd774dcfb602a0b3d4cca2e0bc8.jpg', 100, '2026-03-24 03:15:28'),
(13, 1, 'M14 - OKHYS', 'Inspired by GUCCI GUILTY by Gucci. A bold and sensual fragrance for the contemporary man.', 498.00, 'loose', 'images/products/bc92b21384ab0f836b1928d0a6b181c5.jpg', 100, '2026-03-24 03:15:28'),
(14, 1, 'M15 - CJ', 'Inspired by EROS by Versace. A passionate and powerful fragrance inspired by Greek mythology.', 498.00, 'loose', 'images/products/4b6dd0e4c48ef4a7a487c1937d53439f.jpg', 100, '2026-03-24 03:15:28'),
(15, 1, 'SJ - BIG BOSS', 'Inspired by SAUVAGE by Dior. A raw and noble fragrance evoking wide open spaces and wild nature.', 498.00, 'loose', 'images/products/male-scent.jpg', 100, '2026-03-24 03:15:28'),
(16, 1, 'R - RURU', 'Inspired by FIERCE by Abercrombie & Fitch. A captivating and sensual scent for modern men.', 498.00, 'loose', 'images/products/863326cc9c0a5944bacb4ecb5dd8d3b8.jpg', 100, '2026-03-24 03:15:28'),
(17, 2, 'F1 - DAZZLING', 'Inspired by BVLGARI AMETHYST by Bvlgari. A sparkling and elegant fragrance for radiant women.', 498.00, 'loose', 'images/products/4404f6056abdca03998ba868b1c38266.png', 100, '2026-03-24 03:15:28'),
(18, 2, 'F2 - CHARMING', 'Inspired by SWEET PEA by Bath & Body Works. A sweet and flirty scent perfect for everyday wear.', 498.00, 'loose', 'images/products/b1eb47acf738350d0f25dd6b1fc147d6.png', 100, '2026-03-24 03:15:28'),
(19, 2, 'F4 - SEDUCTIVE', 'Inspired by PARIS HILTON by Paris Hilton. A glamorous and alluring fragrance for confident women.', 498.00, 'loose', 'images/products/2279009f68988c65b8f933bf2cb102f6.png', 100, '2026-03-24 03:15:28'),
(20, 2, 'F5 - LUXURIOUS', 'Inspired by CK ONE SHOCK by Calvin Klein. A sensational and intense scent for bold women.', 498.00, 'loose', 'images/products/b27b53e4423ec0f07b9bfa383d4359ae.png', 100, '2026-03-24 03:15:28'),
(21, 2, 'F7 - DESIRE', 'Inspired by D&G LIGHT BLUE by Dolce & Gabbana. A refreshing Mediterranean fragrance for free spirits.', 498.00, 'loose', 'images/products/7f6663729591758c987e1e8c74bdc890.png', 100, '2026-03-24 03:15:28'),
(22, 2, 'F8 - BLUSH', 'Inspired by LACOSTE PINK by Lacoste. A delicate and feminine fragrance full of freshness.', 498.00, 'loose', 'images/products/1f84bce1481144882bc96276bb2fb268.png', 100, '2026-03-24 03:15:28'),
(23, 2, 'F9 - FAIRY', 'Inspired by INCANTO SHINE by Salvatore Ferragamo. A luminous and enchanting scent for dreamers.', 498.00, 'loose', 'images/products/ef5857f7613313ae8732387b2ea2d86f.png', 100, '2026-03-24 03:15:28'),
(24, 2, 'F11 - SHINENING', 'Inspired by ROMANTIC WISH by Victoria\'s Secret. A romantic and dreamy fragrance for hopeless romantics.', 498.00, 'loose', 'images/products/eaab2c3ab8103ffb616cbda96f264a68.png', 99, '2026-03-24 03:15:28'),
(25, 2, 'F12 - DAVE', 'Inspired by COCO MADAMOISELLE by Chanel Paris. A timeless and iconic fragrance for elegant women.', 498.00, 'loose', 'images/products/ff0df45840ba9befec0b677a1734141e.png', 100, '2026-03-24 03:15:28'),
(26, 2, 'MG1 - AFFABLE', 'Inspired by HAPPY HEART by Clinique. A cheerful and lighthearted fragrance that radiates positivity.', 498.00, 'loose', 'images/products/1cb87e70341471809780d4317be09568.jpg', 100, '2026-03-24 03:15:28'),
(27, 2, 'MG2 - EFFERVESCENT', 'Inspired by MEOW by Katy Perry. A playful and whimsical fragrance for the young at heart.', 498.00, 'loose', 'images/products/805cf6f178d31f7f6b1025628e651225.jpg', 100, '2026-03-24 03:15:28'),
(28, 2, 'MG3 - BENEVOLENT', 'Inspired by BOMBSHELL by Victoria\'s Secret. A captivating and glamorous fragrance for bold women.', 498.00, 'loose', 'images/products/67eaa0ef7d383cd2a2584f8ea7fa272f.jpg', 100, '2026-03-24 03:15:28'),
(29, 2, 'MG4 - SPIRITED', 'Inspired by SELENA by Selena Gomez. A floral and feminine fragrance inspired by the iconic artist.', 498.00, 'loose', 'images/products/41439c794a22a490cb285452cf8d550f.jpg', 100, '2026-03-24 03:15:28'),
(30, 2, 'B - BIANCA', 'Inspired by FIRST INSTINCT by Abercrombie & Fitch. A sensual and magnetic fragrance for fierce women.', 498.00, 'loose', 'images/products/fdeaa5c2b805e158b97f6dd2277f5688.jpg', 100, '2026-03-24 03:15:28'),
(31, 4, 'Gluta Papaya Soap', 'Ardeur de France Gluta Papaya Lightening Soap. Naturally lightens skin while keeping it smooth, moisturized, rejuvenated and glowing. SRP ₱180.', 180.00, 'loose', 'images/products/86c6a913f574545fba6887b7ff462fbf.png', 200, '2026-03-24 03:15:28'),
(32, 4, 'Kojic Collagen Soap', 'Ardeur de France Kojic Collagen Lightening Soap. Formulated with antioxidants that reduce scars, age spots, and discoloration. Makes skin naturally brighter, younger and flawless. SRP ₱180.', 180.00, 'loose', 'images/products/91c9f1bfbe995b8ce6676e8b5ac2d441.png', 200, '2026-03-24 03:15:28'),
(33, 4, 'Ardeur Relaxing Essential Oil', 'Ardeur Relaxing Essential Oil (Lavender). Create a warm, calm atmosphere perfect for sleeping and relaxation. 10ml Roller Ball Bottle. SRP ₱300.', 300.00, 'loose', 'images/products/4325130c2d402fa1f72259408cc0522e.jpg', 150, '2026-03-24 03:15:28'),
(34, 4, 'Ardeur Refreshing Essential Oil', 'Ardeur Refreshing Essential Oil (Peppermint). The fresh scent of peppermint oil will wake up your senses each morning. 10ml Roller Ball Bottle. SRP ₱300.', 300.00, 'loose', 'images/products/e1a127efed04fcd6bfcaf76cf15c8cbb.jpg', 150, '2026-03-24 03:15:28'),
(35, 3, 'Immukira-AG (60 Capsules)', 'Marguax Immu-Pair. Immukira-AG — King of Bitters. Food supplement with Andrographis Paniculata & Insulin Plant. Benefits: Anti-viral, Anti-bacterial, Anti-fungal, Anti-Cancer, Liver & Kidney Detoxifier, Prevents Diabetes & Heart Diseases. 60 capsules | 500mg. SRP ₱600.', 600.00, 'loose', 'images/products/9b4352e30d159c3819ff3aed40bce721.jpg', 100, '2026-03-24 03:15:28'),
(36, 3, 'Immuvit-ZinC (60 Capsules)', 'Marguax Immu-Pair. Immuvit-ZinC — Mother of all Vitamins. Sodium Ascorbate with Zinc + Alkaline & D3. Benefits: Strengthens Immune System, Reduces Chronic Disease, Promotes Eye Health, Supports Healthy Skin & Gums. 60 capsules | 500mg. SRP ₱600.', 600.00, 'loose', 'images/products/5ad07335e02cf9acd1df6f18fbb699f3.jpg', 100, '2026-03-24 03:15:28'),
(37, 3, 'Marguax Organic Green Barley Plus', 'Marguax Healthy Boosters — CLEANSE. Organic Green Barley Plus drink. Enriched with Aloe Vera Content. 10 Sachets x 11g. No Approved Therapeutic Claims.', 750.00, 'loose', 'images/products/2fc988bdeaae5e9a0013e7dd7e7701b0.jpg', 97, '2026-03-24 03:15:28'),
(38, 3, 'Marguax Healthy Slimming Coffee', 'Marguax Healthy Boosters — BURN. Marguax 12-in-1 Healthy Slimming Coffee. Helps with weight management and overall wellness. 10 Sachets x 30g. No Approved Therapeutic Claims.', 750.00, 'loose', 'images/products/527bc98a7f9933e13694f2eaf11416ee.jpg', 96, '2026-03-24 03:15:28'),
(39, 3, 'Marguax Extra Strong Coffee', 'Marguax Healthy Boosters — STRENGTH. Marguax 12-in-1 Extra Strong Coffee Mix. Boosts energy and vitality. 10 Sachets x 30g. No Approved Therapeutic Claims.', 750.00, 'loose', 'images/products/7c7c659c0ec284b9c793c54c8dc8b467.jpg', 97, '2026-03-24 03:15:28'),
(40, 5, 'Silver Package — Fragrance', 'SILVER PACKAGE ₱6,888 — Fragrance Option. Includes: 17 Premium Perfumes (Ardeur de France). ROI: ₱8,466. Become a member and unlock exclusive products and member benefits!', 6888.00, 'package', 'images/products/silver-package.jpg', 50, '2026-03-24 03:15:28'),
(41, 5, 'Silver Package — Health', 'SILVER PACKAGE ₱6,888 — Health Option. Includes: 14 Immukira-AG / ImmuVit-ZinC supplements. ROI: ₱8,400. Become a member and unlock exclusive products and member benefits!', 6888.00, 'package', 'images/products/silver-package.jpg', 50, '2026-03-24 03:15:28'),
(42, 5, 'Silver Package — Boosters', 'SILVER PACKAGE ₱6,888 — Boosters Option. Includes: 11 Boxes + 2 Sachets of Boosters (Organic Green Barley, Healthy Slimming Coffee, Extra Strong Coffee). ROI: ₱8,250.', 6888.00, 'package', 'images/products/silver-package.jpg', 50, '2026-03-24 03:15:28'),
(43, 5, 'Gold Package — Fragrance', 'GOLD PACKAGE ₱10,888 — Fragrance Option. Includes: 26 Premium Perfumes (Ardeur de France). ROI: ₱12,948. Become a member and unlock exclusive products and member benefits!', 10888.00, 'package', 'images/products/gold-package.jpg', 50, '2026-03-24 03:15:28'),
(44, 5, 'Gold Package — Health', 'GOLD PACKAGE ₱10,888 — Health Option. Includes: 22 Immukira-AG / ImmuVit-ZinC supplements. ROI: ₱13,200. Become a member and unlock exclusive products and member benefits!', 10888.00, 'package', 'images/products/gold-package.jpg', 50, '2026-03-24 03:15:28'),
(45, 5, 'Gold Package — Boosters', 'GOLD PACKAGE ₱10,888 — Boosters Option. Includes: 17 Boxes + 5 Sachets of Boosters (Organic Green Barley, Healthy Slimming Coffee, Extra Strong Coffee). ROI: ₱13,060.', 10888.00, 'package', 'images/products/gold-package.jpg', 50, '2026-03-24 03:15:28'),
(46, 5, 'Ruby Package — Fragrance', 'RUBY PACKAGE ₱20,888 — Fragrance Option. Includes: 50 Premium Perfumes (Ardeur de France). ROI: ₱24,900. Become a member and unlock exclusive products and member benefits!', 20888.00, 'package', 'images/products/ruby-package.jpg', 30, '2026-03-24 03:15:28'),
(47, 5, 'Ruby Package — Health', 'RUBY PACKAGE ₱20,888 — Health Option. Includes: 42 Immukira-AG / ImmuVit-ZinC supplements. ROI: ₱25,200. Become a member and unlock exclusive products and member benefits!', 20888.00, 'package', 'images/products/ruby-package.jpg', 30, '2026-03-24 03:15:28'),
(48, 5, 'Ruby Package — Boosters', 'RUBY PACKAGE ₱20,888 — Boosters Option. Includes: 33 Boxes + 5 Sachets of Boosters (Organic Green Barley, Healthy Slimming Coffee, Extra Strong Coffee). ROI: ₱25,060.', 20888.00, 'package', 'images/products/ruby-package.jpg', 30, '2026-03-24 03:15:28'),
(49, 5, 'Emerald Package — Fragrance', 'EMERALD PACKAGE ₱50,888 — Fragrance Option. Includes: 123 Premium Perfumes (Ardeur de France). ROI: ₱61,254. Become a member and unlock exclusive products and member benefits!', 50888.00, 'package', 'images/products/emerald-package.jpg', 20, '2026-03-24 03:15:28'),
(50, 5, 'Emerald Package — Health', 'EMERALD PACKAGE ₱50,888 — Health Option. Includes: 102 Immukira-AG / ImmuVit-ZinC supplements. ROI: ₱61,200. Become a member and unlock exclusive products and member benefits!', 50888.00, 'package', 'images/products/emerald-package.jpg', 20, '2026-03-24 03:15:28'),
(51, 5, 'Emerald Package — Boosters', 'EMERALD PACKAGE ₱50,888 — Boosters Option. Includes: 81 Boxes + 5 Sachets of Boosters (Organic Green Barley, Healthy Slimming Coffee, Extra Strong Coffee). ROI: ₱61,060.', 50888.00, 'package', 'images/products/emerald-package.jpg', 20, '2026-03-24 03:15:28'),
(52, 5, 'Diamond Package — Fragrance', 'DIAMOND PACKAGE ₱100,888 — Fragrance Option. Includes: 243 Premium Perfumes (Ardeur de France). ROI: ₱121,014. Our most premium membership package!', 100888.00, 'package', 'images/products/diamond-package.jpg', 10, '2026-03-24 03:15:28'),
(53, 5, 'Diamond Package — Health', 'DIAMOND PACKAGE ₱100,888 — Health Option. Includes: 202 Immukira-AG / ImmuVit-ZinC supplements. ROI: ₱121,200. Our most premium membership package!', 100888.00, 'package', 'images/products/diamond-package.jpg', 10, '2026-03-24 03:15:28'),
(54, 5, 'Diamond Package — Boosters', 'DIAMOND PACKAGE ₱100,888 — Boosters Option. Includes: 161 Boxes + 5 Sachets of Boosters (Organic Green Barley, Healthy Slimming Coffee, Extra Strong Coffee). ROI: ₱121,060.', 100888.00, 'package', 'images/products/diamond-package.jpg', 10, '2026-03-24 03:15:28'),
(55, 5, 'Ardeur Perfume Bundle (3pcs)', 'MEMBER EXCLUSIVE — Bundle of 3 Ardeur de France premium perfumes of your choice. Available in male and female scents. Special member pricing.', 1299.00, 'member', 'images/products/male-scent.jpg', 49, '2026-03-24 03:15:28'),
(56, 5, 'Immukira + Immuvit Bundle', 'MEMBER EXCLUSIVE — Marguax Immu-Pair Bundle. 1 bottle Immukira-AG + 1 bottle Immuvit-ZinC at a special discounted price for  .', 999.00, 'member', 'images/products/immukira.jpg', 50, '2026-03-24 03:15:28'),
(57, 5, 'Booster Combo Pack', 'MEMBER EXCLUSIVE — All 3 Healthy Boosters in one pack: Organic Green Barley + Healthy Slimming Coffee + Extra Strong Coffee. Member price.', 1999.00, 'member', 'images/products/barley.jpg', 50, '2026-03-24 03:15:28'),
(58, 5, 'Soap + Oil Wellness Kit', 'MEMBER EXCLUSIVE — Complete Wellness Kit: Gluta Papaya Soap + Kojic Collagen Soap + Relaxing Essential Oil + Refreshing Essential Oil.', 699.00, 'member', 'images/products/soap.jpg', 50, '2026-03-24 03:15:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `member_status` enum('member','non-member') DEFAULT 'non-member',
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `contact_number`, `address`, `profile_photo`, `member_status`, `role`, `created_at`) VALUES
(1, 'Admin AWMC', 'atisvincentcarl1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456789', 'AWMC Head Office, Philippines', NULL, 'member', 'admin', '2026-03-24 03:15:28'),
(14, 'Rodalen Dulalia Fernandez', 'rodu.fenandez.ui@phinmaed.com', '$2y$10$j5cmm6eXq/5V5XhK4w1EculDcaa2BNz4C.Nr5UKFLYuuYfNj7BPx.', '09511627316', NULL, NULL, 'non-member', 'customer', '2026-04-08 10:51:37'),
(15, 'Mary Kate Naral', 'mabo.naral.ui@phinmaed.com', '$2y$10$5qZsvq.MdD8y.x3WKVa1R.nWKxRD7xojZSS6ITDyHUmrQdWMv4vPq', '09501137991', NULL, NULL, 'non-member', 'customer', '2026-04-08 11:05:37'),
(16, 'Vincent Carl Atis', 'vinc.atis.ui@phinmaed.com', '$2y$10$WeozehbimgvefxE9Y2eBUeC63.XpHkw7dNcmnqlc5vrC3DODTDnnO', '09482841494', '', 'uploads/profiles/user_16_1776923341.jpg', 'non-member', 'customer', '2026-04-23 05:38:37');

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_accounts`
--

CREATE TABLE `user_payment_accounts` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_type` enum('gcash','paymaya') NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier_type` (`identifier`,`type`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_payment_accounts`
--
ALTER TABLE `user_payment_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user_payment_accounts`
--
ALTER TABLE `user_payment_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_payment_accounts`
--
ALTER TABLE `user_payment_accounts`
  ADD CONSTRAINT `user_payment_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
