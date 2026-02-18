


CREATE DATABASE IF NOT EXISTS avr_management 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE avr_management;


CREATE TABLE IF NOT EXISTS avr_inventory (
    ItemID INT AUTO_INCREMENT PRIMARY KEY,
    Item VARCHAR(255) NOT NULL,
    Description TEXT,
    Model VARCHAR(255),
    SerialNo VARCHAR(255),
    DateReceived DATE,
    Remark TEXT,
    INDEX idx_item (Item),
    INDEX idx_date_received (DateReceived)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS avr_borrowed (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Quantity INT NOT NULL DEFAULT 1,
    Item VARCHAR(255) NOT NULL,
    DateBorrowed DATE NOT NULL,
    DueDate DATE NOT NULL,
    Status VARCHAR(50) DEFAULT 'Active' COMMENT 'Active, Returned, Overdue',
    INDEX idx_item (Item),
    INDEX idx_status (Status),
    INDEX idx_date_borrowed (DateBorrowed),
    INDEX idx_due_date (DueDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS avr_reservation (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Department VARCHAR(255) NOT NULL,
    Date DATE NOT NULL,
    Time TIME NOT NULL,
    Purpose TEXT,
    INDEX idx_department (Department),
    INDEX idx_date (Date),
    INDEX idx_name (Name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS log_attendance (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    GradeSection VARCHAR(255) NOT NULL,
    NoOfStudent INT NOT NULL,
    Date DATE NOT NULL,
    INDEX idx_name (Name),
    INDEX idx_date (Date),
    INDEX idx_grade_section (GradeSection)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

