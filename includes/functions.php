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
?>