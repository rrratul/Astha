CREATE TABLE `elderly_relatives` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `date_of_birth` DATE NOT NULL,
  `health_status` TEXT,
  `communication_preference` ENUM('phone', 'video', 'in-person') DEFAULT 'phone',
  `financial_assistance` DECIMAL(10, 2) DEFAULT 0.00,
  `storytelling_circle` BOOLEAN DEFAULT FALSE,
  `community_interaction` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `health_updates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `relative_id` INT NOT NULL,
  `update_date` DATE NOT NULL,
  `health_update` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`relative_id`) REFERENCES `elderly_relatives`(`id`)
);

CREATE TABLE `volunteer_opportunities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `date` DATE NOT NULL,
  `location` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);