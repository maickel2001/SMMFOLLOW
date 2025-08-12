<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de la connexion admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    redirect('orders.php');
}

// Récupération des détails de la commande
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name, s.platform, s.type, s.delivery_time,
               c.name as category_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        redirect('orders.php');
    }
} catch (Exception $e) {
    redirect('orders.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Commande - SMM Pro</title>
    
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
        
        .order-details-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .order-header {
            background: var(--darker-bg);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-pending { background: rgba(255, 170, 0, 0.2); color: var(--warning-color); }
        .status-processing { background: rgba(0, 170, 255, 0.2); color: var(--info-color); }
        .status-completed { background: rgba(0, 255, 136, 0.2); color: var(--success-color); }
        .status-cancelled { background: rgba(255, 68, 68, 0.2); color: var(--danger-color); }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .info-value {
            color: var(--text-secondary);
        }
        
        .payment-proof {
            max-width: 300px;
            border-radius: 10px;
            border: 2px solid var(--border-color);
        }
        
        .btn-back {
            background: var(--primary-color);
            border: none;
            color: var(--dark-bg);
        }
        
        .btn-back:hover {
            background: var(--secondary-color);
            color: var(--dark-bg);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-eye me-2"></i>Détails de la Commande
                </h1>
                <a href="orders.php" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Retour aux Commandes
                </a>
            </div>
            
            <!-- En-tête de la commande -->
            <div class="order-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                        <small class="text-muted">Créée le <?php echo date('d/m/Y à H:i', strtotime($order['created_at'])); ?></small>
                    </div>
                    <div class="col-md-6 text-md-end">
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
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Informations de la commande -->
                <div class="col-lg-8">
                    <div class="order-details-card">
                        <h4 class="mb-4">
                            <i class="fas fa-info-circle me-2"></i>Informations de la Commande
                        </h4>
                        
                        <div class="info-row">
                            <span class="info-label">Service commandé:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['service_name']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Catégorie:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['category_name']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Plateforme:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['platform']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Type de service:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['type']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Quantité:</span>
                            <span class="info-value"><?php echo number_format($order['quantity'], 0, ',', ' '); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Prix total:</span>
                            <span class="info-value fw-bold text-success"><?php echo formatPrice($order['total_price']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Temps de livraison:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['delivery_time']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Lien à promouvoir:</span>
                            <span class="info-value">
                                <a href="<?php echo htmlspecialchars($order['link_url']); ?>" target="_blank" class="text-primary">
                                    <?php echo htmlspecialchars($order['link_url']); ?>
                                    <i class="fas fa-external-link-alt ms-1"></i>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Informations client et paiement -->
                <div class="col-lg-4">
                    <!-- Informations client -->
                    <div class="order-details-card">
                        <h5 class="mb-3">
                            <i class="fas fa-user me-2"></i>Informations Client
                        </h5>
                        
                        <div class="info-row">
                            <span class="info-label">Nom:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                        </div>
                    </div>
                    
                    <!-- Informations de paiement -->
                    <div class="order-details-card">
                        <h5 class="mb-3">
                            <i class="fas fa-credit-card me-2"></i>Paiement
                        </h5>
                        
                        <div class="info-row">
                            <span class="info-label">Méthode:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Statut:</span>
                            <span class="info-value">
                                <?php if ($order['payment_proof']): ?>
                                    <span class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>Preuve reçue
                                    </span>
                                <?php else: ?>
                                    <span class="text-warning">
                                        <i class="fas fa-clock me-1"></i>En attente
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <?php if ($order['payment_proof']): ?>
                            <div class="text-center mt-3">
                                <h6 class="mb-2">Preuve de paiement:</h6>
                                <img src="../uploads/<?php echo htmlspecialchars($order['payment_proof']); ?>" 
                                     alt="Preuve de paiement" class="payment-proof img-fluid">
                                <div class="mt-2">
                                    <a href="../uploads/<?php echo htmlspecialchars($order['payment_proof']); ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>Voir en grand
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Notes admin -->
                    <?php if ($order['admin_notes']): ?>
                        <div class="order-details-card">
                            <h5 class="mb-3">
                                <i class="fas fa-sticky-note me-2"></i>Notes Admin
                            </h5>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['admin_notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="order-details-card">
                <h5 class="mb-3">
                    <i class="fas fa-cogs me-2"></i>Actions Rapides
                </h5>
                
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="orders.php?action=update_status&order_id=<?php echo $order['id']; ?>" 
                           class="btn btn-outline-success w-100">
                            <i class="fas fa-edit me-2"></i>Modifier le statut
                        </a>
                    </div>
                    
                    <?php if ($order['status'] !== 'Terminée' && $order['status'] !== 'Annulée'): ?>
                        <div class="col-md-3 mb-2">
                            <a href="orders.php?action=mark_processing&order_id=<?php echo $order['id']; ?>" 
                               class="btn btn-outline-info w-100">
                                <i class="fas fa-cogs me-2"></i>Marquer en cours
                            </a>
                        </div>
                        
                        <div class="col-md-3 mb-2">
                            <a href="orders.php?action=mark_completed&order_id=<?php echo $order['id']; ?>" 
                               class="btn btn-outline-success w-100">
                                <i class="fas fa-check me-2"></i>Marquer terminée
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order['status'] !== 'Annulée'): ?>
                        <div class="col-md-3 mb-2">
                            <a href="orders.php?action=cancel_order&order_id=<?php echo $order['id']; ?>" 
                               class="btn btn-outline-danger w-100">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>