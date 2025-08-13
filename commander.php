<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$categories = getAllCategories();
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Vérifier si l'utilisateur est connecté
$isUserLoggedIn = isUserLoggedIn();
$userInfo = null;

if ($isUserLoggedIn) {
    $userInfo = [
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? ''
    ];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = cleanInput($_POST['service_id']);
    $linkUrl = cleanInput($_POST['link_url']);
    $quantity = (int)$_POST['quantity'];
    $paymentMethod = cleanInput($_POST['payment_method']);
    
    // Récupérer les informations utilisateur selon le statut de connexion
    if ($isUserLoggedIn) {
        $customerName = $userInfo['name'];
        $customerEmail = $userInfo['email'];
        $userId = $_SESSION['user_id'] ?? null;
    } else {
        $customerName = cleanInput($_POST['customer_name']);
        $customerEmail = cleanInput($_POST['customer_email']);
        $userId = null;
    }
    
    // Validation
    if (empty($serviceId) || empty($linkUrl) || $quantity < 1000 || empty($paymentMethod)) {
        showAlert('Veuillez remplir tous les champs correctement.', 'danger');
    } elseif (!$isUserLoggedIn && (empty($customerName) || empty($customerEmail))) {
        showAlert('Veuillez remplir tous les champs correctement.', 'danger');
    } else {
        // Calcul du prix total
        $totalPrice = calculateTotalPrice($serviceId, $quantity);
        
        if ($totalPrice > 0) {
            // Génération du numéro de commande
            $orderNumber = generateOrderNumber();
            
            try {
                $pdo = getDBConnection();
                
                if ($isUserLoggedIn && $userId) {
                    // Utilisateur connecté - utiliser user_id
                    $stmt = $pdo->prepare("
                        INSERT INTO orders (order_number, service_id, user_id, customer_email, customer_name, link_url, quantity, total_price, payment_method)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $params = [$orderNumber, $serviceId, $userId, $customerEmail, $customerName, $linkUrl, $quantity, $totalPrice, $paymentMethod];
                } else {
                    // Utilisateur non connecté - pas de user_id
                    $stmt = $pdo->prepare("
                        INSERT INTO orders (order_number, service_id, customer_email, customer_name, link_url, quantity, total_price, payment_method)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $params = [$orderNumber, $serviceId, $customerEmail, $customerName, $linkUrl, $quantity, $totalPrice, $paymentMethod];
                }
                
                if ($stmt->execute($params)) {
                    $orderId = $pdo->lastInsertId();
                    redirect("paiement.php?order_id=" . $orderId);
                } else {
                    showAlert('Erreur lors de la création de la commande.', 'danger');
                }
            } catch (Exception $e) {
                showAlert('Erreur de base de données.', 'danger');
            }
        } else {
            showAlert('Erreur lors du calcul du prix.', 'danger');
        }
    }
}

// Récupération des services de la catégorie sélectionnée
$services = [];
if ($selectedCategory) {
    $services = getServicesByCategory($selectedCategory);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commander - BoostSocial</title>
    <meta name="description" content="Commandez vos services SMM (followers, likes, vues) pour Instagram, TikTok, YouTube et Facebook. Processus simple et sécurisé.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #10b981;
            --accent: #f59e0b;
            --dark: #0f172a;
            --darker: #020617;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: var(--light);
            overflow-x: hidden;
        }

        /* Hamburger Menu */
        .hamburger-menu {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            cursor: pointer;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            backdrop-filter: blur(20px);
        }

        .hamburger-menu:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-lg);
        }

        .hamburger-icon {
            width: 20px;
            height: 2px;
            background: var(--dark);
            position: relative;
            transition: all 0.3s ease;
        }

        .hamburger-icon::before,
        .hamburger-icon::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 2px;
            background: var(--dark);
            transition: all 0.3s ease;
        }

        .hamburger-icon::before {
            top: -6px;
        }

        .hamburger-icon::after {
            bottom: -6px;
        }

        .hamburger-menu.active .hamburger-icon {
            background: transparent;
        }

        .hamburger-menu.active .hamburger-icon::before {
            transform: rotate(45deg);
            top: 0;
        }

        .hamburger-menu.active .hamburger-icon::after {
            transform: rotate(-45deg);
            bottom: 0;
        }

        /* Menu Overlay */
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .menu-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .menu-item {
            display: block;
            margin: 1.5rem 0;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(20px);
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            color: white;
            text-decoration: none;
            box-shadow: var(--shadow-lg);
        }

        .menu-item i {
            margin-right: 1rem;
            width: 20px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, var(--primary) 100%);
            padding: 120px 0 80px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>') no-repeat;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }

        .hero h1 .highlight {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: clamp(1.1rem, 2.5vw, 1.3rem);
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        /* Main Content */
        .main-content {
            padding: 4rem 0;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.1rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Order Form Cards */
        .order-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
            height: 100%;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .order-card h4 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .order-card h4 i {
            color: var(--primary);
            margin-right: 0.75rem;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--gray-light);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-control.error, .form-select.error {
            border-color: #ef4444;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-text {
            color: var(--gray);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Order Summary */
        .order-summary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: center;
        }

        .order-summary h5 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .order-summary h5 i {
            margin-right: 0.75rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .summary-label {
            opacity: 0.9;
        }

        .summary-value {
            font-weight: 600;
        }

        /* Submit Button */
        .btn-submit {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            color: white;
            padding: 1.25rem 3rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Quantity Presets */
        .quantity-presets {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }

        .quantity-preset {
            padding: 0.5rem 1rem;
            background: var(--gray-light);
            border: none;
            border-radius: 25px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-preset:hover {
            background: var(--primary);
            color: white;
        }

        .quantity-preset.active {
            background: var(--primary);
            color: white;
        }

        /* Alert Messages */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border-left: 4px solid #16a34a;
        }

        /* Loading States */
        .loading-spinner {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border: 3px solid var(--gray-light);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-container {
            text-align: center;
            padding: 2rem;
        }

        .loading-container p {
            margin-top: 1rem;
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Error States */
        .error-container {
            text-align: center;
            padding: 2rem;
        }

        .error-container i {
            font-size: 2rem;
            color: #ef4444;
            margin-bottom: 1rem;
        }

        .error-container p {
            color: #ef4444;
            margin-bottom: 1rem;
        }

        .retry-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .retry-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-container {
            text-align: center;
            padding: 2rem;
        }

        .empty-container i {
            font-size: 2rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .empty-container p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* User Info Card */
        .user-info-card {
            background: #f0fdf4; /* Light green background */
            border: 1px solid #a7f3d0; /* Green border */
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .user-info-header {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #16a34a; /* Green text */
            margin-bottom: 1rem;
        }

        .user-info-header i {
            font-size: 2rem;
            margin-right: 0.5rem;
        }

        .user-info-details {
            text-align: left;
        }

        .user-info-item {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            color: var(--dark);
        }

        .user-info-item strong {
            color: var(--primary);
            font-weight: 600;
        }

        /* Guest Info Card */
        .guest-info-card {
            background: #fffbeb; /* Light yellow background */
            border: 1px solid #fde68a; /* Yellow border */
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .guest-info-header {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d97706; /* Orange text */
            margin-bottom: 1rem;
        }

        .guest-info-header i {
            font-size: 2rem;
            margin-right: 0.5rem;
        }

        .guest-info-details p {
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .guest-info-details .btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hamburger-menu {
                top: 15px;
                right: 15px;
                width: 45px;
                height: 45px;
            }
            
            .order-card {
                padding: 2rem;
                margin-bottom: 2rem;
            }
            
            .hero {
                padding: 100px 0 60px;
            }
        }

        @media (max-width: 576px) {
            .hero {
                padding: 80px 0 50px;
            }
            
            .main-content {
                padding: 2rem 0;
            }
            
            .menu-content {
                padding: 1rem;
            }
            
            .menu-item {
                margin: 1rem 0;
                padding: 0.8rem 1.5rem;
                font-size: 1.1rem;
            }
            
            .quantity-presets {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Hamburger Menu Button -->
    <div class="hamburger-menu" id="hamburgerMenu">
        <div class="hamburger-icon"></div>
    </div>

    <!-- Menu Overlay -->
    <div class="menu-overlay" id="menuOverlay">
        <div class="menu-content">
            <a href="index.php" class="menu-item">
                <i class="fas fa-home"></i>Accueil
            </a>
            <a href="services.php" class="menu-item">
                <i class="fas fa-rocket"></i>Services
            </a>
            <a href="about.php" class="menu-item">
                <i class="fas fa-info-circle"></i>À Propos
            </a>
            <a href="contact.php" class="menu-item">
                <i class="fas fa-envelope"></i>Contact
            </a>
            <a href="client/dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Commander vos <span class="highlight">Services</span></h1>
                <p>Boostez votre présence sur les réseaux sociaux en quelques clics. Processus simple, sécurisé et rapide.</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="main-content">
        <div class="container">
            <div class="section-title">
                <h2>Créez votre commande</h2>
                <p>Sélectionnez vos services et remplissez les informations pour commencer</p>
            </div>
            
            <?php if (isset($_SESSION['alert'])): ?>
                <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['alert']['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>
            
            <div class="row">
                <!-- Sélection des services -->
                <div class="col-lg-4 mb-4">
                    <div class="order-card">
                        <h4>
                            <i class="fas fa-list"></i>Choisir un Service
                        </h4>
                        
                        <!-- Sélection de catégorie -->
                        <div class="form-group">
                            <label class="form-label">Catégorie</label>
                            <select class="form-control form-select" id="categorySelect">
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($selectedCategory == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Liste des services -->
                        <div id="servicesList">
                            <?php if ($selectedCategory && !empty($services)): ?>
                                <div class="form-group">
                                    <label class="form-label">Service</label>
                                    <select class="form-control form-select" id="serviceSelect" required>
                                        <option value="">Sélectionner un service</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?php echo $service['id']; ?>" 
                                                    data-price="<?php echo $service['price_per_1000']; ?>"
                                                    data-min="<?php echo $service['min_quantity']; ?>"
                                                    data-max="<?php echo $service['max_quantity']; ?>">
                                                <?php echo htmlspecialchars($service['name']); ?> - 
                                                <?php echo formatPrice($service['price_per_1000']); ?>/1000
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire de commande -->
                <div class="col-lg-8">
                    <div class="order-card">
                        <h4>
                            <i class="fas fa-shopping-cart"></i>Détails de la Commande
                        </h4>
                        
                        <?php if ($isUserLoggedIn): ?>
                        <!-- Informations utilisateur connecté -->
                        <div class="user-info-card">
                            <div class="user-info-header">
                                <i class="fas fa-user-check text-success"></i>
                                <span>Vous êtes connecté</span>
                            </div>
                            <div class="user-info-details">
                                <div class="user-info-item">
                                    <strong>Nom :</strong> <?php echo htmlspecialchars($userInfo['name']); ?>
                                </div>
                                <div class="user-info-item">
                                    <strong>Email :</strong> <?php echo htmlspecialchars($userInfo['email']); ?>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Message pour utilisateurs non connectés -->
                        <div class="guest-info-card">
                            <div class="guest-info-header">
                                <i class="fas fa-user-clock text-warning"></i>
                                <span>Commande en tant qu'invité</span>
                            </div>
                            <div class="guest-info-details">
                                <p>Vous n'êtes pas connecté. Vos informations seront utilisées uniquement pour cette commande.</p>
                                <a href="connexion.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-sign-in-alt me-1"></i>Se connecter
                                </a>
                                <a href="inscription.php" class="btn btn-outline-secondary btn-sm ms-2">
                                    <i class="fas fa-user-plus me-1"></i>S'inscrire
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="orderForm">
                            <!-- Champ caché pour service_id -->
                            <input type="hidden" name="service_id" id="hiddenServiceId" required>
                            
                            <?php if (!$isUserLoggedIn): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Nom complet *</label>
                                        <input type="text" class="form-control" name="customer_name" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email *</label>
                                        <input type="email" class="form-control" name="customer_email" required>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label">Lien à promouvoir *</label>
                                <input type="url" class="form-control" name="link_url" 
                                       placeholder="https://instagram.com/votre-profil" required>
                                <div class="form-text">Entrez l'URL de votre profil ou publication</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Quantité *</label>
                                        <input type="number" class="form-control" name="quantity" id="quantity" 
                                               min="1000" step="1000" required>
                                        <div class="form-text">Minimum: 1 000</div>
                                        
                                        <!-- Presets de quantité -->
                                        <div class="quantity-presets">
                                            <button type="button" class="quantity-preset" data-value="1000">1K</button>
                                            <button type="button" class="quantity-preset" data-value="5000">5K</button>
                                            <button type="button" class="quantity-preset" data-value="10000">10K</button>
                                            <button type="button" class="quantity-preset" data-value="50000">50K</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Méthode de paiement *</label>
                                        <select class="form-control form-select" name="payment_method" required>
                                            <option value="">Choisir...</option>
                                            <option value="MTN Money">MTN Money</option>
                                            <option value="Moov Money">Moov Money</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Résumé de la commande -->
                            <div class="order-summary">
                                <h5>
                                    <i class="fas fa-calculator"></i>Résumé de la Commande
                                </h5>
                                
                                <div class="summary-row">
                                    <span class="summary-label">Service:</span>
                                    <span class="summary-value" id="selectedService">-</span>
                                </div>
                                
                                <div class="summary-row">
                                    <span class="summary-label">Quantité:</span>
                                    <span class="summary-value" id="selectedQuantity">-</span>
                                </div>
                                
                                <div class="summary-row">
                                    <span class="summary-label">Prix total:</span>
                                    <span class="summary-value" id="totalPrice">-</span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-submit" id="submitBtn" disabled>
                                <i class="fas fa-credit-card me-2"></i>Procéder au Paiement
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Hamburger Menu Toggle
        const hamburgerMenu = document.getElementById('hamburgerMenu');
        const menuOverlay = document.getElementById('menuOverlay');

        hamburgerMenu.addEventListener('click', function() {
            hamburgerMenu.classList.toggle('active');
            menuOverlay.classList.toggle('active');
        });

        // Close menu when clicking on overlay
        menuOverlay.addEventListener('click', function(e) {
            if (e.target === menuOverlay) {
                hamburgerMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
            }
        });

        // Close menu when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hamburgerMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
            }
        });

        // Close menu when clicking on menu items
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                hamburgerMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
            });
        });

        // Initialisation de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Si une catégorie est présélectionnée, charger ses services
            const categorySelect = document.getElementById('categorySelect');
            if (categorySelect && categorySelect.value) {
                loadServicesByCategory(categorySelect.value);
            }
        });
        
        // Gestion de la sélection de catégorie
        document.getElementById('categorySelect').addEventListener('change', function() {
            const categoryId = this.value;
            if (categoryId) {
                loadServicesByCategory(categoryId);
            } else {
                // Réinitialiser la liste des services
                document.getElementById('servicesList').innerHTML = '';
                resetOrderSummary();
            }
        });
        
        // Fonction pour charger les services par catégorie via AJAX
        function loadServicesByCategory(categoryId) {
            const servicesList = document.getElementById('servicesList');
            const submitBtn = document.getElementById('submitBtn');
            
            // Afficher un indicateur de chargement
            servicesList.innerHTML = `
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Chargement des services...</p>
                </div>
            `;
            
            // Désactiver le bouton pendant le chargement
            submitBtn.disabled = true;
            
            // Appel AJAX pour récupérer les services
            fetch(`get_services.php?category_id=${categoryId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.services.length > 0) {
                    // Construire la liste des services
                    let servicesHTML = `
                        <div class="form-group">
                            <label class="form-label">Service</label>
                            <select class="form-control form-select" id="serviceSelect" required>
                                <option value="">Sélectionner un service</option>
                    `;
                    
                    data.services.forEach(service => {
                        servicesHTML += `
                            <option value="${service.id}" 
                                    data-price="${service.price_per_1000}"
                                    data-min="${service.min_quantity}"
                                    data-max="${service.max_quantity}">
                                ${service.name} - ${formatPrice(service.price_per_1000)}/1000
                            </option>
                        `;
                    });
                    
                    servicesHTML += `
                            </select>
                        </div>
                    `;
                    
                    servicesList.innerHTML = servicesHTML;
                    
                    // Réattacher l'événement change au nouveau select
                    serviceSelect = document.getElementById('serviceSelect');
                    if (serviceSelect) {
                        serviceSelect.addEventListener('change', updateOrderSummary);
                    }
                    
                    // Réinitialiser le résumé
                    resetOrderSummary();
                    
                } else {
                    // Aucun service trouvé
                    servicesList.innerHTML = `
                        <div class="empty-container">
                            <i class="fas fa-info-circle"></i>
                            <p>Aucun service disponible pour cette catégorie</p>
                        </div>
                    `;
                    resetOrderSummary();
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des services:', error);
                servicesList.innerHTML = `
                    <div class="error-container">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Erreur lors du chargement des services</p>
                        <button class="retry-btn" onclick="loadServicesByCategory(${categoryId})">
                            <i class="fas fa-redo me-1"></i>Réessayer
                        </button>
                    </div>
                `;
                resetOrderSummary();
            });
        }
        
        // Fonction pour réinitialiser le résumé de commande
        function resetOrderSummary() {
            document.getElementById('hiddenServiceId').value = '';
            document.getElementById('selectedService').textContent = '-';
            document.getElementById('selectedQuantity').textContent = '-';
            document.getElementById('totalPrice').textContent = '-';
            document.getElementById('submitBtn').disabled = true;
            
            // Réinitialiser les presets de quantité
            document.querySelectorAll('.quantity-preset').forEach(p => p.classList.remove('active'));
        }
        
        // Fonction pour formater le prix
        function formatPrice(price) {
            return new Intl.NumberFormat('fr-FR').format(price) + ' FCFA';
        }
        
        // Gestion des presets de quantité
        document.querySelectorAll('.quantity-preset').forEach(preset => {
            preset.addEventListener('click', function() {
                const value = this.dataset.value;
                document.getElementById('quantity').value = value;
                
                // Mettre à jour les classes actives
                document.querySelectorAll('.quantity-preset').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                
                updateOrderSummary();
            });
        });
        
        // Variables globales pour le résumé de commande
        let serviceSelect = null;
        const hiddenServiceId = document.getElementById('hiddenServiceId');
        const quantityInput = document.getElementById('quantity');
        const selectedServiceSpan = document.getElementById('selectedService');
        const selectedQuantitySpan = document.getElementById('selectedQuantity');
        const totalPriceSpan = document.getElementById('totalPrice');
        const submitBtn = document.getElementById('submitBtn');
        
        function updateOrderSummary() {
            if (!serviceSelect) return;
            
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            const quantity = parseInt(quantityInput.value) || 0;
            
            if (selectedOption && selectedOption.value && quantity >= 1000) {
                const pricePer1000 = parseFloat(selectedOption.dataset.price);
                const totalPrice = (pricePer1000 * quantity) / 1000;
                
                // Mettre à jour le champ caché
                hiddenServiceId.value = selectedOption.value;
                
                selectedServiceSpan.textContent = selectedOption.text;
                selectedQuantitySpan.textContent = new Intl.NumberFormat('fr-FR').format(quantity);
                totalPriceSpan.textContent = new Intl.NumberFormat('fr-FR').format(totalPrice) + ' FCFA';
                submitBtn.disabled = false;
            } else {
                hiddenServiceId.value = '';
                selectedServiceSpan.textContent = '-';
                selectedQuantitySpan.textContent = '-';
                totalPriceSpan.textContent = '-';
                submitBtn.disabled = true;
            }
        }
        
        // Attacher les événements aux éléments qui existent déjà
        if (quantityInput) {
            quantityInput.addEventListener('input', updateOrderSummary);
        }
        
        // Validation du formulaire
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const serviceId = hiddenServiceId.value;
            const quantity = parseInt(quantityInput.value);
            const linkUrl = document.querySelector('input[name="link_url"]').value.trim();
            const paymentMethod = document.querySelector('select[name="payment_method"]').value;
            
            // Validation côté client
            if (!serviceId) {
                e.preventDefault();
                alert('Veuillez sélectionner un service.');
                return false;
            }
            
            <?php if (!$isUserLoggedIn): ?>
            // Validation des champs utilisateur pour les invités
            const customerName = document.querySelector('input[name="customer_name"]').value.trim();
            const customerEmail = document.querySelector('input[name="customer_email"]').value.trim();
            
            if (!customerName) {
                e.preventDefault();
                alert('Veuillez saisir votre nom complet.');
                return false;
            }
            
            if (!customerEmail) {
                e.preventDefault();
                alert('Veuillez saisir une adresse email valide.');
                return false;
            }
            <?php endif; ?>
            
            if (!linkUrl) {
                e.preventDefault();
                alert('Veuillez saisir le lien à promouvoir.');
                return false;
            }
            
            if (quantity < 1000) {
                e.preventDefault();
                alert('La quantité minimum est de 1 000.');
                return false;
            }
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Veuillez sélectionner une méthode de paiement.');
                return false;
            }
            
            // Si tout est valide, activer le bouton
            submitBtn.disabled = false;
        });
    </script>
</body>
</html>