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

// Récupération des paramètres de filtrage
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Récupération des commandes avec gestion d'erreur
try {
    $userOrders = getUserOrders($currentUser['id']);
    
    // Filtrage par statut
    if ($status && $status !== 'all') {
        $userOrders = array_filter($userOrders, function($order) use ($status) {
            return $order['status'] === $status;
        });
    }
    
    // Filtrage par recherche
    if ($search) {
        $userOrders = array_filter($userOrders, function($order) use ($search) {
            return stripos($order['order_number'], $search) !== false ||
                   stripos($order['service_name'], $search) !== false ||
                   stripos($order['customer_name'], $search) !== false;
        });
    }
    
    // Pagination
    $totalOrders = count($userOrders);
    $totalPages = ceil($totalOrders / $perPage);
    $userOrders = array_slice($userOrders, $offset, $perPage);
    
} catch (Exception $e) {
    $userOrders = [];
    $totalOrders = 0;
    $totalPages = 1;
}

// Calcul des statistiques
$stats = [
    'total' => 0,
    'pending' => 0,
    'processing' => 0,
    'completed' => 0,
    'cancelled' => 0
];

try {
    $allOrders = getUserOrders($currentUser['id']);
    foreach ($allOrders as $order) {
        $stats['total']++;
        switch ($order['status']) {
            case 'En attente': $stats['pending']++; break;
            case 'En cours': $stats['processing']++; break;
            case 'Terminée': $stats['completed']++; break;
            case 'Annulée': $stats['cancelled']++; break;
        }
    }
} catch (Exception $e) {
    // Gestion silencieuse de l'erreur
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes - BoostSocial</title>
    <meta name="description" content="Gérez et suivez toutes vos commandes SMM en temps réel">
    
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

        /* Container principal */
        .main-container {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
        }

        /* Header de la page */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>') no-repeat;
            opacity: 0.3;
        }

        .page-header-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .page-title {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .page-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Stats Cards */
        .stats-section {
            margin-bottom: 3rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--gray-light);
            text-align: center;
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

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray);
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Filtres et recherche */
        .filters-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .filters-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            margin: 0;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border: 2px solid var(--gray-light);
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid var(--gray);
            color: var(--gray);
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: var(--gray);
            color: white;
            transform: translateY(-2px);
        }

        /* Tableau des commandes */
        .orders-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
        }

        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .orders-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .orders-count {
            color: var(--gray);
            font-weight: 500;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .orders-table th {
            background: var(--gray-light);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-radius: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-light);
            vertical-align: middle;
        }

        .orders-table tr:hover {
            background: var(--gray-light);
        }

        .order-number {
            font-weight: 700;
            color: var(--primary);
        }

        .service-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .service-name {
            font-weight: 600;
            color: var(--dark);
        }

        .service-category {
            font-size: 0.85rem;
            color: var(--gray);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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

        .order-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline-info {
            background: transparent;
            border: 2px solid var(--info);
            color: var(--info);
        }

        .btn-outline-info:hover {
            background: var(--info);
            color: white;
            transform: translateY(-2px);
        }

        /* Pagination */
        .pagination-section {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .pagination-info {
            color: var(--gray);
            font-weight: 500;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .page-item {
            margin: 0;
        }

        .page-link {
            padding: 0.5rem 1rem;
            border: 2px solid var(--gray-light);
            border-radius: 8px;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .page-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .page-item.active .page-link {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-item.disabled .page-link {
            color: var(--gray);
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* État vide */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }

        .empty-state h4 {
            color: var(--dark);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .empty-state p {
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0;
            }

            .page-header {
                padding: 2rem 1rem;
                margin: 0 1rem 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                margin: 0 1rem 2rem;
            }

            .filters-section,
            .orders-section {
                margin: 0 1rem 1rem;
                padding: 1.5rem;
            }

            .filters-form {
                grid-template-columns: 1fr;
            }

            .orders-table {
                font-size: 0.9rem;
            }

            .orders-table th,
            .orders-table td {
                padding: 0.75rem 0.5rem;
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
            .page-title {
                font-size: 2rem;
            }

            .page-subtitle {
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-number {
                font-size: 1.75rem;
            }

            .filters-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .orders-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .orders-table {
                font-size: 0.8rem;
            }

            .orders-table th,
            .orders-table td {
                padding: 0.5rem 0.25rem;
            }

            .order-actions {
                flex-direction: column;
                gap: 0.25rem;
            }

            .btn-sm {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="commandes.php">
                            <i class="fas fa-list me-2"></i>Mes Commandes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nouvelle-commande.php">
                            <i class="fas fa-plus me-2"></i>Nouvelle Commande
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

    <!-- Container principal -->
    <div class="main-container">
        <div class="container">
            <!-- Header de la page -->
            <section class="page-header fade-in-up">
                <div class="page-header-content">
                    <h1 class="page-title">Mes Commandes 📋</h1>
                    <p class="page-subtitle">Suivez l'état de toutes vos commandes SMM en temps réel et gérez vos services facilement.</p>
                </div>
            </section>

            <!-- Statistiques -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card slide-in-left">
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                    
                    <div class="stat-card slide-in-left">
                        <div class="stat-number"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label">En Attente</div>
                    </div>
                    
                    <div class="stat-card slide-in-left">
                        <div class="stat-number"><?php echo $stats['processing']; ?></div>
                        <div class="stat-label">En Cours</div>
                    </div>
                    
                    <div class="stat-card slide-in-left">
                        <div class="stat-number"><?php echo $stats['completed']; ?></div>
                        <div class="stat-label">Terminées</div>
                    </div>
                </div>
            </section>

            <!-- Filtres et recherche -->
            <section class="filters-section">
                <div class="filters-header">
                    <h2 class="filters-title">Filtres et Recherche</h2>
                </div>
                
                <form method="GET" class="filters-form">
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-control">
                            <option value="">Tous les statuts</option>
                            <option value="En attente" <?php echo $status === 'En attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="En cours" <?php echo $status === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="Terminée" <?php echo $status === 'Terminée' ? 'selected' : ''; ?>>Terminée</option>
                            <option value="Annulée" <?php echo $status === 'Annulée' ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               class="form-control" placeholder="N° commande, service...">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filtrer
                        </button>
                        <a href="commandes.php" class="btn-secondary ms-2">
                            <i class="fas fa-times me-2"></i>Réinitialiser
                        </a>
                    </div>
                </form>
            </section>

            <!-- Commandes -->
            <section class="orders-section">
                <div class="orders-header">
                    <div>
                        <h2 class="orders-title">Liste des Commandes</h2>
                        <p class="orders-count"><?php echo $totalOrders; ?> commande(s) trouvée(s)</p>
                    </div>
                </div>
                
                <?php if (!empty($userOrders)): ?>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userOrders as $order): ?>
                                    <tr>
                                        <td>
                                            <span class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                                        </td>
                                        <td>
                                            <div class="service-info">
                                                <div class="service-name"><?php echo htmlspecialchars($order['service_name']); ?></div>
                                                <div class="service-category"><?php echo htmlspecialchars($order['category_name'] ?? ''); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($order['quantity']); ?></td>
                                        <td><strong><?php echo number_format($order['total_price'], 0, ',', ' '); ?> FCFA</strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="order-actions">
                                                <a href="commande-details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>Voir
                                                </a>
                                                <?php if ($order['status'] === 'En attente'): ?>
                                                    <a href="commande-details.php?id=<?php echo $order['id']; ?>&action=edit" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-edit me-1"></i>Modifier
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination-section">
                            <div class="pagination-info">
                                Page <?php echo $page; ?> sur <?php echo $totalPages; ?>
                            </div>
                            
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h4>Aucune commande trouvée</h4>
                        <p>
                            <?php if ($search || $status): ?>
                                Aucune commande ne correspond à vos critères de recherche.
                            <?php else: ?>
                                Vous n'avez pas encore passé de commande.
                            <?php endif; ?>
                        </p>
                        <a href="nouvelle-commande.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Première Commande
                        </a>
                    </div>
                <?php endif; ?>
            </section>
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

        // Effet de hover sur les cartes
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Animation des liens de pagination
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>