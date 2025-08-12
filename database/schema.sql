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
    role ENUM('admin', 'moderator') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Nouvelles tables ajoutées

-- Table des tickets de support
CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(20) UNIQUE NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    order_id INT NULL,
    status ENUM('Ouvert', 'En cours', 'Résolu', 'Fermé') DEFAULT 'Ouvert',
    priority ENUM('Faible', 'Normale', 'Élevée', 'Urgente') DEFAULT 'Normale',
    admin_response TEXT,
    admin_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Table des logs d'administration
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Table des notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('order', 'ticket', 'system', 'payment') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    admin_id INT NULL,
    order_id INT NULL,
    ticket_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE
);

-- Table des paramètres du système
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des clients (pour le suivi)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    country VARCHAR(100),
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    first_order_date TIMESTAMP NULL,
    last_order_date TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des promotions et codes de réduction
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    max_discount DECIMAL(10,2) NULL,
    usage_limit INT NULL,
    used_count INT DEFAULT 0,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des avis et témoignages
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    service_id INT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
);

-- Table des FAQ
CREATE TABLE faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(300) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'Général',
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des pages de contenu
CREATE TABLE content_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    meta_description TEXT,
    meta_keywords TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des statistiques de visite
CREATE TABLE page_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_url VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(255),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
INSERT INTO admins (email, password, name, role) VALUES
('admin@smm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur Principal', 'admin'),
('moderator@smm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Modérateur', 'moderator');

-- Insertion des paramètres système par défaut
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'SMM Pro', 'Nom du site'),
('site_description', 'Services de Marketing sur les Réseaux Sociaux', 'Description du site'),
('contact_email', 'contact@smmpro.com', 'Email de contact principal'),
('whatsapp_number', '+225 0123456789', 'Numéro WhatsApp'),
('mtn_money_number', '0123456789', 'Numéro MTN Money'),
('moov_money_number', '0123456789', 'Numéro Moov Money'),
('currency', 'FCFA', 'Devise utilisée'),
('min_order_amount', '1000', 'Montant minimum de commande'),
('auto_approve_orders', 'false', 'Approuver automatiquement les commandes'),
('maintenance_mode', 'false', 'Mode maintenance'),
('google_analytics_id', '', 'ID Google Analytics'),
('facebook_pixel_id', '', 'ID Facebook Pixel');

-- Insertion des FAQ par défaut
INSERT INTO faq (question, answer, category, order_index) VALUES
('Comment fonctionne le service ?', 'Nous fournissons des followers, likes et vues de qualité pour vos comptes de réseaux sociaux. Après votre commande et paiement, nous traitons votre demande dans les délais indiqués.', 'Général', 1),
('Combien de temps faut-il pour recevoir mes followers ?', 'Les délais varient selon le service choisi. Généralement entre 1 heure et 72 heures selon la quantité et le type de service.', 'Livraison', 2),
('Les followers sont-ils réels ?', 'Nous fournissons des followers de haute qualité qui respectent les normes des plateformes sociales.', 'Qualité', 3),
('Comment payer mes commandes ?', 'Nous acceptons les paiements via MTN Money et Moov Money. Les instructions détaillées vous seront fournies après votre commande.', 'Paiement', 4),
('Que faire si je ne suis pas satisfait ?', 'Notre équipe support est disponible 24/7 pour vous aider. Contactez-nous en cas de problème.', 'Support', 5);

-- Insertion des pages de contenu par défaut
INSERT INTO content_pages (slug, title, content, meta_description) VALUES
('a-propos', 'À Propos de SMM Pro', '<h2>Qui sommes-nous ?</h2><p>SMM Pro est votre partenaire de confiance pour le marketing sur les réseaux sociaux. Nous offrons des services de qualité pour booster votre présence en ligne.</p><h2>Notre Mission</h2><p>Permettre aux entreprises et particuliers d''accroître leur visibilité sur les réseaux sociaux de manière efficace et sécurisée.</p>', 'Découvrez SMM Pro, votre partenaire pour le marketing sur les réseaux sociaux'),
('conditions-utilisation', 'Conditions d''Utilisation', '<h2>Conditions Générales</h2><p>En utilisant nos services, vous acceptez les conditions suivantes...</p>', 'Conditions d''utilisation de SMM Pro'),
('politique-confidentialite', 'Politique de Confidentialité', '<h2>Protection de vos Données</h2><p>Nous nous engageons à protéger vos informations personnelles...</p>', 'Politique de confidentialité de SMM Pro'),
('garantie', 'Garantie et Remboursement', '<h2>Notre Garantie</h2><p>Nous garantissons la qualité de nos services...</p>', 'Garantie et politique de remboursement de SMM Pro');

-- Création des index pour optimiser les performances
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_customer_email ON orders(customer_email);
CREATE INDEX idx_orders_created_at ON orders(created_at);
CREATE INDEX idx_services_category ON services(category_id);
CREATE INDEX idx_services_platform ON services(platform);
CREATE INDEX idx_support_tickets_status ON support_tickets(status);
CREATE INDEX idx_support_tickets_customer ON support_tickets(customer_email);
CREATE INDEX idx_admin_logs_admin ON admin_logs(admin_id);
CREATE INDEX idx_admin_logs_action ON admin_logs(action);
CREATE INDEX idx_notifications_admin ON notifications(admin_id);
CREATE INDEX idx_notifications_type ON notifications(type);