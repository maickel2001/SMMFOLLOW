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
        
        /* Navigation Minimaliste */
        .navbar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-lighter);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary) !important;
            text-decoration: none;
            letter-spacing: -0.025em;
        }
        
        .navbar-nav .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-small);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin: 0 0.25rem;
            font-size: 0.95rem;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--accent-primary) !important;
            background: rgba(0, 122, 255, 0.04);
        }
        
        /* Header Principal */
        .main-header {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            padding: 120px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header-content {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
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
            font-size: 3rem;
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
        
        /* Messages d'alerte */
        .alert {
            border-radius: var(--radius-medium);
            padding: 20px 24px;
            margin-bottom: 32px;
            border: none;
            font-weight: 500;
            font-size: 0.95rem;
            box-shadow: var(--shadow-subtle);
        }
        
        .alert-success {
            background: rgba(52, 199, 89, 0.08);
            color: var(--accent-success);
            border: 1px solid rgba(52, 199, 89, 0.2);
        }
        
        .alert-danger {
            background: rgba(255, 59, 48, 0.08);
            color: var(--accent-danger);
            border: 1px solid rgba(255, 59, 48, 0.2);
        }
        
        /* Conteneur principal */
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        /* Résumé de la commande */
        .order-summary {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-xl);
            padding: 40px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-subtle);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .order-summary:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
        }
        
        .order-summary h4 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 32px;
            text-align: center;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .order-summary h4 i {
            color: var(--accent-success);
            font-size: 1.5rem;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .detail-item {
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            padding: 24px;
            border: 1px solid var(--border-lighter);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .detail-item:hover {
            background: var(--bg-tertiary);
            border-color: var(--border-light);
        }
        
        .detail-label {
            color: var(--text-tertiary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .detail-value {
            color: var(--text-primary);
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .detail-value.order-number {
            color: var(--accent-primary);
            font-size: 1.25rem;
        }
        
        .detail-value.price {
            color: var(--accent-success);
            font-size: 1.375rem;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--accent-warning);
            color: white;
        }
        
        /* Instructions de paiement */
        .payment-instructions {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-xl);
            padding: 40px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-subtle);
        }
        
        .payment-instructions h4 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
        }
        
        .payment-instructions h4 i {
            color: var(--accent-primary);
            font-size: 1.5rem;
        }
        
        .payment-method-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-large);
            padding: 32px;
            border: 1px solid var(--border-lighter);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .payment-method-card:hover {
            background: var(--bg-tertiary);
            box-shadow: var(--shadow-medium);
        }
        
        .payment-method-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .payment-method-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            box-shadow: var(--shadow-subtle);
        }
        
        .payment-method-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        .payment-steps {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .payment-steps li {
            padding: 16px 0;
            border-bottom: 1px solid var(--border-lighter);
            display: flex;
            align-items: center;
            gap: 16px;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .payment-steps li:last-child {
            border-bottom: none;
        }
        
        .step-number {
            width: 28px;
            height: 28px;
            background: var(--accent-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            color: white;
            flex-shrink: 0;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-highlight {
            color: var(--accent-primary);
            font-weight: 600;
        }
        
        .warning-alert {
            background: rgba(255, 149, 0, 0.08);
            border: 1px solid rgba(255, 149, 0, 0.2);
            border-radius: var(--radius-medium);
            padding: 20px;
            margin-top: 24px;
            color: var(--accent-warning);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        /* Upload de preuve */
        .proof-upload {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-xl);
            padding: 40px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-subtle);
        }
        
        .proof-upload h4 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
        }
        
        .proof-upload h4 i {
            color: var(--accent-warning);
            font-size: 1.5rem;
        }
        
        .upload-area {
            background: var(--bg-secondary);
            border: 2px dashed var(--border-light);
            border-radius: var(--radius-large);
            padding: 48px 32px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: var(--accent-primary);
            background: rgba(0, 122, 255, 0.02);
        }
        
        .upload-area.dragover {
            border-color: var(--accent-primary);
            background: rgba(0, 122, 255, 0.04);
            transform: scale(1.01);
        }
        
        .upload-icon {
            font-size: 2.5rem;
            color: var(--accent-primary);
            margin-bottom: 20px;
            opacity: 0.8;
        }
        
        .upload-text {
            color: var(--text-primary);
            font-size: 1.125rem;
            margin-bottom: 12px;
            font-weight: 500;
        }
        
        .upload-hint {
            color: var(--text-tertiary);
            font-size: 0.875rem;
            opacity: 0.8;
        }
        
        .file-input {
            display: none;
        }
        
        .upload-btn {
            background: var(--accent-primary);
            border: none;
            border-radius: var(--radius-medium);
            padding: 12px 24px;
            color: white;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            margin-top: 20px;
            box-shadow: var(--shadow-subtle);
        }
        
        .upload-btn:hover {
            background: #0056cc;
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
            color: white;
        }
        
        .file-preview {
            margin-top: 24px;
            padding: 24px;
            background: rgba(0, 122, 255, 0.04);
            border-radius: var(--radius-large);
            border: 1px solid rgba(0, 122, 255, 0.2);
            display: none;
        }
        
        .file-preview img {
            max-width: 100%;
            border-radius: var(--radius-medium);
            box-shadow: var(--shadow-subtle);
        }
        
        /* Actions */
        .action-buttons {
            text-align: center;
            margin-top: 48px;
            margin-bottom: 64px;
        }
        
        .btn-action {
            padding: 14px 28px;
            border-radius: var(--radius-medium);
            font-weight: 500;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin: 0 8px;
            display: inline-block;
            box-shadow: var(--shadow-subtle);
        }
        
        .btn-home {
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            color: var(--text-secondary);
        }
        
        .btn-home:hover {
            border-color: var(--accent-primary);
            color: var(--accent-primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
            text-decoration: none;
        }
        
        .btn-new-order {
            background: var(--accent-primary);
            border: none;
            color: white;
        }
        
        .btn-new-order:hover {
            background: #0056cc;
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
            color: white;
            text-decoration: none;
        }
        
        /* Animations d'entrée */
        .animate-fade-in {
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
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
                padding: 100px 0 60px;
            }
            
            .header-title {
                font-size: 2.5rem;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .payment-method-card {
                padding: 24px;
            }
            
            .payment-steps li {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .action-buttons .btn-action {
                display: block;
                margin: 8px auto;
                max-width: 240px;
            }
            
            .main-container {
                padding: 0 16px;
            }
        }
        
        /* Scrollbar personnalisée */
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
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
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
    <div class="main-container">
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
                    <small style="color: var(--text-tertiary); font-size: 0.875rem;"><?php echo htmlspecialchars($order['category_name'] ?? ''); ?></small>
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