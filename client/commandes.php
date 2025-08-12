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

// Filtres
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Récupération des commandes avec gestion d'erreur
try {
    $orders = getUserOrders($currentUser['id']);
    
    // Filtrage par statut
    if ($status && $status !== 'all') {
        $orders = array_filter($orders, function($order) use ($status) {
            return $order['status'] === $status;
        });
    }
    
    // Filtrage par recherche
    if ($search) {
        $orders = array_filter($orders, function($order) use ($search) {
            return stripos($order['order_number'], $search) !== false ||
                   stripos($order['service_name'], $search) !== false;
        });
    }
} catch (Exception $e) {
    $orders = [];
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
        
        .filters {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .form-control, .form-select {
            background: var(--darker-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        .form-control:focus, .form-select:focus {
            background: var(--darker-bg);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 136, 0.25);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="client-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-shopping-cart me-2"></i>Mes Commandes
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
            
            <!-- Filtres -->
            <div class="filters">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Rechercher</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="N° commande ou service..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all">Tous les statuts</option>
                            <option value="En attente" <?php echo $status === 'En attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="En cours" <?php echo $status === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="Terminée" <?php echo $status === 'Terminée' ? 'selected' : ''; ?>>Terminée</option>
                            <option value="Annulée" <?php echo $status === 'Annulée' ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Filtrer
                            </button>
                            <a href="commandes.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Liste des commandes -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-list me-2"></i>Historique des Commandes
                    </h5>
                    <span class="badge bg-primary"><?php echo count($orders); ?> commande(s)</span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($orders)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>N° Commande</th>
                                        <th>Service</th>
                                        <th>Lien</th>
                                        <th>Quantité</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
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
                                            <td>
                                                <a href="<?php echo htmlspecialchars($order['link_url']); ?>" 
                                                   target="_blank" class="text-primary text-decoration-none">
                                                    <i class="fas fa-external-link-alt me-1"></i>Voir le lien
                                                </a>
                                            </td>
                                            <td><?php echo number_format($order['quantity'], 0, ',', ' '); ?></td>
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
                                                   class="btn btn-sm btn-outline-primary" title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($order['status'] === 'En attente'): ?>
                                                    <a href="commande-details.php?id=<?php echo $order['id']; ?>&action=cancel" 
                                                       class="btn btn-sm btn-outline-danger ms-1" title="Annuler"
                                                       onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-cart"></i>
                            <h4 class="text-muted">Aucune commande trouvée</h4>
                            <p class="text-muted">
                                <?php if ($search || $status): ?>
                                    Aucune commande ne correspond à vos critères de recherche.
                                <?php else: ?>
                                    Vous n'avez pas encore passé de commande.
                                <?php endif; ?>
                            </p>
                            <a href="nouvelle-commande.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Passer ma première commande
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Statistiques rapides -->
            <?php if (!empty($orders)): ?>
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="text-primary"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'En attente')); ?></h5>
                                <p class="text-muted mb-0">En attente</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="text-info"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'En cours')); ?></h5>
                                <p class="text-muted mb-0">En cours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="text-success"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'Terminée')); ?></h5>
                                <p class="text-muted mb-0">Terminées</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="text-danger"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'Annulée')); ?></h5>
                                <p class="text-muted mb-0">Annulées</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>