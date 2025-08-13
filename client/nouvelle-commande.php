<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de la connexion utilisateur
if (!isUserLoggedIn()) {
    redirect('../connexion.php');
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    session_destroy();
    redirect('../connexion.php');
}

$success = '';
$error = '';
$orderData = [];

// Récupération des catégories et services avec cache et optimisation
try {
    $categories = getAllCategories();
    $services = [];
    $serviceCache = [];
    
    foreach ($categories as $category) {
        $categoryServices = getServicesByCategory($category['id']);
        $services[$category['id']] = $categoryServices;
        
        // Créer un cache des services pour un accès rapide
        foreach ($categoryServices as $service) {
            $serviceCache[$service['id']] = $service;
        }
    }
} catch (Exception $e) {
    $categories = [];
    $services = [];
    $serviceCache = [];
    $error = 'Erreur lors du chargement des services. Veuillez réessayer.';
}

// Traitement de la commande avec validation avancée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = (int)($_POST['service_id'] ?? 0);
    $linkUrl = cleanInput($_POST['link_url'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? '';
    $specialInstructions = cleanInput($_POST['special_instructions'] ?? '');
    $startDate = $_POST['start_date'] ?? '';
    
    // Validation avancée
    $validationErrors = [];
    
    if (!$serviceId || !isset($serviceCache[$serviceId])) {
        $validationErrors[] = 'Service invalide sélectionné.';
    }
    
    if (empty($linkUrl) || !filter_var($linkUrl, FILTER_VALIDATE_URL)) {
        $validationErrors[] = 'Veuillez entrer une URL valide.';
    }
    
    if ($quantity < 1000) {
        $validationErrors[] = 'La quantité minimale est de 1 000.';
    }
    
    if (empty($paymentMethod)) {
        $validationErrors[] = 'Veuillez sélectionner une méthode de paiement.';
    }
    
    // Validation spécifique au service
    if ($serviceId && isset($serviceCache[$serviceId])) {
        $selectedService = $serviceCache[$serviceId];
        if ($quantity < $selectedService['min_quantity']) {
            $validationErrors[] = "La quantité minimale pour ce service est de " . number_format($selectedService['min_quantity'], 0, ',', ' ') . ".";
        }
        if ($quantity > $selectedService['max_quantity']) {
            $validationErrors[] = "La quantité maximale pour ce service est de " . number_format($selectedService['max_quantity'], 0, ',', ' ') . ".";
        }
    }
    
    if (empty($validationErrors)) {
        try {
            // Créer la commande avec données étendues
            $orderId = createOrderWithUser(
                $currentUser['id'],
                $serviceId,
                $currentUser['email'],
                $currentUser['first_name'] . ' ' . $currentUser['last_name'],
                $linkUrl,
                $quantity,
                $paymentMethod
            );
            
            if ($orderId) {
                // Sauvegarder les données de la commande pour l'affichage
                $orderData = [
                    'id' => $orderId,
                    'service' => $serviceCache[$serviceId],
                    'quantity' => $quantity,
                    'total_price' => ($serviceCache[$serviceId]['price_per_1000'] * $quantity) / 1000,
                    'link_url' => $linkUrl,
                    'payment_method' => $paymentMethod
                ];
                
                $success = 'Commande créée avec succès ! Redirection vers la page de paiement...';
                
                // Rediriger vers la page de paiement après 3 secondes
                header("refresh:3;url=../paiement.php?order_id=" . $orderId);
            } else {
                $error = 'Erreur lors de la création de la commande. Veuillez réessayer.';
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de la création de la commande : ' . $e->getMessage();
        }
    } else {
        $error = implode(' ', $validationErrors);
    }
}

// Récupération des commandes récentes de l'utilisateur pour suggestions
try {
    $recentOrders = getUserOrders($currentUser['id'], 3);
} catch (Exception $e) {
    $recentOrders = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Commande - SMM Pro</title>
    <meta name="description" content="Créez une nouvelle commande SMM pour booster votre présence sur les réseaux sociaux">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 pour les notifications -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f5f5f7;
            --bg-tertiary: #fafafa;
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --text-tertiary: #6e6e73;
            --accent-primary: #007aff;
            --accent-secondary: #5856d6;
            --accent-success: #34c759;
            --accent-warning: #ff9500;
            --accent-danger: #ff3b30;
            --border-light: #d2d2d7;
            --border-lighter: #e5e5e7;
            --shadow-subtle: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-medium: 0 4px 16px rgba(0, 0, 0, 0.08);
            --shadow-large: 0 8px 32px rgba(0, 0, 0, 0.12);
            --radius-small: 8px;
            --radius-medium: 12px;
            --radius-large: 16px;
            --radius-xl: 24px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: 400;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .client-container {
            padding-top: 20px;
            min-height: 100vh;
            background: var(--bg-primary);
        }
        
        /* Header Principal */
        .main-header {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border-radius: var(--radius-xl);
            padding: 40px;
            margin-bottom: 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-subtle);
        }
        
        .header-content {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            font-size: 2rem;
            color: white;
            box-shadow: var(--shadow-medium);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        
        .header-subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            font-weight: 400;
            margin-bottom: 0;
        }
        
        /* Navigation Client Minimaliste */
        .client-nav {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 24px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-subtle);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        
        .client-nav .nav-link {
            color: var(--text-secondary);
            padding: 12px 20px;
            border-radius: var(--radius-medium);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            margin: 0 4px;
        }
        
        .client-nav .nav-link:hover,
        .client-nav .nav-link.active {
            color: var(--accent-primary);
            background: rgba(0, 122, 255, 0.04);
            transform: translateY(-1px);
        }
        
        /* Cartes et Formulaires Minimalistes */
        .card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            box-shadow: var(--shadow-subtle);
        }
        
        .card-header {
            background: var(--bg-secondary);
            border-color: var(--border-lighter);
            border-radius: var(--radius-large) var(--radius-large) 0 0;
        }
        
        .form-control, .form-select, .form-textarea {
            background: var(--bg-secondary);
            border: 1px solid var(--border-lighter);
            color: var(--text-primary);
            border-radius: var(--radius-medium);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 12px 16px;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus, .form-textarea:focus {
            background: var(--bg-primary);
            border-color: var(--accent-primary);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
            outline: none;
        }
        
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: var(--radius-medium);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
        }
        
        .btn-primary:hover {
            background: #0056cc;
            border-color: #0056cc;
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
        }
        
        .btn-primary:disabled {
            background: var(--border-lighter);
            border-color: var(--border-lighter);
            color: var(--text-tertiary);
            transform: none;
            box-shadow: none;
        }
        
        /* Résumé de Commande Minimaliste */
        .order-summary {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px;
            margin-top: 20px;
            position: sticky;
            top: 20px;
            box-shadow: var(--shadow-subtle);
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--border-lighter);
        }
        
        .summary-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--accent-primary);
            padding-top: 20px;
        }
        
        /* Cartes de Service Minimalistes */
        .service-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 24px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-subtle);
        }
        
        .service-card:hover {
            border-color: var(--accent-primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }
        
        .service-card.selected {
            border-color: var(--accent-primary);
            background: rgba(0, 122, 255, 0.02);
            box-shadow: var(--shadow-medium);
        }
        
        .service-card.selected::after {
            content: '✓';
            position: absolute;
            top: 16px;
            right: 16px;
            background: var(--accent-primary);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .service-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .service-platform {
            color: var(--primary-color);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .service-price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .service-description {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .service-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .service-detail {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .service-detail i {
            color: var(--primary-color);
            width: 16px;
        }
        
        .quantity-input {
            max-width: 200px;
        }
        
        .price-display {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            padding: 25px;
            background: var(--darker-bg);
            border-radius: 15px;
            border: 3px solid var(--primary-color);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
        }
        
        .progress-bar {
            height: 6px;
            background: var(--border-color);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        
        /* Sections de Formulaire Minimalistes */
        .form-section {
            background: var(--bg-primary);
            border-radius: var(--radius-large);
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid var(--border-lighter);
            box-shadow: var(--shadow-subtle);
        }
        
        .form-section h5 {
            color: var(--accent-primary);
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-lighter);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .form-section h5 i {
            font-size: 1.25rem;
        }
        
        /* Suggestions Minimalistes */
        .suggestions {
            background: rgba(0, 122, 255, 0.02);
            border: 1px solid var(--accent-primary);
            border-radius: var(--radius-medium);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-subtle);
        }
        
        .suggestions h6 {
            color: var(--accent-primary);
            margin-bottom: 16px;
            font-weight: 600;
        }
        
        .suggestion-item {
            background: var(--bg-primary);
            border-radius: var(--radius-small);
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--border-lighter);
        }
        
        .suggestion-item:hover {
            border-color: var(--accent-primary);
            background: rgba(0, 122, 255, 0.02);
            transform: translateY(-1px);
        }
        
        .suggestion-item:last-child {
            margin-bottom: 0;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading-spinner.show {
            display: block;
        }
        
        .spinner-border {
            color: var(--primary-color);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 20px;
        }
        
        .floating-label input,
        .floating-label textarea,
        .floating-label select {
            width: 100%;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 1rem;
        }
        
        .floating-label label {
            position: absolute;
            top: 15px;
            left: 20px;
            color: var(--text-secondary);
            transition: all 0.3s ease;
            pointer-events: none;
            background: var(--darker-bg);
            padding: 0 5px;
        }
        
        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label,
        .floating-label textarea:focus + label,
        .floating-label textarea:not(:placeholder-shown) + label,
        .floating-label select:focus + label,
        .floating-label select:not([value=""]) + label {
            top: -8px;
            left: 15px;
            font-size: 0.8rem;
            color: var(--primary-color);
        }
        
        /* ===== QUANTITY SLIDER MODERNE & INTUITIF ===== */
        .quantity-slider {
            width: 100%;
            margin: 30px 0;
            position: relative;
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border-radius: var(--radius-large);
            padding: 30px;
            border: 1px solid var(--border-lighter);
            box-shadow: var(--shadow-subtle);
        }
        
        .quantity-slider-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .quantity-label {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-label i {
            color: var(--accent-primary);
            font-size: 1.2rem;
        }
        
        .quantity-value-display {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: var(--shadow-medium);
            min-width: 80px;
            text-align: center;
        }
        
        /* Slider Range Moderne */
        .quantity-slider input[type="range"] {
            width: 100%;
            height: 12px;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            outline: none;
            -webkit-appearance: none;
            cursor: grab;
            position: relative;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .quantity-slider input[type="range"]:hover {
            transform: scale(1.02);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15), 0 0 0 3px rgba(0, 122, 255, 0.1);
        }
        
        .quantity-slider input[type="range"]:focus {
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15), 0 0 0 4px rgba(0, 122, 255, 0.2);
        }
        
        .quantity-slider input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.4), 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 3px solid var(--accent-primary);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scale(1);
        }
        
        .quantity-slider input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 122, 255, 0.6), 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .quantity-slider input[type="range"]::-webkit-slider-thumb:active {
            transform: scale(0.95);
        }
        
        .quantity-slider input[type="range"]::-moz-range-thumb {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            cursor: pointer;
            border: 3px solid var(--accent-primary);
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.4), 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .quantity-slider input[type="range"]::-moz-range-thumb:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 122, 255, 0.6), 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Track Progress */
        .quantity-slider input[type="range"]::-webkit-slider-runnable-track {
            height: 12px;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
        }
        
        .quantity-slider input[type="range"]::-moz-range-track {
            height: 12px;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
        }
        
        /* Presets de Quantité Modernes */
        .quantity-presets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 12px;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid var(--border-lighter);
        }
        
        .quantity-preset {
            padding: 12px 16px;
            background: var(--bg-primary);
            border: 2px solid var(--border-lighter);
            border-radius: var(--radius-medium);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.9rem;
            font-weight: 600;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .quantity-preset::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 122, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .quantity-preset:hover {
            border-color: var(--accent-primary);
            color: var(--accent-primary);
            background: rgba(0, 122, 255, 0.05);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }
        
        .quantity-preset:hover::before {
            left: 100%;
        }
        
        .quantity-preset.active {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-color: var(--accent-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            position: relative;
        }
        
        .quantity-preset.active::before {
            display: none;
        }
        
        .quantity-preset.active::after {
            content: '✓';
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent-success);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
            animation: checkmarkAppear 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes checkmarkAppear {
            from {
                opacity: 0;
                transform: scale(0) rotate(-180deg);
            }
            to {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }
        
        /* Tooltips intelligents */
        .smart-tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            z-index: 10000;
            pointer-events: none;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .smart-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: rgba(0, 0, 0, 0.9);
        }
        
        /* Marqueurs du slider */
        .slider-marker {
            position: absolute;
            width: 2px;
            height: 20px;
            background: var(--accent-secondary);
            border-radius: 1px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
            opacity: 0.6;
            transition: all 0.3s ease;
        }
        
        .marker-label {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.75rem;
            color: var(--text-tertiary);
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease;
            font-weight: 500;
        }
        
        /* Indicateurs de Limites */
        .quantity-limits {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 0.85rem;
            color: var(--text-tertiary);
        }
        
        .limit-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .limit-item i {
            font-size: 0.8rem;
            color: var(--accent-warning);
        }
        
        /* Animation du Slider */
        .quantity-slider.animate {
            animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive pour le Slider */
        @media (max-width: 768px) {
            .quantity-slider {
                padding: 20px;
                margin: 20px 0;
            }
            
            .quantity-slider-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .quantity-presets {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
            
            .quantity-preset {
                padding: 10px 12px;
                font-size: 0.8rem;
            }
            
            .quantity-value-display {
                font-size: 1rem;
                padding: 6px 12px;
            }
        }
        
        @media (max-width: 480px) {
            .quantity-slider {
                padding: 15px;
                margin: 15px 0;
            }
            
            .quantity-presets {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            
            .quantity-preset {
                padding: 8px 10px;
                font-size: 0.75rem;
            }
            
            .quantity-limits {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }
        
        @media (max-width: 768px) {
            .service-details {
                grid-template-columns: 1fr;
            }
            
            .price-display {
                font-size: 1.5rem;
                padding: 20px;
            }
            
            .order-summary {
                position: static;
                margin-top: 30px;
            }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-slide-up {
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        /* Menus Déroulants Minimalistes */
        .category-selector, .service-selector {
            position: relative;
            margin-bottom: 24px;
        }
        
        .category-dropdown, .service-dropdown {
            position: relative;
            width: 100%;
        }
        
        .category-dropdown-btn, .service-dropdown-btn {
            width: 100%;
            padding: 16px 20px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-medium);
            color: var(--text-primary);
            font-size: 0.95rem;
            font-weight: 500;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        
        .category-dropdown-btn:hover, .service-dropdown-btn:hover {
            border-color: var(--accent-primary);
            background: var(--bg-primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
        }
        
        .category-dropdown-btn.active, .service-dropdown-btn.active {
            border-color: var(--accent-primary);
            background: rgba(0, 122, 255, 0.02);
            box-shadow: var(--shadow-medium);
        }
        
        .dropdown-arrow {
            transition: transform 0.2s ease;
            color: var(--accent-primary);
        }
        
        .category-dropdown-btn.active .dropdown-arrow,
        .service-dropdown-btn.active .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        .category-dropdown-menu, .service-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-medium);
            margin-top: 8px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-large);
        }
        
        .category-dropdown-menu.show, .service-dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .category-option, .service-option {
            padding: 16px 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--border-lighter);
            position: relative;
        }
        
        .category-option:last-child, .service-option:last-child {
            border-bottom: none;
        }
        
        .category-option:hover, .service-option:hover {
            background: rgba(0, 122, 255, 0.02);
            transform: translateX(4px);
        }
        
        .category-option.active, .service-option.active {
            background: rgba(0, 122, 255, 0.04);
            border-left: 3px solid var(--accent-primary);
        }
        
        .category-option-content, .service-option-content {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .category-icon, .service-option-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: var(--radius-small);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
        }
        
        .category-info, .service-option-info {
            flex: 1;
            min-width: 0;
        }
        
        .category-name, .service-option-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1rem;
            margin-bottom: 4px;
        }
        
        .category-description, .service-option-description {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.4;
        }
        
        .category-count {
            flex-shrink: 0;
        }
        
        .service-option-price {
            text-align: right;
            flex-shrink: 0;
        }
        
        .price-amount {
            font-weight: 700;
            color: var(--accent-primary);
            font-size: 1rem;
        }
        
        .price-unit {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }
        
        /* Affichage du Service Sélectionné Minimaliste */
        .selected-service-display {
            margin-top: 24px;
        }
        
        .selected-service-card {
            background: var(--bg-primary);
            border: 1px solid var(--accent-primary);
            border-radius: var(--radius-large);
            padding: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-medium);
        }
        
        .selected-service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
        }
        
        .selected-service-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        
        .selected-service-info h6 {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .selected-service-meta {
            display: flex;
            gap: 12px;
        }
        
        .service-platform, .service-type {
            background: rgba(0, 122, 255, 0.08);
            color: var(--accent-primary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .selected-service-price {
            text-align: right;
        }
        
        .selected-service-price .price-amount {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--accent-primary);
            line-height: 1;
        }
        
        .selected-service-price .price-unit {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .selected-service-details {
            margin-bottom: 24px;
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: rgba(0, 122, 255, 0.02);
            border-radius: var(--radius-small);
            border: 1px solid rgba(0, 122, 255, 0.08);
        }
        
        .detail-item i {
            font-size: 1.1rem;
            width: 20px;
            color: var(--accent-primary);
        }
        
        .detail-item span {
            color: var(--text-secondary);
        }
        
        .detail-item strong {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .service-description-full {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 24px;
            padding: 20px;
            background: rgba(0, 122, 255, 0.02);
            border-radius: var(--radius-small);
            border-left: 3px solid var(--accent-primary);
        }
        
        .service-features {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        
        .feature-tag {
            background: rgba(0, 122, 255, 0.08);
            color: var(--accent-primary);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(0, 122, 255, 0.12);
        }
        
        .change-service-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: transparent;
            border: 1px solid var(--accent-primary);
            color: var(--accent-primary);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            transition: all 0.2s ease;
        }
        
        .change-service-btn:hover {
            background: var(--accent-primary);
            color: white;
            transform: translateY(-1px);
        }
        
        /* Scrollbar personnalisée */
        .category-dropdown-menu::-webkit-scrollbar,
        .service-dropdown-menu::-webkit-scrollbar {
            width: 8px;
        }
        
        .category-dropdown-menu::-webkit-scrollbar-track,
        .service-dropdown-menu::-webkit-scrollbar-track {
            background: var(--border-color);
            border-radius: 4px;
        }
        
        .category-dropdown-menu::-webkit-scrollbar-thumb,
        .service-dropdown-menu::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        
        .category-dropdown-menu::-webkit-scrollbar-thumb:hover,
        .service-dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
        
        /* Animations pour les options */
        .category-option, .service-option {
            animation: slideInRight 0.3s ease forwards;
            opacity: 0;
            transform: translateX(20px);
        }
        
        .category-option:nth-child(1) { animation-delay: 0.1s; }
        .category-option:nth-child(2) { animation-delay: 0.2s; }
        .category-option:nth-child(3) { animation-delay: 0.3s; }
        .category-option:nth-child(4) { animation-delay: 0.4s; }
        .category-option:nth-child(5) { animation-delay: 0.5s; }
        
        @keyframes slideInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Responsive design pour les menus */
        @media (max-width: 768px) {
            .category-option-content, .service-option-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .category-icon, .service-option-icon {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }
            
            .selected-service-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .detail-row {
                grid-template-columns: 1fr;
            }
            
            .service-features {
                justify-content: center;
            }
            
            .change-service-btn {
                position: static;
                margin-top: 20px;
                width: 100%;
            }
        }
        
        /* Améliorations visuelles supplémentaires */
        .category-dropdown-btn:focus, .service-dropdown-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.3);
        }
        
        .category-option:focus, .service-option:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 255, 136, 0.5);
        }
        
        /* Animation de pulsation pour les boutons actifs */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 255, 136, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(0, 255, 136, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 255, 136, 0); }
        }
        
        .category-dropdown-btn.active, .service-dropdown-btn.active {
            animation: pulse 2s infinite;
        }
        
        /* Effet de brillance sur les cartes de service */
        .selected-service-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(0, 255, 136, 0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 3s ease-in-out infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        }
    </style>
