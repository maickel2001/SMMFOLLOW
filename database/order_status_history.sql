-- Table pour l'historique des statuts des commandes
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL,
  `notes` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `admin_id` (`admin_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des données d'exemple pour les commandes existantes
INSERT INTO `order_status_history` (`order_id`, `status`, `notes`, `created_at`) 
SELECT 
    o.id,
    o.status,
    CASE 
        WHEN o.status = 'pending' THEN 'Commande créée et en attente de traitement'
        WHEN o.status = 'processing' THEN 'Commande en cours de traitement'
        WHEN o.status = 'completed' THEN 'Commande livrée avec succès'
        WHEN o.status = 'cancelled' THEN 'Commande annulée'
        ELSE 'Statut mis à jour'
    END,
    o.created_at
FROM `orders` o
WHERE o.id NOT IN (SELECT DISTINCT order_id FROM `order_status_history`);

-- Index pour optimiser les requêtes
CREATE INDEX idx_order_status_history_order_status ON `order_status_history` (`order_id`, `status`);
CREATE INDEX idx_order_status_history_created_at ON `order_status_history` (`created_at`);