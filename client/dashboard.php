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
    <title>Dashboard Client - SMM Pro</title>
    
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
        
        /* Cartes de Statistiques Minimalistes */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stats-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px 24px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-medium);
            border-color: var(--accent-primary);
        }
        
        .stats-card:nth-child(1) { border-top: 4px solid var(--accent-primary); }
        .stats-card:nth-child(2) { border-top: 4px solid var(--accent-secondary); }
        .stats-card:nth-child(3) { border-top: 4px solid var(--accent-warning); }
        .stats-card:nth-child(4) { border-top: 4px solid var(--accent-success); }
        
        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--accent-primary);
            opacity: 0.8;
        }
        
        .stats-card:nth-child(1) .stats-icon { color: var(--accent-primary); }
        .stats-card:nth-child(2) .stats-icon { color: var(--accent-secondary); }
        .stats-card:nth-child(3) .stats-icon { color: var(--accent-warning); }
        .stats-card:nth-child(4) .stats-icon { color: var(--accent-success); }
        
        .stats-number {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }
        
        .stats-label {
            color: var(--text-tertiary);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* Actions Rapides Minimalistes */
        .quick-actions {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-xl);
            padding: 40px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-subtle);
        }
        
        .quick-actions h4 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 32px;
            text-align: center;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .quick-actions h4 i {
            color: var(--accent-primary);
            font-size: 1.5rem;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }
        
        .action-btn {
            background: var(--bg-secondary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px 24px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: var(--text-primary);
            display: block;
        }
        
        .action-btn:hover {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            transform: translateY(-4px);
            box-shadow: var(--shadow-medium);
            text-decoration: none;
            color: var(--text-primary);
        }
        
        .action-icon {
            font-size: 2.25rem;
            margin-bottom: 16px;
            color: var(--accent-primary);
            transition: all 0.3s ease;
        }
        
        .action-btn:hover .action-icon {
            transform: scale(1.1);
        }
        
        .action-title {
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 8px;
            color: var(--text-primary);
        }
        
        .action-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.5;
        }
        

        
        /* Contenu Principal */
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
        }
        
        .content-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow-subtle);
            margin-bottom: 32px;
        }
        
        .content-card h5 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
        }
        
        .content-card h5 i {
            color: var(--accent-primary);
            font-size: 1.25rem;
        }
        
        /* Tableaux Minimalistes */
        .table {
            background: transparent;
            border-radius: var(--radius-medium);
            overflow: hidden;
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
        
        /* Notifications Minimalistes */
        .notification-item {
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            padding: 20px;
            margin-bottom: 16px;
            border-left: 3px solid var(--accent-primary);
            transition: all 0.2s ease;
        }
        
        .notification-item:hover {
            background: var(--bg-tertiary);
            transform: translateX(4px);
        }
        
        .notification-item.unread {
            border-left-color: var(--accent-warning);
            background: rgba(255, 149, 0, 0.04);
        }
        
        .notification-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 1rem;
        }
        
        .notification-message {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 12px;
            line-height: 1.5;
        }
        
        .notification-time {
            color: var(--text-tertiary);
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* États Vides Minimalistes */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.4;
            color: var(--text-tertiary);
        }
        
        .empty-state h5 {
            color: var(--text-secondary);
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .empty-state p {
            margin-bottom: 20px;
            line-height: 1.6;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .stat-item {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .stat-label {
                font-size: 0.875rem;
            }
            
            .orders-table {
                font-size: 0.875rem;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 8px 6px;
            }
            
            .status-badge {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
            
            .notification-item {
                padding: 16px;
                margin-bottom: 12px;
            }
            
            .notification-title {
                font-size: 0.9rem;
            }
            
            .notification-message {
                font-size: 0.8rem;
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
            
            .btn-sm {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }
        
        @media (min-width: 577px) and (max-width: 768px) {
            .main-header {
                padding: 80px 0 60px;
            }
            
            .header-title {
                font-size: 2.5rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .content-card {
                padding: 24px;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .header-title {
                font-size: 3rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
            }
        }
        
        @media (min-width: 1025px) {
            .header-title {
                font-size: 3.5rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 32px;
            }
        }
        
        .btn-primary:hover {
            background: #0056cc;
            border-color: #0056cc;
        }
        
        .btn-outline-primary {
            color: var(--accent-primary);
            border-color: var(--accent-primary);
        }
        
        .btn-outline-primary:hover {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
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
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .main-header {
                padding: 32px 24px;
            }
            
            .header-title {
                font-size: 2rem;
            }
            
            .stats-card {
                padding: 24px 20px;
            }
            
            .quick-actions {
                padding: 32px 24px;
            }
            
            .content-card {
                padding: 24px 20px;
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
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h1 class="header-title">Bienvenue, <?php echo htmlspecialchars($currentUser['first_name']); ?> !</h1>
                    <p class="header-subtitle">Gérez vos commandes et suivez vos services SMM en temps réel avec notre dashboard intelligent.</p>
                </div>
            </div>
            
            <!-- Navigation Client -->
            <div class="client-nav animate-fade-in">
                <nav class="nav nav-pills justify-content-center">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="commandes.php">
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
            
            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stats-card animate-fade-in">
                    <div class="stats-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($userStats['total_orders'], 0, ',', ' '); ?></div>
                    <div class="stats-label">Total Commandes</div>
                </div>
                
                <div class="stats-card animate-fade-in">
                    <div class="stats-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-number"><?php echo number_format($userStats['total_spent'], 0, ',', ' '); ?> FCFA</div>
                    <div class="stats-label">Total Dépensé</div>
                </div>
                
                <div class="stats-card animate-fade-in">
                    <div class="stats-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number">
                        <?php 
                        $pendingCount = 0;
                        foreach ($userStats['orders_by_status'] as $status) {
                            if ($status['status'] === 'En attente') {
                                $pendingCount = $status['count'];
                                break;
                            }
                        }
                        echo number_format($pendingCount, 0, ',', ' ');
                        ?>
                    </div>
                    <div class="stats-label">Commandes en Attente</div>
                </div>
                
                <div class="stats-card animate-fade-in">
                    <div class="stats-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stats-number">
                        <?php 
                        $openTickets = 0;
                        foreach ($userStats['tickets_by_status'] as $ticket) {
                            if ($ticket['status'] === 'Ouvert') {
                                $openTickets = $ticket['count'];
                                break;
                            }
                        }
                        echo number_format($openTickets, 0, ',', ' ');
                        ?>
                    </div>
                    <div class="stats-label">Tickets Ouverts</div>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="quick-actions animate-fade-in">
                <h4>
                    <i class="fas fa-bolt"></i>Actions Rapides
                </h4>
                
                <div class="actions-grid">
                    <a href="nouvelle-commande.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h6 class="action-title">Nouvelle Commande</h6>
                        <p class="action-description">Commander un service SMM</p>
                    </a>
                    
                    <a href="tickets.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <h6 class="action-title">Support Client</h6>
                        <p class="action-description">Créer un ticket de support</p>
                    </a>
                    
                    <a href="commandes.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h6 class="action-title">Mes Commandes</h6>
                        <p class="action-description">Voir l'historique complet</p>
                    </a>
                    
                    <a href="profil.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h6 class="action-title">Mon Profil</h6>
                        <p class="action-description">Modifier mes informations</p>
                    </a>
                </div>
            </div>
            
            <!-- Graphiques et Contenu Principal -->
            <div class="main-content">
                <!-- Colonne Principale -->
                <div class="content-column">
                    <!-- Commandes récentes -->
                    <div class="content-card animate-fade-in">
                        <h5>
                            <i class="fas fa-shopping-cart"></i>Commandes Récentes
                        </h5>
                        
                        <?php if (!empty($recentOrders)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>N° Commande</th>
                                            <th>Service</th>
                                            <th>Prix</th>
                                            <th>Statut</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-bold"><?php echo htmlspecialchars($order['order_number']); ?></span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($order['service_name']); ?></div>
                                                        <small style="color: var(--text-tertiary);"><?php echo htmlspecialchars($order['category_name'] ?? ''); ?></small>
                                                    </div>
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
                                                    <a href="commande-details.php?id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="commandes.php" class="btn btn-primary">
                                    <i class="fas fa-eye me-2"></i>Voir toutes mes commandes
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <h5>Aucune commande</h5>
                                <p>Vous n'avez pas encore passé de commande.</p>
                                <a href="nouvelle-commande.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Première Commande
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="sidebar-column">
                    <!-- Tickets de support -->
                    <div class="content-card animate-fade-in">
                        <h5>
                            <i class="fas fa-ticket-alt"></i>Mes Tickets
                        </h5>
                        
                        <?php if (!empty($recentTickets)): ?>
                            <div class="tickets-list">
                                <?php foreach ($recentTickets as $ticket): ?>
                                    <div class="notification-item">
                                        <div class="notification-title"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                        <div class="notification-message">
                                            <?php echo htmlspecialchars(substr($ticket['message'], 0, 100)) . '...'; ?>
                                        </div>
                                        <div class="notification-time">
                                            <?php echo date('d/m/Y', strtotime($ticket['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="tickets.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Voir tous mes tickets
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-ticket-alt"></i>
                                <h5>Aucun ticket</h5>
                                <p>Vous n'avez pas encore créé de ticket de support.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Notifications -->
                    <div class="content-card animate-fade-in">
                        <h5>
                            <i class="fas fa-bell"></i>Notifications
                        </h5>
                        
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                    <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                    <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                    <div class="notification-time"><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-bell"></i>
                                <h5>Aucune notification</h5>
                                <p>Vous n'avez pas encore de notifications.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
    </script>
</body>
</html>