CREATE DATABASE IF NOT EXISTS cake_shop;
USE cake_shop;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `image_1` varchar(255) DEFAULT NULL,
  `image_2` varchar(255) DEFAULT NULL,
  `image_3` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'minimalist',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `details` text DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `items` text DEFAULT NULL,
  `payment_slip` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin is admin:password
INSERT INTO `admin` (`username`, `password`) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
