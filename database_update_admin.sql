-- Update database untuk menambahkan role system
USE dompet_sesat;

-- Tambah kolom role ke tabel users
ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER password_hash;

-- Update user demo menjadi admin (opsional)
UPDATE users SET role = 'admin' WHERE username = 'demo';

-- Buat user admin baru
INSERT INTO users (username, email, password_hash, role, monthly_budget) VALUES
('admin', 'admin@dompetsesat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0);

-- Catatan: Password default untuk admin adalah 'password'
-- Silakan ubah setelah login pertama kali