CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('relative', 'elderly'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);