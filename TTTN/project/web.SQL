CREATE TABLE `cart` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `quantity` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `message` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `message` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  `method` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` varchar(50) NOT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products`(`id`, `name`, `price`, `image`)
VALUES 
(1, 'Asus Ryzen AL 9', 20000000, 'asus1.jpg'),
(2, 'Asus I5 12500H-RTX ', 21000000, 'asus2.jpg'),
(3, 'Asus I7 12700H ', 22000000, 'asus3.jpg'),
(4, 'Asus I5 12500H-Intel', 19000000, 'asus4.jpg'),
(5, 'Asus R5 7520U', 25000000, 'asus5.jpg'),
(6, 'Apple M1 ', 40000000, 'sp1.jpg'),
(7, 'Apple M2 ', 45000000, 'sp2.jpg'),
(8, 'Apple M3 ', 44000000, 'sp3.jpg'),
(9, 'Apple M2-Pro ', 40000000, 'sp4.jpg'),
(10, 'Apple M3-Pro ', 50000000, 'sp5.jpg');

CREATE TABLE `users` (
    `id` INT(100) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `email` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `password` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `user_type` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
    `token_expiry` DATETIME NULL DEFAULT NULL,
    `reset_token` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `reset_token_expiry` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `users`(`id`, `name`, `email`, `password`, `user_type`) 


ALTER TABLE `cart` ADD PRIMARY KEY (`id`);
ALTER TABLE `message` ADD PRIMARY KEY (`id`);
ALTER TABLE `orders` ADD PRIMARY KEY (`id`);
ALTER TABLE `products` ADD PRIMARY KEY (`id`);
ALTER TABLE `users` ADD PRIMARY KEY (`id`);

ALTER TABLE `cart` MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;
ALTER TABLE `message` MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;
ALTER TABLE `orders` MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;
ALTER TABLE `products` MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users` MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;
