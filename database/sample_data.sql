-- Sample Data for Testing Laravel ERP Integration
-- 
-- This file provides sample data for the legacy 'orders' table
-- Use this to test the Laravel integration without affecting production data

-- Create the orders table (if it doesn't exist)
-- Note: In production, this table already exists from the ColdFusion system
CREATE TABLE IF NOT EXISTS orders (
  order_id INT PRIMARY KEY AUTO_INCREMENT,
  customer_name VARCHAR(255) NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_status (status),
  INDEX idx_created_at (created_at DESC),
  INDEX idx_status_created (status, created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample orders (latest 50+ for testing)
INSERT INTO orders (customer_name, total_amount, status, created_at) VALUES
-- Recent orders (last 7 days)
('John Doe', 150.00, 'pending', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('Jane Smith', 275.50, 'paid', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('Bob Johnson', 89.99, 'processing', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
('Alice Williams', 450.00, 'completed', DATE_SUB(NOW(), INTERVAL 8 HOUR)),
('Charlie Brown', 125.75, 'cancelled', DATE_SUB(NOW(), INTERVAL 12 HOUR)),

('David Miller', 320.00, 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Emma Davis', 199.99, 'paid', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Frank Wilson', 540.25, 'processing', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Grace Taylor', 75.50, 'completed', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Henry Anderson', 890.00, 'paid', DATE_SUB(NOW(), INTERVAL 2 DAY)),

('Ivy Martinez', 165.00, 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Jack Thompson', 425.75, 'processing', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Kelly White', 310.00, 'completed', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Liam Harris', 95.50, 'cancelled', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Mia Clark', 680.00, 'paid', DATE_SUB(NOW(), INTERVAL 4 DAY)),

('Noah Lewis', 220.00, 'pending', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Olivia Walker', 155.25, 'processing', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Paul Hall', 790.00, 'completed', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Quinn Allen', 340.50, 'paid', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Rachel Young', 125.00, 'pending', DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- Older orders (1-4 weeks ago)
('Sam King', 460.00, 'completed', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Tina Wright', 185.75, 'paid', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('Uma Scott', 920.00, 'completed', DATE_SUB(NOW(), INTERVAL 9 DAY)),
('Victor Green', 275.50, 'cancelled', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('Wendy Adams', 540.00, 'paid', DATE_SUB(NOW(), INTERVAL 11 DAY)),

('Xavier Baker', 195.00, 'completed', DATE_SUB(NOW(), INTERVAL 12 DAY)),
('Yara Nelson', 385.25, 'paid', DATE_SUB(NOW(), INTERVAL 13 DAY)),
('Zack Carter', 670.00, 'completed', DATE_SUB(NOW(), INTERVAL 14 DAY)),
('Amy Mitchell', 145.50, 'cancelled', DATE_SUB(NOW(), INTERVAL 15 DAY)),
('Ben Perez', 825.00, 'paid', DATE_SUB(NOW(), INTERVAL 16 DAY)),

('Cara Roberts', 290.00, 'completed', DATE_SUB(NOW(), INTERVAL 17 DAY)),
('Dan Turner', 435.75, 'paid', DATE_SUB(NOW(), INTERVAL 18 DAY)),
('Eva Phillips', 560.00, 'completed', DATE_SUB(NOW(), INTERVAL 19 DAY)),
('Fred Campbell', 175.25, 'cancelled', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('Gina Parker', 710.00, 'paid', DATE_SUB(NOW(), INTERVAL 21 DAY)),

('Hugo Evans', 320.00, 'completed', DATE_SUB(NOW(), INTERVAL 22 DAY)),
('Iris Edwards', 245.50, 'paid', DATE_SUB(NOW(), INTERVAL 23 DAY)),
('Jake Collins', 890.00, 'completed', DATE_SUB(NOW(), INTERVAL 24 DAY)),
('Kate Stewart', 165.00, 'cancelled', DATE_SUB(NOW(), INTERVAL 25 DAY)),
('Leo Sanchez', 625.75, 'paid', DATE_SUB(NOW(), INTERVAL 26 DAY)),

('Maya Morris', 410.00, 'completed', DATE_SUB(NOW(), INTERVAL 27 DAY)),
('Nick Rogers', 335.25, 'paid', DATE_SUB(NOW(), INTERVAL 28 DAY)),
('Opal Reed', 780.00, 'completed', DATE_SUB(NOW(), INTERVAL 29 DAY)),
('Pete Cook', 195.50, 'cancelled', DATE_SUB(NOW(), INTERVAL 30 DAY)),
('Quincy Morgan', 545.00, 'paid', DATE_SUB(NOW(), INTERVAL 31 DAY)),

-- Additional orders for testing (50+ total)
('Ruby Bell', 270.00, 'pending', DATE_SUB(NOW(), INTERVAL 32 DAY)),
('Steve Murphy', 395.75, 'processing', DATE_SUB(NOW(), INTERVAL 33 DAY)),
('Tara Bailey', 650.00, 'completed', DATE_SUB(NOW(), INTERVAL 34 DAY)),
('Umar Rivera', 185.25, 'paid', DATE_SUB(NOW(), INTERVAL 35 DAY)),
('Vera Cooper', 820.00, 'completed', DATE_SUB(NOW(), INTERVAL 36 DAY));

-- Verify data
SELECT 
    status,
    COUNT(*) as count,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value
FROM orders
GROUP BY status
ORDER BY count DESC;

-- Test query: Latest 50 orders
SELECT order_id, customer_name, total_amount, status, created_at
FROM orders
ORDER BY created_at DESC
LIMIT 50;

-- Test query: Filter by status
SELECT order_id, customer_name, total_amount, status, created_at
FROM orders
WHERE status = 'pending'
ORDER BY created_at DESC
LIMIT 50;
