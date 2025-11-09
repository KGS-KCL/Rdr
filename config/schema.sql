-- KGS ISG Yönetim Sistemi Veritabanı Şeması

-- Roller Tablosu
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

-- Kullanıcılar Tablosu
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tckn VARCHAR(11) UNIQUE,
    staff_id VARCHAR(50) UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    photo VARCHAR(255),
    role_id INT NOT NULL,
    company_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Tedarikçi Şirketler Tablosu
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kullanıcılar tablosuna company_id için foreign key eklemesi
ALTER TABLE users ADD FOREIGN KEY (company_id) REFERENCES companies(id);

-- İstenen Evrak Tipleri Tablosu
CREATE TABLE document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_name VARCHAR(255) NOT NULL,
    default_validity_days INT DEFAULT 365,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Evraklar Tablosu (Çalışanlara ait)
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_type_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATE,
    status ENUM('Beklemede', 'Onaylandı', 'Reddedildi', 'Süresi Doldu') DEFAULT 'Beklemede',
    rejection_reason TEXT,
    approved_by INT,
    approved_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (document_type_id) REFERENCES document_types(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Eğitimler Tablosu
CREATE TABLE trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_url VARCHAR(255),
    file_path VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Test Soruları Tablosu
CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_id INT NOT NULL,
    question TEXT NOT NULL,
    FOREIGN KEY (training_id) REFERENCES trainings(id) ON DELETE CASCADE
);

-- Test Cevapları Tablosu
CREATE TABLE quiz_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- Eğitim Atamaları ve Sonuçları
CREATE TABLE training_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    training_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP,
    score DECIMAL(5, 2),
    status ENUM('Atandı', 'Başladı', 'Tamamlandı', 'Başarısız') DEFAULT 'Atandı',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (training_id) REFERENCES trainings(id)
);

-- Kapı Giriş/Çıkış Logları
CREATE TABLE gate_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action ENUM('Giriş', 'Çıkış') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    security_personnel_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (security_personnel_id) REFERENCES users(id)
);

-- Sistem Ayarları
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT
);

-- Sistem Logları
CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    action VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Rolleri ekleyelim
INSERT INTO `roles` (`role_name`) VALUES
('Süper Admin'),
('ISG Uzmanı'),
('Tedarikçi'),
('Alt Kullanıcı'),
('Evrak Kontrol / Güvenlik');
