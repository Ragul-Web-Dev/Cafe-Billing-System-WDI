CREATE DATABASE IF NOT EXISTS `cafe_billing`;
USE `cafe_billing`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `bill_items`;
DROP TABLE IF EXISTS `bills`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 0,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Customers Table
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Bills Table
CREATE TABLE IF NOT EXISTS `bills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `bill_no` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT DEFAULT NULL,
  `user_id` INT NOT NULL,
  `sub_total` DECIMAL(10,2) NOT NULL,
  `gst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `grand_total` DECIMAL(10,2) NOT NULL,
  `payment_mode` ENUM('Cash', 'UPI', 'Card') NOT NULL,
  `payment_status` ENUM('Paid', 'Unpaid') DEFAULT 'Paid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Bill Items Table
CREATE TABLE IF NOT EXISTS `bill_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `bill_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`bill_id`) REFERENCES `bills`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `bill_id` INT NOT NULL,
  `payment_mode` ENUM('Cash', 'UPI', 'Card') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`bill_id`) REFERENCES `bills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed Admin User (password is 'admin123' hashed using PHP password_hash)
INSERT INTO `users` (`name`, `email`, `password`) VALUES
('Administrator', 'admin@cafe.com', '$2y$10$1oBcrlZvIdXIZhRSbnNojOhX6iTVce3Niu.Rl3ImRYcP9DPbDO89G')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seed Sample Products
INSERT INTO `products` (`name`, `category`, `price`, `quantity`, `status`) VALUES
('Espresso', 'Hot Coffee', 45.00, 100, 'Active'),
('Cappuccino', 'Hot Coffee', 75.00, 100, 'Active'),
('Cafe Latte', 'Hot Coffee', 80.00, 80, 'Active'),
('Iced Americano', 'Cold Coffee', 65.00, 120, 'Active'),
('Mocha Frappe', 'Cold Coffee', 110.00, 50, 'Active'),
('Chocolate Muffin', 'Bakery', 60.00, 30, 'Active'),
('Paneer Tikka Sandwich', 'Snacks', 95.00, 25, 'Active'),
('Green Tea', 'Tea', 40.00, 150, 'Active');

-- Seed Sample Customers
INSERT INTO `customers` (`name`, `phone`, `email`, `address`) VALUES
('John Doe', '9876543210', 'john@example.com', '123 Baker Street, London'),
('Jane Smith', '9876543211', 'jane@example.com', '456 Elm Street, New York'),
('Walk-In Customer', '0000000000', 'walkin@cafe.com', 'Walk-In');
