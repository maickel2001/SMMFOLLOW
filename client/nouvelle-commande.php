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
    
    <style>
        :root {
            --dark-bg: #0a0a0a;
            --darker-bg: #1a1a1a;
            --card-bg: #2a2a2a;
            --border-color: #3a3a3a;
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --primary-color: #00ff88;
            --secondary-color: #00cc6a;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }
        
        body {
            background: var(--dark-bg);
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
        }
        
        .client-container {
            padding-top: 20px;
            min-height: 100vh;
            background: var(--dark-bg);
        }
        
        .client-nav {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .client-nav .nav-link {
            color: var(--text-secondary);
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .client-nav .nav-link:hover,
        .client-nav .nav-link.active {
            background: var(--primary-color);
            color: var(--dark-bg);
        }
        
        .card {
            background: var(--card-bg);
            border-color: var(--border-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: var(--darker-bg);
            border-color: var(--border-color);
        }
        
        .form-control, .form-select, .form-textarea {
            background: var(--darker-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus, .form-textarea:focus {
            background: var(--darker-bg);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 136, 0.25);
            transform: translateY(-1px);
        }
        
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--dark-bg);
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--dark-bg);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 255, 136, 0.3);
        }
        
        .btn-primary:disabled {
            background: var(--border-color);
            border-color: var(--border-color);
            transform: none;
            box-shadow: none;
        }
        
        .order-summary {
            background: var(--darker-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            position: sticky;
            top: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .summary-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
            padding-top: 20px;
        }
        
        .service-card {
            background: var(--darker-bg);
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 136, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .service-card:hover::before {
            left: 100%;
        }
        
        .service-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 255, 136, 0.2);
        }
        
        .service-card.selected {
            border-color: var(--primary-color);
            background: rgba(0, 255, 136, 0.1);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
        }
        
        .service-card.selected::after {
            content: '✓';
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary-color);
            color: var(--dark-bg);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
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
        
        .form-section {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
        }
        
        .form-section h5 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h5 i {
            font-size: 1.2rem;
        }
        
        .suggestions {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--primary-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .suggestions h6 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .suggestion-item {
            background: var(--darker-bg);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }
        
        .suggestion-item:hover {
            border-color: var(--primary-color);
            background: rgba(0, 255, 136, 0.05);
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
        
        .quantity-slider {
            width: 100%;
            margin: 20px 0;
        }
        
        .quantity-slider input[type="range"] {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: var(--border-color);
            outline: none;
            -webkit-appearance: none;
        }
        
        .quantity-slider input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }
        
        .quantity-slider input[type="range"]::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }
        
        .quantity-presets {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .quantity-preset {
            padding: 8px 16px;
            background: var(--border-color);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .quantity-preset:hover,
        .quantity-preset.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--dark-bg);
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
    </style>
</head>
<body>
    <div class="client-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-plus me-2"></i>Nouvelle Commande
                </h1>
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">
                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                    </a>
                </div>
            </div>
            
            <!-- Navigation Client -->
            <div class="client-nav">
                <nav class="nav nav-pills">
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
                        <!-- Sélection du service -->
                        <div class="form-section animate-fade-in">
                            <h5><i class="fas fa-cogs me-2"></i>Choisir un Service</h5>
                            <div class="row">
                                <?php foreach ($categories as $category): ?>
                                    <div class="col-12 mb-3">
                                        <h6 class="text-primary mb-3">
                                            <i class="<?php echo htmlspecialchars($category['icon']); ?> me-2"></i>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </h6>
                                        <?php if (isset($services[$category['id']])): ?>
                                            <?php foreach ($services[$category['id']] as $service): ?>
                                                <div class="service-card" data-service-id="<?php echo $service['id']; ?>" 
                                                     data-price="<?php echo $service['price_per_1000']; ?>"
                                                     data-min="<?php echo $service['min_quantity']; ?>"
                                                     data-max="<?php echo $service['max_quantity']; ?>">
                                                    <div class="service-header">
                                                        <div>
                                                            <div class="service-name"><?php echo htmlspecialchars($service['name']); ?></div>
                                                            <div class="service-platform"><?php echo htmlspecialchars($service['platform']); ?> - <?php echo htmlspecialchars($service['type']); ?></div>
                                                        </div>
                                                        <div class="service-price"><?php echo number_format($service['price_per_1000'], 0, ',', ' '); ?> FCFA/1000</div>
                                                    </div>
                                                    <div class="service-description"><?php echo htmlspecialchars($service['description']); ?></div>
                                                    <div class="service-details">
                                                        <div class="service-detail">
                                                            <i class="fas fa-clock"></i>
                                                            <span><?php echo htmlspecialchars($service['delivery_time']); ?></span>
                                                        </div>
                                                        <div class="service-detail">
                                                            <i class="fas fa-chart-line"></i>
                                                            <span>Min: <?php echo number_format($service['min_quantity'], 0, ',', ' '); ?></span>
                                                        </div>
                                                        <div class="service-detail">
                                                            <i class="fas fa-chart-line"></i>
                                                            <span>Max: <?php echo number_format($service['max_quantity'], 0, ',', ' '); ?></span>
                                                        </div>
                                                        <div class="service-detail">
                                                            <i class="fas fa-star"></i>
                                                            <span>Qualité Premium</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
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
                                    
                                    <!-- Slider de quantité -->
                                    <div class="quantity-slider">
                                        <input type="range" id="quantitySlider" min="1000" max="100000" step="1000" value="1000">
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
        
        // Initialisation du slider de quantité
        function initializeQuantitySlider() {
            const slider = document.getElementById('quantitySlider');
            const input = document.getElementById('quantity');
            
            slider.addEventListener('input', function() {
                input.value = this.value;
                updateOrderSummary();
                currentStep = 2;
                updateProgressBar();
            });
        }
        
        // Initialisation des presets de quantité
        function initializeQuantityPresets() {
            document.querySelectorAll('.quantity-preset').forEach(preset => {
                preset.addEventListener('click', function() {
                    const value = parseInt(this.dataset.value);
                    
                    // Mettre à jour l'input et le slider
                    document.getElementById('quantity').value = value;
                    document.getElementById('quantitySlider').value = value;
                    
                    // Mettre à jour les presets actifs
                    document.querySelectorAll('.quantity-preset').forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    
                    updateOrderSummary();
                    currentStep = 2;
                    updateProgressBar();
                });
            });
        }
        
        // Mise à jour des limites de quantité
        function updateQuantityLimits() {
            const slider = document.getElementById('quantitySlider');
            const input = document.getElementById('quantity');
            
            slider.min = selectedMin;
            slider.max = selectedMax;
            input.min = selectedMin;
            input.max = selectedMax;
            
            // Ajuster la valeur si nécessaire
            if (parseInt(input.value) < selectedMin) {
                input.value = selectedMin;
                slider.value = selectedMin;
            } else if (parseInt(input.value) > selectedMax) {
                input.value = selectedMax;
                slider.value = selectedMax;
            }
        }
        
        // Mise à jour du slider de quantité
        function updateQuantitySlider(value) {
            document.getElementById('quantitySlider').value = value;
        }
        
        // Mise à jour de la barre de progression
        function updateProgressBar() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
        }
        
        // Remplir depuis une suggestion
        function fillFromSuggestion(linkUrl, quantity) {
            document.getElementById('link_url').value = linkUrl;
            document.getElementById('quantity').value = quantity;
            document.getElementById('quantitySlider').value = quantity;
            
            // Mettre à jour le preset actif
            document.querySelectorAll('.quantity-preset').forEach(preset => {
                preset.classList.remove('active');
                if (parseInt(preset.dataset.value) === quantity) {
                    preset.classList.add('active');
                }
            });
            
            updateOrderSummary();
            currentStep = 3;
            updateProgressBar();
            
            // Scroll vers le formulaire
            document.getElementById('link_url').scrollIntoView({ behavior: 'smooth' });
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
    </script>
</body>
</html>