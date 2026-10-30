-- Lost and Found Portal Database Schema
-- Run: mariadb -u root lost_and_found < database.sql

USE lost_and_found;

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('lost', 'found') NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    date_occurred DATE NOT NULL,
    description TEXT NOT NULL,
    reporter_name VARCHAR(150) NOT NULL,
    reporter_email VARCHAR(255) NOT NULL,
    status ENUM('Open', 'Claim Requested', 'Resolved') DEFAULT 'Open',
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    claimer_name VARCHAR(150) NOT NULL,
    claimer_email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
);

-- Sample data
INSERT INTO reports (type, title, category, location, date_occurred, description, reporter_name, reporter_email, status) VALUES
('lost', 'Blue Backpack with Laptop', 'Bags & Luggage', 'Central Library - 2nd Floor', '2026-10-28', 'Navy blue JanSport backpack containing a MacBook Air and charger. Has a small keychain on the zipper.', 'Arjun Mehta', 'arjun.m@campus.edu', 'Open'),
('found', 'Student ID Card', 'Documents & ID', 'Science Block - Room 204', '2026-10-29', 'Found a student ID card on the desk after class. Name partially visible.', 'Priya Sharma', 'priya.s@campus.edu', 'Open'),
('lost', 'Silver Wristwatch', 'Accessories', 'Sports Complex - Gym', '2026-10-27', 'Fossil brand silver analog watch. Lost during evening workout session. Has sentimental value.', 'Rahul Verma', 'rahul.v@campus.edu', 'Claim Requested'),
('found', 'Wireless Earbuds Case', 'Electronics', 'Cafeteria - Main Hall', '2026-10-29', 'White Samsung Galaxy Buds case found near the billing counter. Contains earbuds inside.', 'Sneha Iyer', 'sneha.i@campus.edu', 'Open'),
('lost', 'Organic Chemistry Textbook', 'Books & Notes', 'Lecture Hall - LH3', '2026-10-26', 'Morrison & Boyd Organic Chemistry textbook with highlighted chapters 5-8. Name written on first page.', 'Karan Singh', 'karan.s@campus.edu', 'Resolved'),
('found', 'Water Bottle - Green', 'Personal Items', 'Parking Lot - Block A', '2026-10-30', 'Green Milton water bottle found near bike parking. Has stickers on it.', 'Ananya Roy', 'ananya.r@campus.edu', 'Open');
