<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de la connexion utilisateur
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit();
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: ../connexion.php');
    exit();
}

// Récupération de l'ID de la commande
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$orderId) {
    header('Location: commandes.php');
    exit();
}

// Récupération des détails de la commande
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name, s.description as service_description, 
               c.name as category_name, c.icon as category_icon
        FROM orders o
        LEFT JOIN services s ON o.service_id = s.id
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $currentUser['id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: commandes.php');
        exit();
    }
    
    // Récupération de l'historique des statuts
    $stmt = $stmt = $pdo->prepare("
        SELECT * FROM order_status_history 
        WHERE order_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$orderId]);
    $statusHistory = $stmt->fetchAll();
    
    // Récupération des messages de support liés
    $stmt = $pdo->prepare("
        SELECT * FROM support_tickets 
        WHERE order_id = ? AND user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$orderId, $currentUser['id']]);
    $supportTickets = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Erreur lors du chargement de la commande.';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Commande #<?php echo $orderId; ?> - SMM Pro</title>
    <meta name="description" content="Suivez en détail votre commande SMM et son évolution en temps réel.">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f5f5f7;
            --bg-tertiary: #fafafa;
            --bg-dark: #1d1d1f;
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --text-tertiary: #6e6e73;
            --text-light: #ffffff;
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
            overflow-x: hidden;
        }
        
        /* Navigation Minimaliste */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-lighter);
            padding: 16px 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-primary) !important;
            text-decoration: none;
        }
        
        .navbar-brand i {
            color: var(--accent-primary);
        }
        
        .navbar-nav .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 8px 16px;
            margin: 0 4px;
            border-radius: var(--radius-medium);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--accent-primary) !important;
            background: rgba(0, 122, 255, 0.04);
            transform: translateY(-1px);
        }
        
        .navbar-toggler {
            border: none;
            padding: 4px 8px;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        /* Container Principal */
        .client-container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header Principal */
        .main-header {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border-radius: var(--radius-xl);
            padding: 60px 40px;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .main-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(0, 122, 255, 0.03) 50%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2rem;
            color: white;
            box-shadow: var(--shadow-medium);
        }
        
        .header-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 16px;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            line-height: 1.1;
        }
        
        .header-subtitle {
            font-size: 1.125rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Navigation Client */
        .client-nav {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 20px;
            margin-bottom: 40px;
            box-shadow: var(--shadow-subtle);
        }
        
        .nav-pills .nav-link {
            color: var(--text-secondary);
            border-radius: var(--radius-medium);
            padding: 10px 20px;
            margin: 0 8px;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .nav-pills .nav-link:hover {
            color: var(--accent-primary);
            background: rgba(0, 122, 255, 0.04);
        }
        
        .nav-pills .nav-link.active {
            background: var(--accent-primary);
            color: white;
        }
        
        /* Informations Utilisateur */
        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-lighter);
        }
        
        .user-name {
            color: var(--text-primary);
            font-weight: 500;
        }
        
        /* Grille Principale */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        /* Cartes de Contenu */
        .content-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-subtle);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .content-card:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
        }
        
        .content-card h5 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .content-card h5 i {
            color: var(--accent-primary);
        }
        
        /* Statut de la Commande */
        .order-status {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: white;
        }
        
        .status-pending { background: var(--accent-warning); }
        .status-processing { background: var(--accent-primary); }
        .status-completed { background: var(--accent-success); }
        .status-cancelled { background: var(--accent-danger); }
        
        .status-info {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Détails de la Commande */
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .detail-item {
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            padding: 20px;
            border: 1px solid var(--border-lighter);
        }
        
        .detail-label {
            color: var(--text-tertiary);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .detail-value {
            color: var(--text-primary);
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .detail-value.price {
            color: var(--accent-primary);
            font-size: 1.5rem;
        }
        
        /* Historique des Statuts */
        .status-timeline {
            position: relative;
            padding-left: 32px;
        }
        
        .status-timeline::before {
            content: '';
            position: absolute;
            left: 16px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-lighter);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 24px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 8px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--accent-primary);
            border: 3px solid var(--bg-primary);
        }
        
        .timeline-item.completed::before {
            background: var(--accent-success);
        }
        
        .timeline-item.current::before {
            background: var(--accent-warning);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
        }
        
        .timeline-content {
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            padding: 16px;
            border-left: 3px solid var(--accent-primary);
        }
        
        .timeline-status {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .timeline-date {
            color: var(--text-tertiary);
            font-size: 0.875rem;
        }
        
        .timeline-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 8px;
        }
        
        /* Actions Utilisateur */
        .order-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        
        .btn {
            border-radius: var(--radius-medium);
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            padding: 12px 24px;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056cc;
            border-color: #0056cc;
            color: white;
        }
        
        .btn-outline-primary {
            border-color: var(--accent-primary);
            color: var(--accent-primary);
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }
        
        .btn-outline-warning {
            border-color: var(--accent-warning);
            color: var(--accent-warning);
            background: transparent;
        }
        
        .btn-outline-warning:hover {
            background: var(--accent-warning);
            border-color: var(--accent-warning);
            color: white;
        }
        
        /* Tickets de Support */
        .ticket-item {
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            padding: 20px;
            margin-bottom: 16px;
            border-left: 3px solid var(--accent-primary);
            transition: all 0.2s ease;
        }
        
        .ticket-item:hover {
            background: var(--bg-tertiary);
            transform: translateX(4px);
        }
        
        .ticket-subject {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .ticket-message {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 12px;
            line-height: 1.5;
        }
        
        .ticket-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: var(--text-tertiary);
        }
        
        /* Responsive Mobile First */
        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .main-header {
                padding: 40px 24px;
            }
            
            .header-title {
                font-size: 2rem;
            }
            
            .order-details {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .order-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .client-container {
                padding: 20px 16px;
            }
            
            .content-card {
                padding: 24px;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .main-grid {
                grid-template-columns: 1.5fr 1fr;
                gap: 32px;
            }
        }
        
        /* Animations */
        .animate-fade-in {
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-rocket me-2"></i>SMM Pro
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../commander.php">Commander</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../support.php">Support</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="client-container">
        <!-- Header Principal -->
        <div class="main-header animate-fade-in">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h1 class="header-title">Commande #<?php echo $orderId; ?></h1>
                <p class="header-subtitle">Suivez en détail l'évolution de votre commande SMM</p>
            </div>
        </div>
        
        <!-- Navigation Client -->
        <div class="client-nav animate-fade-in">
            <nav class="nav nav-pills justify-content-center">
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
            
            <!-- Informations utilisateur -->
            <div class="user-info">
                <span class="user-name">
                    <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                </a>
            </div>
        </div>
        
        <!-- Grille Principale -->
        <div class="main-grid">
            <!-- Colonne Principale -->
            <div class="main-column">
                <!-- Statut de la Commande -->
                <div class="content-card animate-fade-in">
                    <h5>
                        <i class="fas fa-info-circle"></i>Statut de la Commande
                    </h5>
                    
                    <div class="order-status">
                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                            <?php 
                            $statusLabels = [
                                'pending' => 'En Attente',
                                'processing' => 'En Cours',
                                'completed' => 'Terminée',
                                'cancelled' => 'Annulée'
                            ];
                            echo $statusLabels[$order['status']] ?? $order['status'];
                            ?>
                        </span>
                        <span class="status-info">
                            <?php if ($order['status'] === 'pending'): ?>
                                Votre commande est en attente de traitement
                            <?php elseif ($order['status'] === 'processing'): ?>
                                Votre commande est en cours de traitement
                            <?php elseif ($order['status'] === 'completed'): ?>
                                Votre commande a été livrée avec succès
                            <?php elseif ($order['status'] === 'cancelled'): ?>
                                Votre commande a été annulée
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <!-- Détails de la Commande -->
                    <div class="order-details">
                        <div class="detail-item">
                            <div class="detail-label">Service</div>
                            <div class="detail-value"><?php echo htmlspecialchars($order['service_name']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Catégorie</div>
                            <div class="detail-value">
                                <i class="<?php echo htmlspecialchars($order['category_icon']); ?> me-2"></i>
                                <?php echo htmlspecialchars($order['category_name']); ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Quantité</div>
                            <div class="detail-value"><?php echo number_format($order['quantity']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Lien</div>
                            <div class="detail-value">
                                <a href="<?php echo htmlspecialchars($order['link_url']); ?>" target="_blank" class="text-decoration-none">
                                    <?php echo htmlspecialchars(substr($order['link_url'], 0, 30)) . '...'; ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Prix Total</div>
                            <div class="detail-value price"><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> FCFA</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Date de Commande</div>
                            <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                        </div>
                    </div>
                    
                    <!-- Actions Utilisateur -->
                    <div class="order-actions">
                        <?php if ($order['status'] === 'pending'): ?>
                            <a href="nouvelle-commande.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Nouvelle Commande
                            </a>
                        <?php endif; ?>
                        
                        <a href="tickets.php?order_id=<?php echo $orderId; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-ticket-alt me-2"></i>Support
                        </a>
                        
                        <a href="commandes.php" class="btn btn-outline-warning">
                            <i class="fas fa-arrow-left me-2"></i>Retour aux Commandes
                        </a>
                    </div>
                </div>
                
                <!-- Historique des Statuts -->
                <div class="content-card animate-fade-in">
                    <h5>
                        <i class="fas fa-history"></i>Historique des Statuts
                    </h5>
                    
                    <?php if (!empty($statusHistory)): ?>
                        <div class="status-timeline">
                            <?php foreach ($statusHistory as $index => $status): ?>
                                <div class="timeline-item <?php echo $index === 0 ? 'current' : 'completed'; ?>">
                                    <div class="timeline-content">
                                        <div class="timeline-status">
                                            <?php 
                                            $statusLabels = [
                                                'pending' => 'En Attente',
                                                'processing' => 'En Cours',
                                                'completed' => 'Terminée',
                                                'cancelled' => 'Annulée'
                                            ];
                                            echo $statusLabels[$status['status']] ?? $status['status'];
                                            ?>
                                        </div>
                                        <div class="timeline-date">
                                            <?php echo date('d/m/Y H:i', strtotime($status['created_at'])); ?>
                                        </div>
                                        <?php if ($status['notes']): ?>
                                            <div class="timeline-description">
                                                <?php echo htmlspecialchars($status['notes']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clock fa-2x mb-3"></i>
                            <p>Aucun historique de statut disponible</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar-column">
                <!-- Informations du Service -->
                <div class="content-card animate-fade-in">
                    <h5>
                        <i class="fas fa-cog"></i>Détails du Service
                    </h5>
                    
                    <div class="detail-item">
                        <div class="detail-label">Description</div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($order['service_description']); ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Méthode de Paiement</div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($order['payment_method']); ?>
                        </div>
                    </div>
                    
                    <?php if ($order['payment_proof']): ?>
                        <div class="detail-item">
                            <div class="detail-label">Preuve de Paiement</div>
                            <div class="detail-value">
                                <a href="../uploads/<?php echo htmlspecialchars($order['payment_proof']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-2"></i>Voir
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tickets de Support -->
                <div class="content-card animate-fade-in">
                    <h5>
                        <i class="fas fa-ticket-alt"></i>Tickets de Support
                    </h5>
                    
                    <?php if (!empty($supportTickets)): ?>
                        <?php foreach ($supportTickets as $ticket): ?>
                            <div class="ticket-item">
                                <div class="ticket-subject"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                <div class="ticket-message">
                                    <?php echo htmlspecialchars(substr($ticket['message'], 0, 100)) . '...'; ?>
                                </div>
                                <div class="ticket-meta">
                                    <span><?php echo date('d/m/Y', strtotime($ticket['created_at'])); ?></span>
                                    <span class="badge bg-<?php echo $ticket['status'] === 'open' ? 'warning' : 'success'; ?>">
                                        <?php echo $ticket['status'] === 'open' ? 'Ouvert' : 'Fermé'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="tickets.php?order_id=<?php echo $orderId; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-2"></i>Voir tous les tickets
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-ticket-alt fa-2x mb-3"></i>
                            <p>Aucun ticket de support pour cette commande</p>
                            <a href="tickets.php?order_id=<?php echo $orderId; ?>" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus me-2"></i>Créer un ticket
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Animation des éléments au scroll -->
    <script>
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
        
        // Mise à jour automatique du statut (toutes les 30 secondes)
        setInterval(() => {
            // Ici on pourrait faire un appel AJAX pour vérifier les mises à jour
            console.log('Vérification des mises à jour...');
        }, 30000);
    </script>
</body>
</html>