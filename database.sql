-- Database untuk Dompet Sesat
CREATE DATABASE IF NOT EXISTS dompet_sesat;
USE dompet_sesat;

-- Tabel Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    monthly_budget DECIMAL(15,2) DEFAULT 1500000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Categories
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(10) DEFAULT '💰',
    color VARCHAR(7) DEFAULT '#6b7280',
    type ENUM('income', 'expense') NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Transactions
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO categories (user_id, name, icon, color, type, is_default) VALUES
-- Expense categories
(NULL, 'Makan', '🍚', '#4361ee', 'expense', TRUE),
(NULL, 'Jajan', '🍔', '#f72585', 'expense', TRUE),
(NULL, 'Token', '⚡', '#4cc9f0', 'expense', TRUE),
(NULL, 'Laundry', '👕', '#7209b7', 'expense', TRUE),
(NULL, 'Transport', '🚗', '#fca311', 'expense', TRUE),
(NULL, 'Internet', '🌐', '#2ec4b6', 'expense', TRUE),
(NULL, 'Parkir', '🅿️', '#e71d36', 'expense', TRUE),
(NULL, 'Pulsa', '📱', '#ff9f1c', 'expense', TRUE),
(NULL, 'Entertainment', '🎬', '#9b5de5', 'expense', TRUE),
(NULL, 'Kesehatan', '💊', '#00bbf9', 'expense', TRUE),
(NULL, 'Pendidikan', '📚', '#f15bb5', 'expense', TRUE),
(NULL, 'Lainnya', '💵', '#6b7280', 'expense', TRUE),

-- Income categories
(NULL, 'Gaji', '💰', '#10b981', 'income', TRUE),
(NULL, 'Uang Saku', '🎁', '#3b82f6', 'income', TRUE),
(NULL, 'Bonus', '🏆', '#8b5cf6', 'income', TRUE),
(NULL, 'Investasi', '📈', '#f59e0b', 'income', TRUE),
(NULL, 'Lainnya', '💵', '#6b7280', 'income', TRUE);

-- Insert demo user
INSERT INTO users (username, email, password_hash, monthly_budget) VALUES
('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1500000);

-- Insert sample transactions for demo user
INSERT INTO transactions (user_id, category_id, type, description, amount, date, notes) VALUES
(1, 1, 'expense', 'Makan siang warteg', 15000, '2024-12-09', 'Nasi + ayam'),
(1, 2, 'expense', 'Beli kopi', 8000, '2024-12-09', 'Kopi susu gula aren'),
(1, 3, 'expense', 'Token listrik', 100000, '2024-12-08', 'Token 100rb'),
(1, 5, 'expense', 'Ojol ke kampus', 12000, '2024-12-08', 'Gojek'),
(1, 13, 'income', 'Uang saku bulanan', 1500000, '2024-12-01', 'Transfer dari ortu'),
(1, 1, 'expense', 'Makan malam', 20000, '2024-12-07', 'Ayam geprek'),
(1, 4, 'expense', 'Laundry kiloan', 25000, '2024-12-06', '5kg baju'),
(1, 6, 'expense', 'Bayar wifi', 150000, '2024-12-05', 'Indihome bulanan');