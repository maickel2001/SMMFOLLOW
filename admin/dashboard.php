<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de la connexion admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Récupération des statistiques
try {
    $pdo = getDBConnection();
    
    // Total des commandes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    // Commandes en attente
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'En attente'");
    $stmt->execute();
    $pendingOrders = $stmt->fetch()['total'];
    
    // Commandes en cours
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'En cours'");
    $stmt->execute();
    $processingOrders = $stmt->fetch()['total'];
    
    // Commandes terminées
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'Terminée'");
    $stmt->execute();
    $completedOrders = $stmt->fetch()['total'];
    
    // Chiffre d'affaires total
    $stmt = $pdo->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'Terminée'");
    $totalRevenue = $stmt->fetch()['total'] ?: 0;
    
    // Tickets de support ouverts
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM support_tickets WHERE status = 'Ouvert'");
    $openTickets = $stmt->fetch()['total'];
    
    // Dernières commandes
    $stmt = $pdo->query("
        SELECT o.*, s.name as service_name, c.name as category_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN categories c ON s.category_id = c.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $recentOrders = $stmt->fetchAll();
    
    // Derniers tickets
    $stmt = $pdo->query("
        SELECT t.*, o.order_number
        FROM support_tickets t
        LEFT JOIN orders o ON t.order_id = o.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $recentTickets = $stmt->fetchAll();
    
    // Notifications admin
    $notifications = getAdminNotifications();
    
} catch (Exception $e) {
    $error = 'Erreur de base de données.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SMM Pro</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .admin-container {
            padding-top: 20px;
            min-height: 100vh;
            background: var(--dark-bg);
        }
        
        .stats-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
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
        
        .admin-nav {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .admin-nav .nav-link {
            color: var(--text-secondary);
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .admin-nav .nav-link:hover,
        .admin-nav .nav-link.active {
            background: var(--primary-color);
            color: var(--dark-bg);
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin
                </h1>
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">
                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                    </a>
                </div>
            </div>
            
            <!-- Navigation Admin -->
            <div class="admin-nav">
                <nav class="nav nav-pills">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i>Commandes
                    </a>
                    <a class="nav-link" href="services.php">
                        <i class="fas fa-cogs me-2"></i>Services
                    </a>
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags me-2"></i>Catégories
                    </a>
                    <a class="nav-link" href="tickets.php">
                        <i class="fas fa-ticket-alt me-2"></i>Tickets
                    </a>
                    <a class="nav-link" href="analytics.php">
                        <i class="fas fa-chart-line me-2"></i>Analytics
                    </a>
                </nav>
            </div>
            
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($totalOrders, 0, ',', ' '); ?></div>
                        <div class="stats-label">Total Commandes</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($pendingOrders, 0, ',', ' '); ?></div>
                        <div class="stats-label">En Attente</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($processingOrders, 0, ',', ' '); ?></div>
                        <div class="stats-label">En Cours</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($completedOrders, 0, ',', ' '); ?></div>
                        <div class="stats-label">Terminées</div>
                    </div>
                </div>
            </div>
            
            <!-- Statistiques supplémentaires -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stats-number"><?php echo formatPrice($totalRevenue); ?></div>
                        <div class="stats-label">Chiffre d'Affaires Total</div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($openTickets, 0, ',', ' '); ?></div>
                        <div class="stats-label">Tickets Ouverts</div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($notifications['pending_payment'], 0, ',', ' '); ?></div>
                        <div class="stats-label">Paiements en Attente</div>
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
                        <a href="orders.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h6>Gérer les Commandes</h6>
                            <small class="text-muted">Voir et traiter les commandes</small>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="tickets.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h6>Support Client</h6>
                            <small class="text-muted">Répondre aux tickets</small>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="services.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h6>Gérer les Services</h6>
                            <small class="text-muted">Ajouter/modifier services</small>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="analytics.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h6>Voir les Analytics</h6>
                            <small class="text-muted">Statistiques détaillées</small>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Dernières activités -->
            <div class="row">
                <!-- Dernières commandes -->
                <div class="col-lg-8">
                    <div class="card" style="background: var(--card-bg); border-color: var(--border-color);">
                        <div class="card-header" style="background: var(--darker-bg); border-color: var(--border-color);">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-list me-2"></i>Dernières Commandes
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>N° Commande</th>
                                            <th>Client</th>
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
                                                        <div class="fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($order['service_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($order['category_name']); ?></small>
                                                    </div>
                                                </td>
                                                <td class="fw-bold"><?php echo formatPrice($order['total_price']); ?></td>
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
                                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Derniers tickets -->
                <div class="col-lg-4">
                    <div class="card" style="background: var(--card-bg); border-color: var(--border-color);">
                        <div class="card-header" style="background: var(--darker-bg); border-color: var(--border-color);">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-ticket-alt me-2"></i>Derniers Tickets
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
                                                <?php echo htmlspecialchars($ticket['customer_name']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo getTimeAgo($ticket['created_at']); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-ticket-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Aucun ticket récent</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-footer text-center" style="background: var(--darker-bg); border-color: var(--border-color);">
                                <a href="tickets.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i>Voir tous les tickets
                                </a>
                            </div>
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