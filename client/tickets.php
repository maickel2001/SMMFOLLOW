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

$success = '';
$error = '';

// Traitement de la création d'un ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = cleanInput($_POST['subject'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'Normale';
    $orderId = $_POST['order_id'] ?? null;
    
    if (empty($subject) || empty($message)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            $ticketId = createSupportTicket(
                $currentUser['email'],
                $currentUser['first_name'] . ' ' . $currentUser['last_name'],
                $subject,
                $message,
                $orderId,
                $currentUser['id']
            );
            
            if ($ticketId) {
                $success = 'Ticket de support créé avec succès ! Notre équipe vous répondra dans les plus brefs délais.';
                
                // Envoyer une notification email
                $emailSubject = "Nouveau ticket de support - $subject";
                $emailMessage = "
                    <h2>Nouveau ticket de support créé</h2>
                    <p>Bonjour,</p>
                    <p>Votre ticket de support a été créé avec succès.</p>
                    <p><strong>Sujet :</strong> $subject</p>
                    <p><strong>Priorité :</strong> $priority</p>
                    <p><strong>Message :</strong> $message</p>
                    <p>Notre équipe support traitera votre demande dans les plus brefs délais.</p>
                    <p>Cordialement,<br>L'équipe SMM Pro</p>
                ";
                
                sendEmailNotification($currentUser['email'], $emailSubject, $emailMessage);
            } else {
                $error = 'Erreur lors de la création du ticket.';
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de la création du ticket : ' . $e->getMessage();
        }
    }
}

// Récupération des tickets de l'utilisateur
try {
    $tickets = getSupportTickets(null, null, $currentUser['id']);
} catch (Exception $e) {
    $tickets = [];
}

// Récupération des commandes de l'utilisateur pour le formulaire
try {
    $userOrders = getUserOrders($currentUser['id']);
} catch (Exception $e) {
    $userOrders = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Client - SMM Pro</title>
    
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
        
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
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
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-open { background: rgba(0, 170, 255, 0.2); color: var(--info-color); }
        .status-processing { background: rgba(255, 170, 0, 0.2); color: var(--warning-color); }
        .status-resolved { background: rgba(0, 255, 136, 0.2); color: var(--success-color); }
        .status-closed { background: rgba(255, 68, 68, 0.2); color: var(--danger-color); }
        
        .ticket-item {
            background: var(--darker-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .ticket-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .ticket-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }
        
        .ticket-meta {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .ticket-message {
            color: var(--text-secondary);
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .ticket-response {
            background: rgba(0, 255, 136, 0.1);
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            margin-top: 15px;
            border-radius: 0 5px 5px 0;
        }
        
        .response-header {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
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
                    <i class="fas fa-ticket-alt me-2"></i>Support Client
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
                    <a class="nav-link" href="commandes.php">
                        <i class="fas fa-shopping-cart me-2"></i>Mes Commandes
                    </a>
                    <a class="nav-link" href="nouvelle-commande.php">
                        <i class="fas fa-plus me-2"></i>Nouvelle Commande
                    </a>
                    <a class="nav-link active" href="tickets.php">
                        <i class="fas fa-ticket-alt me-2"></i>Support
                    </a>
                    <a class="nav-link" href="profil.php">
                        <i class="fas fa-user-cog me-2"></i>Mon Profil
                    </a>
                </nav>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Formulaire de création de ticket -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-plus me-2"></i>Nouveau Ticket
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="ticketForm">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Sujet *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           placeholder="Décrivez brièvement votre problème" required 
                                           value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              placeholder="Décrivez votre problème en détail..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priorité</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="Faible">Faible</option>
                                        <option value="Normale" selected>Normale</option>
                                        <option value="Élevée">Élevée</option>
                                        <option value="Urgente">Urgente</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="order_id" class="form-label">Commande concernée (optionnel)</label>
                                    <select class="form-select" id="order_id" name="order_id">
                                        <option value="">Aucune commande spécifique</option>
                                        <?php foreach ($userOrders as $order): ?>
                                            <option value="<?php echo $order['id']; ?>">
                                                <?php echo htmlspecialchars($order['order_number']); ?> - 
                                                <?php echo htmlspecialchars($order['service_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Créer le Ticket
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des tickets -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-list me-2"></i>Mes Tickets de Support
                            </h5>
                            <span class="badge bg-primary"><?php echo count($tickets); ?> ticket(s)</span>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($tickets)): ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <div class="ticket-item">
                                        <div class="ticket-header">
                                            <div>
                                                <div class="ticket-title"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                                <div class="ticket-meta">
                                                    <span class="priority-badge priority-<?php echo strtolower($ticket['priority']); ?>">
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
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="ticket-message">
                                            <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                                        </div>
                                        
                                        <?php if ($ticket['admin_response']): ?>
                                            <div class="ticket-response">
                                                <div class="response-header">
                                                    <i class="fas fa-reply me-2"></i>Réponse de l'équipe support
                                                </div>
                                                <div class="response-message">
                                                    <?php echo nl2br(htmlspecialchars($ticket['admin_response'])); ?>
                                                </div>
                                                <small class="text-muted mt-2 d-block">
                                                    Répondu le <?php echo date('d/m/Y H:i', strtotime($ticket['updated_at'])); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($ticket['status'] === 'Ouvert'): ?>
                                            <div class="mt-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Votre ticket est en cours de traitement par notre équipe support.
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-ticket-alt"></i>
                                    <h4 class="text-muted">Aucun ticket</h4>
                                    <p class="text-muted">Vous n'avez pas encore créé de ticket de support.</p>
                                    <p class="text-muted">Utilisez le formulaire à gauche pour créer votre premier ticket.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informations de contact -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-info-circle me-2"></i>Besoin d'aide immédiate ?
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <i class="fab fa-whatsapp fa-2x text-success mb-2"></i>
                                        <h6>WhatsApp</h6>
                                        <p class="text-muted">+225 0123456789</p>
                                        <small class="text-muted">Réponse immédiate</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                        <h6>Email</h6>
                                        <p class="text-muted">support@smmpro.com</p>
                                        <small class="text-muted">Réponse sous 24h</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                        <h6>Disponibilité</h6>
                                        <p class="text-muted">24h/24 - 7j/7</p>
                                        <small class="text-muted">Support permanent</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Validation du formulaire
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!subject) {
                e.preventDefault();
                alert('Veuillez saisir un sujet pour votre ticket.');
                return false;
            }
            
            if (!message) {
                e.preventDefault();
                alert('Veuillez saisir un message détaillant votre problème.');
                return false;
            }
            
            if (message.length < 10) {
                e.preventDefault();
                alert('Veuillez saisir un message plus détaillé (au moins 10 caractères).');
                return false;
            }
        });
        
        // Auto-resize du textarea
        document.getElementById('message').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    </script>
</body>
</html>