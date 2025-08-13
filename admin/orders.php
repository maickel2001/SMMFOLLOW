<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de la connexion admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)$_POST['order_id'];
    $action = $_POST['action'];
    $status = $_POST['status'] ?? '';
    $notes = cleanInput($_POST['notes'] ?? '');
    
    try {
        $pdo = getDBConnection();
        
        switch ($action) {
            case 'update_status':
                $stmt = $pdo->prepare("UPDATE orders SET status = ?, admin_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$status, $notes, $orderId]);
                showAlert('Statut de la commande mis à jour avec succès.', 'success');
                break;
                
            case 'cancel_order':
                $stmt = $pdo->prepare("UPDATE orders SET status = 'Annulée', admin_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$notes, $orderId]);
                showAlert('Commande annulée avec succès.', 'success');
                break;
        }
    } catch (Exception $e) {
        showAlert('Erreur lors de la mise à jour.', 'danger');
    }
}

// Filtres
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Construction de la requête
$whereConditions = [];
$params = [];

if ($statusFilter) {
    $whereConditions[] = "o.status = ?";
    $params[] = $statusFilter;
}

if ($searchQuery) {
    $whereConditions[] = "(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Récupération des commandes
try {
    $pdo = getDBConnection();
    
    $sql = "
        SELECT o.*, s.name as service_name, c.name as category_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN categories c ON s.category_id = c.id
        $whereClause
        ORDER BY o.created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Erreur de base de données.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes - SMM Pro</title>
    
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
        
        .filters-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
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
        
        .modal-content {
            background: var(--card-bg);
            border-color: var(--border-color);
        }
        
        .modal-header {
            background: var(--darker-bg);
            border-color: var(--border-color);
        }
        
        .modal-footer {
            border-color: var(--border-color);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-shopping-cart me-2"></i>Gestion des Commandes
                </h1>
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au Dashboard
                </a>
            </div>
            
            <!-- Navigation Admin -->
            <div class="admin-nav">
                <nav class="nav nav-pills">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link active" href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i>Commandes
                    </a>
                    <a class="nav-link" href="services.php">
                        <i class="fas fa-cogs me-2"></i>Services
                    </a>
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags me-2"></i>Catégories
                    </a>
                </nav>
            </div>
            
            <!-- Filtres -->
            <div class="filters-card">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="En attente" <?php echo ($statusFilter === 'En attente') ? 'selected' : ''; ?>>En attente</option>
                            <option value="En cours" <?php echo ($statusFilter === 'En cours') ? 'selected' : ''; ?>>En cours</option>
                            <option value="Terminée" <?php echo ($statusFilter === 'Terminée') ? 'selected' : ''; ?>>Terminée</option>
                            <option value="Annulée" <?php echo ($statusFilter === 'Annulée') ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="N° commande, nom client, email..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Liste des commandes -->
            <div class="card" style="background: var(--card-bg); border-color: var(--border-color);">
                <div class="card-header" style="background: var(--darker-bg); border-color: var(--border-color);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-list me-2"></i>Commandes (<?php echo count($orders); ?>)
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
                                    <th>Quantité</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Paiement</th>
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
                                        <td><?php echo number_format($order['quantity'], 0, ',', ' '); ?></td>
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
                                        <td>
                                            <div>
                                                <div><?php echo htmlspecialchars($order['payment_method']); ?></div>
                                                <?php if ($order['payment_proof']): ?>
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>Preuve reçue
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-warning">
                                                        <i class="fas fa-clock me-1"></i>En attente
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($order['status'] !== 'Annulée'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Mise à jour du statut -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Mettre à jour le statut</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="updateOrderId">
                        <input type="hidden" name="action" value="update_status">
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Nouveau statut</label>
                            <select name="status" class="form-select" required>
                                <option value="En attente">En attente</option>
                                <option value="En cours">En cours</option>
                                <option value="Terminée">Terminée</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Notes admin (optionnel)</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Ajoutez des notes sur cette commande..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Annulation -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Annuler la commande</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="cancelOrderId">
                        <input type="hidden" name="action" value="cancel_order">
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Êtes-vous sûr de vouloir annuler cette commande ?
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Motif de l'annulation *</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Indiquez le motif de l'annulation..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Confirmer l'annulation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        function updateStatus(orderId, currentStatus) {
            document.getElementById('updateOrderId').value = orderId;
            document.querySelector('#updateStatusModal select[name="status"]').value = currentStatus;
            new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
        }
        
        function cancelOrder(orderId) {
            document.getElementById('cancelOrderId').value = orderId;
            new bootstrap.Modal(document.getElementById('cancelOrderModal')).show();
        }
        
        function viewOrder(orderId) {
            window.open('order-details.php?id=' + orderId, '_blank');
        }
    </script>
</body>
</html>