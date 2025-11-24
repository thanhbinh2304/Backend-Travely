-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 14, 2025 lúc 09:51 AM
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
  `userID` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `bookingDate` datetime NOT NULL DEFAULT current_timestamp(),
  `numAdults` int(11) NOT NULL,
  `numChildren` int(11) NOT NULL,
  `totalPrice` decimal(10,2) NOT NULL,
  `paymentStatus` enum('pending','paid','refunded') NOT NULL DEFAULT 'pending',
  `bookingStatus` enum('confirmed','cancelled','completed') NOT NULL DEFAULT 'confirmed',
  `specialRequests` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `conversation_id` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `user_id` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `admin_id` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `bookingID` bigint(20) NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `conversation_id` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `sender_id` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `parent_message_id` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `checkout`
--

CREATE TABLE `checkout` (
  `checkoutID` bigint(20) NOT NULL,
  `bookingID` bigint(20) NOT NULL,
  `paymentMethod` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `paymentDate` datetime NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `paymentStatus` enum('Pending','Completed','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `transactionID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã GD từ cổng thanh toán\r\n'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `history`
--

CREATE TABLE `history` (
  `historyID` bigint(20) NOT NULL,
  `userID` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `actionType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `invoice`
--

CREATE TABLE `invoice` (
  `invoiceID` bigint(20) NOT NULL,
  `bookingID` bigint(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `dateIssued` datetime NOT NULL DEFAULT current_timestamp(),
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, '2025_11_04_080034_create_booking_table', 0),
(2, '2025_11_04_080034_create_chat_conversations_table', 0),
(3, '2025_11_04_080034_create_chat_messages_table', 0),
(4, '2025_11_04_080034_create_checkout_table', 0),
(5, '2025_11_04_080034_create_history_table', 0),
(6, '2025_11_04_080034_create_invoice_table', 0),
(7, '2025_11_04_080034_create_permission_role_table', 0),
(8, '2025_11_04_080034_create_permissions_table', 0),
(9, '2025_11_04_080034_create_promotion_table', 0),
(10, '2025_11_04_080034_create_review_table', 0),
(11, '2025_11_04_080034_create_roles_table', 0),
(12, '2025_11_04_080034_create_tour_table', 0),
(13, '2025_11_04_080034_create_tour_images_table', 0),
(14, '2025_11_04_080034_create_tour_itinerary_table', 0),
(15, '2025_11_04_080034_create_users_table', 0),
(16, '2025_11_04_080034_create_wishlist_table', 0),
(17, '2025_11_04_080037_add_foreign_keys_to_booking_table', 0),
(18, '2025_11_04_080037_add_foreign_keys_to_chat_conversations_table', 0),
(19, '2025_11_04_080037_add_foreign_keys_to_chat_messages_table', 0),
(20, '2025_11_04_080037_add_foreign_keys_to_checkout_table', 0),
(21, '2025_11_04_080037_add_foreign_keys_to_history_table', 0),
(22, '2025_11_04_080037_add_foreign_keys_to_invoice_table', 0),
(23, '2025_11_04_080037_add_foreign_keys_to_permission_role_table', 0),
(24, '2025_11_04_080037_add_foreign_keys_to_review_table', 0),
(25, '2025_11_04_080037_add_foreign_keys_to_tour_images_table', 0),
(26, '2025_11_04_080037_add_foreign_keys_to_tour_itinerary_table', 0),
(27, '2025_11_04_080037_add_foreign_keys_to_users_table', 0),
(28, '2025_11_04_080037_add_foreign_keys_to_wishlist_table', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) DEFAULT 0,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotion`
--

CREATE TABLE `promotion` (
  `promotionID` bigint(20) NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `review`
--

CREATE TABLE `review` (
  `reviewID` bigint(20) NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `userID` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` bigint(20) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `active`, `updated_at`, `created_at`, `updated_by`, `created_by`, `description`, `name`) VALUES
(1, 1, '2025-11-04 14:12:30', '2025-11-04 14:12:30', '', '', '', 'admin'),
(2, 1, '2025-11-04 12:52:10', '2025-11-04 12:52:10', '', '', '', 'user');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tour`
--

CREATE TABLE `tour` (
  `tourID` bigint(20) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `priceAdult` decimal(10,2) NOT NULL,
  `priceChild` decimal(10,2) NOT NULL,
  `destination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `availability` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: còn chỗ',
  `startDate` date NOT NULL,
  `endDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tour_images`
--

CREATE TABLE `tour_images` (
  `imageID` bigint(20) NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `imageURL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploadDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tour_itinerary`
--

CREATE TABLE `tour_itinerary` (
  `itineraryID` bigint(20) NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `dayNumber` int(11) NOT NULL,
  `activity` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `userID` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `userName` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `passWord` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phoneNumber` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `refresh_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_token_expires_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`userID`, `userName`, `passWord`, `phoneNumber`, `address`, `email`, `role_id`, `created_at`, `created_by`, `updated_at`, `updated_by`, `refresh_token`, `email_verified`, `verification_token`, `verification_token_expires_at`, `google_id`, `facebook_id`, `avatar_url`, `is_admin`, `is_active`) VALUES
('11899ae6-5c1f-4421-af06-c03f8000804a', 'johndoe1', '$2y$10$XTKV15Y95m5X1b7HpU4wAesWm6aE32GPDYyRhDLOOtjNUw.CuCr8C', NULL, NULL, 'john@gmail.com', 2, '2025-11-05 06:18:36', '11899ae6-5c1f-4421-af06-c03f8000804a', '2025-11-05 06:18:36', 'none', NULL, 0, NULL, NULL, '1234567890', NULL, NULL, 0, 1),
('4f841b20-f793-4c4a-a2aa-f33fb1553c14', 'testfacebookuser', '$2y$10$fpi5Tzb4kJ2GoVY7uCTzVehR5ktV5RzTwsG.Beu2p2oVE6VyC0S5e', NULL, NULL, 'testfb@facebook.com', 2, '2025-11-04 05:52:19', 'facebook', '2025-11-04 05:52:19', 'facebook', NULL, 1, NULL, NULL, NULL, '123456789012345', 'https://graph.facebook.com/123456789012345/picture', 0, 1),
('5003314f-b3b5-404c-b511-c114b2c65db2', 'googleuser', '$2y$10$Z2pHKsMxoVyhqx1CKWGt5uo5Z9i1tlYeCAEjz.FYwV2xfLXh8NAvu', NULL, NULL, 'google@example.com', 2, '2025-11-04 07:56:29', '5003314f-b3b5-404c-b511-c114b2c65db2', '2025-11-04 07:56:29', 'none', NULL, 0, NULL, NULL, '66a2bb09-03f8-4ea3-97c1-2f5eeecd1c2c', NULL, NULL, 0, 1),
('5d5e8dc5-eaa0-4849-a62a-95369a6343bb', 'testuser', '$2y$10$TOjWuf.PzgKASz.bbYmGP.gT2H9r3O38Pn5GN3pXZEvBO4VI3xE9u', '0123456789', '123 Test Street', 'test@example.com', 2, '2025-11-04 07:51:36', '5d5e8dc5-eaa0-4849-a62a-95369a6343bb', '2025-11-04 07:51:36', 'system', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 1),
('63e2fbe9-84ca-4cce-8851-342e060f38c2', 'johndoe2', '$2y$10$ft8MhHioXTbnNkUF6iI7bekY1kYojexmQYqHERXXREOsRwnuyWnPG', NULL, NULL, 'john@facebook.com', 2, '2025-11-05 06:18:58', '63e2fbe9-84ca-4cce-8851-342e060f38c2', '2025-11-05 06:18:58', 'none', NULL, 0, NULL, NULL, NULL, '1234567890', NULL, 0, 1),
('bf73e83a-3bdd-4f39-bd69-9199bb6d9491', 'johndoe', '$2y$10$Y/nMzGZ8MsMt.gHXEFVgCuShGf0mQHC123tdApRbZZLvFUuyBztTO', '+1234567890', '123 Main St, City', 'john@example.com', 2, '2025-11-05 06:12:27', 'bf73e83a-3bdd-4f39-bd69-9199bb6d9491', '2025-11-05 06:12:27', 'none', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 1),
('dc2f5dba-a419-4608-898d-45c62c149d63', 'facebookuser', '$2y$10$ewzw6393zQ97pd5W1NTnv.02LYe9si8nVdX8K6SK7uY6UFHod/Cji', '0987654321', '456 Updated Street', 'facebook@example.com', 2, '2025-11-04 07:57:01', 'dc2f5dba-a419-4608-898d-45c62c149d63', '2025-11-04 07:57:26', 'none', NULL, 0, NULL, NULL, NULL, '6c00886a-3b7a-42f4-a6fa-906b2d32af01', NULL, 0, 1),
('edf05e86-9ade-4fb3-acbe-72687fe9a4ad', 'testuser', '$2y$10$M.kHDfUTuQU9WhX8pyQ.B.XWNis6g6wn2OhIcSSmHvndrwkSOmC/6', '+84987654321', '123 Test Street, Hanoi, Vietnam', 'testuser@example.com', 2, '2025-11-08 01:11:45', 'testuser', '2025-11-08 01:19:40', 'none', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlist`
--

CREATE TABLE `wishlist` (
  `userID` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `tourID` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`bookingID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `tourID` (`tourID`);

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
-- Chỉ mục cho bảng `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`);

--
-- Chỉ mục cho bảng `permission_role`
--
ALTER TABLE `permission_role`
  ADD KEY `role_id` (`role_id`),
  ADD KEY `permission_id` (`permission_id`) USING BTREE;

--
-- Chỉ mục cho bảng `promotion`
--
ALTER TABLE `promotion`
  ADD PRIMARY KEY (`promotionID`);

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
  ADD KEY `role_id` (`role_id`);

--
-- Chỉ mục cho bảng `wishlist`
--
ALTER TABLE `wishlist`
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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
  MODIFY `tourID` bigint(20) NOT NULL AUTO_INCREMENT;

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
