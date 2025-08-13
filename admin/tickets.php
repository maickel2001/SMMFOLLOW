<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de la connexion admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = (int)$_POST['ticket_id'];
    $action = $_POST['action'];
    $status = $_POST['status'] ?? '';
    $adminResponse = cleanInput($_POST['admin_response'] ?? '');
    $priority = $_POST['priority'] ?? '';
    
    try {
        $pdo = getDBConnection();
        
        switch ($action) {
            case 'update_ticket':
                $stmt = $pdo->prepare("
                    UPDATE support_tickets 
                    SET status = ?, priority = ?, admin_response = ?, admin_id = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([$status, $priority, $adminResponse, $_SESSION['admin_id'], $ticketId]);
                
                // Logger l'action
                logAdminAction($_SESSION['admin_id'], 'update_ticket', "Ticket #$ticketId mis à jour");
                
                showAlert('Ticket mis à jour avec succès.', 'success');
                break;
                
            case 'close_ticket':
                $stmt = $pdo->prepare("
                    UPDATE support_tickets 
                    SET status = 'Fermé', admin_id = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([$_SESSION['admin_id'], $ticketId]);
                
                logAdminAction($_SESSION['admin_id'], 'close_ticket', "Ticket #$ticketId fermé");
                showAlert('Ticket fermé avec succès.', 'success');
                break;
        }
    } catch (Exception $e) {
        showAlert('Erreur lors de la mise à jour.', 'danger');
    }
}

// Filtres
$statusFilter = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Construction de la requête
$whereConditions = [];
$params = [];

if ($statusFilter) {
    $whereConditions[] = "t.status = ?";
    $params[] = $statusFilter;
}

if ($priorityFilter) {
    $whereConditions[] = "t.priority = ?";
    $params[] = $priorityFilter;
}

if ($searchQuery) {
    $whereConditions[] = "(t.ticket_number LIKE ? OR t.customer_name LIKE ? OR t.customer_email LIKE ? OR t.subject LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Récupération des tickets
try {
    $pdo = getDBConnection();
    
    $sql = "
        SELECT t.*, o.order_number, a.name as admin_name
        FROM support_tickets t
        LEFT JOIN orders o ON t.order_id = o.id
        LEFT JOIN admins a ON t.admin_id = a.id
        $whereClause
        ORDER BY 
            CASE t.priority 
                WHEN 'Urgente' THEN 1 
                WHEN 'Élevée' THEN 2 
                WHEN 'Normale' THEN 3 
                WHEN 'Faible' THEN 4 
            END,
            t.created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Erreur de base de données.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Tickets - SMM Pro</title>
    
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
        
        .ticket-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .ticket-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .ticket-number {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .ticket-date {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .priority-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .priority-urgent { background: rgba(255, 68, 68, 0.2); color: var(--danger-color); }
        .priority-high { background: rgba(255, 170, 0, 0.2); color: var(--warning-color); }
        .priority-normal { background: rgba(0, 170, 255, 0.2); color: var(--info-color); }
        .priority-low { background: rgba(0, 255, 136, 0.2); color: var(--success-color); }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-open { background: rgba(0, 170, 255, 0.2); color: var(--info-color); }
        .status-processing { background: rgba(255, 170, 0, 0.2); color: var(--warning-color); }
        .status-resolved { background: rgba(0, 255, 136, 0.2); color: var(--success-color); }
        .status-closed { background: rgba(128, 128, 128, 0.2); color: #808080; }
        
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
        
        .ticket-message {
            background: var(--darker-bg);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .ticket-customer-info {
            background: var(--darker-bg);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-ticket-alt me-2"></i>Gestion des Tickets de Support
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
                    <a class="nav-link" href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i>Commandes
                    </a>
                    <a class="nav-link" href="services.php">
                        <i class="fas fa-cogs me-2"></i>Services
                    </a>
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags me-2"></i>Catégories
                    </a>
                    <a class="nav-link active" href="tickets.php">
                        <i class="fas fa-ticket-alt me-2"></i>Tickets
                    </a>
                </nav>
            </div>
            
            <!-- Filtres -->
            <div class="filters-card">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="Ouvert" <?php echo ($statusFilter === 'Ouvert') ? 'selected' : ''; ?>>Ouvert</option>
                            <option value="En cours" <?php echo ($statusFilter === 'En cours') ? 'selected' : ''; ?>>En cours</option>
                            <option value="Résolu" <?php echo ($statusFilter === 'Résolu') ? 'selected' : ''; ?>>Résolu</option>
                            <option value="Fermé" <?php echo ($statusFilter === 'Fermé') ? 'selected' : ''; ?>>Fermé</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Priorité</label>
                        <select name="priority" class="form-select">
                            <option value="">Toutes les priorités</option>
                            <option value="Urgente" <?php echo ($priorityFilter === 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                            <option value="Élevée" <?php echo ($priorityFilter === 'Élevée') ? 'selected' : ''; ?>>Élevée</option>
                            <option value="Normale" <?php echo ($priorityFilter === 'Normale') ? 'selected' : ''; ?>>Normale</option>
                            <option value="Faible" <?php echo ($priorityFilter === 'Faible') ? 'selected' : ''; ?>>Faible</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="N° ticket, nom client, email, sujet..." 
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
            
            <!-- Liste des tickets -->
            <div class="row">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="col-12">
                        <div class="ticket-card">
                            <div class="ticket-header">
                                <div>
                                    <div class="ticket-number"><?php echo htmlspecialchars($ticket['ticket_number']); ?></div>
                                    <div class="ticket-date">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo formatDateFrench($ticket['created_at']); ?>
                                        <span class="ms-3">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo getTimeAgo($ticket['created_at']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center gap-2">
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
                                    
                                    <?php
                                    $statusClass = '';
                                    switch ($ticket['status']) {
                                        case 'Ouvert': $statusClass = 'status-open'; break;
                                        case 'En cours': $statusClass = 'status-processing'; break;
                                        case 'Résolu': $statusClass = 'status-resolved'; break;
                                        case 'Fermé': $statusClass = 'status-closed'; break;
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($ticket['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Informations client -->
                            <div class="ticket-customer-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Client:</strong> <?php echo htmlspecialchars($ticket['customer_name']); ?>
                                        <br>
                                        <strong>Email:</strong> <?php echo htmlspecialchars($ticket['customer_email']); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Sujet:</strong> <?php echo htmlspecialchars($ticket['subject']); ?>
                                        <?php if ($ticket['order_number']): ?>
                                            <br>
                                            <strong>Commande:</strong> 
                                            <a href="order-details.php?id=<?php echo $ticket['order_id']; ?>" class="text-primary">
                                                <?php echo htmlspecialchars($ticket['order_number']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Message du client -->
                            <div class="ticket-message">
                                <strong>Message du client:</strong><br>
                                <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                            </div>
                            
                            <!-- Réponse admin -->
                            <?php if ($ticket['admin_response']): ?>
                                <div class="ticket-message">
                                    <strong>Réponse admin:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($ticket['admin_response'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <?php if ($ticket['admin_name']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Traité par: <?php echo htmlspecialchars($ticket['admin_name']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editTicket(<?php echo htmlspecialchars(json_encode($ticket)); ?>)">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    
                                    <?php if ($ticket['status'] !== 'Fermé'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="closeTicket(<?php echo $ticket['id']; ?>)">
                                            <i class="fas fa-check"></i> Fermer
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="mailto:<?php echo htmlspecialchars($ticket['customer_email']); ?>?subject=Re: <?php echo urlencode($ticket['subject']); ?>" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-envelope"></i> Répondre
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($tickets)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun ticket trouvé</h4>
                    <p class="text-muted">Aucun ticket ne correspond aux critères de recherche.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Modification de ticket -->
    <div class="modal fade" id="editTicketModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Modifier le Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_ticket">
                        <input type="hidden" name="ticket_id" id="editTicketId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Statut</label>
                                    <select name="status" class="form-select" id="editTicketStatus" required>
                                        <option value="Ouvert">Ouvert</option>
                                        <option value="En cours">En cours</option>
                                        <option value="Résolu">Résolu</option>
                                        <option value="Fermé">Fermé</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Priorité</label>
                                    <select name="priority" class="form-select" id="editTicketPriority" required>
                                        <option value="Faible">Faible</option>
                                        <option value="Normale">Normale</option>
                                        <option value="Élevée">Élevée</option>
                                        <option value="Urgente">Urgente</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Réponse admin</label>
                            <textarea name="admin_response" class="form-control" id="editTicketResponse" rows="5" 
                                      placeholder="Ajoutez votre réponse ou note..."></textarea>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        function editTicket(ticket) {
            document.getElementById('editTicketId').value = ticket.id;
            document.getElementById('editTicketStatus').value = ticket.status;
            document.getElementById('editTicketPriority').value = ticket.priority;
            document.getElementById('editTicketResponse').value = ticket.admin_response || '';
            new bootstrap.Modal(document.getElementById('editTicketModal')).show();
        }
        
        function closeTicket(ticketId) {
            if (confirm('Êtes-vous sûr de vouloir fermer ce ticket ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="close_ticket">
                    <input type="hidden" name="ticket_id" value="${ticketId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>