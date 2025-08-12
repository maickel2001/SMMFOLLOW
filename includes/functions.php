<?php
session_start();

// Fonction pour nettoyer les entrées utilisateur
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour générer un numéro de commande unique
function generateOrderNumber() {
    return 'SMM' . date('Ymd') . rand(1000, 9999);
}

// Fonction pour formater le prix en FCFA
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}

// Fonction pour vérifier si l'utilisateur est connecté en tant qu'admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Fonction pour rediriger
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fonction pour afficher les messages d'alerte
function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Fonction pour récupérer les services par catégorie
function getServicesByCategory($categoryId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM services WHERE category_id = ? AND is_active = 1 ORDER BY name");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}

// Fonction pour récupérer toutes les catégories
function getAllCategories() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

// Fonction pour récupérer un service par ID
function getServiceById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Fonction pour calculer le prix total
function calculateTotalPrice($serviceId, $quantity) {
    $service = getServiceById($serviceId);
    if ($service) {
        return ($service['price_per_1000'] * $quantity) / 1000;
    }
    return 0;
}

// Fonction pour uploader une image
function uploadImage($file, $targetDir = 'uploads/') {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $targetDir . $fileName;
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    
    return false;
}

// Nouvelles fonctions ajoutées

// Fonction pour envoyer une notification email
function sendEmailNotification($to, $subject, $message) {
    $headers = "From: noreply@smmpro.com\r\n";
    $headers .= "Reply-To: contact@smmpro.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Fonction pour générer un token de sécurité
function generateSecurityToken() {
    return bin2hex(random_bytes(32));
}

// Fonction pour vérifier un token de sécurité
function verifySecurityToken($token, $storedToken) {
    return hash_equals($storedToken, $token);
}

// Fonction pour obtenir les statistiques avancées
function getAdvancedStats() {
    $pdo = getDBConnection();
    
    $stats = [];
    
    // Statistiques des commandes par mois
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
               COUNT(*) as total_orders,
               SUM(total_price) as total_revenue
        FROM orders 
        WHERE status = 'Terminée'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ");
    $stats['monthly_stats'] = $stmt->fetchAll();
    
    // Top des services
    $stmt = $pdo->query("
        SELECT s.name, COUNT(o.id) as order_count, SUM(o.total_price) as revenue
        FROM services s
        JOIN orders o ON s.id = o.service_id
        WHERE o.status = 'Terminée'
        GROUP BY s.id
        ORDER BY order_count DESC
        LIMIT 10
    ");
    $stats['top_services'] = $stmt->fetchAll();
    
    // Statistiques par plateforme
    $stmt = $pdo->query("
        SELECT s.platform, COUNT(o.id) as order_count, SUM(o.total_price) as revenue
        FROM services s
        JOIN orders o ON s.id = o.service_id
        WHERE o.status = 'Terminée'
        GROUP BY s.platform
        ORDER BY revenue DESC
    ");
    $stats['platform_stats'] = $stmt->fetchAll();
    
    return $stats;
}

// Fonction pour créer un ticket de support
function createSupportTicket($customerEmail, $customerName, $subject, $message, $orderId = null) {
    $pdo = getDBConnection();
    
    $ticketNumber = 'TKT' . date('Ymd') . rand(1000, 9999);
    
    $stmt = $pdo->prepare("
        INSERT INTO support_tickets (ticket_number, customer_email, customer_name, subject, message, order_id, status)
        VALUES (?, ?, ?, ?, ?, ?, 'Ouvert')
    ");
    
    if ($stmt->execute([$ticketNumber, $customerEmail, $customerName, $subject, $message, $orderId])) {
        return $pdo->lastInsertId();
    }
    
    return false;
}

// Fonction pour récupérer les tickets de support
function getSupportTickets($status = null, $limit = null) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM support_tickets ORDER BY created_at DESC";
    $params = [];
    
    if ($status) {
        $sql = "SELECT * FROM support_tickets WHERE status = ? ORDER BY created_at DESC";
        $params = [$status];
    }
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Fonction pour mettre à jour le statut d'un ticket
function updateTicketStatus($ticketId, $status, $adminResponse = null) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        UPDATE support_tickets 
        SET status = ?, admin_response = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    return $stmt->execute([$status, $adminResponse, $ticketId]);
}

// Fonction pour obtenir l'historique des commandes d'un client
function getCustomerOrderHistory($email) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name, c.name as category_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE o.customer_email = ?
        ORDER BY o.created_at DESC
    ");
    
    $stmt->execute([$email]);
    return $stmt->fetchAll();
}

// Fonction pour vérifier le statut d'une commande
function getOrderStatus($orderNumber) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        WHERE o.order_number = ?
    ");
    
    $stmt->execute([$orderNumber]);
    return $stmt->fetch();
}

// Fonction pour créer une facture PDF
function generateInvoice($orderId) {
    // Cette fonction nécessiterait une bibliothèque comme TCPDF ou FPDF
    // Pour l'instant, retournons les données de la facture
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name, s.platform, s.type,
               c.name as category_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE o.id = ?
    ");
    
    $stmt->execute([$orderId]);
    return $stmt->fetch();
}

// Fonction pour obtenir les notifications admin
function getAdminNotifications() {
    $pdo = getDBConnection();
    
    $notifications = [];
    
    // Commandes en attente de paiement
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM orders 
        WHERE status = 'En attente' AND payment_proof IS NULL
    ");
    $stmt->execute();
    $notifications['pending_payment'] = $stmt->fetch()['count'];
    
    // Nouveaux tickets de support
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM support_tickets 
        WHERE status = 'Ouvert'
    ");
    $stmt->execute();
    $notifications['new_tickets'] = $stmt->fetch()['count'];
    
    // Commandes en cours
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM orders 
        WHERE status = 'En cours'
    ");
    $stmt->execute();
    $notifications['processing_orders'] = $stmt->fetch()['count'];
    
    return $notifications;
}

// Fonction pour logger les actions admin
function logAdminAction($adminId, $action, $details = null) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    return $stmt->execute([$adminId, $action, $details, $ipAddress, $userAgent]);
}

// Fonction pour nettoyer les anciens logs
function cleanupOldLogs($days = 30) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        DELETE FROM admin_logs 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
    ");
    
    return $stmt->execute([$days]);
}

