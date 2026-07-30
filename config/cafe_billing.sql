CREATE DATABASE IF NOT EXISTS cafe_billing;
USE cafe_billing;

CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(20)
);

INSERT INTO users(name,email,password,role)
VALUES
('Administrator','admin@cafe.com',MD5('admin123'),'Admin');