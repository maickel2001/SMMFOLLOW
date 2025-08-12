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
        SELECT o.*, s.name as service_name, s.platform, s.type
        FROM orders o
        JOIN services s ON o.service_id = s.id
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
                $stmt = $pdo->prepare("UPDATE orders SET payment_proof = ? WHERE id = ?");
                if ($stmt->execute([$uploadedFile, $orderId])) {
                    showAlert('Preuve de paiement uploadée avec succès ! Votre commande sera traitée dans les plus brefs délais.', 'success');
                } else {
                    showAlert('Erreur lors de la mise à jour de la commande.', 'danger');
                }
            } catch (Exception $e) {
                showAlert('Erreur de base de données.', 'danger');
            }
        } else {
            showAlert('Erreur lors de l\'upload du fichier. Vérifiez le format (JPG/PNG).', 'danger');
        }
    } else {
        showAlert('Veuillez sélectionner un fichier valide.', 'danger');
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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
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

    <!-- Main Content -->
    <div class="section" style="padding-top: 120px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if (isset($_SESSION['alert'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['alert']['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['alert']); ?>
                    <?php endif; ?>
                    
                    <!-- Résumé de la commande -->
                    <div class="order-form mb-4">
                        <h4 class="mb-4 text-center">
                            <i class="fas fa-check-circle text-success me-2"></i>Commande Confirmée
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Numéro de commande:</strong></p>
                                <p class="text-primary fw-bold"><?php echo htmlspecialchars($order['order_number']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Service:</strong></p>
                                <p><?php echo htmlspecialchars($order['service_name']); ?></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Quantité:</strong></p>
                                <p><?php echo number_format($order['quantity'], 0, ',', ' '); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Prix total:</strong></p>
                                <p class="text-success fw-bold"><?php echo formatPrice($order['total_price']); ?></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Méthode de paiement:</strong></p>
                                <p><?php echo htmlspecialchars($order['payment_method']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Statut:</strong></p>
                                <span class="badge bg-warning">En attente de paiement</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instructions de paiement -->
                    <div class="order-form mb-4">
                        <h4 class="mb-4">
                            <i class="fas fa-mobile-alt me-2"></i>Instructions de Paiement
                        </h4>
                        
                        <?php if ($order['payment_method'] === 'MTN Money'): ?>
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle me-2"></i>Paiement via MTN Money</h5>
                                <ol class="mb-0">
                                    <li>Composez <strong>*126#</strong> sur votre téléphone</li>
                                    <li>Sélectionnez <strong>"Envoyer de l'argent"</strong></li>
                                    <li>Entrez le numéro: <strong>0123456789</strong></li>
                                    <li>Entrez le montant: <strong><?php echo formatPrice($order['total_price']); ?></strong></li>
                                    <li>Confirmez la transaction</li>
                                    <li>Prenez une capture d'écran de la confirmation</li>
                                </ol>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle me-2"></i>Paiement via Moov Money</h5>
                                <ol class="mb-0">
                                    <li>Composez <strong>*155#</strong> sur votre téléphone</li>
                                    <li>Sélectionnez <strong>"Transfert d'argent"</strong></li>
                                    <li>Entrez le numéro: <strong>0123456789</strong></li>
                                    <li>Entrez le montant: <strong><?php echo formatPrice($order['total_price']); ?></strong></li>
                                    <li>Confirmez la transaction</li>
                                    <li>Prenez une capture d'écran de la confirmation</li>
                                </ol>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Conservez le reçu de transaction et prenez une capture d'écran de la confirmation de paiement.
                        </div>
                    </div>
                    
                    <!-- Upload de la preuve de paiement -->
                    <div class="order-form">
                        <h4 class="mb-4">
                            <i class="fas fa-upload me-2"></i>Preuve de Paiement
                        </h4>
                        
                        <?php if ($order['payment_proof']): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Preuve de paiement reçue !</strong> Votre commande sera traitée dans les plus brefs délais.
                            </div>
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="form-label">Capture d'écran du paiement *</label>
                                    <input type="file" class="form-control" name="payment_proof" 
                                           accept=".jpg,.jpeg,.png" required>
                                    <small class="text-muted">Formats acceptés: JPG, PNG. Taille max: 5MB</small>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-upload me-2"></i>Envoyer la Preuve
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Informations supplémentaires -->
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                        <a href="commander.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nouvelle commande
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 SMM Pro. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>