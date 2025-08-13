<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

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
                    redirect("../paiement.php?order_id=" . $orderId);
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
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--light) 0%, var(--gray-light) 100%);
            min-height: 100vh;
            color: var(--dark);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Header Principal */
        .main-header {
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, var(--primary) 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .main-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>') no-repeat;
            opacity: 0.3;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff, #e0e7ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Container principal */
        .main-container {
            padding: 2rem 0;
            min-height: calc(100vh - 200px);
        }
        
        /* Formulaire de commande */
        .order-form-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .form-subtitle {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--gray-light);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background: white;
        }
        
        .form-control:hover {
            border-color: var(--primary);
            background: white;
        }
        
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }
        
        /* Sélecteur de catégorie et service */
        .category-selector, .service-selector {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .category-dropdown, .service-dropdown {
            position: relative;
        }
        
        .category-dropdown-btn, .service-dropdown-btn {
            width: 100%;
            padding: 1rem 1.5rem;
            background: white;
            border: 2px solid var(--gray-light);
            border-radius: 12px;
            text-align: left;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-dropdown-btn:hover, .service-dropdown-btn:hover {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        
        .category-dropdown-btn.active, .service-dropdown-btn.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        
        .dropdown-arrow {
            transition: transform 0.3s ease;
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
            background: white;
            border: 2px solid var(--gray-light);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            margin-top: 0.5rem;
        }
        
        .category-dropdown-menu.show, .service-dropdown-menu.show {
            display: block;
        }
        
        .category-option, .service-option {
            padding: 1rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .category-option:last-child, .service-option:last-child {
            border-bottom: none;
        }
        
        .category-option:hover, .service-option:hover {
            background: rgba(99, 102, 241, 0.05);
        }
        
        .category-option-content, .service-option-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .category-icon, .service-option-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .category-info, .service-option-info {
            flex: 1;
        }
        
        .category-name, .service-option-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        
        .category-description, .service-option-description {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .category-count, .service-option-price {
            text-align: right;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .price-amount {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .price-unit {
            color: var(--gray);
            font-size: 0.8rem;
        }
        
        /* Boutons */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 1rem;
            padding: 1rem 2rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: var(--shadow);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #059669, var(--success));
            color: white;
        }
        
        /* Résumé de la commande */
        .order-summary {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .summary-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }
        
        .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .summary-subtitle {
            color: var(--gray);
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .summary-item {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--gray-light);
            text-align: center;
        }
        
        .summary-label {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .summary-price {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0;
            }
            
            .main-header {
                padding: 2rem 1rem;
                margin: 0 1rem 2rem;
            }
            
            .order-form-section {
                margin: 0 1rem 1rem;
                padding: 1.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .header-title {
                font-size: 2rem;
            }
            
            .header-subtitle {
                font-size: 1rem;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 0.875rem 1.75rem;
                font-size: 0.9rem;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--gray-light);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <!-- Header Principal -->
    <header class="main-header">
        <div class="header-content">
            <h1 class="header-title">Commander 🚀</h1>
            <p class="header-subtitle">Boostez votre présence sur les réseaux sociaux avec nos services premium de followers, likes et vues. Processus simple, rapide et sécurisé.</p>
        </div>
    </header>

    <!-- Container principal -->
    <div class="main-container">
        <div class="container">
            <!-- Formulaire de commande -->
            <section class="order-form-section fade-in-up">
                <div class="form-header">
                    <h2 class="form-title">Créer votre commande</h2>
                    <p class="form-subtitle">Remplissez les informations ci-dessous pour créer votre commande SMM</p>
                </div>
                
                <form method="POST" id="orderForm">
                    <!-- Champ caché pour l'ID du service -->
                    <input type="hidden" name="service_id" id="hiddenServiceId" required>
                    
                    <div class="form-row">
                        <!-- Sélection de la catégorie -->
                        <div class="form-group">
                            <label class="form-label">Catégorie de Service</label>
                            <div class="category-selector">
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
                        </div>
                        
                        <!-- Sélection du service -->
                        <div class="form-group">
                            <label class="form-label">Service Spécifique</label>
                            <div class="service-selector" id="serviceSelector" style="display: none;">
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
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <!-- URL du lien -->
                        <div class="form-group">
                            <label class="form-label" for="link_url">URL du lien</label>
                            <input type="url" class="form-control" id="link_url" name="link_url" 
                                   placeholder="https://instagram.com/username ou https://tiktok.com/@username" 
                                   required>
                        </div>
                        
                        <!-- Quantité -->
                        <div class="form-group">
                            <label class="form-label" for="quantity">Quantité</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                   min="1000" step="1000" placeholder="1000" required>
                            <small class="form-text text-muted">Quantité minimale : 1 000</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <!-- Méthode de paiement -->
                        <div class="form-group">
                            <label class="form-label" for="payment_method">Méthode de paiement</label>
                            <select class="form-control form-select" id="payment_method" name="payment_method" required>
                                <option value="">Sélectionner une méthode</option>
                                <option value="mtn_money">MTN Money</option>
                                <option value="moov_money">Moov Money</option>
                            </select>
                        </div>
                        
                        <!-- Informations client (si non connecté) -->
                        <?php if (!$isUserLoggedIn): ?>
                            <div class="form-group">
                                <label class="form-label" for="customer_name">Nom complet</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                       placeholder="Votre nom complet" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="customer_email">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email" 
                                       placeholder="votre@email.com" required>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Bouton de soumission -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket me-2"></i>Créer la commande
                        </button>
                    </div>
                </form>
            </section>
            
            <!-- Résumé de la commande -->
            <section class="order-summary fade-in-up" id="orderSummary" style="display: none;">
                <div class="summary-header">
                    <h3 class="summary-title">Résumé de la Commande</h3>
                    <p class="summary-subtitle">Vérifiez les détails de votre commande avant de continuer</p>
                </div>
                
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Service</div>
                        <div class="summary-value" id="summaryService">-</div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Quantité</div>
                        <div class="summary-value" id="summaryQuantity">-</div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Prix par 1000</div>
                        <div class="summary-value" id="summaryPricePer1000">-</div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Prix total</div>
                        <div class="summary-price" id="summaryTotalPrice">-</div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Variables globales
        let selectedService = null;
        let selectedPrice = 0;
        let selectedMin = 1000;
        let selectedMax = 100000;
        
        // Initialisation du sélecteur de catégorie
        function initializeCategorySelector() {
            const categoryDropdownBtn = document.getElementById('categoryDropdownBtn');
            const categoryDropdownMenu = document.getElementById('categoryDropdownMenu');
            const serviceSelector = document.getElementById('serviceSelector');
            
            if (categoryDropdownBtn && categoryDropdownMenu) {
                categoryDropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    categoryDropdownMenu.classList.toggle('show');
                    categoryDropdownBtn.classList.toggle('active');
                    
                    // Masquer le sélecteur de service
                    if (serviceSelector) serviceSelector.style.display = 'none';
                    
                    // Reset la sélection
                    selectedService = null;
                    selectedPrice = 0;
                    selectedMin = 1000;
                    selectedMax = 100000;
                    
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
                        if (serviceSelector) serviceSelector.style.display = 'block';
                    }
                });
            }
        }
        
        // Charger les services pour une catégorie spécifique
        function loadServices(categoryId, services) {
            const serviceDropdownMenu = document.getElementById('serviceDropdownMenu');
            if (!serviceDropdownMenu) return;
            
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
                        selectService(service);
                    });
                    
                    serviceDropdownMenu.appendChild(serviceOption);
                });
            } else {
                serviceDropdownMenu.innerHTML = '<div class="text-center p-3 text-muted">Aucun service disponible pour cette catégorie</div>';
            }
        }
        
        // Sélectionner un service
        function selectService(service) {
            selectedService = service;
            selectedPrice = service.price_per_1000;
            selectedMin = service.min_quantity;
            selectedMax = service.max_quantity;
            
            // Mettre à jour l'affichage du service sélectionné
            document.querySelector('.selected-service').innerHTML = `
                <i class="fas fa-cog me-2"></i>${service.name}
            `;
            
            // Masquer le sélecteur de service
            const serviceSelector = document.getElementById('serviceSelector');
            if (serviceSelector) serviceSelector.style.display = 'none';
            
            // Mettre à jour le résumé
            updateOrderSummary();
            
            // Mettre à jour le champ caché
            const hiddenServiceId = document.getElementById('hiddenServiceId');
            if (hiddenServiceId) hiddenServiceId.value = service.id;
        }
        
        // Initialiser le sélecteur de service
        function initializeServiceSelector() {
            const serviceDropdownBtn = document.getElementById('serviceDropdownBtn');
            const serviceDropdownMenu = document.getElementById('serviceDropdownMenu');
            
            if (serviceDropdownBtn && serviceDropdownMenu) {
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
        }
        
        // Mettre à jour le résumé de la commande
        function updateOrderSummary() {
            const orderSummary = document.getElementById('orderSummary');
            const summaryService = document.getElementById('summaryService');
            const summaryQuantity = document.getElementById('summaryQuantity');
            const summaryPricePer1000 = document.getElementById('summaryPricePer1000');
            const summaryTotalPrice = document.getElementById('summaryTotalPrice');
            
            if (selectedService) {
                // Afficher le résumé
                orderSummary.style.display = 'block';
                
                // Mettre à jour les informations
                summaryService.textContent = selectedService.name;
                summaryPricePer1000.textContent = selectedService.price_per_1000 + ' FCFA';
                
                // Calculer le prix total basé sur la quantité saisie
                const quantity = parseInt(document.getElementById('quantity').value) || 1000;
                const totalPrice = (selectedService.price_per_1000 * quantity) / 1000;
                
                summaryQuantity.textContent = quantity.toLocaleString();
                summaryTotalPrice.textContent = totalPrice.toLocaleString() + ' FCFA';
            } else {
                // Masquer le résumé
                orderSummary.style.display = 'none';
            }
        }
        
        // Écouter les changements de quantité
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantity');
            if (quantityInput) {
                quantityInput.addEventListener('input', updateOrderSummary);
            }
            
            // Initialiser les sélecteurs
            initializeCategorySelector();
            initializeServiceSelector();
        });
    </script>
</body>
</html>