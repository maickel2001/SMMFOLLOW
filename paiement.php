<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$orderId) {
    redirect('commander.php');
}

// Récupération des détails de la commande
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name, s.platform, s.type, c.name as category_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        redirect('commander.php');
    }
} catch (Exception $e) {
    redirect('commander.php');
}

// Traitement de l'upload de la preuve de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = uploadImage($_FILES['payment_proof']);
        
        if ($uploadedFile) {
            try {
                $stmt = $pdo->prepare("UPDATE orders SET payment_proof = ?, status = 'En attente' WHERE id = ?");
                if ($stmt->execute([$uploadedFile, $orderId])) {
                    $success = 'Preuve de paiement uploadée avec succès ! Votre commande sera traitée dans les plus brefs délais.';
                    $order['payment_proof'] = $uploadedFile;
                    $order['status'] = 'En attente';
                } else {
                    $error = 'Erreur lors de la mise à jour de la commande.';
                }
            } catch (Exception $e) {
                $error = 'Erreur de base de données.';
            }
        } else {
            $error = 'Erreur lors de l\'upload du fichier. Vérifiez le format (JPG/PNG).';
        }
    } else {
        $error = 'Veuillez sélectionner un fichier valide.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - SMM Pro</title>
    
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
            --gradient-secondary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-warning: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-success: linear-gradient(135deg, #28a745, #20c997);
        }
        
        body {
            background: var(--dark-bg);
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        
        /* Navigation Améliorée */
        .navbar {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color) !important;
            text-decoration: none;
        }
        
        .navbar-nav .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin: 0 0.25rem;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
            background: rgba(0, 255, 136, 0.1);
            transform: translateY(-2px);
        }
        
        /* Header Principal */
        .main-header {
            background: var(--gradient-success);
            padding: 80px 0 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(40, 167, 69, 0.2);
        }
        
        .main-header::before {
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
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .header-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        /* Messages d'alerte */
        .alert {
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            border: none;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 2px solid rgba(40, 167, 69, 0.3);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 2px solid rgba(220, 53, 69, 0.3);
        }
        
        /* Résumé de la commande */
        .order-summary {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .order-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-success);
        }
        
        .order-summary h4 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .order-summary h4 i {
            color: var(--success-color);
            font-size: 2rem;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .detail-item {
            background: var(--darker-bg);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .detail-item:hover {
            transform: translateY(-3px);
            border-color: var(--primary-color);
            box-shadow: 0 8px 25px rgba(0, 255, 136, 0.2);
        }
        
        .detail-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .detail-value {
            color: var(--text-primary);
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .detail-value.order-number {
            color: var(--primary-color);
            font-size: 1.4rem;
        }
        
        .detail-value.price {
            color: var(--success-color);
            font-size: 1.6rem;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: var(--gradient-warning);
            color: var(--dark-bg);
        }
        
        /* Instructions de paiement */
        .payment-instructions {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .payment-instructions h4 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.8rem;
        }
        
        .payment-instructions h4 i {
            color: var(--info-color);
            font-size: 2rem;
        }
        
        .payment-method-card {
            background: var(--darker-bg);
            border-radius: 20px;
            padding: 30px;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .payment-method-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-info);
        }
        
        .payment-method-card:hover {
            transform: translateY(-5px);
            border-color: var(--info-color);
            box-shadow: 0 15px 35px rgba(23, 162, 184, 0.3);
        }
        
        .payment-method-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .payment-method-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-info);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--dark-bg);
        }
        
        .payment-method-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }
        
        .payment-steps {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .payment-steps li {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--text-secondary);
            font-size: 1rem;
        }
        
        .payment-steps li:last-child {
            border-bottom: none;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--dark-bg);
            flex-shrink: 0;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-highlight {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .warning-alert {
            background: rgba(255, 193, 7, 0.2);
            border: 2px solid rgba(255, 193, 7, 0.3);
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
            color: #ffc107;
            font-weight: 600;
        }
        
        /* Upload de preuve */
        .proof-upload {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .proof-upload h4 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.8rem;
        }
        
        .proof-upload h4 i {
            color: var(--warning-color);
            font-size: 2rem;
        }
        
        .upload-area {
            background: var(--darker-bg);
            border: 2px dashed var(--border-color);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: var(--primary-color);
            background: rgba(0, 255, 136, 0.05);
        }
        
        .upload-area.dragover {
            border-color: var(--primary-color);
            background: rgba(0, 255, 136, 0.1);
            transform: scale(1.02);
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .upload-text {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .upload-hint {
            color: var(--text-secondary);
            font-size: 0.9rem;
            opacity: 0.7;
        }
        
        .file-input {
            display: none;
        }
        
        .upload-btn {
            background: var(--gradient-primary);
            border: none;
            border-radius: 15px;
            padding: 15px 30px;
            color: var(--dark-bg);
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .upload-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 255, 136, 0.4);
            color: var(--dark-bg);
        }
        
        .file-preview {
            margin-top: 20px;
            padding: 20px;
            background: rgba(0, 255, 136, 0.1);
            border-radius: 15px;
            border: 1px solid var(--primary-color);
            display: none;
        }
        
        .file-preview img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        /* Actions */
        .action-buttons {
            text-align: center;
            margin-top: 40px;
        }
        
        .btn-action {
            padding: 15px 30px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0 10px;
            display: inline-block;
        }
        
        .btn-home {
            background: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
        }
        
        .btn-home:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-3px);
            text-decoration: none;
        }
        
        .btn-new-order {
            background: var(--gradient-primary);
            border: none;
            color: var(--dark-bg);
        }
        
        .btn-new-order:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 255, 136, 0.4);
            color: var(--dark-bg);
            text-decoration: none;
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
        .animate-fade-in:nth-child(4) { animation-delay: 0.4s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header {
                padding: 60px 0 40px;
            }
            
            .header-title {
                font-size: 2.5rem;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .payment-method-card {
                padding: 20px;
            }
            
            .payment-steps li {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .action-buttons .btn-action {
                display: block;
                margin: 10px auto;
                max-width: 250px;
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
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-rocket me-2"></i>SMM Pro
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="commander.php">Commander</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Principal -->
    <div class="main-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <h1 class="header-title">Paiement Sécurisé</h1>
            <p class="header-subtitle">Finalisez votre commande en toute sécurité</p>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="container" style="padding-top: 40px;">
        <!-- Messages d'alerte -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success animate-fade-in">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger animate-fade-in">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Résumé de la commande -->
        <div class="order-summary animate-fade-in">
            <h4>
                <i class="fas fa-check-circle"></i>Commande Confirmée
            </h4>
            
            <div class="order-details">
                <div class="detail-item">
                    <div class="detail-label">Numéro de commande</div>
                    <div class="detail-value order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Service</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order['service_name']); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($order['category_name'] ?? ''); ?></small>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Quantité</div>
                    <div class="detail-value"><?php echo number_format($order['quantity'], 0, ',', ' '); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Prix total</div>
                    <div class="detail-value price"><?php echo formatPrice($order['total_price']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Méthode de paiement</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order['payment_method']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Statut</div>
                    <div class="detail-value">
                        <span class="status-badge">
                            <?php echo $order['payment_proof'] ? 'En attente' : 'En attente de paiement'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructions de paiement -->
        <div class="payment-instructions animate-fade-in">
            <h4>
                <i class="fas fa-mobile-alt"></i>Instructions de Paiement
            </h4>
            
            <div class="payment-method-card">
                <div class="payment-method-header">
                    <div class="payment-method-icon">
                        <i class="fas fa-<?php echo $order['payment_method'] === 'MTN Money' ? 'mobile' : 'credit-card'; ?>"></i>
                    </div>
                    <h5 class="payment-method-title">
                        Paiement via <?php echo htmlspecialchars($order['payment_method']); ?>
                    </h5>
                </div>
                
                <ol class="payment-steps">
                    <?php if ($order['payment_method'] === 'MTN Money'): ?>
                        <li>
                            <div class="step-number">1</div>
                            <div class="step-content">Composez <span class="step-highlight">*126#</span> sur votre téléphone</div>
                        </li>
                        <li>
                            <div class="step-number">2</div>
                            <div class="step-content">Sélectionnez <span class="step-highlight">"Envoyer de l'argent"</span></div>
                        </li>
                        <li>
                            <div class="step-number">3</div>
                            <div class="step-content">Entrez le numéro: <span class="step-highlight">0123456789</span></div>
                        </li>
                        <li>
                            <div class="step-number">4</div>
                            <div class="step-content">Entrez le montant: <span class="step-highlight"><?php echo formatPrice($order['total_price']); ?></span></div>
                        </li>
                        <li>
                            <div class="step-number">5</div>
                            <div class="step-content">Confirmez la transaction</div>
                        </li>
                        <li>
                            <div class="step-number">6</div>
                            <div class="step-content">Prenez une capture d'écran de la confirmation</div>
                        </li>
                    <?php else: ?>
                        <li>
                            <div class="step-number">1</div>
                            <div class="step-content">Composez <span class="step-highlight">*155#</span> sur votre téléphone</div>
                        </li>
                        <li>
                            <div class="step-number">2</div>
                            <div class="step-content">Sélectionnez <span class="step-highlight">"Transfert d'argent"</span></div>
                        </li>
                        <li>
                            <div class="step-number">3</div>
                            <div class="step-content">Entrez le numéro: <span class="step-highlight">0123456789</span></div>
                        </li>
                        <li>
                            <div class="step-number">4</div>
                            <div class="step-content">Entrez le montant: <span class="step-highlight"><?php echo formatPrice($order['total_price']); ?></span></div>
                        </li>
                        <li>
                            <div class="step-number">5</div>
                            <div class="step-content">Confirmez la transaction</div>
                        </li>
                        <li>
                            <div class="step-number">6</div>
                            <div class="step-content">Prenez une capture d'écran de la confirmation</div>
                        </li>
                    <?php endif; ?>
                </ol>
                
                <div class="warning-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Conservez le reçu de transaction et prenez une capture d'écran de la confirmation de paiement.
                </div>
            </div>
        </div>
        
        <!-- Upload de la preuve de paiement -->
        <div class="proof-upload animate-fade-in">
            <h4>
                <i class="fas fa-upload"></i>Preuve de Paiement
            </h4>
            
            <?php if ($order['payment_proof']): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Preuve de paiement reçue !</strong> Votre commande sera traitée dans les plus brefs délais.
                </div>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">Cliquez ou glissez-déposez votre capture d'écran ici</div>
                        <div class="upload-hint">Formats acceptés: JPG, PNG. Taille max: 5MB</div>
                        <input type="file" class="file-input" name="payment_proof" id="paymentProof" 
                               accept=".jpg,.jpeg,.png" required>
                        <button type="button" class="upload-btn" onclick="document.getElementById('paymentProof').click()">
                            <i class="fas fa-folder-open me-2"></i>Sélectionner un fichier
                        </button>
                    </div>
                    
                    <div class="file-preview" id="filePreview">
                        <img id="previewImage" src="" alt="Aperçu">
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="upload-btn">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer la Preuve
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Actions -->
        <div class="action-buttons animate-fade-in">
            <a href="index.php" class="btn-action btn-home">
                <i class="fas fa-home me-2"></i>Retour à l'accueil
            </a>
            <a href="commander.php" class="btn-action btn-new-order">
                <i class="fas fa-plus me-2"></i>Nouvelle commande
            </a>
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
        
        // Gestion de l'upload de fichier
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('paymentProof');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');
        
        if (uploadArea && fileInput) {
            // Clic sur la zone d'upload
            uploadArea.addEventListener('click', () => fileInput.click());
            
            // Drag and drop
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect(files[0]);
                }
            });
            
            // Sélection de fichier
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                }
            });
        }
        
        function handleFileSelect(file) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    filePreview.style.display = 'block';
                    uploadArea.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                alert('Veuillez sélectionner un fichier image valide (JPG, PNG).');
            }
        }
        
        // Validation du formulaire
        document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
            const file = fileInput.files[0];
            if (!file) {
                e.preventDefault();
                alert('Veuillez sélectionner un fichier.');
                return false;
            }
            
            if (!file.type.startsWith('image/')) {
                e.preventDefault();
                alert('Veuillez sélectionner un fichier image valide.');
                return false;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('Le fichier est trop volumineux. Taille maximum: 5MB.');
                return false;
            }
        });
    </script>
</body>
</html>