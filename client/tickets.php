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
        .page-header {
            background: var(--gradient-warning);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(240, 147, 251, 0.2);
        }
        
        .page-header::before {
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
        
        .header-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .header-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .header-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .header-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 0;
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
        
        /* Formulaire de création de ticket */
        .ticket-form-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 35px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .ticket-form-section h5 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.3rem;
        }
        
        .ticket-form-section h5 i {
            color: var(--warning-color);
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        
        .form-control, .form-select {
            background: var(--darker-bg);
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 15px 20px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--warning-color);
            box-shadow: 0 0 0 3px rgba(240, 147, 251, 0.2);
            background: var(--darker-bg);
        }
        
        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }
        
        .btn-create-ticket {
            background: var(--gradient-warning);
            border: none;
            border-radius: 15px;
            padding: 15px 30px;
            color: var(--dark-bg);
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn-create-ticket::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s ease;
        }
        
        .btn-create-ticket:hover::before {
            left: 0;
        }
        
        .btn-create-ticket:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(240, 147, 251, 0.4);
        }
        
        /* Liste des tickets */
        .tickets-list-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 35px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .tickets-list-section h5 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.3rem;
        }
        
        .tickets-list-section h5 i {
            color: var(--info-color);
            font-size: 1.5rem;
        }
        
        .ticket-item {
            background: var(--darker-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .ticket-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--gradient-info);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .ticket-item:hover::before {
            transform: scaleY(1);
        }
        
        .ticket-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 25px rgba(0, 255, 136, 0.1);
            border-color: var(--info-color);
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .ticket-subject {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }
        
        .ticket-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .ticket-priority {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .priority-urgent {
            background: var(--gradient-warning);
            color: var(--dark-bg);
        }
        
        .priority-high {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: #fff;
        }
        
        .priority-normal {
            background: var(--gradient-info);
            color: #fff;
        }
        
        .priority-low {
            background: var(--gradient-primary);
            color: var(--dark-bg);
        }
        
        .ticket-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-open {
            background: var(--gradient-info);
            color: #fff;
        }
        
        .status-processing {
            background: var(--gradient-warning);
            color: var(--dark-bg);
        }
        
        .status-resolved {
            background: var(--gradient-primary);
            color: var(--dark-bg);
        }
        
        .status-closed {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: #fff;
        }
        
        .ticket-message {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
            border-left: 3px solid var(--info-color);
        }
        
        .ticket-response {
            background: rgba(0, 255, 136, 0.1);
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            margin-top: 15px;
            border-radius: 0 10px 10px 0;
        }
        
        .response-header {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .ticket-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .ticket-date {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .ticket-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-ticket-action {
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-view-ticket {
            background: var(--gradient-info);
            color: #fff;
        }
        
        .btn-view-ticket:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
            color: #fff;
        }
        
        .btn-reply-ticket {
            background: var(--gradient-primary);
            color: var(--dark-bg);
        }
        
        .btn-reply-ticket:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.4);
            color: var(--dark-bg);
        }
        
        /* Contact d'urgence */
        .emergency-contact {
            background: var(--gradient-warning);
            border-radius: 20px;
            padding: 25px;
            margin-top: 30px;
            text-align: center;
            color: var(--dark-bg);
        }
        
        .emergency-contact h6 {
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .emergency-contact p {
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .contact-methods {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .contact-method {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            font-weight: 600;
        }
        
        /* États vides */
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
        
        /* Animations d'entrée */
        .animate-fade-in {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
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
            .page-header {
                padding: 30px 20px;
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
        }
        
        /* Scrollbar personnalisée */
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
                    <i class="fas fa-ticket-alt me-3"></i>Support Client
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
            
            <!-- Header de la page -->
            <div class="page-header animate-fade-in">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h2 class="header-title">Support Client</h2>
                    <p class="header-subtitle">Nous sommes là pour vous aider 24h/24 et 7j/7</p>
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