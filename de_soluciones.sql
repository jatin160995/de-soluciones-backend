-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 20, 2026 at 10:39 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `de_soluciones`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(60) DEFAULT NULL,
  `recipient_name` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `line1` varchar(200) NOT NULL,
  `line2` varchar(200) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Honduras',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_commission_plans`
--

CREATE TABLE `agent_commission_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('flat_percent','volume_bonus','tiered_percent') NOT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`config`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agent_commission_plans`
--

INSERT INTO `agent_commission_plans` (`id`, `user_id`, `type`, `config`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 4, 'tiered_percent', '{\"deduction_percent\":15,\"threshold_lps\":1000,\"below_percent\":4,\"above_percent\":5}', 1, '2026-08-18 06:15:32', '2026-08-18 06:15:32');

-- --------------------------------------------------------

--
-- Table structure for table `agent_commission_statements`
--

CREATE TABLE `agent_commission_statements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `period_month` date NOT NULL,
  `delivered_sales_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `commission_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `commission_currency` char(3) NOT NULL DEFAULT 'HNL',
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `paid_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agent_commission_statements`
--

INSERT INTO `agent_commission_statements` (`id`, `user_id`, `period_month`, `delivered_sales_total`, `commission_amount`, `commission_currency`, `status`, `paid_at`, `paid_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, 4, '2026-08-01', 500.00, 17.00, 'HNL', 'pending', NULL, NULL, NULL, '2026-08-18 06:26:01', '2026-08-18 06:35:24');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('product','job','rental') NOT NULL DEFAULT 'product',
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `type`, `name`, `slug`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(2, NULL, 'product', 'Electronics', 'electronics', 0, 1, '2026-08-05 01:06:14', '2026-08-05 01:06:14');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(40) NOT NULL,
  `type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL,
  `max_uses` int(10) UNSIGNED DEFAULT NULL,
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `max_uses`, `used_count`, `starts_at`, `expires_at`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'percent', 10.00, NULL, 0, '2026-08-19 00:00:00', '2026-08-31 00:00:00', 1, '2026-08-19 03:23:52', '2026-08-19 03:23:52');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` varchar(150) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`manipulations`)),
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`custom_properties`)),
  `generated_conversions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`generated_conversions`)),
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responsive_images`)),
  `order_column` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `model_type`, `model_id`, `uuid`, `collection_name`, `name`, `file_name`, `mime_type`, `disk`, `conversions_disk`, `size`, `manipulations`, `custom_properties`, `generated_conversions`, `responsive_images`, `order_column`, `created_at`, `updated_at`) VALUES
