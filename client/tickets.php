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

// Traitement de la création d'un ticket
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
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
                
                // Réinitialiser le formulaire
                $_POST = array();
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
            background: linear-gradient(135deg, var(--accent-warning) 0%, var(--accent-danger) 100%);
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
        
        /* Messages d'alerte */
        .alert {
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: none;
            font-weight: 600;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        /* Formulaire de Création de Ticket Minimaliste */
        .ticket-form-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-xl);
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-subtle);
        }
        
        .ticket-form-section h5 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
        }
        
        .ticket-form-section h5 i {
            color: var(--accent-warning);
            font-size: 1.25rem;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            background: var(--bg-secondary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-medium);
            padding: 12px 16px;
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-warning);
            box-shadow: 0 0 0 3px rgba(255, 149, 0, 0.1);
            background: var(--bg-primary);
        }
        
        .form-control::placeholder {
            color: var(--text-tertiary);
            opacity: 0.7;
        }
        
        .btn-create-ticket {
            background: var(--accent-warning);
            border: none;
            border-radius: var(--radius-medium);
            padding: 12px 24px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            box-shadow: var(--shadow-subtle);
        }
        
        .btn-create-ticket:hover {
            background: #e6850e;
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
        }
        
        /* Liste des Tickets Minimaliste */
        .tickets-list-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow-subtle);
        }
        
        .tickets-list-section h5 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
        }
        
        .tickets-list-section h5 i {
            color: var(--accent-primary);
            font-size: 1.25rem;
        }
        
        .ticket-item {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 24px;
            margin-bottom: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            box-shadow: var(--shadow-subtle);
        }
        
        .ticket-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            border-color: var(--accent-primary);
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .ticket-subject {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .ticket-meta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .ticket-priority {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
        }
        
        .priority-urgent {
            background: var(--accent-danger);
        }
        
        .priority-high {
            background: #ff6b6b;
        }
        
        .priority-normal {
            background: var(--accent-primary);
        }
        
        .priority-low {
            background: var(--accent-success);
        }
        
        .ticket-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
        }
        
        .status-open {
            background: var(--accent-primary);
        }
        
        .status-processing {
            background: var(--accent-warning);
        }
        
        .status-resolved {
            background: var(--accent-success);
        }
        
        .status-closed {
            background: var(--text-tertiary);
        }
        
        .ticket-message {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 16px;
            padding: 16px;
            background: rgba(0, 122, 255, 0.02);
            border-radius: var(--radius-small);
            border-left: 3px solid var(--accent-primary);
        }
        
        .ticket-response {
            background: rgba(52, 199, 89, 0.08);
            border-left: 3px solid var(--accent-success);
            padding: 16px;
            margin-top: 16px;
            border-radius: 0 var(--radius-small) var(--radius-small) 0;
        }
        
        .response-header {
            font-weight: 600;
            color: var(--accent-success);
            margin-bottom: 8px;
            font-size: 0.875rem;
        }
        
        .ticket-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .ticket-date {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .ticket-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-ticket-action {
            padding: 6px 12px;
            border-radius: var(--radius-small);
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-view-ticket {
            background: var(--accent-primary);
            color: white;
        }
        
        .btn-view-ticket:hover {
            background: #0056cc;
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }
        
        .btn-reply-ticket {
            background: var(--accent-success);
            color: white;
        }
        
        .btn-reply-ticket:hover {
            background: #28a745;
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }
        
        /* Contact d'Urgence Minimaliste */
        .emergency-contact {
            background: linear-gradient(135deg, var(--accent-warning) 0%, var(--accent-danger) 100%);
            border-radius: var(--radius-large);
            padding: 32px;
            margin-top: 32px;
            text-align: center;
            color: white;
            box-shadow: var(--shadow-medium);
        }
        
        .emergency-contact h6 {
            font-weight: 700;
            margin-bottom: 16px;
            font-size: 1.25rem;
        }
        
        .emergency-contact p {
            margin-bottom: 24px;
            opacity: 0.9;
        }
        
        .contact-methods {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .contact-method {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-medium);
            font-weight: 600;
            backdrop-filter: blur(10px);
        }
        
        /* États Vides Minimalistes */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.4;
            color: var(--text-tertiary);
        }
        
        .empty-state h5 {
            color: var(--text-secondary);
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .empty-state p {
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 1.1rem;
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
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header {
                padding: 32px 24px;
            }
            
            .header-title {
                font-size: 2rem;
            }
            
            .ticket-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .ticket-meta {
                justify-content: center;
            }
            
            .ticket-footer {
                flex-direction: column;
                text-align: center;
            }
            
            .contact-methods {
                flex-direction: column;
                align-items: center;
            }
            
            .ticket-form-section,
            .tickets-list-section {
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
                        <i class="fas fa-headset"></i>
                    </div>
                    <h1 class="header-title">Support Client</h1>
                    <p class="header-subtitle">Nous sommes là pour vous aider 24h/24 et 7j/7</p>
                </div>
            </div>
            
            <!-- Navigation Client -->
            <div class="client-nav animate-fade-in">
                <nav class="nav nav-pills justify-content-center">
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
            
            <!-- Messages d'alerte -->
            <?php if ($success): ?>
                <div class="alert alert-success animate-fade-in">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger animate-fade-in">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulaire de création de ticket -->
            <div class="ticket-form-section animate-fade-in">
                <h5>
                    <i class="fas fa-plus-circle"></i>Créer un Nouveau Ticket
                </h5>
                
                <form method="POST" id="ticketForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subject" class="form-label">Sujet *</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       placeholder="Décrivez brièvement votre problème" required 
                                       value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority" class="form-label">Priorité</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="Faible" <?php echo ($_POST['priority'] ?? 'Normale') === 'Faible' ? 'selected' : ''; ?>>Faible</option>
                                    <option value="Normale" <?php echo ($_POST['priority'] ?? 'Normale') === 'Normale' ? 'selected' : ''; ?>>Normale</option>
                                    <option value="Élevée" <?php echo ($_POST['priority'] ?? 'Normale') === 'Élevée' ? 'selected' : ''; ?>>Élevée</option>
                                    <option value="Urgente" <?php echo ($_POST['priority'] ?? 'Normale') === 'Urgente' ? 'selected' : ''; ?>>Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" 
                                  placeholder="Décrivez votre problème en détail..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="order_id" class="form-label">Commande concernée (optionnel)</label>
                        <select class="form-select" id="order_id" name="order_id">
                            <option value="">Aucune commande spécifique</option>
                            <?php foreach ($userOrders as $order): ?>
                                <option value="<?php echo $order['id']; ?>" <?php echo ($_POST['order_id'] ?? '') == $order['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($order['order_number']); ?> - 
                                    <?php echo htmlspecialchars($order['service_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn-create-ticket">
                            <i class="fas fa-paper-plane me-2"></i>Créer le Ticket
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Liste des tickets -->
            <div class="tickets-list-section animate-fade-in">
                <h5>
                    <i class="fas fa-list"></i>Mes Tickets de Support
                </h5>
                
                <?php if (!empty($tickets)): ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket-item">
                            <div class="ticket-header">
                                <div>
                                    <div class="ticket-subject"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                    <div class="ticket-meta">
                                        <?php
                                        $priorityClass = '';
                                        switch ($ticket['priority']) {
                                            case 'Urgente': $priorityClass = 'priority-urgent'; break;
                                            case 'Élevée': $priorityClass = 'priority-high'; break;
                                            case 'Normale': $priorityClass = 'priority-normal'; break;
                                            case 'Faible': $priorityClass = 'priority-low'; break;
                                        }
                                        ?>
                                        <span class="ticket-priority <?php echo $priorityClass; ?>">
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
                                        <span class="ticket-status <?php echo $statusClass; ?>">
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
                        <h5>Aucun ticket</h5>
                        <p>Vous n'avez pas encore créé de ticket de support.</p>
                        <p>Utilisez le formulaire ci-dessus pour créer votre premier ticket.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Contact d'urgence -->
            <div class="emergency-contact animate-fade-in">
                <h6>
                    <i class="fas fa-exclamation-triangle me-2"></i>Besoin d'aide immédiate ?
                </h6>
                <p>Notre équipe support est disponible pour vous aider rapidement</p>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <i class="fas fa-envelope"></i>
                        <span>support@smmpro.com</span>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-phone"></i>
                        <span>+225 0123456789</span>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-comments"></i>
                        <span>Chat en ligne</span>
                    </div>
                </div>
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
        
        // Auto-resize du textarea
        document.getElementById('message').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
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
    </script>
</body>
</html>