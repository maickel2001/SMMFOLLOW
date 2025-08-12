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
    <title>Mes Commandes - SMM Pro</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        /* Statistiques Minimalistes */
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px 24px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-medium);
            border-color: var(--accent-primary);
        }
        
        .stat-card:nth-child(1) { border-top: 4px solid var(--accent-primary); }
        .stat-card:nth-child(2) { border-top: 4px solid var(--accent-warning); }
        .stat-card:nth-child(3) { border-top: 4px solid var(--accent-primary); }
        .stat-card:nth-child(4) { border-top: 4px solid var(--accent-success); }
        .stat-card:nth-child(5) { border-top: 4px solid var(--accent-danger); }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--accent-primary);
            opacity: 0.8;
        }
        
        .stat-card:nth-child(1) .stat-icon { color: var(--accent-primary); }
        .stat-card:nth-child(2) .stat-icon { color: var(--accent-warning); }
        .stat-card:nth-child(3) .stat-icon { color: var(--accent-primary); }
        .stat-card:nth-child(4) .stat-icon { color: var(--accent-success); }
        .stat-card:nth-child(5) .stat-icon { color: var(--accent-danger); }
        
        .stat-number {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }
        
        .stat-label {
            color: var(--text-tertiary);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* Filtres Minimalistes */
        .filters-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-xl);
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-subtle);
        }
        
        .filters-section h5 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
        }
        
        .filters-section h5 i {
            color: var(--accent-primary);
            font-size: 1.25rem;
        }
        
        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            color: var(--text-tertiary);
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.875rem;
        }
        
        .filter-control {
            background: var(--bg-secondary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-medium);
            padding: 12px 16px;
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .filter-control:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }
        
        .filter-control::placeholder {
            color: var(--text-tertiary);
            opacity: 0.7;
        }
        
        .filter-btn {
            background: var(--accent-primary);
            border: none;
            border-radius: var(--radius-medium);
            padding: 12px 24px;
            color: white;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            box-shadow: var(--shadow-subtle);
        }
        
        .filter-btn:hover {
            background: #0056cc;
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
            color: white;
        }
        
        .clear-filters {
            background: transparent;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-medium);
            padding: 12px 24px;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            text-decoration: none;
        }
        
        .clear-filters:hover {
            border-color: var(--accent-primary);
            color: var(--accent-primary);
            transform: translateY(-1px);
            text-decoration: none;
        }
        
        /* Tableau Minimaliste */
        .orders-table-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow-subtle);
        }
        
        .orders-table-section h5 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
        }
        
        .orders-table-section h5 i {
            color: var(--accent-primary);
            font-size: 1.25rem;
        }
        
        .table {
            background: transparent;
            border-radius: var(--radius-medium);
            overflow: hidden;
            margin-bottom: 0;
        }
        
        .table th {
            background: var(--bg-secondary);
            border-color: var(--border-lighter);
            color: var(--text-primary);
            font-weight: 600;
            padding: 20px 16px;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .table td {
            border-color: var(--border-lighter);
            color: var(--text-secondary);
            padding: 20px 16px;
            vertical-align: middle;
            transition: all 0.2s ease;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: rgba(0, 122, 255, 0.02);
        }
        
        /* Badges de Statut Minimalistes */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: white;
        }
        
        .status-pending { 
            background: var(--accent-warning);
        }
        .status-processing { 
            background: var(--accent-primary);
        }
        .status-completed { 
            background: var(--accent-success);
        }
        .status-cancelled { 
            background: var(--accent-danger);
        }
        
        /* Boutons d'Action Minimalistes */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 6px 12px;
            border-radius: var(--radius-small);
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-view {
            background: var(--accent-primary);
            color: white;
        }
        
        .btn-view:hover {
            background: #0056cc;
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }
        
        .btn-support {
            background: var(--accent-warning);
            color: white;
        }
        
        .btn-support:hover {
            background: #e6850e;
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }
        
        /* Pagination Minimaliste */
        .pagination-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 24px;
            margin-top: 32px;
            box-shadow: var(--shadow-subtle);
        }
        
        .pagination-info {
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .pagination {
            justify-content: center;
            gap: 8px;
        }
        
        .page-link {
            background: var(--bg-secondary);
            border: 1px solid var(--border-lighter);
            color: var(--text-primary);
            border-radius: var(--radius-small);
            padding: 8px 12px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .page-link:hover {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
            text-decoration: none;
        }
        
        .page-item.active .page-link {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
            font-weight: 600;
        }
        
        .page-item.disabled .page-link {
            background: var(--border-lighter);
            border-color: var(--border-lighter);
            color: var(--text-tertiary);
            cursor: not-allowed;
        }
        
        /* États Vides Minimalistes */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 24px;
            opacity: 0.4;
            color: var(--text-tertiary);
        }
        
        .empty-state h5 {
            color: var(--text-secondary);
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .empty-state p {
            margin-bottom: 24px;
            line-height: 1.6;
            font-size: 1.1rem;
        }
        
        /* Boutons Minimalistes */
        .btn {
            border-radius: var(--radius-medium);
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
        }
        
        .btn-primary {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
        }
        
        .btn-primary:hover {
            background: #0056cc;
            border-color: #0056cc;
        }
        
        .btn-outline-danger {
            color: var(--accent-danger);
            border-color: var(--accent-danger);
        }
        
        .btn-outline-danger:hover {
            background: var(--accent-danger);
            border-color: var(--accent-danger);
        }
        
        /* Animations d'Entrée */
        .animate-fade-in {
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .animate-fade-in:nth-child(1) { animation-delay: 0.1s; }
        .animate-fade-in:nth-child(2) { animation-delay: 0.2s; }
        .animate-fade-in:nth-child(3) { animation-delay: 0.3s; }
        .animate-fade-in:nth-child(4) { animation-delay: 0.4s; }
        .animate-fade-in:nth-child(5) { animation-delay: 0.5s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Mobile First */
        @media (max-width: 576px) {
            .main-header {
                padding: 60px 0 40px;
            }
            
            .header-title {
                font-size: 2rem;
                line-height: 1.2;
            }
            
            .header-subtitle {
                font-size: 1rem;
                line-height: 1.5;
            }
            
            .client-container {
                padding: 20px 16px;
            }
            
            .content-card {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .content-card h5 {
                font-size: 1.125rem;
                margin-bottom: 16px;
            }
            
            .stats-overview {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .stat-label {
                font-size: 0.875rem;
            }
            
            .filters-section {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            
            .filter-control {
                flex: 1;
            }
            
            .filter-control select,
            .filter-control input {
                width: 100%;
                padding: 10px 14px;
                font-size: 0.9rem;
            }
            
            .filter-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .clear-filters {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .orders-table-section {
                overflow-x: auto;
            }
            
            .table {
                font-size: 0.875rem;
                min-width: 600px;
            }
            
            .table th,
            .table td {
                padding: 12px 8px;
                white-space: nowrap;
            }
            
            .status-badge {
                font-size: 0.7rem;
                padding: 4px 8px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 6px;
            }
            
            .btn-action {
                padding: 8px 12px;
                font-size: 0.8rem;
                justify-content: center;
            }
            
            .pagination-section {
                flex-direction: column;
                gap: 16px;
                align-items: center;
            }
            
            .pagination-info {
                text-align: center;
                font-size: 0.875rem;
            }
            
            .pagination {
                justify-content: center;
            }
            
            .page-link {
                padding: 8px 12px;
                font-size: 0.875rem;
            }
            
            .empty-state {
                padding: 40px 16px;
            }
            
            .empty-state i {
                font-size: 2.5rem;
            }
            
            .empty-state h5 {
                font-size: 1.125rem;
            }
            
            .empty-state p {
                font-size: 0.875rem;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .navbar-nav .nav-link {
                padding: 6px 12px;
                margin: 2px;
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 0.875rem;
            }
            
            .btn-lg {
                padding: 12px 24px;
                font-size: 1rem;
            }
        }
        
        @media (min-width: 577px) and (max-width: 768px) {
            .main-header {
                padding: 80px 0 60px;
            }
            
            .header-title {
                font-size: 2.5rem;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .filters-section {
                flex-wrap: wrap;
                gap: 16px;
            }
            
            .content-card {
                padding: 24px;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .header-title {
                font-size: 3rem;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
            }
        }
        
        @media (min-width: 1025px) {
            .header-title {
                font-size: 3.5rem;
            }
            
            .stats-overview {
                grid-template-columns: repeat(4, 1fr);
                gap: 32px;
            }
        }
        
        /* Scrollbar Personnalisée */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--border-light);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-tertiary);
        }
    </style>
</head>
<body>
    <div class="client-container">
        <div class="container-fluid">
            <!-- Header Principal -->
            <div class="main-header animate-fade-in">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h1 class="header-title">Mes Commandes</h1>
                    <p class="header-subtitle">Suivez l'état de toutes vos commandes SMM en temps réel</p>
                </div>
            </div>
            
            <!-- Navigation Client -->
            <div class="client-nav animate-fade-in">
                <nav class="nav nav-pills justify-content-center">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link active" href="commandes.php">
                        <i class="fas fa-shopping-cart me-2"></i>Mes Commandes
                    </a>
                    <a class="nav-link" href="nouvelle-commande.php">
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
            
            <!-- Statistiques des commandes -->
            <div class="stats-overview">
                <div class="stat-card animate-fade-in">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total'], 0, ',', ' '); ?></div>
                    <div class="stat-label">Total Commandes</div>
                </div>
                
                <div class="stat-card animate-fade-in">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['pending'], 0, ',', ' '); ?></div>
                    <div class="stat-label">En Attente</div>
                </div>
                
                <div class="stat-card animate-fade-in">
                    <div class="stat-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['processing'], 0, ',', ' '); ?></div>
                    <div class="stat-label">En Cours</div>
                </div>
                
                <div class="stat-card animate-fade-in">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['completed'], 0, ',', ' '); ?></div>
                    <div class="stat-label">Terminées</div>
                </div>
                
                <div class="stat-card animate-fade-in">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['cancelled'], 0, ',', ' '); ?></div>
                    <div class="stat-label">Annulées</div>
                </div>
            </div>
            
            <!-- Filtres et recherche -->
            <div class="filters-section animate-fade-in">
                <h5>
                    <i class="fas fa-filter"></i>Filtres et Recherche
                </h5>
                
                <form method="GET" class="filters-row">
                    <div class="filter-group">
                        <label for="status">Statut</label>
                        <select name="status" id="status" class="filter-control">
                            <option value="">Tous les statuts</option>
                            <option value="En attente" <?php echo $status === 'En attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="En cours" <?php echo $status === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="Terminée" <?php echo $status === 'Terminée' ? 'selected' : ''; ?>>Terminée</option>
                            <option value="Annulée" <?php echo $status === 'Annulée' ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Rechercher</label>
                        <input type="text" name="search" id="search" class="filter-control" 
                               placeholder="N° commande, service, nom..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="filter-btn">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="commandes.php" class="clear-filters">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Tableau des commandes -->
            <div class="orders-table-section animate-fade-in">
                <h5>
                    <i class="fas fa-list"></i>Liste des Commandes
                </h5>
                
                <?php if (!empty($userOrders)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Service</th>
                                    <th>Client</th>
                                    <th>Lien</th>
                                    <th>Quantité</th>
                                    <th>Prix Total</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userOrders as $order): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold"><?php echo htmlspecialchars($order['order_number']); ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($order['service_name']); ?></div>
                                                <small style="color: var(--text-tertiary);"><?php echo htmlspecialchars($order['category_name'] ?? 'Catégorie'); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <small style="color: var(--text-tertiary);"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($order['link_url']); ?>" 
                                               target="_blank" class="text-primary text-decoration-none">
                                                <i class="fas fa-external-link-alt me-1"></i>Voir
                                            </a>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?php echo number_format($order['quantity'], 0, ',', ' '); ?></span>
                                        </td>
                                        <td class="fw-bold"><?php echo number_format($order['total_price'], 0, ',', ' '); ?> FCFA</td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch ($order['status']) {
                                                case 'En attente': $statusClass = 'status-pending'; break;
                                                case 'En cours': $statusClass = 'status-processing'; break;
                                                case 'Terminée': $statusClass = 'status-completed'; break;
                                                case 'Annulée': $statusClass = 'status-cancelled'; break;
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="commande-details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn-action btn-view">
                                                    <i class="fas fa-eye"></i>Voir
                                                </a>
                                                <a href="tickets.php?order_id=<?php echo $order['id']; ?>" 
                                                   class="btn-action btn-support">
                                                    <i class="fas fa-ticket-alt"></i>Support
                                                </a>
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
                                Affichage de <?php echo $offset + 1; ?> à <?php echo min($offset + $perPage, $totalOrders); ?> 
                                sur <?php echo $totalOrders; ?> commandes
                            </div>
                            
                            <nav aria-label="Navigation des pages">
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
                            </nav>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h5>Aucune commande trouvée</h5>
                        <p>
                            <?php if ($status || $search): ?>
                                Aucune commande ne correspond à vos critères de recherche.
                                <br>Essayez de modifier vos filtres ou de passer votre première commande.
                            <?php else: ?>
                                Vous n'avez pas encore passé de commande.
                                <br>Commencez dès maintenant avec nos services SMM de qualité !
                            <?php endif; ?>
                        </p>
                        <a href="nouvelle-commande.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Première Commande
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Animation des éléments au scroll -->
    <script>
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

        document.querySelectorAll('.animate-fade-in').forEach(el => {
            observer.observe(el);
        });
        
        // Auto-submit du formulaire lors du changement de statut
        document.getElementById('status').addEventListener('change', function() {
            this.form.submit();
        });
        
        // Recherche en temps réel (optionnel)
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    </script>
</body>
</html>