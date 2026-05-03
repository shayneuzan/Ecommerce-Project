-- Traventa Database Schema

CREATE TABLE IF NOT EXISTS destination (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS hotel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    hotel_name VARCHAR(150) NOT NULL,
    address VARCHAR(255),
    rating INT,
    FOREIGN KEY (destination_id) REFERENCES destination(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS guide (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    guide_name VARCHAR(150) NOT NULL,
    language VARCHAR(100),
    price DECIMAL(10,2),
    FOREIGN KEY (destination_id) REFERENCES destination(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS package (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    hotel_id INT NOT NULL,
    guide_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    duration_days INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    price_child DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    min_age INT NOT NULL DEFAULT 0,
    available_slots INT NOT NULL DEFAULT 10,
    image_url VARCHAR(500),
    FOREIGN KEY (destination_id) REFERENCES destination(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotel(id) ON DELETE CASCADE,
    FOREIGN KEY (guide_id) REFERENCES guide(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    totp_secret VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    booking_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES package(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS favorite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES package(id) ON DELETE CASCADE
);


