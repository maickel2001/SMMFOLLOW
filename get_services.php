<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier que c'est une requête AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Accès direct non autorisé');
}

// Vérifier la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Méthode non autorisée');
}

// Récupérer l'ID de la catégorie
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($categoryId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de catégorie invalide'
    ]);
    exit;
}

try {
    // Récupérer les services de la catégorie
    $services = getServicesByCategory($categoryId);
    
    if ($services && count($services) > 0) {
        echo json_encode([
            'success' => true,
            'services' => $services,
            'count' => count($services)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'services' => [],
            'count' => 0,
            'message' => 'Aucun service trouvé pour cette catégorie'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des services',
        'error' => $e->getMessage()
    ]);
}
?>