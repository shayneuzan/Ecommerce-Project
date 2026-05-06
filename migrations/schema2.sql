ALTER TABLE booking
ADD COLUMN reference VARCHAR(20) NULL UNIQUE AFTER package_id,
ADD COLUMN travel_date DATE NOT NULL AFTER booking_date,
ADD COLUMN payment_status ENUM('unpaid', 'paid', 'failed') NOT NULL DEFAULT 'unpaid' AFTER status,
ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER payment_status;

ALTER TABLE booking
MODIFY COLUMN booking_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE booking
MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending'; 



CREATE TABLE IF NOT EXISTS bookingpassenger (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    passenger_type ENUM('adult', 'child', 'infant') NOT NULL,  
    price DECIMAL(10,2) NOT NULL,            
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE
);