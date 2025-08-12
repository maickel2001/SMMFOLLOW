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
        
        .stats-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }
        
        .stats-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .stats-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .quick-actions {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            background: var(--darker-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-primary);
            display: block;
        }
        
        .action-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            color: var(--text-primary);
            text-decoration: none;
        }
        
        .action-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .card {
            background: var(--card-bg);
            border-color: var(--border-color);
        }
        
        .card-header {
            background: var(--darker-bg);
            border-color: var(--border-color);
        }
        
        .table {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .table th {
            background: var(--darker-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        .table td {
            border-color: var(--border-color);
            color: var(--text-secondary);
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending { background: rgba(255, 170, 0, 0.2); color: var(--warning-color); }
        .status-processing { background: rgba(0, 170, 255, 0.2); color: var(--info-color); }
        .status-completed { background: rgba(0, 255, 136, 0.2); color: var(--success-color); }
        .status-cancelled { background: rgba(255, 68, 68, 0.2); color: var(--danger-color); }
        
        .priority-badge {
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .priority-urgent { background: rgba(255, 68, 68, 0.2); color: var(--danger-color); }
        .priority-high { background: rgba(255, 170, 0, 0.2); color: var(--warning-color); }
        .priority-normal { background: rgba(0, 170, 255, 0.2); color: var(--info-color); }
        .priority-low { background: rgba(0, 255, 136, 0.2); color: var(--success-color); }
        
        .user-welcome {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            color: var(--dark-bg);
        }
        
        .welcome-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .notification-item {
            background: var(--darker-bg);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary-color);
        }
        
        .notification-item.unread {
            border-left-color: var(--warning-color);
            background: rgba(255, 170, 0, 0.1);
        }
        
        .notification-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }
        
        .notification-message {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .notification-time {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--dark-bg);
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--dark-bg);
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--dark-bg);
        }
    </style>
</head>
<body>
    <div class="client-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard Client
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
            <div class="user-welcome">
                <div class="text-center">
                    <div class="welcome-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h2 class="mb-2">Bienvenue, <?php echo htmlspecialchars($currentUser['first_name']); ?> !</h2>
                    <p class="mb-0">Gérez vos commandes et suivez vos services SMM en temps réel.</p>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($userStats['total_orders'], 0, ',', ' '); ?></div>
                        <div class="stats-label">Total Commandes</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($userStats['total_spent'], 0, ',', ' '); ?> FCFA</div>
                        <div class="stats-label">Total Dépensé</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
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
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
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
            </div>
            
            <!-- Actions rapides -->
            <div class="quick-actions">
                <h4 class="mb-4 text-white">
                    <i class="fas fa-bolt me-2"></i>Actions Rapides
                </h4>
                
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="nouvelle-commande.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h6>Nouvelle Commande</h6>
                            <small class="text-muted">Commander un service</small>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="tickets.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h6>Support Client</h6>
                            <small class="text-muted">Créer un ticket</small>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="commandes.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-list"></i>
                            </div>
                            <h6>Mes Commandes</h6>
                            <small class="text-muted">Voir l'historique</small>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="profil.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <h6>Mon Profil</h6>
                            <small class="text-muted">Modifier mes infos</small>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="row">
                <!-- Commandes récentes -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-shopping-cart me-2"></i>Commandes Récentes
                            </h5>
                        </div>
                        <div class="card-body p-0">
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
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                                    <h5 class="text-muted">Aucune commande</h5>
                                    <p class="text-muted">Vous n'avez pas encore passé de commande.</p>
                                    <a href="nouvelle-commande.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Première Commande
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($recentOrders)): ?>
                                <div class="card-footer text-center">
                                    <a href="commandes.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>Voir toutes mes commandes
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Tickets de support -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-ticket-alt me-2"></i>Mes Tickets
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recentTickets)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recentTickets as $ticket): ?>
                                        <div class="list-group-item" style="background: transparent; border-color: var(--border-color);">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($ticket['subject']); ?></h6>
                                                <?php
                                                $priorityClass = '';
                                                switch ($ticket['priority']) {
                                                    case 'Urgente': $priorityClass = 'priority-urgent'; break;
                                                    case 'Élevée': $priorityClass = 'priority-high'; break;
                                                    case 'Normale': $priorityClass = 'priority-normal'; break;
                                                    case 'Faible': $priorityClass = 'priority-low'; break;
                                                }
                                                ?>
                                                <span class="priority-badge <?php echo $priorityClass; ?>">
                                                    <?php echo htmlspecialchars($ticket['priority']); ?>
                                                </span>
                                            </div>
                                            <p class="mb-1 text-muted small">
                                                <?php echo htmlspecialchars(substr($ticket['message'], 0, 100)) . '...'; ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($ticket['created_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-ticket-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Aucun ticket</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-footer text-center">
                                <a href="tickets.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i>Voir tous mes tickets
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notifications -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-bell me-2"></i>Notifications
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($notifications)): ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                        <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                        <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                        <div class="notification-time"><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-bell fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Aucune notification</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>