// Fonction pour obtenir les métriques de performance
function getPerformanceMetrics() {
    $pdo = getDBConnection();
    
    $metrics = [];
    
    // Temps moyen de traitement des commandes
    $stmt = $pdo->query("
        SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_processing_time
        FROM orders 
        WHERE status = 'Terminée' AND updated_at > created_at
    ");
    $metrics['avg_processing_time'] = $stmt->fetch()['avg_processing_time'];
    
    // Taux de conversion (commandes terminées / total)
    $stmt = $pdo->query("
        SELECT 
            COUNT(CASE WHEN status = 'Terminée' THEN 1 END) as completed,
            COUNT(*) as total
        FROM orders
    ");
    $result = $stmt->fetch();
    $metrics['conversion_rate'] = $result['total'] > 0 ? 
        round(($result['completed'] / $result['total']) * 100, 2) : 0;
    
    return $metrics;
}

// Fonction pour vérifier la validité d'un email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Fonction pour formater la date en français
function formatDateFrench($date) {
    $months = [
        '01' => 'janvier', '02' => 'février', '03' => 'mars', '04' => 'avril',
        '05' => 'mai', '06' => 'juin', '07' => 'juillet', '08' => 'août',
        '09' => 'septembre', '10' => 'octobre', '11' => 'novembre', '12' => 'décembre'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[date('m', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

// Fonction pour obtenir le temps écoulé depuis une date
function getTimeAgo($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return "À l'instant";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "Il y a $minutes minute" . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "Il y a $hours heure" . ($hours > 1 ? 's' : '');
    } else {
        $days = floor($diff / 86400);
        return "Il y a $days jour" . ($days > 1 ? 's' : '');
    }
}
?>