(1, 'store', 1, '99f5079a-ee8f-4c1e-8a5f-0feb30b81b7c', 'logo', 'store-icon-logo-illustration-vector', '01KZ83EKZFVAHBREEDTWEAPE6A.jpg', 'image/jpeg', 'local', 'local', 26595, '[]', '{\"custom_headers\":{\"ContentType\":\"image\\/jpeg\"}}', '[]', '[]', 1, '2026-08-04 23:08:33', '2026-08-04 23:08:33'),
(2, 'category', 2, '6b347859-a130-4a1b-a9bf-30d81882ab78', 'image', 'store-icon-logo-illustration-vector', '01KZ8A63CM5R957HA0GM4NSWTH.jpg', 'image/jpeg', 'local', 'local', 23080, '[]', '{\"custom_headers\":{\"ContentType\":\"image\\/jpeg\"}}', '[]', '[]', 1, '2026-08-05 01:06:14', '2026-08-05 01:06:14'),
(3, 'product', 1, '16f44491-bc52-43ea-b365-943e4db423d6', 'images', '0dad72_040a526d32cf49308cb87ad8f2045fb5~mv2_jpg_v1_fit_w_500,h_500,q_90_file', '01KZ8E4WYRYY06E6JQAXZQAAP8.avif', 'image/avif', 'local', 'local', 24968, '[]', '{\"custom_headers\":{\"ContentType\":\"image\\/avif\"}}', '{\"thumb\":true}', '[]', 1, '2026-08-05 02:15:29', '2026-08-05 04:55:04'),
(6, 'product', 3, '9f26d1f9-b3c7-42c7-b43e-d7105c002618', 'images', 'HERVIDOR DE 7 HUEVO', '0dad72_d1ecd3a845da47848b2617b545382d01~mv2_jpeg_v1_fit_w_225,h_225,q_90_file.jpg', 'image/jpeg', 'public', 'public', 19282, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_d1ecd3a845da47848b2617b545382d01~mv2.jpeg\\/v1\\/fit\\/w_225,h_225,q_90\\/file.jpg\\/v1\\/fill\\/w_315,h_315,al_c,lg_1,q_80,enc_avif,quality_auto\\/0dad72_d1ecd3a845da47848b2617b545382d01~mv2_jpeg_v1_fit_w_225%2Ch_225%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:27', '2026-08-05 04:55:04'),
(7, 'product', 4, '1fd74c7e-a7a6-40f5-808f-294b76db9412', 'images', 'DISPENSADOR DE CEPILLO Y PASTA  CON DOS VASOS', '0dad72_872ec7144a3b427abee0bcfda378da18~mv2_jpeg_v1_fit_w_863,h_775,q_90_file.jpg', 'image/jpeg', 'public', 'public', 27291, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_872ec7144a3b427abee0bcfda378da18~mv2.jpeg\\/v1\\/fit\\/w_863,h_775,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_298,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_872ec7144a3b427abee0bcfda378da18~mv2_jpeg_v1_fit_w_863%2Ch_775%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:28', '2026-08-05 04:55:04'),
(8, 'product', 5, '346e8091-7166-4ba1-8263-e96e2878a581', 'images', 'TAZA AUTOAGITADORA CON IMAN MAGNETICO', '0dad72_4231a0ede1df4b0a8214dd0fb5b99324~mv2_jpeg_v1_fit_w_640,h_640,q_90_file.jpg', 'image/jpeg', 'public', 'public', 26657, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_4231a0ede1df4b0a8214dd0fb5b99324~mv2.jpeg\\/v1\\/fit\\/w_640,h_640,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_4231a0ede1df4b0a8214dd0fb5b99324~mv2_jpeg_v1_fit_w_640%2Ch_640%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:28', '2026-08-05 04:55:04'),
(9, 'product', 6, '48bb3b0e-6570-4b7e-9d38-812b38741b90', 'images', 'FRESH BREATH ESSENCE', '0dad72_b827d0eb4c2b4c749d6a7ceafb079f36~mv2_jpeg_v1_fit_w_183,h_275,q_90_file.jpg', 'image/jpeg', 'public', 'public', 28313, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_b827d0eb4c2b4c749d6a7ceafb079f36~mv2.jpeg\\/v1\\/fit\\/w_183,h_275,q_90\\/file.jpg\\/v1\\/fill\\/w_221,h_332,al_c,lg_1,q_80,enc_avif,quality_auto\\/0dad72_b827d0eb4c2b4c749d6a7ceafb079f36~mv2_jpeg_v1_fit_w_183%2Ch_275%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:29', '2026-08-05 04:55:04'),
(10, 'product', 7, 'bff0d78f-5f71-460b-b8c6-54f916ecc0cf', 'images', 'MINI PLANCHA PORTATIL', '0dad72_95c97ea0bf43452d8dc8f4f00b737fb3~mv2_jpeg_v1_fit_w_225,h_225,q_90_file.jpg', 'image/jpeg', 'public', 'public', 23273, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_95c97ea0bf43452d8dc8f4f00b737fb3~mv2.jpeg\\/v1\\/fit\\/w_225,h_225,q_90\\/file.jpg\\/v1\\/fill\\/w_315,h_315,al_c,lg_1,q_80,enc_avif,quality_auto\\/0dad72_95c97ea0bf43452d8dc8f4f00b737fb3~mv2_jpeg_v1_fit_w_225%2Ch_225%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:29', '2026-08-05 04:55:04'),
(11, 'product', 8, '33acb2d4-5248-4411-8072-9b1220aeca05', 'images', 'CORTACÉSPED INALÁMBRICO DE ALTA POTENCIA DE 20000 RPM', '0dad72_0c041a2b58ae49dbbc6b443d19dbffa6~mv2_webp_v1_fit_w_1080,h_1080,q_90_file.webp', 'image/webp', 'public', 'public', 38614, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_0c041a2b58ae49dbbc6b443d19dbffa6~mv2.webp\\/v1\\/fit\\/w_1080,h_1080,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_0c041a2b58ae49dbbc6b443d19dbffa6~mv2_webp_v1_fit_w_1080%2Ch_1080%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:30', '2026-08-05 04:55:04'),
(12, 'product', 9, '0a612dc0-4a15-40b9-aa71-e06e52c0fad7', 'images', 'PROTECTOR DE VOLTAJE 120V', '0dad72_6762c61d2ce74dc4a3b85635be72aeb3~mv2_png_v1_fit_w_800,h_800,q_90_file.png', 'image/png', 'public', 'public', 167374, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_6762c61d2ce74dc4a3b85635be72aeb3~mv2.png\\/v1\\/fit\\/w_800,h_800,q_90\\/file.png\\/v1\\/fill\\/w_332,h_332,al_c,q_85,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_6762c61d2ce74dc4a3b85635be72aeb3~mv2_png_v1_fit_w_800%2Ch_800%2Cq_90_file.png\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:31', '2026-08-05 04:55:04'),
(13, 'product', 10, '6b495e24-df59-4ec0-932c-c3a3df0fc499', 'images', 'MASAJEDOR ELÉCTRICO GEL', '0dad72_a810e55577444d3d92cf43ae8ce6071c~mv2_webp_v1_fit_w_600,h_600,q_90_file.webp', 'image/webp', 'public', 'public', 27322, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_a810e55577444d3d92cf43ae8ce6071c~mv2.webp\\/v1\\/fit\\/w_600,h_600,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_a810e55577444d3d92cf43ae8ce6071c~mv2_webp_v1_fit_w_600%2Ch_600%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:31', '2026-08-05 04:55:04'),
(15, 'product', 11, '6858549d-b02a-4d15-a857-5cdf1c1f126f', 'images', 'LUPA 2 LED', '0dad72_db9569ae490e491fa03935db32f62544~mv2_jpg_v1_fit_w_240,h_210,q_90_file.jpg', 'image/jpeg', 'public', 'public', 17366, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_db9569ae490e491fa03935db32f62544~mv2.jpg\\/v1\\/fit\\/w_240,h_210,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_291,al_c,lg_1,q_80,enc_avif,quality_auto\\/0dad72_db9569ae490e491fa03935db32f62544~mv2_jpg_v1_fit_w_240%2Ch_210%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:33', '2026-08-05 04:55:04'),
(16, 'product', 12, 'f85f5eac-59f9-48cb-988d-e1cd03c99075', 'images', 'LENTES PARA VISION', '0dad72_1b1f1cfe4b0c4c7db5e911c73e5c09d0~mv2_jpg_v1_fit_w_894,h_806,q_90_file.jpg', 'image/jpeg', 'public', 'public', 30962, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_1b1f1cfe4b0c4c7db5e911c73e5c09d0~mv2.jpg\\/v1\\/fit\\/w_894,h_806,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_299,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_1b1f1cfe4b0c4c7db5e911c73e5c09d0~mv2_jpg_v1_fit_w_894%2Ch_806%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:33', '2026-08-05 04:55:04'),
(17, 'product', 13, '1b400754-5c16-4d8e-95b9-a1a657e16992', 'images', 'STUND ROLLING CAR 360', '0dad72_504e05bc33044cc285ed32df203c76e5~mv2_jpg_v1_fit_w_960,h_1000,q_90_file.jpg', 'image/jpeg', 'public', 'public', 40016, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_504e05bc33044cc285ed32df203c76e5~mv2.jpg\\/v1\\/fit\\/w_960,h_1000,q_90\\/file.jpg\\/v1\\/fill\\/w_319,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_504e05bc33044cc285ed32df203c76e5~mv2_jpg_v1_fit_w_960%2Ch_1000%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:34', '2026-08-05 04:55:04'),
(18, 'product', 14, '992bd700-e931-4bad-b026-ea3f9521209d', 'images', 'ASTROANUTA SENTADO', '0dad72_d563acdbba854f8fbe2a120b9a8f24e0~mv2_jpg_v1_fit_w_960,h_959,q_90_file.jpg', 'image/jpeg', 'public', 'public', 46926, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_d563acdbba854f8fbe2a120b9a8f24e0~mv2.jpg\\/v1\\/fit\\/w_960,h_959,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_d563acdbba854f8fbe2a120b9a8f24e0~mv2_jpg_v1_fit_w_960%2Ch_959%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:35', '2026-08-05 04:55:04'),
(19, 'product', 15, '26a9846e-34be-4371-85e4-0a90fb763e63', 'images', 'ASTRONAUTA BASE DE LUNA', '0dad72_f8ebbe7cc11b40ff824ff52b9fc53623~mv2_jpg_v1_fit_w_535,h_535,q_90_file.jpg', 'image/jpeg', 'public', 'public', 49109, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_f8ebbe7cc11b40ff824ff52b9fc53623~mv2.jpg\\/v1\\/fit\\/w_535,h_535,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_f8ebbe7cc11b40ff824ff52b9fc53623~mv2_jpg_v1_fit_w_535%2Ch_535%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:35', '2026-08-05 04:55:04'),
(20, 'product', 16, '35af342f-b147-47d9-a7bb-894662c000a9', 'images', 'ASTRONAUTA DE BLUETHOTH', '0dad72_09a2a394567c4f249f69b50b97485ebb~mv2_jpg_v1_fit_w_1080,h_1080,q_90_file.jpg', 'image/jpeg', 'public', 'public', 23732, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_09a2a394567c4f249f69b50b97485ebb~mv2.jpg\\/v1\\/fit\\/w_1080,h_1080,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_09a2a394567c4f249f69b50b97485ebb~mv2_jpg_v1_fit_w_1080%2Ch_1080%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:36', '2026-08-05 04:55:04'),
(21, 'product', 17, 'c4fc2e59-7d02-4fdf-8e28-2924a3466b90', 'images', 'ASTRONAUTA PLAY BOY', '0dad72_78229d1c50b54715ad4227a8c500e20f~mv2_jpg_v1_fit_w_678,h_1000,q_90_file.jpg', 'image/jpeg', 'public', 'public', 19119, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_78229d1c50b54715ad4227a8c500e20f~mv2.jpg\\/v1\\/fit\\/w_678,h_1000,q_90\\/file.jpg\\/v1\\/fill\\/w_225,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_78229d1c50b54715ad4227a8c500e20f~mv2_jpg_v1_fit_w_678%2Ch_1000%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:37', '2026-08-05 04:55:04'),
(22, 'product', 18, 'cfa4a87d-3133-4d4d-bce4-b91ab77c515f', 'images', 'LIMPIADOR DE FARO', '0dad72_feee5226d3424c46a4eca8be3cd15300~mv2_jpg_v1_fit_w_640,h_640,q_90_file.jpg', 'image/jpeg', 'public', 'public', 36505, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_feee5226d3424c46a4eca8be3cd15300~mv2.jpg\\/v1\\/fit\\/w_640,h_640,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_feee5226d3424c46a4eca8be3cd15300~mv2_jpg_v1_fit_w_640%2Ch_640%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:37', '2026-08-05 04:55:04'),
(23, 'product', 19, 'dc9d20af-9987-4a85-ad0b-bbaefe730f11', 'images', 'SUJETADOR DE BUMPER', '0dad72_fb964556ff5e4482b3ab6b610bd025da~mv2_webp_v1_fit_w_1000,h_1000,q_90_file.webp', 'image/webp', 'public', 'public', 9964, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_fb964556ff5e4482b3ab6b610bd025da~mv2.webp\\/v1\\/fit\\/w_1000,h_1000,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_fb964556ff5e4482b3ab6b610bd025da~mv2_webp_v1_fit_w_1000%2Ch_1000%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:38', '2026-08-05 04:55:04'),
(24, 'product', 20, '62e7cf27-43a2-437a-b5f2-e9dd0d34e788', 'images', 'ENCHUFE USB AUTOMOVIL', '0dad72_51a9d7702986432bb3c8104e3abc213c~mv2_jpg_v1_fit_w_894,h_742,q_90_file.jpg', 'image/jpeg', 'public', 'public', 13664, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_51a9d7702986432bb3c8104e3abc213c~mv2.jpg\\/v1\\/fit\\/w_894,h_742,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_276,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_51a9d7702986432bb3c8104e3abc213c~mv2_jpg_v1_fit_w_894%2Ch_742%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:39', '2026-08-05 04:55:04'),
(25, 'product', 21, '637ade08-8217-4cf4-a994-e57828c0fc4b', 'images', 'CONVERTIDOR DE AUDIO', '0dad72_c5f8700a269d44928beaa8a095630793~mv2_jpg_v1_fit_w_535,h_535,q_90_file.jpg', 'image/jpeg', 'public', 'public', 23007, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_c5f8700a269d44928beaa8a095630793~mv2.jpg\\/v1\\/fit\\/w_535,h_535,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_c5f8700a269d44928beaa8a095630793~mv2_jpg_v1_fit_w_535%2Ch_535%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:39', '2026-08-05 04:55:04'),
(26, 'product', 22, '1c7988fc-6873-4602-8b8f-3971c1578df0', 'images', 'CONVERTIDOR DE RCA A VGA', '0dad72_27bdb15a3009421382339acaa646202d~mv2_jpg_v1_fit_w_970,h_600,q_90_file.jpg', 'image/jpeg', 'public', 'public', 20375, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_27bdb15a3009421382339acaa646202d~mv2.jpg\\/v1\\/fit\\/w_970,h_600,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_205,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_27bdb15a3009421382339acaa646202d~mv2_jpg_v1_fit_w_970%2Ch_600%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:40', '2026-08-05 04:55:04'),
(27, 'product', 23, 'bba6b529-c94f-477b-aa5e-93be27fb6f76', 'images', 'HIDROLAVADORA DE 48 V', '0dad72_165fbc04cd3d4b98997e58979850ad61~mv2_jpg_v1_fit_w_736,h_960,q_90_file.jpg', 'image/jpeg', 'public', 'public', 41823, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_165fbc04cd3d4b98997e58979850ad61~mv2.jpg\\/v1\\/fit\\/w_736,h_960,q_90\\/file.jpg\\/v1\\/fill\\/w_255,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_165fbc04cd3d4b98997e58979850ad61~mv2_jpg_v1_fit_w_736%2Ch_960%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:40', '2026-08-05 04:55:04'),
(28, 'product', 24, '414a2875-61df-4547-8b5d-7d58386fbb2c', 'images', 'CORRECTOR DE POSTURA SIBOTE', '0dad72_67455393323f436d9d71f18316796394~mv2_jpg_v1_fit_w_640,h_640,q_90_file.jpg', 'image/jpeg', 'public', 'public', 22319, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_67455393323f436d9d71f18316796394~mv2.jpg\\/v1\\/fit\\/w_640,h_640,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_67455393323f436d9d71f18316796394~mv2_jpg_v1_fit_w_640%2Ch_640%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:41', '2026-08-05 04:55:04'),
(29, 'product', 25, 'be4fa931-b094-4b72-b17a-a994f757bde4', 'images', 'PARLANTE G', '0dad72_d00140e834df48c39d3c354f34348de3~mv2_webp_v1_fit_w_500,h_413,q_90_file.webp', 'image/webp', 'public', 'public', 15360, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_d00140e834df48c39d3c354f34348de3~mv2.webp\\/v1\\/fit\\/w_500,h_413,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_274,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_d00140e834df48c39d3c354f34348de3~mv2_webp_v1_fit_w_500%2Ch_413%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:42', '2026-08-05 04:55:04'),
(30, 'product', 26, '6ec730f2-61c2-4067-8194-fb1361784664', 'images', 'SOPORTE DE MANO', '0dad72_4695b7af735b4fa9acc19bc21a5f70e8~mv2_jpg_v1_fit_w_1000,h_1000,q_90_file.jpg', 'image/jpeg', 'public', 'public', 25344, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_4695b7af735b4fa9acc19bc21a5f70e8~mv2.jpg\\/v1\\/fit\\/w_1000,h_1000,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_4695b7af735b4fa9acc19bc21a5f70e8~mv2_jpg_v1_fit_w_1000%2Ch_1000%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:42', '2026-08-05 04:55:04'),
(31, 'product', 27, '5c67b00d-8aee-4da8-831f-1bba8bd5b1eb', 'images', 'MUNEQUERA', '0dad72_a948f168742443aaa9c65baf2686b16a~mv2_jpg_v1_fit_w_1600,h_1600,q_90_file.jpg', 'image/jpeg', 'public', 'public', 23789, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_a948f168742443aaa9c65baf2686b16a~mv2.jpg\\/v1\\/fit\\/w_1600,h_1600,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_a948f168742443aaa9c65baf2686b16a~mv2_jpg_v1_fit_w_1600%2Ch_1600%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:43', '2026-08-05 04:55:04'),
(32, 'product', 28, '2f7f891b-fa7f-4ca5-bf95-dfed3777c2ff', 'images', 'WONDER PATCH', '0dad72_d65474ea687049409afe9639c200c92b~mv2_jpg_v1_fit_w_225,h_225,q_90_file.jpg', 'image/jpeg', 'public', 'public', 28359, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_d65474ea687049409afe9639c200c92b~mv2.jpg\\/v1\\/fit\\/w_225,h_225,q_90\\/file.jpg\\/v1\\/fill\\/w_315,h_315,al_c,lg_1,q_80,enc_avif,quality_auto\\/0dad72_d65474ea687049409afe9639c200c92b~mv2_jpg_v1_fit_w_225%2Ch_225%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:43', '2026-08-05 04:55:04'),
(33, 'product', 29, '0367eba8-4427-4dca-904c-24c42c1862c3', 'images', 'CORTADORA VEGETALES', '0dad72_0e3074e0f655496c95c40e69d77bc23a~mv2_jpg_v1_fit_w_421,h_550,q_90_file.jpg', 'image/jpeg', 'public', 'public', 24327, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_0e3074e0f655496c95c40e69d77bc23a~mv2.jpg\\/v1\\/fit\\/w_421,h_550,q_90\\/file.jpg\\/v1\\/fill\\/w_254,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_0e3074e0f655496c95c40e69d77bc23a~mv2_jpg_v1_fit_w_421%2Ch_550%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:44', '2026-08-05 04:55:04'),
(34, 'product', 30, '20dba8c1-d485-4a58-8589-e5f29702caa5', 'images', 'CODERA LARGA', '0dad72_eb0062bccc8a43a781620f44a0d59e55~mv2_jpg_v1_fit_w_1500,h_1500,q_90_file.jpg', 'image/jpeg', 'public', 'public', 27046, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_eb0062bccc8a43a781620f44a0d59e55~mv2.jpg\\/v1\\/fit\\/w_1500,h_1500,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_eb0062bccc8a43a781620f44a0d59e55~mv2_jpg_v1_fit_w_1500%2Ch_1500%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:45', '2026-08-05 04:55:04'),
(35, 'product', 31, '9347e7f6-4936-4f22-afc9-792769d0a216', 'images', 'RODILLERA LARGA', '0dad72_03e82522a67946f3b856a25ce915ea35~mv2_webp_v1_fit_w_540,h_540,q_90_file.webp', 'image/webp', 'public', 'public', 23260, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_03e82522a67946f3b856a25ce915ea35~mv2.webp\\/v1\\/fit\\/w_540,h_540,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_03e82522a67946f3b856a25ce915ea35~mv2_webp_v1_fit_w_540%2Ch_540%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:45', '2026-08-05 04:55:04'),
(36, 'product', 32, '16d85726-accf-45cf-9a00-4255b2c3dd73', 'images', 'CAMISA SAUNA MUJER', '0dad72_ce516768bf6f45b6982626998dd8bd83~mv2_jpg_v1_fit_w_617,h_720,q_90_file.jpg', 'image/jpeg', 'public', 'public', 31841, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_ce516768bf6f45b6982626998dd8bd83~mv2.jpg\\/v1\\/fit\\/w_617,h_720,q_90\\/file.jpg\\/v1\\/fill\\/w_285,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_ce516768bf6f45b6982626998dd8bd83~mv2_jpg_v1_fit_w_617%2Ch_720%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:46', '2026-08-05 04:55:04'),
(37, 'product', 33, '259c8eec-b864-4fcf-9421-ff41a05fbc26', 'images', 'PULVERIZADORA 450 W', '0dad72_6a0e22462d704cc0b6fac23c839d0af8~mv2_jpg_v1_fit_w_1080,h_864,q_90_file.jpg', 'image/jpeg', 'public', 'public', 27118, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_6a0e22462d704cc0b6fac23c839d0af8~mv2.jpg\\/v1\\/fit\\/w_1080,h_864,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_266,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_6a0e22462d704cc0b6fac23c839d0af8~mv2_jpg_v1_fit_w_1080%2Ch_864%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:47', '2026-08-05 04:55:04'),
(38, 'product', 34, 'ca8ddce8-9268-4677-8d57-7f93b7e3dcaf', 'images', 'PULVERIZADORA 550 W', '0dad72_965b1ba7e16b409fad1347a54f09e4ba~mv2_jpg_v1_fit_w_900,h_900,q_90_file.jpg', 'image/jpeg', 'public', 'public', 36859, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_965b1ba7e16b409fad1347a54f09e4ba~mv2.jpg\\/v1\\/fit\\/w_900,h_900,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_965b1ba7e16b409fad1347a54f09e4ba~mv2_jpg_v1_fit_w_900%2Ch_900%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:47', '2026-08-05 04:55:04'),
(39, 'product', 35, 'ee8506fb-9ead-470e-bcb0-d03714faaf60', 'images', 'LUCES NAVIDEÑAS DE 10m', '0dad72_0a9f237751674f188408b13571ee51ff~mv2_webp_v1_fit_w_500,h_402,q_90_file.webp', 'image/webp', 'public', 'public', 27494, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_0a9f237751674f188408b13571ee51ff~mv2.webp\\/v1\\/fit\\/w_500,h_402,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_267,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_0a9f237751674f188408b13571ee51ff~mv2_webp_v1_fit_w_500%2Ch_402%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:48', '2026-08-05 04:55:04'),
(40, 'product', 36, 'a2cb5bd8-ae0b-4194-a28f-78b44eda3109', 'images', 'MOLINO ELÉCTRICO 110', '0dad72_12da769f4c7f46cb83b81f5c4e2a25b9~mv2_webp_v1_fit_w_463,h_500,q_90_file.webp', 'image/webp', 'public', 'public', 9868, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_12da769f4c7f46cb83b81f5c4e2a25b9~mv2.webp\\/v1\\/fit\\/w_463,h_500,q_90\\/file.webp\\/v1\\/fill\\/w_307,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_12da769f4c7f46cb83b81f5c4e2a25b9~mv2_webp_v1_fit_w_463%2Ch_500%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:48', '2026-08-05 04:55:04'),
(41, 'product', 37, '36037c5b-c1dd-449d-92f7-15e0fbd04338', 'images', 'RODILLERA SILICON', '0dad72_25009c9b69b7490e8310b7da74231c17~mv2_webp_v1_fit_w_442,h_500,q_90_file.webp', 'image/webp', 'public', 'public', 23800, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_25009c9b69b7490e8310b7da74231c17~mv2.webp\\/v1\\/fit\\/w_442,h_500,q_90\\/file.webp\\/v1\\/fill\\/w_293,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_25009c9b69b7490e8310b7da74231c17~mv2_webp_v1_fit_w_442%2Ch_500%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:49', '2026-08-05 04:55:04'),
(42, 'product', 38, '71a43992-2634-4849-a92e-3856b2ab5838', 'images', 'CEPILLO 3 EN 1', '0dad72_85bcd33fb65149b683daabc1d581be09~mv2_webp_v1_fit_w_1000,h_1000,q_90_file.webp', 'image/webp', 'public', 'public', 21940, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_85bcd33fb65149b683daabc1d581be09~mv2.webp\\/v1\\/fit\\/w_1000,h_1000,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_85bcd33fb65149b683daabc1d581be09~mv2_webp_v1_fit_w_1000%2Ch_1000%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:50', '2026-08-05 04:55:04'),
(43, 'product', 39, '8ffba854-2c4a-4ef2-95a8-c94762bb880b', 'images', 'CHAPIADORA  24V', '0dad72_3f619f65196e483f9931240c8abeef70~mv2_jpg_v1_fit_w_335,h_335,q_90_file.jpg', 'image/jpeg', 'public', 'public', 45203, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_3f619f65196e483f9931240c8abeef70~mv2.jpg\\/v1\\/fit\\/w_335,h_335,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_3f619f65196e483f9931240c8abeef70~mv2_jpg_v1_fit_w_335%2Ch_335%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:50', '2026-08-05 04:55:04'),
(44, 'product', 40, '68e3e80b-d888-4dc7-9275-2b652165c82e', 'images', 'CINTA DOBLE CARA', '0dad72_599c9cb78aff49db98e133f65513f598~mv2_webp_v1_fit_w_612,h_612,q_90_file.webp', 'image/webp', 'public', 'public', 16124, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_599c9cb78aff49db98e133f65513f598~mv2.webp\\/v1\\/fit\\/w_612,h_612,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_599c9cb78aff49db98e133f65513f598~mv2_webp_v1_fit_w_612%2Ch_612%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:51', '2026-08-05 04:55:04'),
(45, 'product', 41, 'f8a0dfaf-4b6d-46d5-9ca3-ca3bdd443e5a', 'images', 'BARRA GAMER BLUETHHOTH', '0dad72_dbf26fa5c51c4bd4b11761d7667a0b96~mv2_jpg_v1_fit_w_225,h_225,q_90_file.jpg', 'image/jpeg', 'public', 'public', 14250, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_dbf26fa5c51c4bd4b11761d7667a0b96~mv2.jpg\\/v1\\/fit\\/w_225,h_225,q_90\\/file.jpg\\/v1\\/fill\\/w_315,h_315,al_c,lg_1,q_80,enc_avif,quality_auto\\/0dad72_dbf26fa5c51c4bd4b11761d7667a0b96~mv2_jpg_v1_fit_w_225%2Ch_225%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:52', '2026-08-05 04:55:04'),
(46, 'product', 42, '59be20d5-0607-4e39-8fdc-bf6ba453aa06', 'images', 'REGADERA PORTATIL', '0dad72_017346aa71e945908c1c2a089d09f359~mv2_jpg_v1_fit_w_225,h_225,q_90_file.jpg', 'image/jpeg', 'public', 'public', 23271, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_017346aa71e945908c1c2a089d09f359~mv2.jpg\\/v1\\/fit\\/w_225,h_225,q_90\\/file.jpg\\/v1\\/fill\\/w_315,h_315,al_c,lg_1,q_80,enc_avif,quality_auto\\/0dad72_017346aa71e945908c1c2a089d09f359~mv2_jpg_v1_fit_w_225%2Ch_225%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:52', '2026-08-05 04:55:04'),
(47, 'product', 43, '0f9a0c0c-e76a-4faa-9dbb-76577c5c508a', 'images', 'BLUETOOTH AUXILIAR PARA CARRO', '0dad72_f75cd82155d249dabb81325904609e19~mv2_jpg_v1_fit_w_600,h_600,q_90_file.jpg', 'image/jpeg', 'public', 'public', 21495, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_f75cd82155d249dabb81325904609e19~mv2.jpg\\/v1\\/fit\\/w_600,h_600,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_f75cd82155d249dabb81325904609e19~mv2_jpg_v1_fit_w_600%2Ch_600%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:53', '2026-08-05 04:55:04'),
(48, 'product', 44, '2f57a698-4f4a-4c87-83d6-2a5d168f8814', 'images', 'RODILLERA KNNE SUPPORT', '0dad72_93ee16aff86649a4bc0b9443493d8256~mv2_jpg_v1_fit_w_300,h_300,q_90_file.jpg', 'image/jpeg', 'public', 'public', 30786, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_93ee16aff86649a4bc0b9443493d8256~mv2.jpg\\/v1\\/fit\\/w_300,h_300,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,lg_1,q_80,enc_avif,quality_auto\\/0dad72_93ee16aff86649a4bc0b9443493d8256~mv2_jpg_v1_fit_w_300%2Ch_300%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:53', '2026-08-05 04:55:04'),
(49, 'product', 45, '7e7c0680-4530-4b77-8c64-ea5b08b67706', 'images', 'AMPLIFICADOR AUDITIVO PROFESIONAL', '0dad72_a7f7c9b4f6e84645ae9da67ab0bd9392~mv2_webp_v1_fit_w_500,h_494,q_90_file.webp', 'image/webp', 'public', 'public', 7818, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_a7f7c9b4f6e84645ae9da67ab0bd9392~mv2.webp\\/v1\\/fit\\/w_500,h_494,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_328,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_a7f7c9b4f6e84645ae9da67ab0bd9392~mv2_webp_v1_fit_w_500%2Ch_494%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:54', '2026-08-05 04:55:04'),
(50, 'product', 46, 'a258359b-65d5-4ccf-913e-f654844ae80d', 'images', 'AUDIFONO COMOSTEL', '0dad72_628e0c27eef946588c954a29069578b9~mv2_jpg_v1_fit_w_159,h_318,q_90_file.jpg', 'image/jpeg', 'public', 'public', 7540, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_628e0c27eef946588c954a29069578b9~mv2.jpg\\/v1\\/fit\\/w_159,h_318,q_90\\/file.jpg\\/v1\\/fill\\/w_166,h_332,al_c,lg_1,q_80,enc_avif,quality_auto\\/0dad72_628e0c27eef946588c954a29069578b9~mv2_jpg_v1_fit_w_159%2Ch_318%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:55', '2026-08-05 04:55:04'),
(51, 'product', 47, '841b4fd9-d4a6-4181-a4d2-3a2fa79b124b', 'images', 'LUZ DE BICICLETA RECARGABLE', '0dad72_884fba01eb6540e18c08bedc118ae8d3~mv2_jpg_v1_fit_w_1200,h_1200,q_90_file.jpg', 'image/jpeg', 'public', 'public', 22159, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_884fba01eb6540e18c08bedc118ae8d3~mv2.jpg\\/v1\\/fit\\/w_1200,h_1200,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_884fba01eb6540e18c08bedc118ae8d3~mv2_jpg_v1_fit_w_1200%2Ch_1200%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:55', '2026-08-05 04:55:04'),
(52, 'product', 48, 'f05fda81-2340-4ac7-8bfc-96db7c6bc485', 'images', 'FAJA DE POSTURA NEGRA UNISEX', '0dad72_bc097f75fb0449979d9d9377d1909e2f~mv2_webp_v1_fit_w_1920,h_1208,q_90_file.webp', 'image/webp', 'public', 'public', 16140, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_bc097f75fb0449979d9d9377d1909e2f~mv2.webp\\/v1\\/fit\\/w_1920,h_1208,q_90\\/file.webp\\/v1\\/fill\\/w_332,h_209,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_bc097f75fb0449979d9d9377d1909e2f~mv2_webp_v1_fit_w_1920%2Ch_1208%2Cq_90_file.webp\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:56', '2026-08-05 04:55:04'),
(53, 'product', 49, '8d55fddc-8fde-4ee3-b717-b17986df3ab0', 'images', 'FOCO RECARGABLE DE 7W', '0dad72_a986ceb5343b4b2585c658e9fae52b90~mv2_jpg_v1_fit_w_1070,h_1070,q_90_file.jpg', 'image/jpeg', 'public', 'public', 23674, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_a986ceb5343b4b2585c658e9fae52b90~mv2.jpg\\/v1\\/fit\\/w_1070,h_1070,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_a986ceb5343b4b2585c658e9fae52b90~mv2_jpg_v1_fit_w_1070%2Ch_1070%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:57', '2026-08-05 04:55:04'),
(54, 'product', 50, 'd45507c0-004a-442e-81be-52d0e107bafe', 'images', 'CORTADORA DE PELO', '0dad72_3ec16a62410f4fe4834f3e52266f111b~mv2_jpg_v1_fit_w_640,h_640,q_90_file.jpg', 'image/jpeg', 'public', 'public', 24868, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_3ec16a62410f4fe4834f3e52266f111b~mv2.jpg\\/v1\\/fit\\/w_640,h_640,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_3ec16a62410f4fe4834f3e52266f111b~mv2_jpg_v1_fit_w_640%2Ch_640%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:57', '2026-08-05 04:55:04'),
(55, 'product', 51, '4f3c35ca-3572-4d78-9484-ce3a87cb2429', 'images', 'PARLANTE DE GATO', '0dad72_b756f1fb31f440b98e03b704ed534a76~mv2_jpeg_v1_fit_w_600,h_600,q_90_file.jpg', 'image/jpeg', 'public', 'public', 26564, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_b756f1fb31f440b98e03b704ed534a76~mv2.jpeg\\/v1\\/fit\\/w_600,h_600,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_b756f1fb31f440b98e03b704ed534a76~mv2_jpeg_v1_fit_w_600%2Ch_600%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:58', '2026-08-05 04:55:04'),
(56, 'product', 52, '5f2f3331-e640-40b1-8c29-23c1aae180af', 'images', 'CHAPIADORA TOTAL 24V', '0dad72_28b593d27bf64dd18f01632e1f5b91c2~mv2_jpg_v1_fit_w_1027,h_1280,q_90_file.jpg', 'image/jpeg', 'public', 'public', 11808, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_28b593d27bf64dd18f01632e1f5b91c2~mv2.jpg\\/v1\\/fit\\/w_1027,h_1280,q_90\\/file.jpg\\/v1\\/fill\\/w_266,h_332,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_28b593d27bf64dd18f01632e1f5b91c2~mv2_jpg_v1_fit_w_1027%2Ch_1280%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:59', '2026-08-05 04:55:04'),
(57, 'product', 53, 'e4537571-528b-4832-8014-aa343e269ee3', 'images', 'VENTILADORA DE PC', '0dad72_6d06860e7efa4ab38a39e4ed99837bf3~mv2_jpg_v1_fit_w_822,h_652,q_90_file.jpg', 'image/jpeg', 'public', 'public', 7279, '[]', '{\"source_image_url\":\"https:\\/\\/static.wixstatic.com\\/media\\/0dad72_6d06860e7efa4ab38a39e4ed99837bf3~mv2.jpg\\/v1\\/fit\\/w_822,h_652,q_90\\/file.jpg\\/v1\\/fill\\/w_332,h_263,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto\\/0dad72_6d06860e7efa4ab38a39e4ed99837bf3~mv2_jpg_v1_fit_w_822%2Ch_652%2Cq_90_file.jpg\"}', '{\"thumb\":true}', '[]', 1, '2026-08-05 04:53:59', '2026-08-05 04:55:04'),
(58, 'product', 2, '4b9253b9-bdf9-4db7-9498-ec3b7e323f27', 'images', '0dad72_1dc54bd77c9a4eb480f8f47b5e05656d~mv2_jpeg_v1_fit_w_1280,h_1280,q_90_file', '01M0F41WP77AQKPFQEFN22XX2Q.jpg', 'image/jpeg', 'local', 'local', 16947, '[]', '{\"custom_headers\":{\"ContentType\":\"image\\/jpeg\"}}', '{\"thumb\":true}', '[]', 1, '2026-08-20 02:49:28', '2026-08-20 02:49:28');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'user', 1),
(1, 'user', 3),
(2, 'user', 2),
(3, 'user', 4);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sales_agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `shipping_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shipping_snapshot`)),
  `status` enum('pending','phone_verified','confirmed','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cod','card','paypal') NOT NULL DEFAULT 'cod',
  `customer_name` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `store_id`, `user_id`, `sales_agent_id`, `address_id`, `shipping_snapshot`, `status`, `subtotal`, `shipping_cost`, `discount_percent`, `discount_amount`, `total`, `payment_method`, `customer_name`, `customer_phone`, `customer_email`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'ORD-TEST-0001', 1, NULL, 4, NULL, NULL, 'delivered', 500.00, 0.00, 0.00, 0.00, 500.00, 'cod', 'Daniel Prueba', '+504 9999-0000', 'prueba@example.com', 'Pedido de prueba para verificar el observer', '2026-08-17 08:57:28', '2026-08-18 06:25:37', NULL),
(2, 'ORD-TEST-0002', 1, NULL, 4, NULL, NULL, 'confirmed', 500.00, 0.00, 5.00, 25.00, 475.00, 'cod', 'Jatin Raja', '+504 9999-1212', 'jatin@example.com', 'Pedido de prueba para verificar el observer', '2026-08-19 03:23:25', '2026-08-19 03:03:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(180) NOT NULL,
  `variant_attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variant_attributes`)),
  `sku` varchar(80) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `variant_id`, `product_name`, `variant_attributes`, `sku`, `unit_price`, `quantity`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Producto de Prueba', '{\"size\":\"M\",\"color\":\"Azul\"}', 'SKU-TEST-001', 250.00, 2, 500.00, '2026-08-17 09:17:06', '2026-08-17 09:17:06');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL,
  `note` text DEFAULT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `note`, `changed_by`, `created_at`) VALUES
(1, 1, 'confirmed', 'Cambiado de \"delivered\" a \"confirmed\"', 1, '2026-08-17 03:42:20'),
(2, 1, 'delivered', 'Test Note', 1, '2026-08-17 08:08:12'),
(3, 1, 'confirmed', 'Cambiado de \"delivered\" a \"confirmed\"', 1, '2026-08-17 08:08:23'),
(4, 1, 'shipped', 'Cambiado de \"confirmed\" a \"shipped\"', 1, '2026-08-17 08:08:37'),
(5, 1, 'returned', 'Cambiado de \"shipped\" a \"returned\"', 1, '2026-08-17 21:45:18'),
(6, 1, 'shipped', 'Cambiado de \"returned\" a \"shipped\"', 1, '2026-08-17 21:45:35'),
(7, 1, 'returned', 'Cambiado de \"shipped\" a \"returned\"', 1, '2026-08-17 21:47:22'),
(8, 1, 'cancelled', 'Cambiado de \"returned\" a \"cancelled\"', 1, '2026-08-17 21:47:39'),
(9, 1, 'returned', 'Cambiado de \"cancelled\" a \"returned\"', 1, '2026-08-17 21:48:05'),
(10, 1, 'delivered', 'Cambiado de \"returned\" a \"delivered\"', 4, '2026-08-18 06:25:37'),
(11, 2, 'confirmed', 'confirming for testing assigning process\n', 4, '2026-08-18 21:55:04');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `code` varchar(10) NOT NULL,
  `purpose` enum('order_verification','account_verification','password_reset') NOT NULL DEFAULT 'order_verification',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `gateway` enum('cod','stripe','paypal') NOT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'HNL',
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_response`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'ViewAny:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(2, 'View:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(3, 'Create:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(4, 'Update:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(5, 'Delete:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(6, 'DeleteAny:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(7, 'Restore:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(8, 'ForceDelete:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(9, 'ForceDeleteAny:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(10, 'RestoreAny:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(11, 'Replicate:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(12, 'Reorder:Role', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(13, 'ViewAny:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(14, 'View:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(15, 'Create:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(16, 'Update:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(17, 'Delete:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(18, 'DeleteAny:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(19, 'Restore:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(20, 'ForceDelete:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(21, 'ForceDeleteAny:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(22, 'RestoreAny:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(23, 'Replicate:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(24, 'Reorder:User', 'web', '2026-08-04 22:49:53', '2026-08-04 22:49:53'),
(25, 'ViewAny:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(26, 'View:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(27, 'Create:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(28, 'Update:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(29, 'Delete:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(30, 'DeleteAny:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(31, 'Restore:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(32, 'ForceDelete:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(33, 'ForceDeleteAny:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(34, 'RestoreAny:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(35, 'Replicate:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(36, 'Reorder:Category', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(37, 'ViewAny:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(38, 'View:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(39, 'Create:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(40, 'Update:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(41, 'Delete:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(42, 'DeleteAny:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(43, 'Restore:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(44, 'ForceDelete:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(45, 'ForceDeleteAny:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(46, 'RestoreAny:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(47, 'Replicate:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(48, 'Reorder:Store', 'web', '2026-08-05 01:01:48', '2026-08-05 01:01:48'),
(49, 'manage_commissions', 'web', '2026-08-18 04:01:14', '2026-08-18 04:01:14'),
(50, 'ViewAny:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(51, 'View:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(52, 'Create:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(53, 'Update:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(54, 'Delete:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(55, 'ForceDeleteAny:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(56, 'DeleteAny:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(57, 'RestoreAny:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(58, 'Restore:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(59, 'ForceDelete:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(60, 'Reorder:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(61, 'Replicate:Order', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17'),
(62, 'ViewAny:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(63, 'View:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(64, 'Create:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(65, 'Update:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(66, 'Delete:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(67, 'DeleteAny:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(68, 'Restore:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(69, 'ForceDelete:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(70, 'ForceDeleteAny:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(71, 'RestoreAny:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(72, 'Replicate:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(73, 'Reorder:Product', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(74, 'View:MyCommissions', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(75, 'View:AgentCommissionWidget', 'web', '2026-08-19 03:19:47', '2026-08-19 03:19:47'),
(76, 'ViewAny:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(77, 'View:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(78, 'Create:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(79, 'Update:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(80, 'Delete:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(81, 'DeleteAny:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(82, 'Restore:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(83, 'ForceDelete:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(84, 'ForceDeleteAny:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(85, 'RestoreAny:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(86, 'Replicate:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28'),
(87, 'Reorder:Coupon', 'web', '2026-08-19 03:50:28', '2026-08-19 03:50:28');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discounted_price` decimal(10,2) DEFAULT NULL,
  `status` enum('draft','active','inactive') NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `store_id`, `category_id`, `name`, `slug`, `description`, `base_price`, `discounted_price`, `status`, `is_featured`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'CAR FM PLAYER', 'car-fm-player', 'Imported from: https://www.de-soluciones.com/product-page/car-fm-player', 380.00, NULL, 'active', 1, '2026-08-05 02:15:29', '2026-08-20 02:46:20', NULL),
(2, 1, NULL, 'CORRECTORES DE JUANETES', 'correctores-de-juanetes', 'Imported from: https://www.de-soluciones.com/product-page/correctores-de-juanetes', 800.00, NULL, 'active', 1, '2026-08-05 04:53:25', '2026-08-20 02:47:09', NULL),
(3, 1, NULL, 'HERVIDOR DE 7 HUEVO', 'hervidor-de-7-huevo', 'Imported from: https://www.de-soluciones.com/product-page/hervidor-de-7-huevo', 550.00, NULL, 'active', 0, '2026-08-05 04:53:26', '2026-08-05 04:53:26', NULL),
(4, 1, NULL, 'DISPENSADOR DE CEPILLO Y PASTA  CON DOS VASOS', 'dispensador-de-cepillo-y-pasta-con-dos-vasos', 'Imported from: https://www.de-soluciones.com/product-page/dispensador-de-cepillo-y-pasta-con-dos-vasos', 750.00, NULL, 'active', 0, '2026-08-05 04:53:27', '2026-08-05 04:53:27', NULL),
(5, 1, NULL, 'TAZA AUTOAGITADORA CON IMAN MAGNETICO', 'taza-autoagitadora-con-iman-magnetico', 'Imported from: https://www.de-soluciones.com/product-page/taza-autoagitadora-con-iman-magnetico', 650.00, NULL, 'active', 0, '2026-08-05 04:53:28', '2026-08-05 04:53:28', NULL),
(6, 1, NULL, 'FRESH BREATH ESSENCE', 'fresh-breath-essence', 'Imported from: https://www.de-soluciones.com/product-page/fresh-breath-essence', 750.00, NULL, 'active', 0, '2026-08-05 04:53:28', '2026-08-05 04:53:28', NULL),
(7, 1, NULL, 'MINI PLANCHA PORTATIL', 'mini-plancha-portatil', 'Imported from: https://www.de-soluciones.com/product-page/mini-plancha-portatil', 650.00, NULL, 'active', 1, '2026-08-05 04:53:29', '2026-08-20 02:47:20', NULL),
(8, 1, NULL, 'CORTACÉSPED INALÁMBRICO DE ALTA POTENCIA DE 20000 RPM', 'cortacesped-inalambrico-de-alta-potencia-de-20000-rpm', 'Imported from: https://www.de-soluciones.com/product-page/cortac%C3%A9sped-inal%C3%A1mbrico-de-alta-potencia-de-20000-rpm', 1000.00, NULL, 'active', 0, '2026-08-05 04:53:29', '2026-08-05 04:53:29', NULL),
(9, 1, NULL, 'PROTECTOR DE VOLTAJE 120V', 'protector-de-voltaje-120v', 'Imported from: https://www.de-soluciones.com/product-page/protector-de-voltaje-120v', 380.00, NULL, 'active', 1, '2026-08-05 04:53:30', '2026-08-20 02:47:28', NULL),
(10, 1, NULL, 'MASAJEDOR ELÉCTRICO GEL', 'masajedor-electrico-gel', 'Imported from: https://www.de-soluciones.com/product-page/masajedor-el%C3%A9ctrico-gel', 380.00, NULL, 'active', 0, '2026-08-05 04:53:31', '2026-08-05 04:53:31', NULL),
(11, 1, NULL, 'LUPA 2 LED', 'lupa-2-led', 'Imported from: https://www.de-soluciones.com/product-page/lupa-2-led', 280.00, NULL, 'active', 0, '2026-08-05 04:53:32', '2026-08-05 04:53:32', NULL),
(12, 1, NULL, 'LENTES PARA VISION', 'lentes-para-vision', 'Imported from: https://www.de-soluciones.com/product-page/lentes-para-vision', 300.00, NULL, 'active', 0, '2026-08-05 04:53:33', '2026-08-05 04:53:33', NULL),
(13, 1, NULL, 'STUND ROLLING CAR 360', 'stund-rolling-car-360', 'Imported from: https://www.de-soluciones.com/product-page/stund-rolling-car-360', 320.00, NULL, 'active', 0, '2026-08-05 04:53:33', '2026-08-05 04:53:33', NULL),
(14, 1, NULL, 'ASTROANUTA SENTADO', 'astroanuta-sentado', 'Imported from: https://www.de-soluciones.com/product-page/astroanuta-sentado', 1000.00, NULL, 'active', 0, '2026-08-05 04:53:34', '2026-08-05 04:53:34', NULL),
(15, 1, NULL, 'ASTRONAUTA BASE DE LUNA', 'astronauta-base-de-luna', 'Imported from: https://www.de-soluciones.com/product-page/astronauta-base-de-luna', 1000.00, NULL, 'active', 0, '2026-08-05 04:53:35', '2026-08-05 04:53:35', NULL),
(16, 1, NULL, 'ASTRONAUTA DE BLUETHOTH', 'astronauta-de-bluethoth', 'Imported from: https://www.de-soluciones.com/product-page/astronauta-de-bluethoth', 1150.00, NULL, 'active', 0, '2026-08-05 04:53:35', '2026-08-05 04:53:35', NULL),
(17, 1, NULL, 'ASTRONAUTA PLAY BOY', 'astronauta-play-boy', 'Imported from: https://www.de-soluciones.com/product-page/astronauta-play-boy', 1100.00, NULL, 'active', 0, '2026-08-05 04:53:36', '2026-08-05 04:53:36', NULL),
(18, 1, NULL, 'LIMPIADOR DE FARO', 'limpiador-de-faro', 'Imported from: https://www.de-soluciones.com/product-page/limpiador-de-faro', 700.00, NULL, 'active', 0, '2026-08-05 04:53:37', '2026-08-05 04:53:37', NULL),
(19, 1, NULL, 'SUJETADOR DE BUMPER', 'sujetador-de-bumper', 'Imported from: https://www.de-soluciones.com/product-page/sujetador-de-bumper', 380.00, NULL, 'active', 0, '2026-08-05 04:53:37', '2026-08-05 04:53:37', NULL),
(20, 1, NULL, 'ENCHUFE USB AUTOMOVIL', 'enchufe-usb-automovil', 'Imported from: https://www.de-soluciones.com/product-page/enchufe-usb-automovil', 550.00, NULL, 'active', 0, '2026-08-05 04:53:38', '2026-08-05 04:53:38', NULL),
(21, 1, NULL, 'CONVERTIDOR DE AUDIO', 'convertidor-de-audio', 'Imported from: https://www.de-soluciones.com/product-page/convertidor-de-audio', 550.00, NULL, 'active', 0, '2026-08-05 04:53:39', '2026-08-05 04:53:39', NULL),
(22, 1, NULL, 'CONVERTIDOR DE RCA A VGA', 'convertidor-de-rca-a-vga', 'Imported from: https://www.de-soluciones.com/product-page/convertidor-de-rca-a-vga', 380.00, NULL, 'active', 0, '2026-08-05 04:53:39', '2026-08-05 04:53:39', NULL),
(23, 1, NULL, 'HIDROLAVADORA DE 48 V', 'hidrolavadora-de-48-v', 'Imported from: https://www.de-soluciones.com/product-page/hidrolavadora-de-48-v', 1360.00, 1156.00, 'active', 0, '2026-08-05 04:53:40', '2026-08-05 04:53:40', NULL),
(24, 1, NULL, 'CORRECTOR DE POSTURA SIBOTE', 'corrector-de-postura-sibote', 'Imported from: https://www.de-soluciones.com/product-page/corrector-de-postura-sibote', 389.00, NULL, 'active', 0, '2026-08-05 04:53:40', '2026-08-05 04:53:40', NULL),
(25, 1, NULL, 'PARLANTE G', 'parlante-g', 'Imported from: https://www.de-soluciones.com/product-page/parlante-g', 560.00, NULL, 'active', 0, '2026-08-05 04:53:41', '2026-08-05 04:53:41', NULL),
(26, 1, NULL, 'SOPORTE DE MANO', 'soporte-de-mano', 'Imported from: https://www.de-soluciones.com/product-page/soporte-de-mano', 380.00, NULL, 'active', 0, '2026-08-05 04:53:42', '2026-08-05 04:53:42', NULL),
(27, 1, NULL, 'MUNEQUERA', 'munequera', 'Imported from: https://www.de-soluciones.com/product-page/munequera', 380.00, NULL, 'active', 0, '2026-08-05 04:53:42', '2026-08-05 04:53:42', NULL),
(28, 1, NULL, 'WONDER PATCH', 'wonder-patch', 'Imported from: https://www.de-soluciones.com/product-page/wonder-patch', 190.00, NULL, 'active', 0, '2026-08-05 04:53:43', '2026-08-05 04:53:43', NULL),
(29, 1, NULL, 'CORTADORA VEGETALES', 'cortadora-vegetales', 'Imported from: https://www.de-soluciones.com/product-page/cortadora-vegetales', 600.00, NULL, 'active', 0, '2026-08-05 04:53:43', '2026-08-05 04:53:43', NULL),
(30, 1, NULL, 'CODERA LARGA', 'codera-larga', 'Imported from: https://www.de-soluciones.com/product-page/codera-larga', 380.00, NULL, 'active', 0, '2026-08-05 04:53:44', '2026-08-05 04:53:44', NULL),
(31, 1, NULL, 'RODILLERA LARGA', 'rodillera-larga', 'Imported from: https://www.de-soluciones.com/product-page/rodillera-larga', 380.00, NULL, 'active', 0, '2026-08-05 04:53:45', '2026-08-05 04:53:45', NULL),
(32, 1, NULL, 'CAMISA SAUNA MUJER', 'camisa-sauna-mujer', 'Imported from: https://www.de-soluciones.com/product-page/camisa-sauna-mujer', 450.00, NULL, 'active', 0, '2026-08-05 04:53:46', '2026-08-05 04:53:46', NULL),
(33, 1, NULL, 'PULVERIZADORA 450 W', 'pulverizadora-450-w', 'Imported from: https://www.de-soluciones.com/product-page/pulverizadora-450-w', 1250.00, NULL, 'active', 0, '2026-08-05 04:53:46', '2026-08-05 04:53:46', NULL),
(34, 1, NULL, 'PULVERIZADORA 550 W', 'pulverizadora-550-w', 'Imported from: https://www.de-soluciones.com/product-page/pulverizadora-550-w', 1300.00, NULL, 'active', 0, '2026-08-05 04:53:47', '2026-08-05 04:53:47', NULL),
(35, 1, NULL, 'LUCES NAVIDEÑAS DE 10m', 'luces-navidenas-de-10m', 'Imported from: https://www.de-soluciones.com/product-page/luces-navide%C3%B1as-de-10m', 300.00, NULL, 'active', 0, '2026-08-05 04:53:47', '2026-08-05 04:53:47', NULL),
(36, 1, NULL, 'MOLINO ELÉCTRICO 110', 'molino-electrico-110', 'Imported from: https://www.de-soluciones.com/product-page/molino-el%C3%A9ctrico-110', 650.00, NULL, 'active', 0, '2026-08-05 04:53:48', '2026-08-05 04:53:48', NULL),
(37, 1, NULL, 'RODILLERA SILICON', 'rodillera-silicon', 'Imported from: https://www.de-soluciones.com/product-page/rodillera-silicon', 550.00, NULL, 'active', 0, '2026-08-05 04:53:48', '2026-08-05 04:53:48', NULL),
(38, 1, NULL, 'CEPILLO 3 EN 1', 'cepillo-3-en-1', 'Imported from: https://www.de-soluciones.com/product-page/cepillo-3-en-1', 480.00, NULL, 'active', 0, '2026-08-05 04:53:49', '2026-08-05 04:53:49', NULL),
(39, 1, NULL, 'CHAPIADORA  24V', 'chapiadora-24v', 'Imported from: https://www.de-soluciones.com/product-page/chapiadora-24v', 1000.00, NULL, 'active', 0, '2026-08-05 04:53:50', '2026-08-05 04:53:50', NULL),
(40, 1, NULL, 'CINTA DOBLE CARA', 'cinta-doble-cara', 'Imported from: https://www.de-soluciones.com/product-page/cinta-doble-cara', 250.00, NULL, 'active', 0, '2026-08-05 04:53:50', '2026-08-05 04:53:50', NULL),
(41, 1, NULL, 'BARRA GAMER BLUETHHOTH', 'barra-gamer-bluethhoth', 'Imported from: https://www.de-soluciones.com/product-page/barra-gamer-bluethhoth', 650.00, NULL, 'active', 0, '2026-08-05 04:53:51', '2026-08-05 04:53:51', NULL),
(42, 1, NULL, 'REGADERA PORTATIL', 'regadera-portatil', 'Imported from: https://www.de-soluciones.com/product-page/regadera-portatil', 650.00, NULL, 'active', 0, '2026-08-05 04:53:52', '2026-08-05 04:53:52', NULL),
(43, 1, NULL, 'BLUETOOTH AUXILIAR PARA CARRO', 'bluetooth-auxiliar-para-carro', 'Imported from: https://www.de-soluciones.com/product-page/bluetooth-auxiliar-para-carro', 280.00, NULL, 'active', 0, '2026-08-05 04:53:52', '2026-08-05 04:53:52', NULL),
(44, 1, NULL, 'RODILLERA KNNE SUPPORT', 'rodillera-knne-support', 'Imported from: https://www.de-soluciones.com/product-page/rodillera-knne-support', 280.00, NULL, 'active', 0, '2026-08-05 04:53:53', '2026-08-05 04:53:53', NULL),
(45, 1, NULL, 'AMPLIFICADOR AUDITIVO PROFESIONAL', 'amplificador-auditivo-profesional', 'Imported from: https://www.de-soluciones.com/product-page/amplificador-auditivo-profesional', 2800.00, 1960.00, 'active', 0, '2026-08-05 04:53:54', '2026-08-05 04:53:54', NULL),
(46, 1, NULL, 'AUDIFONO COMOSTEL', 'audifono-comostel', 'Imported from: https://www.de-soluciones.com/product-page/audifono-somostel', 400.00, NULL, 'active', 0, '2026-08-05 04:53:54', '2026-08-05 04:53:54', NULL),
(47, 1, NULL, 'LUZ DE BICICLETA RECARGABLE', 'luz-de-bicicleta-recargable', 'Imported from: https://www.de-soluciones.com/product-page/luz-de-bicicleta-recargable', 380.00, NULL, 'active', 0, '2026-08-05 04:53:55', '2026-08-05 04:53:55', NULL),
(48, 1, NULL, 'FAJA DE POSTURA NEGRA UNISEX', 'faja-de-postura-negra-unisex', 'Imported from: https://www.de-soluciones.com/product-page/corrector-de-postura-espalda-lumbar-unisex', 480.00, NULL, 'active', 0, '2026-08-05 04:53:55', '2026-08-05 04:53:55', NULL),
(49, 1, NULL, 'FOCO RECARGABLE DE 7W', 'foco-recargable-de-7w', 'Imported from: https://www.de-soluciones.com/product-page/foco-recargable-de-7w', 95.00, NULL, 'active', 0, '2026-08-05 04:53:56', '2026-08-05 04:53:56', NULL),
(50, 1, NULL, 'CORTADORA DE PELO', 'cortadora-de-pelo', 'Imported from: https://www.de-soluciones.com/product-page/cortadora-de-pelo', 500.00, NULL, 'active', 0, '2026-08-05 04:53:57', '2026-08-05 04:53:57', NULL),
(51, 1, NULL, 'PARLANTE DE GATO', 'parlante-de-gato', 'Imported from: https://www.de-soluciones.com/product-page/parlante-de-gato', 380.00, NULL, 'active', 0, '2026-08-05 04:53:57', '2026-08-05 04:53:57', NULL),
(52, 1, NULL, 'CHAPIADORA TOTAL 24V', 'chapiadora-total-24v', 'Imported from: https://www.de-soluciones.com/product-page/chapiadora-total-24v', 1350.00, 1012.50, 'active', 0, '2026-08-05 04:53:58', '2026-08-05 04:53:58', NULL),
(53, 1, NULL, 'VENTILADORA DE PC', 'ventiladora-de-pc', 'Imported from: https://www.de-soluciones.com/product-page/ventiladora-de-pc', 480.00, NULL, 'active', 0, '2026-08-05 04:53:59', '2026-08-05 04:53:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `alt_text` varchar(180) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(80) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `attr_size` varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`attributes`,'$.size'))) STORED,
  `attr_color` varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`attributes`,'$.color'))) STORED,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `price`, `discounted_price`, `stock_quantity`, `attributes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'M1-SKU', 380.00, 360.00, 10, '{\"Processor\":\"M1\",\"size\":\"13\\\"\",\"color\":\"Mid night\"}', 1, '2026-08-05 03:37:32', '2026-08-05 03:55:32');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-08-04 22:31:57', '2026-08-04 22:31:57'),
(2, 'panel_user', 'web', '2026-08-04 22:34:35', '2026-08-04 22:34:35'),
(3, 'sales_agent', 'web', '2026-08-18 06:12:17', '2026-08-18 06:12:17');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(50, 3),
(51, 1),
(51, 3),
(52, 1),
(52, 3),
(53, 1),
(53, 3),
(54, 1),
(54, 3),
(55, 1),
(55, 3),
(56, 1),
(56, 3),
(57, 1),
(57, 3),
(58, 1),
(58, 3),
(59, 1),
(59, 3),
(60, 1),
(60, 3),
(61, 1),
(61, 3),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1);

-- --------------------------------------------------------

--
-- Table structure for table `seo_meta`
--

CREATE TABLE `seo_meta` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seoable_type` varchar(150) NOT NULL,
  `seoable_id` bigint(20) UNSIGNED NOT NULL,
  `meta_title` varchar(160) DEFAULT NULL,
  `meta_description` varchar(320) DEFAULT NULL,
  `og_image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seo_meta`
--

INSERT INTO `seo_meta` (`id`, `seoable_type`, `seoable_id`, `meta_title`, `meta_description`, `og_image_path`, `created_at`, `updated_at`) VALUES
(1, 'product', 1, NULL, NULL, NULL, '2026-08-20 02:46:20', '2026-08-20 02:46:20'),
(2, 'product', 2, NULL, NULL, NULL, '2026-08-20 02:47:09', '2026-08-20 02:47:09'),
(3, 'product', 7, NULL, NULL, NULL, '2026-08-20 02:47:20', '2026-08-20 02:47:20'),
(4, 'product', 9, NULL, NULL, NULL, '2026-08-20 02:47:28', '2026-08-20 02:47:28');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `owner_user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(170) NOT NULL,
  `description` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(30) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `owner_user_id`, `name`, `slug`, `description`, `logo_path`, `whatsapp_number`, `status`, `commission_rate`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 'Main Store', 'main-store', NULL, NULL, NULL, 'active', 0.00, '2026-08-04 23:08:33', '2026-08-04 23:08:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `store_settings`
--

CREATE TABLE `store_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `store_users`
--

CREATE TABLE `store_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_in_store` enum('owner','manager','sales_agent') NOT NULL DEFAULT 'sales_agent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telescope_entries`
--

CREATE TABLE `telescope_entries` (
  `sequence` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `batch_id` char(36) NOT NULL,
  `family_hash` varchar(255) DEFAULT NULL,
  `should_display_on_index` tinyint(1) NOT NULL DEFAULT 1,
  `type` varchar(20) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telescope_entries_tags`
--

CREATE TABLE `telescope_entries_tags` (
  `entry_uuid` char(36) NOT NULL,
  `tag` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telescope_monitoring`
--

CREATE TABLE `telescope_monitoring` (
  `tag` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `phone_verified_at`, `status`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin', 'superadmin@gmail.com', NULL, '$2y$12$GJItfVnINIJpBJ0toyXgvOOcdelyMgR0BP.I1sgeE3EUk8bieJkxO', NULL, NULL, 'active', 'GzcWlN5el6f6CMDAz7WoUUvitq3JFYu0Cm9wQVOODTKtOfzj808SWLWJ0mUF', '2026-08-04 22:29:19', '2026-08-04 22:29:19', NULL),
(2, 'Staff', 'staff@gmail.com', NULL, '$2y$12$1dn11vb.FsWP8zRt6Vgb3uDfoz3aNwC.mW1.h/p4HwwWpjEpw6UW.', NULL, NULL, 'active', NULL, '2026-08-04 22:51:18', '2026-08-04 22:51:18', NULL),
(3, 'Main Store Manager', 'mainstore@gmail.com', NULL, '$2y$12$AvlckO0ZeQvapwVLgPgJ0.i5iZrlLw2hkRIr5Ui3UX3dmUlwq/22q', NULL, NULL, 'active', NULL, '2026-08-04 23:07:25', '2026-08-04 23:07:25', NULL),
(4, 'Sales Agent', 'sales@gmail.com', NULL, '$2y$12$XgXsr76eNHu9enKBy3joMO5ofKDNLb5Z.DTzx86YP8oCatVz27hbu', NULL, NULL, 'active', NULL, '2026-08-18 06:15:32', '2026-08-18 06:15:32', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_idx` (`user_id`);

--
-- Indexes for table `agent_commission_plans`
--
ALTER TABLE `agent_commission_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `agent_commission_plans_user_unique` (`user_id`);

--
-- Indexes for table `agent_commission_statements`
--
ALTER TABLE `agent_commission_statements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `agent_commission_statements_user_period_unique` (`user_id`,`period_month`),
  ADD KEY `agent_commission_statements_status_idx` (`status`),
  ADD KEY `agent_commission_statements_paid_by_fk` (`paid_by`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carts_user_idx` (`user_id`),
  ADD KEY `carts_session_idx` (`session_token`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_cart_variant_unique` (`cart_id`,`variant_id`),
  ADD KEY `cart_items_variant_idx` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`),
  ADD KEY `categories_parent_idx` (`parent_id`),
  ADD KEY `categories_type_idx` (`type`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_movements_variant_idx` (`variant_id`),
  ADD KEY `inventory_movements_user_fk` (`created_by`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_uuid_unique` (`uuid`),
  ADD KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `media_order_column_index` (`order_column`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_store_idx` (`store_id`),
  ADD KEY `orders_user_idx` (`user_id`),
  ADD KEY `orders_agent_idx` (`sales_agent_id`),
  ADD KEY `orders_status_idx` (`status`),
  ADD KEY `orders_address_fk` (`address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_idx` (`order_id`),
  ADD KEY `order_items_variant_idx` (`variant_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_status_history_order_idx` (`order_id`),
  ADD KEY `order_status_history_user_fk` (`changed_by`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otp_verifications_user_idx` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_order_idx` (`order_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_store_slug_unique` (`store_id`,`slug`),
  ADD KEY `products_category_idx` (`category_id`),
  ADD KEY `products_status_idx` (`status`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_idx` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_sku_unique` (`sku`),
  ADD KEY `product_variants_product_idx` (`product_id`),
  ADD KEY `product_variants_size_idx` (`attr_size`),
  ADD KEY `product_variants_color_idx` (`attr_color`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `seo_meta`
--
ALTER TABLE `seo_meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seo_meta_seoable_unique` (`seoable_type`,`seoable_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stores_slug_unique` (`slug`),
  ADD KEY `stores_owner_idx` (`owner_user_id`);

--
-- Indexes for table `store_settings`
--
ALTER TABLE `store_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `store_settings_store_key_unique` (`store_id`,`key`);

--
-- Indexes for table `store_users`
--
ALTER TABLE `store_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `store_users_unique` (`store_id`,`user_id`),
  ADD KEY `store_users_user_idx` (`user_id`);

--
-- Indexes for table `telescope_entries`
--
ALTER TABLE `telescope_entries`
  ADD PRIMARY KEY (`sequence`),
  ADD UNIQUE KEY `telescope_entries_uuid_unique` (`uuid`),
  ADD KEY `telescope_entries_batch_id_index` (`batch_id`),
  ADD KEY `telescope_entries_family_hash_index` (`family_hash`),
  ADD KEY `telescope_entries_created_at_index` (`created_at`),
  ADD KEY `telescope_entries_type_should_display_on_index_index` (`type`,`should_display_on_index`);

--
-- Indexes for table `telescope_entries_tags`
--
ALTER TABLE `telescope_entries_tags`
  ADD PRIMARY KEY (`entry_uuid`,`tag`),
  ADD KEY `telescope_entries_tags_tag_index` (`tag`);

--
-- Indexes for table `telescope_monitoring`
--
ALTER TABLE `telescope_monitoring`
  ADD PRIMARY KEY (`tag`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agent_commission_plans`
--
ALTER TABLE `agent_commission_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agent_commission_statements`
--
ALTER TABLE `agent_commission_statements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `seo_meta`
--
ALTER TABLE `seo_meta`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `store_settings`
--
ALTER TABLE `store_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_users`
--
ALTER TABLE `store_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `telescope_entries`
--
ALTER TABLE `telescope_entries`
  MODIFY `sequence` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agent_commission_plans`
--
ALTER TABLE `agent_commission_plans`
  ADD CONSTRAINT `agent_commission_plans_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agent_commission_statements`
--
ALTER TABLE `agent_commission_statements`
  ADD CONSTRAINT `agent_commission_statements_paid_by_fk` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `agent_commission_statements_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_fk` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_parent_fk` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_address_fk` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_agent_fk` FOREIGN KEY (`sales_agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  ADD CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_history_user_fk` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD CONSTRAINT `otp_verifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_owner_fk` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `store_settings`
--
ALTER TABLE `store_settings`
  ADD CONSTRAINT `store_settings_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `store_users`
--
ALTER TABLE `store_users`
  ADD CONSTRAINT `store_users_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `store_users_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `telescope_entries_tags`
--
ALTER TABLE `telescope_entries_tags`
  ADD CONSTRAINT `telescope_entries_tags_entry_uuid_foreign` FOREIGN KEY (`entry_uuid`) REFERENCES `telescope_entries` (`uuid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
