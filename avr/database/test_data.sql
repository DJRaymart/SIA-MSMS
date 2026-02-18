


USE avr_management;


INSERT INTO avr_inventory (Item, Description, Model, SerialNo, DateReceived, Remark) VALUES
('Projector', 'HD Projector for presentations', 'Epson EB-X41', 'EPX41-2024-001', '2024-01-15', 'Good condition'),
('Laptop', 'Dell Latitude laptop for presentations', 'Dell Latitude 5520', 'DL5520-2024-002', '2024-01-20', 'New'),
('Microphone', 'Wireless microphone system', 'Shure SM58', 'SH58-2024-003', '2024-02-01', 'Excellent'),
('Speaker', 'Portable Bluetooth speaker', 'JBL Flip 6', 'JBLF6-2024-004', '2024-02-10', 'Working well'),
('Camera', 'HD Video Camera for recording', 'Canon VIXIA HF R800', 'CVHF-2024-005', '2024-02-15', 'Good'),
('Tablet', 'iPad for presentations', 'iPad Pro 12.9', 'IPAD-2024-006', '2024-03-01', 'New'),
('Screen', 'Projection screen 100 inches', 'Elite Screens Sable', 'ES100-2024-007', '2024-03-10', 'Excellent'),
('Sound System', 'Complete sound system with mixer', 'Yamaha MG10XU', 'YMG10-2024-008', '2024-03-15', 'Professional grade'),
('Document Camera', 'Document camera for presentations', 'IPEVO V4K', 'IPV4K-2024-009', '2024-04-01', 'Good'),
('HDMI Cable', 'High-speed HDMI cable 10ft', 'Amazon Basics', 'ABHD-2024-010', '2024-04-05', 'Standard');


INSERT INTO avr_borrowed (Name, Quantity, Item, DateBorrowed, DueDate, Status) VALUES
('John Smith', 1, 'Projector', '2024-12-01', '2024-12-05', 'Active'),
('Maria Garcia', 1, 'Laptop', '2024-12-02', '2024-12-06', 'Active'),
('David Johnson', 2, 'Microphone', '2024-11-25', '2024-11-29', 'Returned'),
('Sarah Williams', 1, 'Speaker', '2024-12-03', '2024-12-07', 'Active'),
('Michael Brown', 1, 'Camera', '2024-11-20', '2024-11-24', 'Returned'),
('Emily Davis', 1, 'Tablet', '2024-11-28', '2024-12-02', 'Overdue'),
('Robert Miller', 1, 'Projector', '2024-11-15', '2024-11-19', 'Returned'),
('Jessica Wilson', 1, 'Sound System', '2024-12-04', '2024-12-08', 'Active'),
('Christopher Moore', 1, 'Document Camera', '2024-11-22', '2024-11-26', 'Returned'),
('Amanda Taylor', 1, 'Laptop', '2024-11-30', '2024-12-04', 'Active');


INSERT INTO avr_reservation (Name, Department, Date, Time, Purpose) VALUES
('Dr. James Anderson', 'Mathematics', '2024-12-10', '09:00:00', 'Faculty meeting and presentation'),
('Prof. Lisa Martinez', 'Science', '2024-12-10', '14:00:00', 'Science fair preparation'),
('Mr. Thomas Lee', 'English', '2024-12-11', '10:00:00', 'Literature presentation'),
('Ms. Jennifer White', 'History', '2024-12-11', '15:00:00', 'Historical documentary viewing'),
('Dr. Robert Harris', 'Mathematics', '2024-12-12', '09:30:00', 'Math competition practice'),
('Prof. Susan Clark', 'Science', '2024-12-12', '13:00:00', 'Lab demonstration'),
('Mr. Daniel Lewis', 'English', '2024-12-13', '11:00:00', 'Poetry reading session'),
('Ms. Patricia Walker', 'Arts', '2024-12-13', '14:30:00', 'Art presentation'),
('Dr. Mark Hall', 'Mathematics', '2024-12-14', '10:00:00', 'Math workshop'),
('Prof. Nancy Allen', 'Science', '2024-12-14', '15:00:00', 'Science project review'),
('Mr. Kevin Young', 'English', '2024-12-15', '09:00:00', 'Book discussion'),
('Ms. Michelle King', 'History', '2024-12-15', '13:30:00', 'History documentary');


INSERT INTO log_attendance (Name, GradeSection, NoOfStudent, Date) VALUES
('Mr. John Thompson', 'Grade 10 - Section A', 35, '2024-12-01'),
('Ms. Sarah Martinez', 'Grade 9 - Section B', 32, '2024-12-01'),
('Dr. Robert Chen', 'Grade 11 - Section C', 38, '2024-12-02'),
('Prof. Maria Rodriguez', 'Grade 8 - Section A', 30, '2024-12-02'),
('Mr. David Kim', 'Grade 10 - Section B', 36, '2024-12-03'),
('Ms. Jennifer Park', 'Grade 9 - Section C', 33, '2024-12-03'),
('Dr. Michael Johnson', 'Grade 11 - Section A', 37, '2024-12-04'),
('Prof. Lisa Brown', 'Grade 8 - Section B', 31, '2024-12-04'),
('Mr. Christopher Lee', 'Grade 10 - Section C', 34, '2024-12-05'),
('Ms. Amanda Davis', 'Grade 9 - Section A', 32, '2024-12-05'),
('Dr. James Wilson', 'Grade 11 - Section B', 39, '2024-12-06'),
('Prof. Patricia Moore', 'Grade 8 - Section C', 29, '2024-12-06'),
('Mr. Kevin Taylor', 'Grade 10 - Section A', 35, '2024-12-07'),
('Ms. Michelle Anderson', 'Grade 9 - Section B', 33, '2024-12-07'),
('Dr. Mark Thomas', 'Grade 11 - Section C', 38, '2024-12-08'),
('Prof. Nancy Jackson', 'Grade 8 - Section A', 30, '2024-12-08'),
('Mr. Daniel White', 'Grade 10 - Section B', 36, '2024-12-09'),
('Ms. Susan Harris', 'Grade 9 - Section C', 34, '2024-12-09'),
('Dr. Robert Martin', 'Grade 11 - Section A', 37, '2024-12-10'),
('Prof. Jennifer Thompson', 'Grade 8 - Section B', 31, '2024-12-10');

