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

// Récupération des statistiques utilisateur avec gestion d'erreur
try {
    $userStats = getUserStats($currentUser['id']);
} catch (Exception $e) {
    $userStats = [
        'total_orders' => 0,
        'total_spent' => 0,
        'orders_by_status' => [],
        'tickets_by_status' => []
    ];
}

// Récupération des commandes récentes avec gestion d'erreur
try {
    $recentOrders = getUserOrders($currentUser['id'], 5);
} catch (Exception $e) {
    $recentOrders = [];
}

// Récupération des tickets récents avec gestion d'erreur
try {
    $recentTickets = getSupportTickets(null, 5, $currentUser['id']);
} catch (Exception $e) {
    $recentTickets = [];
}

// Récupération des notifications avec gestion d'erreur
try {
    $notifications = getUserNotifications($currentUser['id'], 5);
} catch (Exception $e) {
    $notifications = [];
}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BoostSocial</title>
    <meta name="description" content="Votre tableau de bord personnel pour gérer vos commandes SMM et suivre vos performances">
    
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

        /* Navigation Apple-like */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .navbar-toggler:focus {
            box-shadow: none;
            outline: none;
        }

        .navbar-toggler:hover {
            background: var(--gray-light);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(15, 23, 42, 0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
        }

        .navbar-brand span {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: var(--dark);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .nav-link:hover {
            background: var(--gray-light);
            color: var(--primary);
        }

        .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Bouton hamburger personnalisé */
        .hamburger-btn {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 30px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1001;
            transition: all 0.3s ease;
        }

        .hamburger-line {
            width: 100%;
            height: 3px;
            background: var(--dark);
            border-radius: 2px;
            transition: all 0.3s ease;
            transform-origin: center;
        }

        .hamburger-btn:hover .hamburger-line {
            background: var(--primary);
        }

        /* Animation du hamburger */
        .hamburger-btn.active .hamburger-line:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }

        .hamburger-btn.active .hamburger-line:nth-child(2) {
            opacity: 0;
        }

        .hamburger-btn.active .hamburger-line:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }

        /* Menu mobile */
        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 0 0 16px 16px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-light);
            border-top: none;
            overflow: hidden;
            transform: translateY(-100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mobile-menu.active {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }

        .btn-logout {
            background: transparent;
            border: 2px solid var(--danger);
            color: var(--danger);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
        }

        /* Dashboard Container */
        .dashboard-container {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>') no-repeat;
            opacity: 0.3;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .welcome-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-welcome {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-welcome.primary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-welcome.primary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: white;
        }

        .btn-welcome.secondary {
            background: white;
            color: var(--primary);
        }

        .btn-welcome.secondary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Stats Cards */
        .stats-section {
            margin-bottom: 3rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--gray-light);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            color: white;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray);
            font-weight: 500;
            font-size: 1rem;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .section-action {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .section-action:hover {
            color: var(--primary-dark);
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            background: var(--gray-light);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-radius: 8px;
        }

        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .orders-table tr:hover {
            background: var(--gray-light);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 149, 0, 0.1);
            color: var(--warning);
        }

        .status-processing {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .status-completed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s ease;
            border: 1px solid var(--gray-light);
            box-shadow: var(--shadow);
        }

        .quick-action:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: var(--primary);
            text-decoration: none;
        }

        .quick-action-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.2rem;
            color: white;
        }

        .quick-action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .quick-action-desc {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem 0;
            }

            .welcome-section {
                padding: 2rem 1rem;
                margin: 0 1rem 2rem;
            }

            .welcome-actions {
                flex-direction: column;
            }

            .btn-welcome {
                width: 100%;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .content-section {
                margin: 0 1rem 1rem;
                padding: 1.5rem;
            }

            .orders-table {
                font-size: 0.9rem;
            }

            .orders-table th,
            .orders-table td {
                padding: 0.75rem 0.5rem;
            }

            .quick-actions {
                grid-template-columns: 1fr;
                margin: 0 1rem 1rem;
            }

            /* Bouton hamburger visible sur mobile */
            .hamburger-btn {
                display: flex;
            }

            /* Menu mobile */
            .mobile-menu {
                position: fixed;
                top: 80px;
                left: 0;
                right: 0;
                height: calc(100vh - 80px);
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                border-radius: 0;
                box-shadow: var(--shadow-lg);
                border: none;
                border-top: 1px solid var(--gray-light);
                overflow-y: auto;
                transform: translateX(-100%);
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .mobile-menu.active {
                transform: translateX(0);
                opacity: 1;
            }

            .navbar-nav {
                text-align: center;
            }

            .nav-item {
                margin: 0.5rem 0;
            }

            .nav-link {
                padding: 0.75rem 1rem;
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .nav-link:hover {
                background: var(--primary);
                color: white;
                transform: translateX(5px);
            }

            .user-menu {
                flex-direction: column;
                gap: 0.5rem;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--gray-light);
            }

            .user-avatar {
                margin: 0 auto;
            }
        }

        @media (max-width: 480px) {
            .welcome-title {
                font-size: 1.8rem;
            }

            .welcome-subtitle {
                font-size: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .section-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
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

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .slide-in-left {
            animation: slideInLeft 0.8s ease-out forwards;
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
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-rocket me-2"></i>Boost<span>Social</span>
            </a>
            
            <!-- Bouton hamburger personnalisé -->
            <button class="hamburger-btn" type="button" id="hamburgerBtn" aria-label="Toggle navigation">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            
            <!-- Menu de navigation -->
            <div class="mobile-menu" id="mobileMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="nouvelle-commande.php">
                            <i class="fas fa-plus me-2"></i>Nouvelle Commande
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="commandes.php">
                            <i class="fas fa-list me-2"></i>Mes Commandes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tickets.php">
                            <i class="fas fa-headset me-2"></i>Support
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">
                            <i class="fas fa-user me-2"></i>Profil
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="user-menu">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                </div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <section class="welcome-section fade-in-up">
                <div class="welcome-content text-center">
                    <h1 class="welcome-title">Bonjour, <?php echo htmlspecialchars($currentUser['name']); ?> ! 👋</h1>
                    <p class="welcome-subtitle">Bienvenue sur votre tableau de bord personnel. Gérez vos commandes et suivez vos performances en temps réel.</p>
                    
                    <div class="welcome-actions">
                        <a href="nouvelle-commande.php" class="btn-welcome primary">
                            <i class="fas fa-plus me-2"></i>Nouvelle Commande
                        </a>
                        <a href="commandes.php" class="btn-welcome secondary">
                            <i class="fas fa-chart-line me-2"></i>Voir Toutes mes Commandes
                        </a>
                    </div>
                </div>
            </section>

            <!-- Stats Section -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card slide-in-left">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($userStats['total_orders']); ?></div>
                        <div class="stat-label">Commandes Totales</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+12% ce mois</span>
                        </div>
                    </div>
                    
                    <div class="stat-card slide-in-left">
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($userStats['total_spent'], 0, ',', ' '); ?> FCFA</div>
                        <div class="stat-label">Total Dépensé</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+8% ce mois</span>
                        </div>
                    </div>
                    
                    <div class="stat-card slide-in-left">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo isset($userStats['orders_by_status']['Terminée']) ? $userStats['orders_by_status']['Terminée'] : 0; ?></div>
                        <div class="stat-label">Commandes Terminées</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+15% ce mois</span>
                        </div>
                    </div>
                    
                    <div class="stat-card slide-in-left">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo isset($userStats['orders_by_status']['En cours']) ? $userStats['orders_by_status']['En cours'] : 0; ?></div>
                        <div class="stat-label">En Cours</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+5% ce mois</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Actions Rapides</h2>
                </div>
                
                <div class="quick-actions">
                    <a href="nouvelle-commande.php" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="quick-action-title">Nouvelle Commande</div>
                        <div class="quick-action-desc">Commander des followers, likes ou vues</div>
                    </a>
                    
                    <a href="commandes.php" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="quick-action-title">Mes Commandes</div>
                        <div class="quick-action-desc">Suivre l'état de vos commandes</div>
                    </a>
                    
                    <a href="tickets.php" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="quick-action-title">Support</div>
                        <div class="quick-action-desc">Créer un ticket de support</div>
                    </a>
                    
                    <a href="profil.php" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="quick-action-title">Mon Profil</div>
                        <div class="quick-action-desc">Gérer vos informations</div>
                    </a>
                </div>
            </section>

            <!-- Recent Orders -->
            <section class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Commandes Récentes</h2>
                    <a href="commandes.php" class="section-action">Voir Toutes <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                
                <?php if (!empty($recentOrders)): ?>
                    <div class="table-responsive">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Service</th>
                                    <th>Quantité</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                        <td><?php echo number_format($order['quantity']); ?></td>
                                        <td><strong><?php echo number_format($order['total_price'], 0, ',', ' '); ?> FCFA</strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="commande-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Voir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Aucune commande pour le moment</h4>
                        <p class="text-muted">Commencez par créer votre première commande !</p>
                        <a href="nouvelle-commande.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Première Commande
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Recent Tickets -->
            <?php if (!empty($recentTickets)): ?>
            <section class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Tickets de Support Récents</h2>
                    <a href="tickets.php" class="section-action">Voir Tous <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>N° Ticket</th>
                                <th>Sujet</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTickets as $ticket): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($ticket['ticket_number']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                            <?php echo htmlspecialchars($ticket['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($ticket['created_at'])); ?></td>
                                    <td>
                                        <a href="tickets.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Intersection Observer pour les animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);

        // Observer tous les éléments avec la classe fade-in-up
        document.querySelectorAll('.fade-in-up').forEach(el => {
            observer.observe(el);
        });

        // Animation des cartes de stats
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Smooth scroll pour les ancres
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Effet de hover sur les cartes
        document.querySelectorAll('.stat-card, .quick-action').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Mise à jour en temps réel des stats (simulation)
        setInterval(() => {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const currentValue = parseInt(stat.textContent.replace(/\D/g, ''));
                const randomChange = Math.floor(Math.random() * 5) + 1;
                const newValue = currentValue + randomChange;
                
                if (stat.textContent.includes('FCFA')) {
                    stat.textContent = newValue.toLocaleString() + ' FCFA';
                } else {
                    stat.textContent = newValue.toLocaleString();
                }
            });
        }, 30000); // Mise à jour toutes les 30 secondes

        // Menu hamburger mobile
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const body = document.body;

        if (hamburgerBtn && mobileMenu) {
            hamburgerBtn.addEventListener('click', () => {
                hamburgerBtn.classList.toggle('active');
                mobileMenu.classList.toggle('active');
                body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
            });

            // Fermer le menu en cliquant sur un lien
            const mobileLinks = mobileMenu.querySelectorAll('.nav-link');
            mobileLinks.forEach(link => {
                link.addEventListener('click', () => {
                    hamburgerBtn.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                });
            });

            // Fermer le menu en cliquant à l'extérieur
            document.addEventListener('click', (e) => {
                if (!hamburgerBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                    hamburgerBtn.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                }
            });
        }
    </script>
</body>
</html>