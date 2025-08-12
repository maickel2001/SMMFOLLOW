-- Création de la base de données SMM
CREATE DATABASE IF NOT EXISTS smm_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smm_website;

-- Table des catégories de services
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des services
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price_per_1000 DECIMAL(10,2) NOT NULL,
    min_quantity INT NOT NULL DEFAULT 1000,
    max_quantity INT NOT NULL DEFAULT 100000,
    platform ENUM('Instagram', 'TikTok', 'YouTube', 'Facebook') NOT NULL,
    type ENUM('Followers', 'Likes', 'Views', 'Comments') NOT NULL,
    delivery_time VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Table des commandes
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    service_id INT NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    link_url TEXT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('En attente', 'En cours', 'Terminée', 'Annulée') DEFAULT 'En attente',
    payment_proof VARCHAR(255),
    payment_method ENUM('MTN Money', 'Moov Money') NOT NULL,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Table des administrateurs
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion des données par défaut
INSERT INTO categories (name, description, icon) VALUES
('Instagram', 'Services pour Instagram', 'fab fa-instagram'),
('TikTok', 'Services pour TikTok', 'fab fa-tiktok'),
('YouTube', 'Services pour YouTube', 'fab fa-youtube'),
('Facebook', 'Services pour Facebook', 'fab fa-facebook');

INSERT INTO services (category_id, name, description, price_per_1000, min_quantity, max_quantity, platform, type, delivery_time) VALUES
(1, 'Followers Instagram', 'Followers réels pour Instagram', 5000.00, 1000, 100000, 'Instagram', 'Followers', '24-48h'),
(1, 'Likes Instagram', 'Likes pour vos posts Instagram', 2000.00, 1000, 100000, 'Instagram', 'Likes', '1-2h'),
(1, 'Vues Instagram Reels', 'Vues pour vos Reels Instagram', 1500.00, 1000, 100000, 'Instagram', 'Views', '1-3h'),
(2, 'Followers TikTok', 'Followers pour TikTok', 4000.00, 1000, 100000, 'TikTok', 'Followers', '24-48h'),
(2, 'Likes TikTok', 'Likes pour vos vidéos TikTok', 1800.00, 1000, 100000, 'TikTok', 'Likes', '1-2h'),
(2, 'Vues TikTok', 'Vues pour vos vidéos TikTok', 1200.00, 1000, 100000, 'TikTok', 'Views', '1-3h'),
(3, 'Vues YouTube', 'Vues pour vos vidéos YouTube', 3000.00, 1000, 100000, 'YouTube', 'Views', '24-72h'),
(3, 'Likes YouTube', 'Likes pour vos vidéos YouTube', 2500.00, 1000, 100000, 'YouTube', 'Likes', '1-2h'),
(3, 'Abonnés YouTube', 'Abonnés pour votre chaîne YouTube', 8000.00, 1000, 100000, 'YouTube', 'Followers', '48-72h'),
(4, 'Likes Facebook', 'Likes pour vos posts Facebook', 2200.00, 1000, 100000, 'Facebook', 'Likes', '1-2h'),
(4, 'Followers Facebook', 'Followers pour votre page Facebook', 4500.00, 1000, 100000, 'Facebook', 'Followers', '24-48h');

-- Insertion de l'admin par défaut (mot de passe: admin123)
INSERT INTO admins (email, password, name) VALUES
('admin@smm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur');