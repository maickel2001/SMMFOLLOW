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
            --gradient-primary: linear-gradient(135deg, #00ff88 0%, #00cc6a 100%);
            --gradient-secondary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-warning: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-info: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        body {
            background: var(--dark-bg);
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        
        .client-container {
            padding-top: 20px;
            min-height: 100vh;
            background: var(--dark-bg);
        }
        
        /* Navigation Client Améliorée */
        .client-nav {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .client-nav .nav-link {
            color: var(--text-secondary);
            padding: 15px 25px;
            border-radius: 15px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        
        .client-nav .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-primary);
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
        }
        
        .client-nav .nav-link:hover::before,
        .client-nav .nav-link.active::before {
            left: 0;
        }
        
        .client-nav .nav-link:hover,
        .client-nav .nav-link.active {
            color: var(--dark-bg);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 255, 136, 0.3);
        }
        
        /* Header Amélioré */
        .page-header {
            background: var(--gradient-primary);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 255, 136, 0.2);
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .header-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .header-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .header-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .header-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        /* Statistiques Améliorées */
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .stat-card:nth-child(1)::before { background: var(--gradient-primary); }
        .stat-card:nth-child(2)::before { background: var(--gradient-warning); }
        .stat-card:nth-child(3)::before { background: var(--gradient-info); }
        .stat-card:nth-child(4)::before { background: var(--gradient-secondary); }
        .stat-card:nth-child(5)::before { background: var(--gradient-warning); }
        
        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 255, 136, 0.2);
            border-color: var(--primary-color);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-card:nth-child(1) .stat-icon { background: var(--gradient-primary); }
        .stat-card:nth-child(2) .stat-icon { background: var(--gradient-warning); }
        .stat-card:nth-child(3) .stat-icon { background: var(--gradient-info); }
        .stat-card:nth-child(4) .stat-icon { background: var(--gradient-secondary); }
        .stat-card:nth-child(5) .stat-icon { background: var(--gradient-warning); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-card:nth-child(1) .stat-number { background: var(--gradient-primary); }
        .stat-card:nth-child(2) .stat-number { background: var(--gradient-warning); }
        .stat-card:nth-child(3) .stat-number { background: var(--gradient-info); }
        .stat-card:nth-child(4) .stat-number { background: var(--gradient-secondary); }
        .stat-card:nth-child(5) .stat-number { background: var(--gradient-warning); }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Filtres Améliorés */
        .filters-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .filters-section h5 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.3rem;
        }
        
        .filters-section h5 i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .filter-control {
            background: var(--darker-bg);
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 12px 18px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .filter-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.2);
        }
        
        .filter-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }
        
        .filter-btn {
            background: var(--gradient-primary);
            border: none;
            border-radius: 15px;
            padding: 12px 25px;
            color: var(--dark-bg);
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .filter-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s ease;
        }
        
        .filter-btn:hover::before {
            left: 0;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
        }
        
        .clear-filters {
            background: transparent;
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 12px 25px;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .clear-filters:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        /* Tableau Amélioré */
        .orders-table-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .orders-table-section h5 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.3rem;
        }
        
        .orders-table-section h5 i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .table {
            background: transparent;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 0;
        }
        
        .table th {
            background: var(--darker-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
            font-weight: 600;
            padding: 20px 15px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }
        
        .table th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .table th:hover::after {
            transform: scaleX(1);
        }
        
        .table td {
            border-color: var(--border-color);
            color: var(--text-secondary);
            padding: 20px 15px;
            vertical-align: middle;
            transition: all 0.3s ease;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .table tbody tr::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--gradient-primary);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .table tbody tr:hover::before {
            transform: scaleY(1);
        }
        
        .table tbody tr:hover {
            background: rgba(0, 255, 136, 0.05);
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.1);
        }
        
        /* Badges de Statut Améliorés */
        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s ease;
        }
        
        .status-badge:hover::before {
            left: 0;
        }
        
        .status-pending { 
            background: linear-gradient(135deg, #ffc107, #ff8f00);
            color: #000;
        }
        .status-processing { 
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: #fff;
        }
        .status-completed { 
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
        }
        .status-cancelled { 
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: #fff;
        }
        
        /* Boutons d'Action Améliorés */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-view {
            background: var(--gradient-info);
            color: #fff;
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.4);
            color: #fff;
        }
        
        .btn-support {
            background: var(--gradient-warning);
            color: #fff;
        }
        
        .btn-support:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
            color: #fff;
        }
        
        /* Pagination Améliorée */
        .pagination-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .pagination-info {
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .pagination {
            justify-content: center;
            gap: 10px;
        }
        
        .page-link {
            background: var(--darker-bg);
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 12px;
            padding: 10px 16px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .page-link:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--dark-bg);
            transform: translateY(-2px);
        }
        
        .page-item.active .page-link {
            background: var(--gradient-primary);
            border-color: var(--primary-color);
            color: var(--dark-bg);
            font-weight: 600;
        }
        
        .page-item.disabled .page-link {
            background: var(--border-color);
            border-color: var(--border-color);
            color: var(--text-secondary);
            cursor: not-allowed;
        }
        
        /* États Vides Améliorés */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 5rem;
            margin-bottom: 25px;
            opacity: 0.5;
            animation: float 3s ease-in-out infinite;
        }
        
        .empty-state h5 {
            color: var(--text-secondary);
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .empty-state p {
            margin-bottom: 25px;
            line-height: 1.6;
            font-size: 1.1rem;
        }
        
        /* Animations d'Entrée */
        .animate-fade-in {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .stats-overview {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .filters-row {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                padding: 30px 20px;
            }
            
            .header-title {
                font-size: 2rem;
            }
            
            .stat-card {
                padding: 20px 15px;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
        
        /* Scrollbar Personnalisée */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--border-color);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="client-container">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-shopping-cart me-3"></i>Mes Commandes
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
            </div>
            
            <!-- Header de la page -->
            <div class="page-header animate-fade-in">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2 class="header-title">Mes Commandes</h2>
                    <p class="header-subtitle">Suivez l'état de toutes vos commandes SMM en temps réel</p>
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
                                                <small class="text-muted"><?php echo htmlspecialchars($order['category_name'] ?? 'Catégorie'); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
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
    
    <script>
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