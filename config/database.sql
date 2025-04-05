-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 05, 2025 lúc 05:44 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.1.25

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
-- Cấu trúc bảng cho bảng `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(3, 'Admin', 'admin@example.com', '$2y$10$ajq.ADEu96ewhzLEaZhcOe3GtxbAljwUeYocvguHbSkxgwZB4/Y/m', '2025-03-04 14:05:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `comments_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `content`, `image`, `author`, `category`, `tags`, `comments_count`, `created_at`, `updated_at`) VALUES
(1, 'Tips for Solo Travel in Europe', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'blog-1.jpg', 'John Doe', 'Travel Tips', 'solo travel, europe, tips', 2, '2025-03-04 13:23:28', '2025-03-04 13:23:28'),
(2, 'Best Hidden Beaches in Southeast Asia', 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'blog-2.jpg', 'Jane Smith', 'Destinations', 'beaches, asia, hidden gems', 3, '2025-03-04 13:23:28', '2025-03-04 13:23:28'),
(3, 'Budget Travel Guide: South America', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.', 'blog-3.jpg', 'Mike Johnson', 'Travel Guide', 'budget travel, south america', 1, '2025-03-04 13:23:28', '2025-03-04 13:23:28'),
(4, 'Tips for Solo Travel in Europe', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'blog-1.jpg', 'John Doe', 'Travel Tips', 'solo travel, europe, tips', 2, '2025-03-04 13:38:20', '2025-03-04 13:38:20'),
(5, 'Best Hidden Beaches in Southeast Asia', 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'blog-2.jpg', 'Jane Smith', 'Destinations', 'beaches, asia, hidden gems', 3, '2025-03-04 13:38:20', '2025-03-04 13:38:20'),
(6, 'Budget Travel Guide: South America', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.', 'blog-3.jpg', 'Mike Johnson', 'Travel Guide', 'budget travel, south america', 1, '2025-03-04 13:38:20', '2025-03-04 13:38:20'),
(7, 'Tips for Solo Travel in Europe', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'blog-1.jpg', 'John Doe', 'Travel Tips', 'solo travel, europe, tips', 2, '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(8, 'Best Hidden Beaches in Southeast Asia', 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'blog-2.jpg', 'Jane Smith', 'Destinations', 'beaches, asia, hidden gems', 3, '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(9, 'Budget Travel Guide: South America', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.', 'blog-3.jpg', 'Mike Johnson', 'Travel Guide', 'budget travel, south america', 1, '2025-03-04 13:46:53', '2025-03-04 13:46:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `tour_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `checkin_date` date NOT NULL,
  `checkout_date` date NOT NULL,
  `guests` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bookings`
--

INSERT INTO `bookings` (`id`, `tour_id`, `name`, `email`, `phone`, `checkin_date`, `checkout_date`, `guests`, `status`, `created_at`) VALUES
(1, 3, 'Phạm Thắng', 'thang001510@gmail.com', '0766526344', '0000-00-00', '0000-00-00', 2, 'confirmed', '2025-03-04 14:53:02'),
(2, 5, 'Phạm Thắng', 'thang001510@gmail.com', '0766526344', '2025-03-10', '2025-03-20', 1, 'confirmed', '2025-03-04 15:01:30'),
(3, 5, 'Phạm Thắng', 'thang001510@gmail.com', '0766526344', '2025-03-10', '2025-03-20', 1, 'confirmed', '2025-03-04 15:05:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved','spam') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `name`, `email`, `content`, `status`, `created_at`) VALUES
(1, 1, 'Alice Cooper', 'alice@example.com', 'Great tips! I especially loved the section about train travel.', 'pending', '2025-03-04 13:23:28'),
(2, 1, 'Bob Wilson', 'bob@example.com', 'Would love to see more specific recommendations for Eastern Europe.', 'pending', '2025-03-04 13:23:28'),
(3, 2, 'Carol Davis', 'carol@example.com', 'These beaches look amazing! Adding them to my bucket list.', 'pending', '2025-03-04 13:23:28'),
(4, 2, 'David Brown', 'david@example.com', 'I visited the third beach on the list last summer. It was even better in person!', 'pending', '2025-03-04 13:23:28'),
(5, 2, 'Eve Martin', 'eve@example.com', 'Could you add some tips about the best time to visit these beaches?', 'pending', '2025-03-04 13:23:28'),
(6, 3, 'Frank Miller', 'frank@example.com', 'This guide saved me so much money on my recent trip to Peru!', 'pending', '2025-03-04 13:23:28'),
(7, 1, 'Alice Cooper', 'alice@example.com', 'Great tips! I especially loved the section about train travel.', 'pending', '2025-03-04 13:38:20'),
(8, 1, 'Bob Wilson', 'bob@example.com', 'Would love to see more specific recommendations for Eastern Europe.', 'pending', '2025-03-04 13:38:20'),
(9, 2, 'Carol Davis', 'carol@example.com', 'These beaches look amazing! Adding them to my bucket list.', 'pending', '2025-03-04 13:38:20'),
(10, 2, 'David Brown', 'david@example.com', 'I visited the third beach on the list last summer. It was even better in person!', 'pending', '2025-03-04 13:38:20'),
(11, 2, 'Eve Martin', 'eve@example.com', 'Could you add some tips about the best time to visit these beaches?', 'pending', '2025-03-04 13:38:20'),
(12, 3, 'Frank Miller', 'frank@example.com', 'This guide saved me so much money on my recent trip to Peru!', 'pending', '2025-03-04 13:38:20'),
(13, 1, 'Alice Cooper', 'alice@example.com', 'Great tips! I especially loved the section about train travel.', 'approved', '2025-03-04 13:46:53'),
(14, 1, 'Bob Wilson', 'bob@example.com', 'Would love to see more specific recommendations for Eastern Europe.', 'pending', '2025-03-04 13:46:53'),
(15, 2, 'Carol Davis', 'carol@example.com', 'These beaches look amazing! Adding them to my bucket list.', 'pending', '2025-03-04 13:46:53'),
(16, 2, 'David Brown', 'david@example.com', 'I visited the third beach on the list last summer. It was even better in person!', 'pending', '2025-03-04 13:46:53'),
(17, 2, 'Eve Martin', 'eve@example.com', 'Could you add some tips about the best time to visit these beaches?', 'pending', '2025-03-04 13:46:53'),
(18, 3, 'Frank Miller', 'frank@example.com', 'This guide saved me so much money on my recent trip to Peru!', 'pending', '2025-03-04 13:46:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `is_read`) VALUES
(1, 'Phạm Thắng', 'thang001510@gmail.com', 'Support', 'Hehe', '2025-03-04 14:43:04', 1),
(2, 'Phạm Thắng', 'thang001510@gmail.com', 'Support', 'Hihi', '2025-03-04 14:47:19', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `amenities` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `description`, `location`, `address`, `rating`, `amenities`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Luxury Palace Hotel', 'Experience ultimate luxury in the heart of the city with stunning views and world-class service.', 'Paris, France', '123 Champs-Élysées, 75008 Paris', 4.8, 'Swimming Pool,Spa,Restaurant,Fitness Center,Free WiFi', 'hotel-1.jpg', '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(2, 'Seaside Resort & Spa', 'Beachfront paradise with private beach access and luxury spa treatments.', 'Bali, Indonesia', 'Jalan Beach Road 45, Kuta, Bali', 4.6, 'Private Beach,Spa,Multiple Restaurants,Infinity Pool,Free WiFi', 'hotel-2.jpg', '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(3, 'Mountain View Lodge', 'Cozy mountain retreat with breathtaking views and outdoor activities.', 'Swiss Alps, Switzerland', 'Alpine Road 78, Zermatt 3920', 4.7, 'Ski Access,Restaurant,Fireplace Lounge,Spa,Mountain Views', 'hotel-3.jpg', '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(4, 'Urban Boutique Hotel', 'Modern boutique hotel in the city center with unique design and personalized service.', 'New York, USA', '456 Madison Avenue, New York, NY 10022', 4.5, 'Restaurant,Bar,Business Center,Free WiFi,Fitness Center', 'hotel-4.jpg', '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(5, 'Desert Oasis Resort', 'Luxury desert resort offering unique experiences and traditional hospitality.', 'Dubai, UAE', 'Desert Road 99, Dubai', 4.9, 'Private Pool Villas,Spa,Desert Activities,Multiple Restaurants,Butler Service', 'hotel-5.jpg', '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(6, 'Historic Grand Hotel', 'Classic luxury hotel with rich history and timeless elegance.', 'London, UK', '789 Piccadilly, London W1J 7QR', 4.7, 'Fine Dining,Tea Lounge,Spa,Fitness Center,Concierge Service', 'hotel-6.jpg', '2025-03-04 13:46:53', '2025-03-04 13:46:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hotel_bookings`
--

CREATE TABLE `hotel_bookings` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `checkin_date` date NOT NULL,
  `checkout_date` date NOT NULL,
  `guests` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hotel_bookings`
--

INSERT INTO `hotel_bookings` (`id`, `hotel_id`, `room_type_id`, `name`, `email`, `phone`, `checkin_date`, `checkout_date`, `guests`, `total_price`, `status`, `created_at`) VALUES
(2, 1, 1, 'Phạm Thắng', 'thang001510@gmail.com', '0766526344', '2025-03-12', '2025-03-19', 0, 1750.00, 'confirmed', '2025-03-04 15:16:22'),
(3, 1, 1, 'Phạm Thắng', 'thang001510@gmail.com', '0766526344', '2025-03-10', '2025-03-18', 0, 2000.00, 'pending', '2025-03-05 03:18:26');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `room_types`
--

CREATE TABLE `room_types` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 2,
  `available_rooms` int(11) NOT NULL DEFAULT 0,
  `amenities` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `room_types`
--

INSERT INTO `room_types` (`id`, `hotel_id`, `name`, `description`, `price`, `capacity`, `available_rooms`, `amenities`, `image`, `created_at`) VALUES
(1, 1, 'Deluxe Room', 'Spacious room with city views and modern amenities', 250.00, 2, 2, 'King Bed,City View,Mini Bar,Free WiFi,Room Service', 'room-1.jpg', '2025-03-04 14:22:02'),
(2, 1, 'Executive Suite', 'Luxury suite with separate living area and premium amenities', 450.00, 2, 2, 'King Bed,Living Room,City View,Mini Bar,Butler Service', 'room-2.jpg', '2025-03-04 14:22:02'),
(3, 2, 'Ocean View Room', 'Beautiful room with ocean views and balcony', 300.00, 2, 2, 'King Bed,Ocean View,Balcony,Mini Bar,Free WiFi', 'room-3.jpg', '2025-03-04 14:22:02'),
(4, 2, 'Beach Villa', 'Private villa with direct beach access and pool', 800.00, 4, 4, 'Two Bedrooms,Private Pool,Beach Access,Kitchen,Butler Service', 'room-4.jpg', '2025-03-04 14:22:02'),
(5, 3, 'Mountain Room', 'Cozy room with mountain views', 200.00, 2, 2, 'Queen Bed,Mountain View,Fireplace,Mini Bar,Free WiFi', 'room-5.jpg', '2025-03-04 14:22:02'),
(6, 3, 'Alpine Suite', 'Spacious suite with panoramic mountain views', 400.00, 3, 3, 'King Bed,Living Room,Panoramic View,Fireplace,Kitchen', 'room-6.jpg', '2025-03-04 14:22:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tours`
--

INSERT INTO `tours` (`id`, `name`, `description`, `price`, `duration`, `location`, `image`, `rating`, `created_at`, `updated_at`) VALUES
(1, 'Paris Adventure', 'Experience the magic of Paris with our guided tour including Eiffel Tower, Louvre, and Seine River cruise.', 1200.00, '5 days 4 nights', 'Paris, France', 'destination-1.jpg', 4.5, '2025-03-04 13:16:37', '2025-03-04 13:16:37'),
(2, 'Berlin Explorer', 'Discover the rich history and vibrant culture of Berlin with visits to the Brandenburg Gate, East Side Gallery, and more.', 900.00, '4 days 3 nights', 'Berlin, Germany', 'destination-2.jpg', 4.2, '2025-03-04 13:16:37', '2025-03-04 13:16:37'),
(3, 'London Calling', 'See the best of London including Big Ben, Tower Bridge, and Buckingham Palace with our comprehensive city tour.', 1100.00, '5 days 4 nights', 'London, UK', 'destination-3.jpg', 4.7, '2025-03-04 13:16:37', '2025-03-04 13:16:37'),
(4, 'Rome Classic', 'Walk through history in Rome visiting the Colosseum, Vatican, and Roman Forum with expert guides.', 1300.00, '6 days 5 nights', 'Rome, Italy', 'destination-4.jpg', 4.8, '2025-03-04 13:16:37', '2025-03-04 13:16:37'),
(5, 'Barcelona Sun', 'Enjoy the Mediterranean charm of Barcelona with visits to Sagrada Familia, Park Güell, and Las Ramblas.', 950.00, '4 days 3 nights', 'Barcelona, Spain', 'destination-5.jpg', 4.4, '2025-03-04 13:16:37', '2025-03-04 13:16:37'),
(6, 'Amsterdam Canal', 'Experience the unique beauty of Amsterdam with canal tours, museum visits, and bicycle tours.', 850.00, '3 days 2 nights', 'Amsterdam, Netherlands', 'destination-6.jpg', 4.3, '2025-03-04 13:16:37', '2025-03-04 13:16:37'),
(7, 'Paris Adventure', 'Experience the magic of Paris with our guided tour including Eiffel Tower, Louvre, and Seine River cruise.', 1200.00, '5 days 4 nights', 'Paris, France', 'destination-1.jpg', 4.5, '2025-03-04 13:23:28', '2025-03-04 13:23:28'),
(8, 'Berlin Explorer', 'Discover the rich history and vibrant culture of Berlin with visits to the Brandenburg Gate, East Side Gallery, and more.', 900.00, '4 days 3 nights', 'Berlin, Germany', 'destination-2.jpg', 4.2, '2025-03-04 13:23:28', '2025-03-04 13:23:28'),
(9, 'London Calling', 'See the best of London including Big Ben, Tower Bridge, and Buckingham Palace with our comprehensive city tour.', 1100.00, '5 days 4 nights', 'London, UK', 'destination-3.jpg', 4.7, '2025-03-04 13:23:28', '2025-03-04 13:23:28'),
(10, 'Rome Classic', 'Walk through history in Rome visiting the Colosseum, Vatican, and Roman Forum with expert guides.', 1300.00, '6 days 5 nights', 'Rome, Italy', 'destination-4.jpg', 4.8, '2025-03-04 13:23:28', '2025-03-04 13:23:28'),
(11, 'Barcelona Sun', 'Enjoy the Mediterranean charm of Barcelona with visits to Sagrada Familia, Park Güell, and Las Ramblas.', 950.00, '4 days 3 nights', 'Barcelona, Spain', 'destination-5.jpg', 4.4, '2025-03-04 13:23:28', '2025-03-04 13:23:28'),
(12, 'Amsterdam Canal', 'Experience the unique beauty of Amsterdam with canal tours, museum visits, and bicycle tours.', 850.00, '3 days 2 nights', 'Amsterdam, Netherlands', 'destination-6.jpg', 4.3, '2025-03-04 13:23:28', '2025-03-04 13:23:28'),
(13, 'Paris Adventure', 'Experience the magic of Paris with our guided tour including Eiffel Tower, Louvre, and Seine River cruise.', 1200.00, '5 days 4 nights', 'Paris, France', 'destination-1.jpg', 4.5, '2025-03-04 13:38:20', '2025-03-04 13:38:20'),
(14, 'Berlin Explorer', 'Discover the rich history and vibrant culture of Berlin with visits to the Brandenburg Gate, East Side Gallery, and more.', 900.00, '4 days 3 nights', 'Berlin, Germany', 'destination-2.jpg', 4.2, '2025-03-04 13:38:20', '2025-03-04 13:38:20'),
(15, 'London Calling', 'See the best of London including Big Ben, Tower Bridge, and Buckingham Palace with our comprehensive city tour.', 1100.00, '5 days 4 nights', 'London, UK', 'destination-3.jpg', 4.7, '2025-03-04 13:38:20', '2025-03-04 13:38:20'),
(16, 'Rome Classic', 'Walk through history in Rome visiting the Colosseum, Vatican, and Roman Forum with expert guides.', 1300.00, '6 days 5 nights', 'Rome, Italy', 'destination-4.jpg', 4.8, '2025-03-04 13:38:20', '2025-03-04 13:38:20'),
(17, 'Barcelona Sun', 'Enjoy the Mediterranean charm of Barcelona with visits to Sagrada Familia, Park Güell, and Las Ramblas.', 950.00, '4 days 3 nights', 'Barcelona, Spain', 'destination-5.jpg', 4.4, '2025-03-04 13:38:20', '2025-03-04 13:38:20'),
(18, 'Amsterdam Canal', 'Experience the unique beauty of Amsterdam with canal tours, museum visits, and bicycle tours.', 850.00, '3 days 2 nights', 'Amsterdam, Netherlands', 'destination-6.jpg', 4.3, '2025-03-04 13:38:20', '2025-03-04 13:38:20'),
(19, 'Paris Adventure', 'Experience the magic of Paris with our guided tour including Eiffel Tower, Louvre, and Seine River cruise.', 1200.00, '5 days 4 nights', 'Paris, France', 'destination-1.jpg', 4.5, '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(20, 'Berlin Explorer', 'Discover the rich history and vibrant culture of Berlin with visits to the Brandenburg Gate, East Side Gallery, and more.', 900.00, '4 days 3 nights', 'Berlin, Germany', 'destination-2.jpg', 4.2, '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(21, 'London Calling', 'See the best of London including Big Ben, Tower Bridge, and Buckingham Palace with our comprehensive city tour.', 1100.00, '5 days 4 nights', 'London, UK', 'destination-3.jpg', 4.7, '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(22, 'Rome Classic', 'Walk through history in Rome visiting the Colosseum, Vatican, and Roman Forum with expert guides.', 1300.00, '6 days 5 nights', 'Rome, Italy', 'destination-4.jpg', 4.8, '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(23, 'Barcelona Sun', 'Enjoy the Mediterranean charm of Barcelona with visits to Sagrada Familia, Park Güell, and Las Ramblas.', 950.00, '4 days 3 nights', 'Barcelona, Spain', 'destination-5.jpg', 4.4, '2025-03-04 13:46:53', '2025-03-04 13:46:53'),
(24, 'Amsterdam Canal', 'Experience the unique beauty of Amsterdam with canal tours, museum visits, and bicycle tours.', 850.00, '3 days 2 nights', 'Amsterdam, Netherlands', 'destination-6.jpg', 4.3, '2025-03-04 13:46:53', '2025-03-04 13:46:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NULL, '2025-03-04 14:29:28', '2025-03-04 14:29:28');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tour_id` (`tour_id`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- Chỉ mục cho bảng `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Chỉ mục cho bảng `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `room_types`
--
ALTER TABLE `room_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD CONSTRAINT `hotel_bookings_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hotel_bookings_ibfk_2` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `room_types`
--
ALTER TABLE `room_types`
  ADD CONSTRAINT `room_types_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
