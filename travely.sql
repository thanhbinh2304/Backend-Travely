-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 05, 2026 lúc 05:41 PM
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
-- Cơ sở dữ liệu: `travely`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `booking`
--

CREATE TABLE `booking` (
  `bookingID` bigint(20) NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `userID` char(36) NOT NULL,
  `bookingDate` datetime NOT NULL DEFAULT current_timestamp(),
  `numAdults` int(11) NOT NULL,
  `numChildren` int(11) NOT NULL,
  `totalPrice` decimal(10,2) NOT NULL,
  `paymentStatus` enum('pending','paid','refunded') NOT NULL DEFAULT 'pending',
  `bookingStatus` enum('confirmed','cancelled','completed') NOT NULL DEFAULT 'confirmed',
  `specialRequests` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `conversation_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `admin_id` char(36) NOT NULL,
  `bookingID` bigint(20) NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` char(36) NOT NULL,
  `conversation_id` char(36) NOT NULL,
  `sender_id` char(36) NOT NULL,
  `parent_message_id` char(36) NOT NULL,
  `message_text` text NOT NULL,
  `message_type` enum('text','image','file','voice','system') NOT NULL DEFAULT 'text',
  `attachment_url` varchar(500) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_size` bigint(20) DEFAULT NULL,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` timestamp NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `checkout`
--

CREATE TABLE `checkout` (
  `checkoutID` bigint(20) NOT NULL,
  `bookingID` bigint(20) NOT NULL,
  `paymentMethod` varchar(50) NOT NULL,
  `paymentDate` datetime NOT NULL DEFAULT current_timestamp(),
  `refundDate` timestamp NULL DEFAULT NULL,
  `refundAmount` decimal(15,2) DEFAULT NULL,
  `refundReason` text DEFAULT NULL,
  `refundBy` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paymentStatus` enum('Pending','Completed','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `transactionID` varchar(255) NOT NULL COMMENT 'Mã GD từ cổng thanh toán\r\n',
  `paymentData` text DEFAULT NULL,
  `qrCode` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `history`
--

CREATE TABLE `history` (
  `historyID` bigint(20) NOT NULL,
  `userID` char(36) NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `actionType` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `invoice`
--

CREATE TABLE `invoice` (
  `invoiceID` bigint(20) NOT NULL,
  `bookingID` bigint(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `dateIssued` datetime NOT NULL DEFAULT current_timestamp(),
  `details` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(2, '2025_11_04_080034_create_booking_table', 1),
(3, '2025_11_04_080034_create_chat_conversations_table', 1),
(4, '2025_11_04_080034_create_chat_messages_table', 1),
(5, '2025_11_04_080034_create_checkout_table', 1),
(6, '2025_11_04_080034_create_history_table', 1),
(7, '2025_11_04_080034_create_invoice_table', 1),
(8, '2025_11_04_080034_create_permission_role_table', 1),
(9, '2025_11_04_080034_create_permissions_table', 1),
(10, '2025_11_04_080034_create_promotion_table', 1),
(11, '2025_11_04_080034_create_review_table', 1),
(12, '2025_11_04_080034_create_roles_table', 1),
(13, '2025_11_04_080034_create_tour_images_table', 1),
(14, '2025_11_04_080034_create_tour_itinerary_table', 1),
(15, '2025_11_04_080034_create_tour_table', 1),
(16, '2025_11_04_080034_create_users_table', 1),
(17, '2025_11_04_080034_create_wishlist_table', 1),
(18, '2025_11_04_080037_add_foreign_keys_to_booking_table', 1),
(19, '2025_11_04_080037_add_foreign_keys_to_chat_conversations_table', 1),
(20, '2025_11_04_080037_add_foreign_keys_to_chat_messages_table', 1),
(21, '2025_11_04_080037_add_foreign_keys_to_checkout_table', 1),
(22, '2025_11_04_080037_add_foreign_keys_to_history_table', 1),
(23, '2025_11_04_080037_add_foreign_keys_to_invoice_table', 1),
(24, '2025_11_04_080037_add_foreign_keys_to_permission_role_table', 1),
(25, '2025_11_04_080037_add_foreign_keys_to_review_table', 1),
(26, '2025_11_04_080037_add_foreign_keys_to_tour_images_table', 1),
(27, '2025_11_04_080037_add_foreign_keys_to_tour_itinerary_table', 1),
(28, '2025_11_04_080037_add_foreign_keys_to_users_table', 1),
(29, '2025_11_04_080037_add_foreign_keys_to_wishlist_table', 1),
(30, '2025_11_04_080100_add_facebook_id_to_users_table', 1),
(31, '2025_11_29_141215_add_payment_fields_to_checkout_table', 1),
(32, '2025_11_29_183340_add_review_fields_to_review_table', 1),
(33, '2025_11_29_185423_add_refund_fields_to_checkout_table', 1),
(34, '2025_11_29_185913_create_notifications_table', 1),
(35, '2025_12_09_000000_add_last_login_to_users_table', 1),
(36, '2025_12_09_165620_add_timestamps_to_tour_table', 1),
(37, '2025_12_11_000001_add_code_to_promotion_table', 1),
(38, '2025_12_11_081105_add_primary_key_to_wishlist_table', 1),
(39, '2025_12_11_095947_add_code_to_promotion_table', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` varchar(255) NOT NULL,
  `updated_by` varchar(255) NOT NULL,
  `api_path` varchar(255) NOT NULL,
  `method` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT 0,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotion`
--

CREATE TABLE `promotion` (
  `promotionID` bigint(20) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `review`
--

CREATE TABLE `review` (
  `reviewID` bigint(20) NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `userID` char(36) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `images` text DEFAULT NULL,
  `status` enum('pending','approved','hidden') NOT NULL DEFAULT 'pending',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` char(36) DEFAULT NULL,
  `is_verified_purchase` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` bigint(20) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `active`, `updated_at`, `created_at`, `updated_by`, `created_by`, `description`, `name`) VALUES
(1, 1, '2025-12-16 10:51:01', '2025-12-16 10:51:01', 'seeder', 'seeder', 'Administrator role', 'Admin'),
(2, 1, '2025-12-16 10:51:01', '2025-12-16 10:51:01', 'seeder', 'seeder', 'Normal user role', 'User');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tour`
--

CREATE TABLE `tour` (
  `tourID` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `priceAdult` decimal(10,2) NOT NULL,
  `priceChild` decimal(10,2) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `availability` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: còn chỗ',
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tour`
--

INSERT INTO `tour` (`tourID`, `title`, `description`, `quantity`, `priceAdult`, `priceChild`, `destination`, `availability`, `startDate`, `endDate`, `created_at`, `updated_at`) VALUES
(1, 'Distinctio voluptas aliquam ratione.', 'Commodi dolorem ut provident incidunt recusandae eum quam. Facilis doloribus hic nihil eligendi. Et amet odit saepe sed commodi.\n\nSit quaerat id cum omnis qui fugiat quo. Corrupti accusantium beatae voluptates aut. Esse eveniet nisi velit saepe cumque aut. Ullam repellat totam amet aliquid minus quaerat debitis.\n\nFacilis atque qui dolor incidunt nostrum nihil. Fugit quo deserunt tempore culpa. Voluptatibus fugiat qui dolor. Qui voluptatem est et aut magni aut. Est sit cumque molestiae praesentium maiores.', 79, 9077507.00, 3701247.00, 'Modestoberg', 1, '2026-05-26', '2026-06-06', '2025-12-16 10:51:02', '2025-12-16 10:51:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tour_images`
--

CREATE TABLE `tour_images` (
  `imageID` bigint(20) NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `imageURL` varchar(255) NOT NULL,
  `uploadDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tour_itinerary`
--

CREATE TABLE `tour_itinerary` (
  `itineraryID` bigint(20) NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `dayNumber` int(11) NOT NULL,
  `activity` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `userID` char(36) NOT NULL,
  `userName` varchar(32) NOT NULL,
  `passWord` text NOT NULL,
  `phoneNumber` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(255) NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `verification_token_expires_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`userID`, `userName`, `passWord`, `phoneNumber`, `address`, `email`, `role_id`, `created_at`, `created_by`, `updated_at`, `updated_by`, `last_login`, `refresh_token`, `email_verified`, `verification_token`, `verification_token_expires_at`, `google_id`, `facebook_id`, `avatar_url`, `is_admin`) VALUES
('0f4b8c1c-8fb5-4bc3-81df-4bfddc65eb7a', 'mschneider', '$2y$10$4UuIvJUsdtjjRcPqOoHphu95cSBzw1Ysv0H7r2v9513kPlzcMugoO', '3271361762', '9322 Lucienne Groves\nNorth Christberg, ND 50028', 'terrell01@example.org', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00dd44?text=people+totam', 0),
('19ea7e9c-6bc5-4479-bd16-0ae313998d50', 'gulgowski.victoria', '$2y$10$DfnU5Gjs1IVXMpTPvx5kuO6Y951NvNhbAw8qT9ZpKQIPG9hOb4.Te', '0778793140', '439 Ignatius Divide Suite 290\nWest Abbigail, DC 45336', 'marlee.barrows@example.org', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/002288?text=people+debitis', 0),
('1bf88f34-cdba-4ae3-958f-9f61b3751b2c', 'admin', '$2y$10$qSPlPRKxduPVzBmsnWRMyuS0a8ywfourXDNfm5Ru2D0E1CLbVqVxm', '5492893808', '60476 Gottlieb Shoals\nPort Deshaunburgh, IA 97386', 'admin@travely.com', 1, '2025-12-16 10:51:01', 'seeder', '2025-12-16 10:51:01', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/0077aa?text=people+et', 1),
('1c558a58-9727-4f8b-8fe1-8294fcaccb44', 'cara44', '$2y$10$jUjQVgsx1LQaAD1M/jQVuuR212ptVf8v7QYznFckgaIIYKW7OIM7W', '2460035003', '8932 Kuvalis Drive Apt. 596\nDoylechester, CA 75912-2905', 'langworth.janis@example.com', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/008888?text=people+dolorem', 0),
('2be1271c-8615-4956-9c3d-4ceedf9bd2e4', 'ukilback', '$2y$10$DqpCSZaem0tp4LNxGJgFJe/PCVNVIT.vn.ZFstZBbq/5gW3H0P6Y6', '2379526630', '2859 Aufderhar Key\nJayville, DE 11169', 'holly.wolff@example.net', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/008877?text=people+rerum', 0),
('3688a49f-d08a-4970-895a-1095675b16a0', 'verner02', '$2y$10$UhdgEftFyC34sTOuFdIPyeXp6eBL5VfsE0qeNzbyTDKSSBvyUN1NC', '9915227783', '8004 Adonis Freeway\nFritzchester, HI 41490', 'annabelle.deckow@example.org', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00ee99?text=people+illum', 0),
('40ec9258-5d63-4b3e-be5e-fb8942af7c04', 'swift.sarah', '$2y$10$PhqCdX1nlD6RmIwHSoqVfuHB30VfrvjZPSSx.rMKveIle12lhP9x6', '9786258301', '38164 Beatty Alley\nHirtheland, AK 42241', 'vschimmel@example.com', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00ee44?text=people+dolorem', 0),
('485c28e0-2084-4800-b573-37ef71ff36f1', 'wstokes', '$2y$10$vkq4ScRE2bIHYQwGKz1UvuXuCfXn0iqhsxwEN9PJt3z1Pk8c8N5wC', '5945168648', '4503 Willard Extension\nMilfordchester, OR 19777', 'poconner@example.com', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/003399?text=people+ratione', 0),
('4c6c92e5-cd63-4822-bbb5-775be296afc3', 'vhalvorson', '$2y$10$qAFdxv0MtVeb1gsYldkume5x08IkHwthXEkzVymSHqPIDK9r.vo5y', '5277802576', '143 Ryan Springs\nNorth Joaquin, WA 48766-6313', 'eborer@example.com', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00ff88?text=people+exercitationem', 0),
('7e119a45-0d16-4793-924e-f655d20d7351', 'kub.elvie', '$2y$10$sieeVuBAudEPKSILNTPQvOtso0m0cMRMh.SOuGSe5WPHT7kzVXZDe', '4156849570', '282 Larkin Rapids\nNorth Elsaville, OK 95799', 'elnora.osinski@example.com', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00ddee?text=people+repellat', 0),
('87756ded-272a-435a-a704-4f54d2fbb400', 'jenifer97', '$2y$10$pqFZqaKReflOE8/1rtIxuOGHBVL79K0ojMIuAo50ox5wqKAO03OCK', '1818658669', '97673 Krajcik Pass Suite 469\nLake Fatimaton, MO 39878-1047', 'emelie28@example.com', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00cc88?text=people+mollitia', 0),
('892e045d-5c41-4a71-8bdc-e171249d20b2', 'lueilwitz.camron', '$2y$10$5vpvGYvkoP1VJU.bQ/XSeORnBLFLBA3PyB0iv8htmeu7eCMrzgg9.', '1581740386', '4672 Estrella Underpass\nSouth Lorenzo, SD 52740-0515', 'bergnaum.paolo@example.net', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00ff11?text=people+perferendis', 0),
('89e85247-16dc-4f7c-8c3d-a53e46cf48c4', 'raul.mayer', '$2y$10$oLXvxfHPGe0VLt863BDIuOgULOFET7E/EiJXBEO0fRpKpp62vFWvi', '8825913369', '97076 Howe Parkways Suite 555\nRobertsshire, UT 08999-1024', 'giles.bahringer@example.com', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00eedd?text=people+in', 0),
('8ba7caf2-5066-4659-bca9-946d89299af3', 'boyle.haylee', '$2y$10$AmKixDIbOv/YuiO4BNpoJ.xmI78i53njS6P63yfQHeejxNgy97amS', '5017260871', '349 Feil Landing\nChristiansentown, NC 98451', 'misty98@example.org', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/000088?text=people+reiciendis', 0),
('a58f2a2e-a61b-47d0-aa8f-b668c0ceec37', 'mohr.deanna', '$2y$10$jToEHBcwtwyf/S9Fwly5K.7OoJJVSlbAAwlkWhLl7.GTZ.m/TDYRK', '4189480246', '9358 Larry Rest Apt. 230\nSouth Raphaellefort, MN 14222-8173', 'eschiller@example.net', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/0044aa?text=people+nesciunt', 0),
('b2943653-d5cf-461d-8cdd-aa331188c8cd', 'marquardt.jonathon', '$2y$10$suoLWQYZqyex7LJ9uWNRBexcDBL.iCXxeF4Au5JCp4P/5vcxDvJP.', '1469376704', '160 Greenholt Manor\nLake Tonishire, LA 67127', 'jane03@example.net', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/002266?text=people+repellendus', 0),
('bcbbc185-567b-4f54-a21f-e6647a66a36f', 'kendrick34', '$2y$10$cwwtQP8yqJFNT6SX0jVVqeUF77/amvWj4P0A1vqlfIlRZgoa8ZNS2', '2825050848', '3344 Thiel Via\nMartinashire, CT 11702-8709', 'ykonopelski@example.com', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/000011?text=people+id', 0),
('d3574e01-acc7-44c1-b63b-66759148a8cf', 'marcos.bashirian', '$2y$10$XjwhyFtKM1ugK/XF/QhWJ.RytcdWASRrWQdWMDOmvHiP4g/215dqC', '7478389669', '54378 Keeling Walks\nSophiafort, ME 32051', 'stella03@example.net', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/004400?text=people+ab', 0),
('d3a3eec2-3cae-4e11-9bb6-d06d47a28153', 'llangworth', '$2y$10$h68QkS9f.d5CgI8.eiE/FeLmCcFQRIPNIu9KHQyPU/Tdi2Dl0Oyjy', '7471314544', '446 Harvey Fork\nReynoldsberg, WA 51712', 'timmothy71@example.org', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00ffee?text=people+tempore', 0),
('e3ce16f7-f710-472c-b618-91c904c24daa', 'rosenbaum.krystal', '$2y$10$9/Ic8TpQCnCEL6d6coO.i.3nOre9jQPaxLGZ9gMyi2VQWMNbKZ8GK', '8946180726', '949 Hannah Expressway Suite 246\nEast Rainaburgh, TN 63832', 'tkreiger@example.org', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/0088bb?text=people+facere', 0),
('f8c5b6e4-80cc-4310-b2b4-a5f212bec96f', 'afadel', '$2y$10$dXIKXIqGPpVcEGiXRMXN0uxeQvSNCcaGalRNYUWHZN3j5PCHxFxwK', '6019711905', '11611 Noemy Roads Suite 740\nLake Sammie, MN 21750', 'alana.goodwin@example.com', 2, '2025-12-16 10:51:02', 'seeder', '2025-12-16 10:51:02', 'seeder', NULL, NULL, 1, NULL, NULL, NULL, NULL, 'https://via.placeholder.com/200x200.png/00cc33?text=people+corrupti', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlist`
--

CREATE TABLE `wishlist` (
  `userID` char(36) NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`bookingID`),
  ADD KEY `tourID` (`tourID`),
  ADD KEY `userID` (`userID`);

--
-- Chỉ mục cho bảng `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `bookingID` (`bookingID`);

--
-- Chỉ mục cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `parent_message_id` (`parent_message_id`);

--
-- Chỉ mục cho bảng `checkout`
--
ALTER TABLE `checkout`
  ADD PRIMARY KEY (`checkoutID`),
  ADD KEY `bookingID` (`bookingID`);

--
-- Chỉ mục cho bảng `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`historyID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `tourID` (`tourID`);

--
-- Chỉ mục cho bảng `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoiceID`),
  ADD KEY `bookingID` (`bookingID`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Chỉ mục cho bảng `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`);

--
-- Chỉ mục cho bảng `permission_role`
--
ALTER TABLE `permission_role`
  ADD KEY `permission_id` (`permission_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Chỉ mục cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Chỉ mục cho bảng `promotion`
--
ALTER TABLE `promotion`
  ADD PRIMARY KEY (`promotionID`),
  ADD UNIQUE KEY `promotion_code_unique` (`code`);

--
-- Chỉ mục cho bảng `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`reviewID`),
  ADD KEY `tourID` (`tourID`),
  ADD KEY `userID` (`userID`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Chỉ mục cho bảng `tour`
--
ALTER TABLE `tour`
  ADD PRIMARY KEY (`tourID`);

--
-- Chỉ mục cho bảng `tour_images`
--
ALTER TABLE `tour_images`
  ADD PRIMARY KEY (`imageID`),
  ADD KEY `tourID` (`tourID`);

--
-- Chỉ mục cho bảng `tour_itinerary`
--
ALTER TABLE `tour_itinerary`
  ADD PRIMARY KEY (`itineraryID`),
  ADD KEY `tourID` (`tourID`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `users_facebook_id_index` (`facebook_id`);

--
-- Chỉ mục cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`userID`,`tourID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `tourID` (`tourID`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `booking`
--
ALTER TABLE `booking`
  MODIFY `bookingID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `checkout`
--
ALTER TABLE `checkout`
  MODIFY `checkoutID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `history`
--
ALTER TABLE `history`
  MODIFY `historyID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoiceID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `promotion`
--
ALTER TABLE `promotion`
  MODIFY `promotionID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `review`
--
ALTER TABLE `review`
  MODIFY `reviewID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tour`
--
ALTER TABLE `tour`
  MODIFY `tourID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `tour_images`
--
ALTER TABLE `tour_images`
  MODIFY `imageID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tour_itinerary`
--
ALTER TABLE `tour_itinerary`
  MODIFY `itineraryID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`tourID`) REFERENCES `tour` (`tourID`);

--
-- Các ràng buộc cho bảng `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `chat_conversations_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `chat_conversations_ibfk_3` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`);

--
-- Các ràng buộc cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`conversation_id`),
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `chat_messages_ibfk_3` FOREIGN KEY (`parent_message_id`) REFERENCES `chat_messages` (`message_id`);

--
-- Các ràng buộc cho bảng `checkout`
--
ALTER TABLE `checkout`
  ADD CONSTRAINT `checkout_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`);

--
-- Các ràng buộc cho bảng `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `history_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `history_ibfk_2` FOREIGN KEY (`tourID`) REFERENCES `tour` (`tourID`);

--
-- Các ràng buộc cho bảng `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`);

--
-- Các ràng buộc cho bảng `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`),
  ADD CONSTRAINT `permission_role_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`);

--
-- Các ràng buộc cho bảng `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`tourID`) REFERENCES `tour` (`tourID`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Các ràng buộc cho bảng `tour_images`
--
ALTER TABLE `tour_images`
  ADD CONSTRAINT `tour_images_ibfk_1` FOREIGN KEY (`tourID`) REFERENCES `tour` (`tourID`);

--
-- Các ràng buộc cho bảng `tour_itinerary`
--
ALTER TABLE `tour_itinerary`
  ADD CONSTRAINT `tour_itinerary_ibfk_1` FOREIGN KEY (`tourID`) REFERENCES `tour` (`tourID`);

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Các ràng buộc cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`tourID`) REFERENCES `tour` (`tourID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
