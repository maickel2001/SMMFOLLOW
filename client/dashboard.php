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

// Calcul des statistiques pour les graphiques
$orderStatusData = [];
$ticketStatusData = [];

foreach ($userStats['orders_by_status'] as $status) {
    $orderStatusData[] = [
        'label' => $status['status'],
        'value' => $status['count'],
        'color' => getStatusColor($status['status'])
    ];
}

foreach ($userStats['tickets_by_status'] as $ticket) {
    $ticketStatusData[] = [
        'label' => $ticket['status'],
        'value' => $ticket['count'],
        'color' => getTicketStatusColor($ticket['status'])
    ];
}

function getStatusColor($status) {
    switch ($status) {
        case 'En attente': return '#ffc107';
        case 'En cours': return '#17a2b8';
        case 'Terminée': return '#28a745';
        case 'Annulée': return '#dc3545';
        default: return '#6c757d';
    }
}

function getTicketStatusColor($status) {
    switch ($status) {
        case 'Ouvert': return '#17a2b8';
        case 'En cours': return '#ffc107';
        case 'Résolu': return '#28a745';
        case 'Fermé': return '#6c757d';
        default: return '#6c757d';
    }
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        .dashboard-header {
            background: var(--gradient-primary);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 255, 136, 0.2);
        }
        
        .dashboard-header::before {
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
        
        .welcome-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .welcome-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .welcome-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        /* Cartes de Statistiques Améliorées */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stats-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }
        
        .stats-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 255, 136, 0.2);
            border-color: var(--primary-color);
        }
        
        .stats-card:nth-child(1)::before { background: var(--gradient-primary); }
        .stats-card:nth-child(2)::before { background: var(--gradient-secondary); }
        .stats-card:nth-child(3)::before { background: var(--gradient-warning); }
        .stats-card:nth-child(4)::before { background: var(--gradient-info); }
        
        .stats-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stats-card:nth-child(1) .stats-icon { background: var(--gradient-primary); }
        .stats-card:nth-child(2) .stats-icon { background: var(--gradient-secondary); }
        .stats-card:nth-child(3) .stats-icon { background: var(--gradient-warning); }
        .stats-card:nth-child(4) .stats-icon { background: var(--gradient-info); }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stats-card:nth-child(1) .stats-number { background: var(--gradient-primary); }
        .stats-card:nth-child(2) .stats-number { background: var(--gradient-secondary); }
        .stats-card:nth-child(3) .stats-number { background: var(--gradient-warning); }
        .stats-card:nth-child(4) .stats-number { background: var(--gradient-info); }
        
        .stats-label {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Actions Rapides Améliorées */
        .quick-actions {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 35px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .quick-actions h4 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            font-size: 1.5rem;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .action-btn {
            background: var(--darker-bg);
            border: 2px solid var(--border-color);
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: var(--text-primary);
            display: block;
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-primary);
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }
        
        .action-btn:hover::before {
            left: 0;
        }
        
        .action-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0, 255, 136, 0.3);
        }
        
        .action-content {
            position: relative;
            z-index: 2;
        }
        
        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .action-btn:hover .action-icon {
            color: var(--dark-bg);
            transform: scale(1.1);
        }
        
        .action-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }
        
        .action-btn:hover .action-title {
            color: var(--dark-bg);
        }
        
        .action-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .action-btn:hover .action-description {
            color: var(--dark-bg);
        }
        
        /* Graphiques et Visualisations */
        .charts-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 35px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .charts-section h4 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            font-size: 1.5rem;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }
        
        .chart-container {
            background: var(--darker-bg);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid var(--border-color);
        }
        
        .chart-title {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        /* Contenu Principal */
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .content-card h5 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.3rem;
        }
        
        .content-card h5 i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        /* Tableaux Améliorés */
        .table {
            background: transparent;
            border-radius: 15px;
            overflow: hidden;
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
        }
        
        .table td {
            border-color: var(--border-color);
            color: var(--text-secondary);
            padding: 20px 15px;
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: rgba(0, 255, 136, 0.05);
            transform: scale(1.01);
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
        
        /* Notifications Améliorées */
        .notification-item {
            background: var(--darker-bg);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .notification-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 136, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .notification-item:hover::before {
            left: 100%;
        }
        
        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }
        
        .notification-item.unread {
            border-left-color: var(--warning-color);
            background: rgba(255, 193, 7, 0.1);
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
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .notification-time {
            color: var(--text-secondary);
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* États Vides Améliorés */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h5 {
            color: var(--text-secondary);
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .empty-state p {
            margin-bottom: 20px;
            line-height: 1.6;
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
            
            .charts-grid {
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
            
            .dashboard-header {
                padding: 30px 20px;
            }
            
            .welcome-title {
                font-size: 2rem;
            }
            
            .stats-card {
                padding: 25px 20px;
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
                    <i class="fas fa-tachometer-alt me-3"></i>Dashboard Client
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
            </div>
            
            <!-- Message de bienvenue -->
            <div class="dashboard-header animate-fade-in">
                <div class="welcome-content">
                    <div class="welcome-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h2 class="welcome-title">Bienvenue, <?php echo htmlspecialchars($currentUser['first_name']); ?> !</h2>
                    <p class="welcome-subtitle">Gérez vos commandes et suivez vos services SMM en temps réel avec notre dashboard intelligent.</p>
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
                    <i class="fas fa-bolt me-2"></i>Actions Rapides
                </h4>
                
                <div class="actions-grid">
                    <a href="nouvelle-commande.php" class="action-btn">
                        <div class="action-content">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h6 class="action-title">Nouvelle Commande</h6>
                            <p class="action-description">Commander un service SMM</p>
                        </div>
                    </a>
                    
                    <a href="tickets.php" class="action-btn">
                        <div class="action-content">
                            <div class="action-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h6 class="action-title">Support Client</h6>
                            <p class="action-description">Créer un ticket de support</p>
                        </div>
                    </a>
                    
                    <a href="commandes.php" class="action-btn">
                        <div class="action-content">
                            <div class="action-icon">
                                <i class="fas fa-list"></i>
                            </div>
                            <h6 class="action-title">Mes Commandes</h6>
                            <p class="action-description">Voir l'historique complet</p>
                        </div>
                    </a>
                    
                    <a href="profil.php" class="action-btn">
                        <div class="action-content">
                            <div class="action-icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <h6 class="action-title">Mon Profil</h6>
                            <p class="action-description">Modifier mes informations</p>
                        </div>
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
                                                        <small class="text-muted"><?php echo htmlspecialchars($order['category_name']); ?></small>
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
                    
                    <!-- Graphiques -->
                    <div class="charts-section animate-fade-in">
                        <h4>
                            <i class="fas fa-chart-pie me-2"></i>Statistiques Visuelles
                        </h4>
                        
                        <div class="charts-grid">
                            <div class="chart-container">
                                <div class="chart-title">Répartition des Commandes par Statut</div>
                                <canvas id="orderStatusChart" width="400" height="300"></canvas>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-title">Répartition des Tickets par Statut</div>
                                <canvas id="ticketStatusChart" width="400" height="300"></canvas>
                            </div>
                        </div>
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
    
    <!-- Scripts pour les graphiques -->
    <script>
        // Données pour les graphiques
        const orderStatusData = <?php echo json_encode($orderStatusData); ?>;
        const ticketStatusData = <?php echo json_encode($ticketStatusData); ?>;
        
        // Configuration des graphiques
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#ffffff',
                        font: {
                            size: 12
                        }
                    }
                }
            }
        };
        
        // Graphique des commandes par statut
        if (orderStatusData.length > 0) {
            const orderCtx = document.getElementById('orderStatusChart').getContext('2d');
            new Chart(orderCtx, {
                type: 'doughnut',
                data: {
                    labels: orderStatusData.map(item => item.label),
                    datasets: [{
                        data: orderStatusData.map(item => item.value),
                        backgroundColor: orderStatusData.map(item => item.color),
                        borderWidth: 2,
                        borderColor: '#2a2a2a'
                    }]
                },
                options: chartOptions
            });
        }
        
        // Graphique des tickets par statut
        if (ticketStatusData.length > 0) {
            const ticketCtx = document.getElementById('ticketStatusChart').getContext('2d');
            new Chart(ticketCtx, {
                type: 'doughnut',
                data: {
                    labels: ticketStatusData.map(item => item.label),
                    datasets: [{
                        data: ticketStatusData.map(item => item.value),
                        backgroundColor: ticketStatusData.map(item => item.color),
                        borderWidth: 2,
                        borderColor: '#2a2a2a'
                    }]
                },
                options: chartOptions
            });
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

        document.querySelectorAll('.animate-fade-in').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>