</head>
<body>
    <div class="client-container">
        <div class="container">
            <!-- Header Principal -->
            <div class="main-header animate-fade-in">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h1 class="header-title">Nouvelle Commande</h1>
                    <p class="header-subtitle">Créez votre commande SMM en quelques étapes simples</p>
                </div>
            </div>
            
            <!-- Navigation Client -->
            <div class="client-nav animate-fade-in">
                <nav class="nav nav-pills justify-content-center">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="commandes.php">
                        <i class="fas fa-shopping-cart me-2"></i>Mes Commandes
                    </a>
                    <a class="nav-link active" href="nouvelle-commande.php">
                        <i class="fas fa-plus me-2"></i>Nouvelle Commande
                    </a>
                    <a class="nav-link" href="tickets.php">
                        <i class="fas fa-ticket-alt me-2"></i>Support
                    </a>
                    <a class="nav-link" href="profil.php">
                        <i class="fas fa-user-cog me-2"></i>Mon Profil
                    </a>
                </nav>
                
                <!-- Informations utilisateur -->
                <div class="d-flex justify-content-end align-items-center mt-3">
                    <span class="text-muted me-3">
                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                    </a>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show animate-fade-in" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show animate-fade-in" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Suggestions basées sur l'historique -->
            <?php if (!empty($recentOrders)): ?>
                <div class="suggestions animate-fade-in">
                    <h6><i class="fas fa-lightbulb me-2"></i>Suggestions basées sur vos commandes récentes</h6>
                    <div class="row">
                        <?php foreach (array_slice($recentOrders, 0, 3) as $order): ?>
                            <div class="col-md-4">
                                <div class="suggestion-item" onclick="fillFromSuggestion('<?php echo htmlspecialchars($order['link_url']); ?>', <?php echo $order['quantity']; ?>)">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($order['service_name']); ?></strong>
                                            <br>
                                            <small class="text-muted">Quantité: <?php echo number_format($order['quantity'], 0, ',', ' '); ?></small>
                                        </div>
                                        <i class="fas fa-arrow-left text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Formulaire de commande -->
                <div class="col-lg-8">
                    <form method="POST" id="orderForm">
                        <!-- Champ caché pour l'ID du service -->
                        <input type="hidden" name="service_id" id="hiddenServiceId" required>
                        
                        <!-- Sélection du service -->
                        <div class="form-section animate-fade-in">
                            <h5><i class="fas fa-cogs me-2"></i>Choisir un Service</h5>
                            
                            <!-- Sélecteur de catégorie -->
                            <div class="category-selector mb-4">
                                <label class="form-label">Catégorie de Service</label>
                                <div class="category-dropdown">
                                    <button class="category-dropdown-btn" type="button" id="categoryDropdownBtn">
                                        <span class="selected-category">
                                            <i class="fas fa-th-large me-2"></i>Sélectionner une catégorie
                                        </span>
                                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                                    </button>
                                    <div class="category-dropdown-menu" id="categoryDropdownMenu">
                                        <?php foreach ($categories as $category): ?>
                                            <div class="category-option" data-category-id="<?php echo $category['id']; ?>">
                                                <div class="category-option-content">
                                                    <div class="category-icon">
                                                        <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                                                    </div>
                                                    <div class="category-info">
                                                        <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                                                        <div class="category-description"><?php echo htmlspecialchars($category['description'] ?? 'Services de qualité premium'); ?></div>
                                                    </div>
                                                    <div class="category-count">
                                                        <span class="badge bg-primary"><?php echo count($services[$category['id']] ?? []); ?> services</span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sélecteur de service -->
                            <div class="service-selector" id="serviceSelector" style="display: none;">
                                <label class="form-label">Service Spécifique</label>
                                <div class="service-dropdown">
                                    <button class="service-dropdown-btn" type="button" id="serviceDropdownBtn">
                                        <span class="selected-service">
                                            <i class="fas fa-cog me-2"></i>Choisir un service
                                        </span>
                                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                                    </button>
                                    <div class="service-dropdown-menu" id="serviceDropdownMenu">
                                        <!-- Les services seront chargés dynamiquement -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Service sélectionné - Affichage détaillé -->
                            <div class="selected-service-display" id="selectedServiceDisplay" style="display: none;">
                                <div class="selected-service-card">
                                    <div class="selected-service-header">
                                        <div class="selected-service-info">
                                            <h6 class="selected-service-name" id="selectedServiceName"></h6>
                                            <div class="selected-service-meta">
                                                <span class="service-platform" id="selectedServicePlatform"></span>
                                                <span class="service-type" id="selectedServiceType"></span>
                                            </div>
                                        </div>
                                        <div class="selected-service-price">
                                            <div class="price-amount" id="selectedServicePrice"></div>
                                            <div class="price-unit">FCFA/1000</div>
                                        </div>
                                    </div>
                                    
                                    <div class="selected-service-details">
                                        <div class="detail-row">
                                            <div class="detail-item">
                                                <i class="fas fa-clock text-primary"></i>
                                                <span>Délai: </span>
                                                <strong id="selectedServiceDelivery"></strong>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-chart-line text-success"></i>
                                                <span>Min: </span>
                                                <strong id="selectedServiceMin"></strong>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-chart-line text-warning"></i>
                                                <span>Max: </span>
                                                <strong id="selectedServiceMax"></strong>
                                            </div>
                                        </div>
                                        
                                        <div class="service-description-full" id="selectedServiceDescription"></div>
                                        
                                        <div class="service-features">
                                            <div class="feature-tag">
                                                <i class="fas fa-star text-warning"></i>
                                                Qualité Premium
                                            </div>
                                            <div class="feature-tag">
                                                <i class="fas fa-shield-alt text-success"></i>
                                                Garantie 100%
                                            </div>
                                            <div class="feature-tag">
                                                <i class="fas fa-rocket text-primary"></i>
                                                Livraison Rapide
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="button" class="btn btn-outline-primary btn-sm change-service-btn" onclick="showServiceSelector()">
                                        <i class="fas fa-edit me-1"></i>Changer de service
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Détails de la commande -->
                        <div class="form-section animate-fade-in">
                            <h5><i class="fas fa-edit me-2"></i>Détails de la Commande</h5>
                            
                            <div class="floating-label">
                                <input type="url" class="form-control" id="link_url" name="link_url" 
                                       placeholder=" " required value="<?php echo htmlspecialchars($_POST['link_url'] ?? ''); ?>">
                                <label for="link_url">Lien à Promouvoir *</label>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Entrez l'URL de votre post Instagram, vidéo TikTok, YouTube ou page Facebook
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="floating-label">
                                        <input type="number" class="form-control quantity-input" id="quantity" name="quantity" 
                                               min="1000" step="1000" placeholder=" " required 
                                               value="<?php echo htmlspecialchars($_POST['quantity'] ?? '1000'); ?>">
                                        <label for="quantity">Quantité *</label>
                                        <div class="form-text">Quantité minimale : 1 000</div>
                                    </div>
                                    
                                    <!-- Slider de quantité moderne et intuitif -->
                                    <div class="quantity-slider animate">
                                        <div class="quantity-slider-header">
                                            <div class="quantity-label">
                                                <i class="fas fa-sliders-h"></i>
                                                Ajustez la quantité
                                            </div>
                                            <div class="quantity-value-display" id="quantityDisplay">
                                                1,000
                                            </div>
                                        </div>
                                        
                                        <input type="range" id="quantitySlider" min="1000" max="100000" step="1000" value="1000">
                                        
                                        <div class="quantity-limits">
                                            <div class="limit-item">
                                                <i class="fas fa-arrow-down"></i>
                                                <span id="minLimit">1,000</span>
                                            </div>
                                            <div class="limit-item">
                                                <i class="fas fa-arrow-up"></i>
                                                <span id="maxLimit">100,000</span>
                                            </div>
                                        </div>
                                        
                                        <div class="quantity-presets">
                                            <span class="quantity-preset" data-value="1000">1K</span>
                                            <span class="quantity-preset" data-value="5000">5K</span>
                                            <span class="quantity-preset" data-value="10000">10K</span>
                                            <span class="quantity-preset" data-value="25000">25K</span>
                                            <span class="quantity-preset" data-value="50000">50K</span>
                                            <span class="quantity-preset" data-value="100000">100K</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="price-display" id="priceDisplay">
                                        0 FCFA
                                    </div>
                                </div>
                            </div>
                            
                            <div class="floating-label">
                                <textarea class="form-control" id="special_instructions" name="special_instructions" 
                                          rows="3" placeholder=" "><?php echo htmlspecialchars($_POST['special_instructions'] ?? ''); ?></textarea>
                                <label for="special_instructions">Instructions spéciales (optionnel)</label>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Ajoutez des instructions spécifiques pour votre commande
                                </div>
                            </div>
                        </div>
                        
                        <!-- Méthode de paiement -->
                        <div class="form-section animate-fade-in">
                            <h5><i class="fas fa-credit-card me-2"></i>Méthode de Paiement</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="floating-label">
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="">Choisir une méthode de paiement</option>
                                            <option value="MTN Money" <?php echo ($_POST['payment_method'] ?? '') === 'MTN Money' ? 'selected' : ''; ?>>
                                                <i class="fas fa-mobile-alt me-2"></i>MTN Money
                                            </option>
                                            <option value="Moov Money" <?php echo ($_POST['payment_method'] ?? '') === 'Moov Money' ? 'selected' : ''; ?>>
                                                <i class="fas fa-mobile-alt me-2"></i>Moov Money
                                            </option>
                                        </select>
                                        <label for="payment_method">Méthode de Paiement *</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        <strong>Paiement Sécurisé</strong><br>
                                        <small>Toutes les transactions sont sécurisées et protégées</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bouton de commande -->
                        <div class="text-center animate-fade-in">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                <i class="fas fa-shopping-cart me-2"></i>Créer la Commande
                            </button>
                            
                            <div class="loading-spinner" id="loadingSpinner">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                                <p class="mt-2">Traitement de votre commande...</p>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Résumé de la commande -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h5 class="mb-4 text-white">
                            <i class="fas fa-receipt me-2"></i>Résumé de la Commande
                        </h5>
                        
                        <div id="orderSummary">
                            <div class="text-center text-muted">
                                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                <p>Sélectionnez un service pour voir le résumé</p>
                            </div>
                        </div>
                        
                        <!-- Barre de progression -->
                        <div class="progress-bar mt-4">
                            <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                        </div>
                        <small class="text-muted text-center d-block mt-2">Progression de la commande</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JS -->
    <script>
        let selectedService = null;
        let selectedPrice = 0;
        let selectedMin = 1000;
        let selectedMax = 100000;
        let currentStep = 0;
        const totalSteps = 4;
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            initializeQuantitySlider();
            initializeQuantityPresets();
            updateProgressBar();
            initializeCategorySelector();
            initializeServiceSelector();
        });
        
        // Sélection du service
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('click', function() {
                // Désélectionner tous les services
                document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
                
                // Sélectionner ce service
                this.classList.add('selected');
                
                selectedService = this.dataset.serviceId;
                selectedPrice = parseFloat(this.dataset.price);
                selectedMin = parseInt(this.dataset.min);
                selectedMax = parseInt(this.dataset.max);
                
                // Mettre à jour les limites du slider
                updateQuantityLimits();
                
                // Mettre à jour le résumé
                updateOrderSummary();
                
                // Activer le bouton de soumission
                document.getElementById('submitBtn').disabled = false;
                
                // Animation
                this.classList.add('animate-slide-up');
                
                // Mettre à jour la progression
                currentStep = 1;
                updateProgressBar();
            });
        });
        
        // Mise à jour de la quantité
        document.getElementById('quantity').addEventListener('input', function() {
            updateOrderSummary();
            updateQuantitySlider(this.value);
            currentStep = 2;
            updateProgressBar();
        });
        
        // Mise à jour du résumé
        function updateOrderSummary() {
            const quantity = parseInt(document.getElementById('quantity').value) || 0;
            const totalPrice = (selectedPrice * quantity) / 1000;
            
            if (selectedService && quantity > 0) {
                const summaryHtml = `
                    <div class="summary-item">
                        <span>Service sélectionné</span>
                        <span id="selectedServiceName">Chargement...</span>
                    </div>
                    <div class="summary-item">
                        <span>Quantité</span>
                        <span>${quantity.toLocaleString()}</span>
                    </div>
                    <div class="summary-item">
                        <span>Prix par 1000</span>
                        <span>${selectedPrice.toLocaleString()} FCFA</span>
                    </div>
                    <div class="summary-item">
                        <span>Total</span>
                        <span>${totalPrice.toLocaleString()} FCFA</span>
                    </div>
                `;
                
                document.getElementById('orderSummary').innerHTML = summaryHtml;
                
                // Mettre à jour l'affichage du prix
                document.getElementById('priceDisplay').textContent = totalPrice.toLocaleString() + ' FCFA';
                
                // Mettre à jour le nom du service sélectionné
                const selectedCard = document.querySelector('.service-card.selected');
                if (selectedCard) {
                    const serviceName = selectedCard.querySelector('.service-name').textContent;
                    document.getElementById('selectedServiceName').textContent = serviceName;
                }
            }
        }
        
        // Initialisation du slider de quantité moderne
        function initializeQuantitySlider() {
            const slider = document.getElementById('quantitySlider');
            const input = document.getElementById('quantity');
            const quantityDisplay = document.getElementById('quantityDisplay');
            const minLimit = document.getElementById('minLimit');
            const maxLimit = document.getElementById('maxLimit');
            
            // Fonction de formatage des nombres
            function formatNumber(num) {
                return new Intl.NumberFormat('fr-FR').format(num);
            }
            
            // Mise à jour de l'affichage de la quantité avec animation fluide
            function updateQuantityDisplay(value) {
                const oldValue = parseInt(quantityDisplay.textContent.replace(/\D/g, ''));
                const newValue = value;
                
                // Animation de comptage si la valeur change
                if (oldValue !== newValue) {
                    animateNumber(oldValue, newValue, 500);
                }
                
                // Animation de mise à jour
                quantityDisplay.style.transform = 'scale(1.1)';
                quantityDisplay.style.color = '#ff6b35';
                setTimeout(() => {
                    quantityDisplay.style.transform = 'scale(1)';
                    quantityDisplay.style.color = 'white';
                }, 300);
            }
            
            // Animation de comptage fluide
            function animateNumber(start, end, duration) {
                const startTime = performance.now();
                const difference = end - start;
                
                function updateNumber(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // Fonction d'easing cubic-bezier
                    const easeProgress = progress < 0.5 
                        ? 4 * progress * progress * progress 
                        : 1 - Math.pow(-2 * progress + 2, 3) / 2;
                    
                    const currentValue = Math.round(start + (difference * easeProgress));
                    quantityDisplay.textContent = formatNumber(currentValue);
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateNumber);
                    }
                }
                
                requestAnimationFrame(updateNumber);
            }
            
            // Mise à jour des limites
            function updateLimits() {
                if (selectedService) {
                    minLimit.textContent = formatNumber(selectedService.min_quantity);
                    maxLimit.textContent = formatNumber(selectedService.max_quantity);
                }
            }
            
            // Événement de changement du slider
            slider.addEventListener('input', function() {
                const value = parseInt(this.value);
                input.value = value;
                updateQuantityDisplay(value);
                updateOrderSummary();
                currentStep = 2;
                updateProgressBar();
                
                // Mise à jour du preset actif
                updateActivePreset(value);
            });
            
            // Événement de fin de glissement
            slider.addEventListener('change', function() {
                // Animation de fin
                slider.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    slider.style.transform = 'scale(1)';
                }, 200);
            });
            
            // Initialisation
            updateQuantityDisplay(parseInt(slider.value));
            updateLimits();
        }
        
        // Initialisation des presets de quantité modernes
        function initializeQuantityPresets() {
            document.querySelectorAll('.quantity-preset').forEach(preset => {
                preset.addEventListener('click', function() {
                    const value = parseInt(this.dataset.value);
                    
                    // Vérifier les limites du service sélectionné
                    if (selectedService) {
                        if (value < selectedService.min_quantity) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Quantité trop faible',
                                text: `La quantité minimale pour ce service est de ${new Intl.NumberFormat('fr-FR').format(selectedService.min_quantity)}.`,
                                confirmButtonColor: '#00ff88'
                            });
                            return;
                        }
                        if (value > selectedService.max_quantity) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Quantité trop élevée',
                                text: `La quantité maximale pour ce service est de ${new Intl.NumberFormat('fr-FR').format(selectedService.max_quantity)}.`,
                                confirmButtonColor: '#00ff88'
                            });
                            return;
                        }
                    }
                    
                    // Mettre à jour l'input et le slider
                    document.getElementById('quantity').value = value;
                    document.getElementById('quantitySlider').value = value;
                    
                    // Mettre à jour l'affichage
                    updateQuantityDisplay(value);
                    
                    // Mettre à jour les presets actifs avec animation
                    document.querySelectorAll('.quantity-preset').forEach(p => {
                        p.classList.remove('active');
                        p.style.transform = 'scale(1)';
                    });
                    
                    this.classList.add('active');
                    this.style.transform = 'scale(1.05)';
                    
                    // Effet de particules
                    addPresetParticleEffect(this);
                    
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                    
                    updateOrderSummary();
                    currentStep = 2;
                    updateProgressBar();
                });
            });
        }
        
        // Mise à jour du preset actif
        function updateActivePreset(value) {
            document.querySelectorAll('.quantity-preset').forEach(preset => {
                const presetValue = parseInt(preset.dataset.value);
                if (presetValue === value) {
                    preset.classList.add('active');
                } else {
                    preset.classList.remove('active');
                }
            });
        }
        
        // Mise à jour des limites de quantité modernes
        function updateQuantityLimits() {
            const slider = document.getElementById('quantitySlider');
            const input = document.getElementById('quantity');
            const minLimit = document.getElementById('minLimit');
            const maxLimit = document.getElementById('maxLimit');
            
            // Mise à jour des attributs min/max
            slider.min = selectedMin;
            slider.max = selectedMax;
            input.min = selectedMin;
            input.max = selectedMax;
            
            // Mise à jour de l'affichage des limites
            minLimit.textContent = new Intl.NumberFormat('fr-FR').format(selectedMin);
            maxLimit.textContent = new Intl.NumberFormat('fr-FR').format(selectedMax);
            
            // Mise à jour de l'accessibilité du slider
            const slider = document.getElementById('quantitySlider');
            slider.setAttribute('aria-valuemin', selectedMin);
            slider.setAttribute('aria-valuemax', selectedMax);
            slider.setAttribute('aria-valuenow', slider.value);
            slider.setAttribute('aria-valuetext', `${formatNumber(slider.value)} followers`);
            
            // Ajuster la valeur si nécessaire avec animation
            let currentValue = parseInt(input.value);
            let newValue = currentValue;
            
            if (currentValue < selectedMin) {
                newValue = selectedMin;
            } else if (currentValue > selectedMax) {
                newValue = selectedMax;
            }
            
            if (newValue !== currentValue) {
                // Animation de mise à jour
                input.value = newValue;
                slider.value = newValue;
                updateQuantityDisplay(newValue);
                
                // Mise à jour du preset actif
                updateActivePreset(newValue);
                
                // Notification visuelle
                Swal.fire({
                    icon: 'info',
                    title: 'Quantité ajustée',
                    text: `La quantité a été ajustée à ${new Intl.NumberFormat('fr-FR').format(newValue)} selon les limites du service.`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    confirmButtonColor: '#00ff88'
                });
            }
            
            // Mise à jour de l'ordre
            updateOrderSummary();
        }
        
        // Mise à jour du slider de quantité moderne
        function updateQuantitySlider(value) {
            const slider = document.getElementById('quantitySlider');
            const input = document.getElementById('quantity');
            
            // Mise à jour des valeurs
            slider.value = value;
            input.value = value;
            
            // Mise à jour de l'affichage
            updateQuantityDisplay(value);
            
            // Mise à jour du preset actif
            updateActivePreset(value);
            
            // Animation de mise à jour
            slider.style.transform = 'scale(1.02)';
            setTimeout(() => {
                slider.style.transform = 'scale(1)';
            }, 200);
        }
        
        // Mise à jour de la barre de progression
        function updateProgressBar() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
        }
        
        // Remplir depuis une suggestion avec animation
        function fillFromSuggestion(linkUrl, quantity) {
            // Animation de remplissage
            const linkInput = document.getElementById('link_url');
            const quantityInput = document.getElementById('quantity');
            const quantitySlider = document.getElementById('quantitySlider');
            
            // Remplir les champs avec animation
            linkInput.value = linkUrl;
            quantityInput.value = quantity;
            quantitySlider.value = quantity;
            
            // Mise à jour de l'affichage de la quantité
            updateQuantityDisplay(quantity);
            
            // Mettre à jour le preset actif avec animation
            document.querySelectorAll('.quantity-preset').forEach(preset => {
                preset.classList.remove('active');
                if (parseInt(preset.dataset.value) === quantity) {
                    preset.classList.add('active');
                    preset.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        preset.style.transform = 'scale(1)';
                    }, 300);
                }
            });
            
            // Mise à jour de l'ordre
            updateOrderSummary();
            currentStep = 3;
            updateProgressBar();
            
            // Animation de confirmation
            Swal.fire({
                icon: 'success',
                title: 'Suggestion appliquée !',
                text: `Quantité: ${new Intl.NumberFormat('fr-FR').format(quantity)}`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                confirmButtonColor: '#00ff88'
            });
            
            // Scroll vers le formulaire avec animation
            document.getElementById('link_url').scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
        }
        
        // Validation du formulaire
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedService) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Service non sélectionné',
                    text: 'Veuillez sélectionner un service avant de continuer.',
                    confirmButtonColor: '#00ff88'
                });
                return false;
            }
            
            const quantity = parseInt(document.getElementById('quantity').value);
            if (quantity < selectedMin || quantity > selectedMax) {
                Swal.fire({
                    icon: 'error',
                    title: 'Quantité invalide',
                    text: `La quantité doit être comprise entre ${selectedMin.toLocaleString()} et ${selectedMax.toLocaleString()}.`,
                    confirmButtonColor: '#00ff88'
                });
                return false;
            }
            
            const paymentMethod = document.getElementById('payment_method').value;
            if (!paymentMethod) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Méthode de paiement manquante',
                    text: 'Veuillez sélectionner une méthode de paiement.',
                    confirmButtonColor: '#00ff88'
                });
                return false;
            }
            
            // Afficher le spinner de chargement
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('loadingSpinner').classList.add('show');
            
            // Soumettre le formulaire
            this.submit();
        });
        
        // Validation en temps réel
        document.getElementById('link_url').addEventListener('blur', function() {
            if (this.value && !isValidUrl(this.value)) {
                this.classList.add('is-invalid');
                Swal.fire({
                    icon: 'warning',
                    title: 'URL invalide',
                    text: 'Veuillez entrer une URL valide (commençant par http:// ou https://)',
                    confirmButtonColor: '#00ff88'
                });
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Fonction de validation d'URL
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        // Animation des éléments au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.form-section').forEach(el => {
            observer.observe(el);
        });
        
        // Auto-save des données du formulaire
        const formData = new FormData();
        const formInputs = document.querySelectorAll('#orderForm input, #orderForm textarea, #orderForm select');
        
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                formData.set(this.name, this.value);
                localStorage.setItem('orderFormData', JSON.stringify(Object.fromEntries(formData)));
            });
        });
        
        // Restaurer les données sauvegardées
        const savedData = localStorage.getItem('orderFormData');
        if (savedData) {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const input = document.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = data[key];
                }
            });
        }
        
        // Nettoyer le localStorage après soumission réussie
        if (document.querySelector('.alert-success')) {
            localStorage.removeItem('orderFormData');
        }

        // Initialisation du sélecteur de catégorie
        function initializeCategorySelector() {
            const categoryDropdownBtn = document.getElementById('categoryDropdownBtn');
            const categoryDropdownMenu = document.getElementById('categoryDropdownMenu');
            const serviceSelector = document.getElementById('serviceSelector');
            const selectedServiceDisplay = document.getElementById('selectedServiceDisplay');

            categoryDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                categoryDropdownMenu.classList.toggle('show');
                categoryDropdownBtn.classList.toggle('active');
                
                // Masquer le sélecteur de service et l'affichage du service sélectionné
                serviceSelector.style.display = 'none';
                selectedServiceDisplay.style.display = 'none';
                
                // Reset la sélection
                selectedService = null;
                selectedPrice = 0;
                selectedMin = 1000;
                selectedMax = 100000;
                
                // Mettre à jour la progression
                currentStep = 0;
                updateProgressBar();
                updateOrderSummary();
            });

            // Fermer le menu si on clique ailleurs
            document.addEventListener('click', function(event) {
                if (!categoryDropdownBtn.contains(event.target) && !categoryDropdownMenu.contains(event.target)) {
                    categoryDropdownMenu.classList.remove('show');
                    categoryDropdownBtn.classList.remove('active');
                }
            });

            // Gestion de la sélection de catégorie
            categoryDropdownMenu.addEventListener('click', function(event) {
                const categoryOption = event.target.closest('.category-option');
                if (categoryOption) {
                    const categoryId = parseInt(categoryOption.dataset.categoryId);
                    const services = <?php echo json_encode($services); ?>;
                    const categoryName = categoryOption.querySelector('.category-name').textContent;
                    const categoryIcon = categoryOption.querySelector('.category-icon i').className;

                    // Mettre à jour l'affichage de la catégorie sélectionnée
                    document.querySelector('.selected-category').innerHTML = `
                        <i class="${categoryIcon} me-2"></i>${categoryName}
                    `;
                    
                    // Fermer le menu de catégorie
                    categoryDropdownMenu.classList.remove('show');
                    categoryDropdownBtn.classList.remove('active');

                    // Charger les services pour cette catégorie
                    loadServices(categoryId, services[categoryId]);
                    
                    // Afficher le sélecteur de service
                    serviceSelector.style.display = 'block';
                    
                    // Mettre à jour la progression
                    currentStep = 1;
                    updateProgressBar();
                }
            });
        }

        // Charger les services pour une catégorie spécifique
        function loadServices(categoryId, services) {
            const serviceDropdownMenu = document.getElementById('serviceDropdownMenu');
            serviceDropdownMenu.innerHTML = ''; // Vider les services précédents

            if (services && services.length > 0) {
                services.forEach((service, index) => {
                    const serviceOption = document.createElement('div');
                    serviceOption.classList.add('service-option');
                    serviceOption.style.animationDelay = `${(index + 1) * 0.1}s`;
                    
                    serviceOption.innerHTML = `
                        <div class="service-option-content">
                            <div class="service-option-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="service-option-info">
                                <div class="service-option-name">${service.name}</div>
                                <div class="service-option-description">${service.description}</div>
                            </div>
                            <div class="service-option-price">
                                <span class="price-amount">${parseInt(service.price_per_1000).toLocaleString()}</span>
                                <span class="price-unit">FCFA/1000</span>
                            </div>
                        </div>
                    `;
                    
                    serviceOption.addEventListener('click', function() {
                        // Retirer la classe active de tous les services
                        document.querySelectorAll('.service-option').forEach(opt => opt.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Mettre à jour les variables globales
                        selectedService = service.id;
                        selectedPrice = parseFloat(service.price_per_1000);
                        selectedMin = parseInt(service.min_quantity);
                        selectedMax = parseInt(service.max_quantity);
                        
                        // Mettre à jour le champ caché du formulaire
                        document.getElementById('hiddenServiceId').value = service.id;
                        
                        // Mettre à jour les limites de quantité
                        updateQuantityLimits();
                        
                        // Mettre à jour le résumé
                        updateOrderSummary();
                        
                        // Mettre à jour l'affichage du service sélectionné
                        document.getElementById('selectedServiceName').textContent = service.name;
                        document.getElementById('selectedServicePlatform').textContent = service.platform || 'Réseau social';
                        document.getElementById('selectedServiceType').textContent = service.type || 'Service SMM';
                        document.getElementById('selectedServicePrice').textContent = parseInt(service.price_per_1000).toLocaleString();
                        document.getElementById('selectedServiceDelivery').textContent = service.delivery_time;
                        document.getElementById('selectedServiceMin').textContent = parseInt(service.min_quantity).toLocaleString();
                        document.getElementById('selectedServiceMax').textContent = parseInt(service.max_quantity).toLocaleString();
                        document.getElementById('selectedServiceDescription').textContent = service.description;
                        
                        // Masquer le sélecteur de service et afficher le service sélectionné
                        document.getElementById('serviceSelector').style.display = 'none';
                        document.getElementById('selectedServiceDisplay').style.display = 'block';
                        
                        // Mettre à jour la progression
                        currentStep = 2;
                        updateProgressBar();
                        
                        // Activer le bouton de soumission
                        document.getElementById('submitBtn').disabled = false;
                    });
                    
                    serviceDropdownMenu.appendChild(serviceOption);
                });
            } else {
                serviceDropdownMenu.innerHTML = `
                    <div class="service-option text-center text-muted">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Aucun service disponible pour cette catégorie.</p>
                    </div>
                `;
            }
        }

        // Initialisation du sélecteur de service
        function initializeServiceSelector() {
            const serviceDropdownBtn = document.getElementById('serviceDropdownBtn');
            const serviceDropdownMenu = document.getElementById('serviceDropdownMenu');

            serviceDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                serviceDropdownMenu.classList.toggle('show');
                serviceDropdownBtn.classList.toggle('active');
            });

            // Fermer le menu si on clique ailleurs
            document.addEventListener('click', function(event) {
                if (!serviceDropdownBtn.contains(event.target) && !serviceDropdownMenu.contains(event.target)) {
                    serviceDropdownMenu.classList.remove('show');
                    serviceDropdownBtn.classList.remove('active');
                }
            });
        }

        // Fonction pour revenir au sélecteur de service
        function showServiceSelector() {
            // Masquer l'affichage du service sélectionné
            document.getElementById('selectedServiceDisplay').style.display = 'none';
            
            // Afficher le sélecteur de service
            document.getElementById('serviceSelector').style.display = 'block';
            
            // Mettre à jour la progression
            currentStep = 1;
            updateProgressBar();
        }
        
        // ===== EFFETS VISUELS AVANCÉS DU SLIDER =====
        
        // Animation de pulsation pour le slider
        function addSliderPulseEffect() {
            const slider = document.getElementById('quantitySlider');
            const thumb = slider.querySelector('::-webkit-slider-thumb');
            
            // Ajouter un effet de pulsation subtil
            setInterval(() => {
                if (document.activeElement === slider) {
                    slider.style.boxShadow = '0 0 0 4px rgba(0, 122, 255, 0.1)';
                    setTimeout(() => {
                        slider.style.boxShadow = 'none';
                    }, 200);
                }
            }, 3000);
        }
        
        // Effet de particules pour les presets
        function addPresetParticleEffect(preset) {
            // Créer des particules d'étoiles
            for (let i = 0; i < 5; i++) {
                const particle = document.createElement('div');
                particle.className = 'preset-particle';
                particle.style.cssText = `
                    position: absolute;
                    width: 4px;
                    height: 4px;
                    background: var(--accent-primary);
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 1000;
                    animation: particleFloat 1s ease-out forwards;
                `;
                
                // Position aléatoire autour du preset
                const rect = preset.getBoundingClientRect();
                particle.style.left = (rect.left + rect.width / 2) + 'px';
                particle.style.top = (rect.top + rect.height / 2) + 'px';
                
                document.body.appendChild(particle);
                
                // Supprimer la particule après l'animation
                setTimeout(() => {
                    if (particle.parentNode) {
                        particle.parentNode.removeChild(particle);
                    }
                }, 1000);
            }
        }
        
        // CSS pour les particules
        const particleStyle = document.createElement('style');
        particleStyle.textContent = `
            @keyframes particleFloat {
                0% {
                    opacity: 1;
                    transform: translate(0, 0) scale(1);
                }
                100% {
                    opacity: 0;
                    transform: translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px) scale(0);
                }
            }
        `;
        document.head.appendChild(particleStyle);
        
        // Amélioration de l'expérience tactile et accessibilité
        function enhanceTouchExperience() {
            const slider = document.getElementById('quantitySlider');
            
            // Effet de feedback tactile
            slider.addEventListener('touchstart', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            slider.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
            });
            
            // Effet de glissement fluide
            let isDragging = false;
            
            slider.addEventListener('mousedown', function() {
                isDragging = true;
                this.style.cursor = 'grabbing';
                this.setAttribute('aria-pressed', 'true');
            });
            
            document.addEventListener('mouseup', function() {
                isDragging = false;
                slider.style.cursor = 'grab';
                slider.setAttribute('aria-pressed', 'false');
            });
            
            document.addEventListener('mousemove', function(e) {
                if (isDragging) {
                    // Calculer la nouvelle valeur basée sur la position de la souris
                    const rect = slider.getBoundingClientRect();
                    const percent = (e.clientX - rect.left) / rect.width;
                    const newValue = Math.round(percent * (selectedMax - selectedMin) + selectedMin);
                    
                    if (newValue >= selectedMin && newValue <= selectedMax) {
                        updateQuantitySlider(newValue);
                    }
                }
            });
            
            // Support du clavier pour l'accessibilité
            slider.addEventListener('keydown', function(e) {
                let newValue = parseInt(this.value);
                
                switch(e.key) {
                    case 'ArrowRight':
                    case 'ArrowUp':
                        e.preventDefault();
                        newValue = Math.min(newValue + 1000, selectedMax);
                        break;
                    case 'ArrowLeft':
                    case 'ArrowDown':
                        e.preventDefault();
                        newValue = Math.max(newValue - 1000, selectedMin);
                        break;
                    case 'Home':
                        e.preventDefault();
                        newValue = selectedMin;
                        break;
                    case 'End':
                        e.preventDefault();
                        newValue = selectedMax;
                        break;
                    case 'PageUp':
                        e.preventDefault();
                        newValue = Math.min(newValue + 10000, selectedMax);
                        break;
                    case 'PageDown':
                        e.preventDefault();
                        newValue = Math.max(newValue - 10000, selectedMin);
                        break;
                }
                
                if (newValue !== parseInt(this.value)) {
                    updateQuantitySlider(newValue);
                }
            });
            
            // Amélioration de l'accessibilité
            slider.setAttribute('role', 'slider');
            slider.setAttribute('aria-valuemin', selectedMin);
            slider.setAttribute('aria-valuemax', selectedMax);
            slider.setAttribute('aria-valuenow', slider.value);
            slider.setAttribute('aria-valuetext', `${formatNumber(slider.value)} followers`);
            slider.setAttribute('aria-label', 'Sélectionner la quantité de followers');
        }
        
        // Initialisation des effets avancés
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                addSliderPulseEffect();
                enhanceTouchExperience();
                addSmartTooltips();
                addSliderMarkers();
            }, 1000);
        });
        
        // Ajout de tooltips intelligents
        function addSmartTooltips() {
            const slider = document.getElementById('quantitySlider');
            const presets = document.querySelectorAll('.quantity-preset');
            
            // Tooltip pour le slider
            slider.addEventListener('mouseenter', function() {
                showTooltip(this, 'Glissez pour ajuster la quantité ou utilisez les flèches du clavier');
            });
            
            slider.addEventListener('mouseleave', function() {
                hideTooltip();
            });
            
            // Tooltips pour les presets
            presets.forEach(preset => {
                const value = parseInt(preset.dataset.value);
                const tooltipText = `${formatNumber(value)} followers - Cliquez pour sélectionner rapidement`;
                
                preset.addEventListener('mouseenter', function() {
                    showTooltip(this, tooltipText);
                });
                
                preset.addEventListener('mouseleave', function() {
                    hideTooltip();
                });
            });
        }
        
        // Affichage des tooltips
        function showTooltip(element, text) {
            hideTooltip();
            
            const tooltip = document.createElement('div');
            tooltip.className = 'smart-tooltip';
            tooltip.textContent = text;
            tooltip.style.cssText = `
                position: absolute;
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 0.85rem;
                z-index: 10000;
                pointer-events: none;
                white-space: nowrap;
                opacity: 0;
                transform: translateY(10px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            `;
            
            document.body.appendChild(tooltip);
            
            // Positionner le tooltip
            const rect = element.getBoundingClientRect();
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            
            // Animation d'apparition
            setTimeout(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateY(0)';
            }, 10);
        }
        
        function hideTooltip() {
            const existingTooltip = document.querySelector('.smart-tooltip');
            if (existingTooltip) {
                existingTooltip.remove();
            }
        }
        
        // Ajout de marqueurs visuels sur le slider
        function addSliderMarkers() {
            const slider = document.getElementById('quantitySlider');
            const sliderContainer = slider.parentElement;
            
            // Créer des marqueurs pour les valeurs importantes
            const markers = [1000, 10000, 25000, 50000, 100000];
            
            markers.forEach((value, index) => {
                const marker = document.createElement('div');
                marker.className = 'slider-marker';
                marker.style.cssText = `
                    position: absolute;
                    width: 2px;
                    height: 20px;
                    background: var(--accent-secondary);
                    border-radius: 1px;
                    top: 50%;
                    transform: translateY(-50%);
                    z-index: 1;
                    opacity: 0.6;
                    transition: all 0.3s ease;
                `;
                
                // Positionner le marqueur
                const percent = ((value - selectedMin) / (selectedMax - selectedMin)) * 100;
                marker.style.left = `${percent}%`;
                
                // Ajouter le label du marqueur
                const label = document.createElement('div');
                label.className = 'marker-label';
                label.textContent = formatNumber(value);
                label.style.cssText = `
                    position: absolute;
                    top: -30px;
                    left: 50%;
                    transform: translateX(-50%);
                    font-size: 0.75rem;
                    color: var(--text-tertiary);
                    white-space: nowrap;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                `;
                
                marker.appendChild(label);
                sliderContainer.appendChild(marker);
                
                // Afficher le label au hover
                marker.addEventListener('mouseenter', function() {
                    this.style.opacity = '1';
                    this.style.height = '25px';
                    this.style.background = 'var(--accent-primary)';
                    label.style.opacity = '1';
                });
                
                marker.addEventListener('mouseleave', function() {
                    this.style.opacity = '0.6';
                    this.style.height = '20px';
                    this.style.background = 'var(--accent-secondary)';
                    label.style.opacity = '0';
                });
            });
        }
        
        // Amélioration de l'expérience haptique
        function addHapticFeedback() {
            const slider = document.getElementById('quantitySlider');
            
            // Vérifier si l'API Vibration est supportée
            if ('vibrate' in navigator) {
                let lastValue = parseInt(slider.value);
                
                slider.addEventListener('input', function() {
                    const currentValue = parseInt(this.value);
                    
                    // Vibration pour les changements de valeur
                    if (currentValue !== lastValue) {
                        // Vibration courte pour les petits changements
                        if (Math.abs(currentValue - lastValue) <= 5000) {
                            navigator.vibrate(10);
                        } else {
                            // Vibration plus longue pour les gros changements
                            navigator.vibrate([10, 50, 10]);
                        }
                        lastValue = currentValue;
                    }
                });
                
                // Vibration pour les presets
                document.querySelectorAll('.quantity-preset').forEach(preset => {
                    preset.addEventListener('click', function() {
                        navigator.vibrate(20);
                    });
                });
            }
        }
        
        // Initialisation des effets haptiques
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                addHapticFeedback();
                addSliderParticleEffects();
            }, 1500);
        });
        
        // Effets de particules lors du glissement
        function addSliderParticleEffects() {
            const slider = document.getElementById('quantitySlider');
            let isDragging = false;
            let lastValue = parseInt(slider.value);
            
            slider.addEventListener('mousedown', function() {
                isDragging = true;
            });
            
            document.addEventListener('mouseup', function() {
                isDragging = false;
            });
            
            slider.addEventListener('input', function() {
                if (isDragging) {
                    const currentValue = parseInt(this.value);
                    
                    // Créer des particules lors du glissement
                    if (currentValue !== lastValue) {
                        createSliderParticles(this, currentValue);
                        lastValue = currentValue;
                    }
                }
            });
        }
        
        // Création de particules pour le slider
        function createSliderParticles(slider, value) {
            const rect = slider.getBoundingClientRect();
            const percent = ((value - selectedMin) / (selectedMax - selectedMin)) * 100;
            const x = rect.left + (rect.width * percent / 100);
            const y = rect.top + rect.height / 2;
            
            // Créer 3 particules
            for (let i = 0; i < 3; i++) {
                const particle = document.createElement('div');
                particle.className = 'slider-particle';
                particle.style.cssText = `
                    position: fixed;
                    width: 4px;
                    height: 4px;
                    background: linear-gradient(45deg, var(--accent-primary), var(--accent-secondary));
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 1000;
                    left: ${x}px;
                    top: ${y}px;
                    animation: sliderParticleFloat 0.8s ease-out forwards;
                `;
                
                document.body.appendChild(particle);
                
                // Supprimer la particule après l'animation
                setTimeout(() => {
                    if (particle.parentNode) {
                        particle.parentNode.removeChild(particle);
                    }
                }, 800);
            }
        }
        
        // CSS pour les particules du slider
        const sliderParticleStyle = document.createElement('style');
        sliderParticleStyle.textContent = `
            @keyframes sliderParticleFloat {
                0% {
                    opacity: 1;
                    transform: translate(0, 0) scale(1);
                }
                100% {
                    opacity: 0;
                    transform: translate(${Math.random() * 60 - 30}px, ${Math.random() * 60 - 30}px) scale(0);
                }
            }
        `;
        document.head.appendChild(sliderParticleStyle);
    </script>
</body>
</html>