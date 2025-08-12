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

// Fonction pour vérifier si l'utilisateur est connecté
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fonction pour obtenir les informations de l'utilisateur connecté
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
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
function createSupportTicket($customerEmail, $customerName, $subject, $message, $orderId = null, $userId = null) {
    $pdo = getDBConnection();
    
    $ticketNumber = 'TKT' . date('Ymd') . rand(1000, 9999);
    
    $stmt = $pdo->prepare("
        INSERT INTO support_tickets (ticket_number, user_id, customer_email, customer_name, subject, message, order_id, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Ouvert')
    ");
    
    if ($stmt->execute([$ticketNumber, $userId, $customerEmail, $customerName, $subject, $message, $orderId])) {
        return $pdo->lastInsertId();
    }
    
    return false;
}

// Fonction pour récupérer les tickets de support
function getSupportTickets($status = null, $limit = null, $userId = null) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM support_tickets";
    $params = [];
    $conditions = [];
    
    if ($status) {
        $conditions[] = "status = ?";
        $params[] = $status;
    }
    
    if ($userId) {
        $conditions[] = "user_id = ?";
        $params[] = $userId;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $sql .= " ORDER BY created_at DESC";
    
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
function getCustomerOrderHistory($email, $userId = null) {
    $pdo = getDBConnection();
    
    $sql = "
        SELECT o.*, s.name as service_name, c.name as category_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN categories c ON s.category_id = c.id
    ";
    
    $params = [];
    if ($userId) {
        $sql .= " WHERE o.user_id = ?";
        $params[] = $userId;
    } else {
        $sql .= " WHERE o.customer_email = ?";
        $params[] = $email;
    }
    
    $sql .= " ORDER BY o.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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

// Fonction pour obtenir les notifications utilisateur
function getUserNotifications($userId, $limit = 10) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

// Fonction pour marquer une notification comme lue
function markNotificationAsRead($notificationId, $userId) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND user_id = ?
    ");
    
    return $stmt->execute([$notificationId, $userId]);
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

// Nouvelles fonctions pour la gestion des utilisateurs

// Fonction pour créer un nouvel utilisateur
function createUser($username, $email, $password, $firstName, $lastName, $phone = null, $country = null) {
    $pdo = getDBConnection();
    
    // Vérifier si l'email ou le nom d'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    
    if ($stmt->fetch()) {
        return false; // Utilisateur existe déjà
    }
    
    // Hasher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Générer un token de vérification
    $verificationToken = generateSecurityToken();
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, first_name, last_name, phone, country, verification_token)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName, $phone, $country, $verificationToken])) {
        return $pdo->lastInsertId();
    }
    
    return false;
}

// Fonction pour authentifier un utilisateur
function authenticateUser($email, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND is_active = 1");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Mettre à jour la dernière connexion
        $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return $user;
    }
    
    return false;
}

// Fonction pour obtenir les statistiques d'un utilisateur
function getUserStats($userId) {
    $pdo = getDBConnection();
    
    $stats = [];
    
    // Total des commandes
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['total_orders'] = $stmt->fetch()['total'];
    
    // Commandes par statut
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM orders 
        WHERE user_id = ? 
        GROUP BY status
    ");
    $stmt->execute([$userId]);
    $stats['orders_by_status'] = $stmt->fetchAll();
    
    // Total dépensé
    $stmt = $pdo->prepare("
        SELECT SUM(total_price) as total 
        FROM orders 
        WHERE user_id = ? AND status = 'Terminée'
    ");
    $stmt->execute([$userId]);
    $stats['total_spent'] = $stmt->fetch()['total'] ?: 0;
    
    // Tickets de support
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM support_tickets 
        WHERE user_id = ? 
        GROUP BY status
    ");
    $stmt->execute([$userId]);
    $stats['tickets_by_status'] = $stmt->fetchAll();
    
    return $stats;
}

// Fonction pour mettre à jour le profil utilisateur
function updateUserProfile($userId, $firstName, $lastName, $phone, $country) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET first_name = ?, last_name = ?, phone = ?, country = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    return $stmt->execute([$firstName, $lastName, $phone, $country, $userId]);
}

// Fonction pour changer le mot de passe
function changeUserPassword($userId, $currentPassword, $newPassword) {
    $pdo = getDBConnection();
    
    // Vérifier l'ancien mot de passe
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return false;
    }
    
    // Hasher le nouveau mot de passe
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    return $stmt->execute([$hashedPassword, $userId]);
}

// Fonction pour récupérer les commandes d'un utilisateur
function getUserOrders($userId, $limit = null) {
    $pdo = getDBConnection();
    
    $sql = "
        SELECT o.*, s.name as service_name, s.platform, c.name as category_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Fonction pour créer une commande avec un utilisateur connecté
function createOrderWithUser($userId, $serviceId, $customerEmail, $customerName, $linkUrl, $quantity, $paymentMethod) {
    $pdo = getDBConnection();
    
    $totalPrice = calculateTotalPrice($serviceId, $quantity);
    $orderNumber = generateOrderNumber();
    
    $stmt = $pdo->prepare("
        INSERT INTO orders (order_number, user_id, service_id, customer_email, customer_name, link_url, quantity, total_price, payment_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$orderNumber, $userId, $serviceId, $customerEmail, $customerName, $linkUrl, $quantity, $totalPrice, $paymentMethod])) {
        return $pdo->lastInsertId();
    }
    
    return false;
}
?>