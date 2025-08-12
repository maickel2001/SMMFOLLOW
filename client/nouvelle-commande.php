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

// Récupération des catégories et services
try {
    $categories = getAllCategories();
    $services = [];
    foreach ($categories as $category) {
        $services[$category['id']] = getServicesByCategory($category['id']);
    }
} catch (Exception $e) {
    $categories = [];
    $services = [];
}

// Traitement de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = $_POST['service_id'] ?? '';
    $linkUrl = cleanInput($_POST['link_url'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? '';
    
    // Validation
    if (empty($serviceId) || empty($linkUrl) || $quantity < 1000 || empty($paymentMethod)) {
        $error = 'Veuillez remplir tous les champs correctement.';
    } else {
        try {
            // Créer la commande
            $orderId = createOrderWithUser(
                $currentUser['id'],
                $serviceId,
                $currentUser['email'],
                $currentUser['first_name'] . ' ' . $currentUser['last_name'],
                $linkUrl,
                $quantity,
                $paymentMethod
            );
            
            if ($orderId) {
                $success = 'Commande créée avec succès ! Vous allez être redirigé vers la page de paiement.';
                
                // Rediriger vers la page de paiement après 2 secondes
                header("refresh:2;url=../paiement.php?order_id=" . $orderId);
            } else {
                $error = 'Erreur lors de la création de la commande.';
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de la création de la commande : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Commande - SMM Pro</title>
    
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
        
        .order-summary {
            background: var(--darker-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .summary-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-color);
        }
        
        .service-card {
            background: var(--darker-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .service-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .service-card.selected {
            border-color: var(--primary-color);
            background: rgba(0, 255, 136, 0.1);
        }
        
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .service-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .service-price {
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .service-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .service-details {
            display: flex;
            gap: 20px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .quantity-input {
            max-width: 200px;
        }
        
        .price-display {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            padding: 20px;
            background: var(--darker-bg);
            border-radius: 10px;
            border: 2px solid var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="client-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-plus me-2"></i>Nouvelle Commande
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
                    <a class="nav-link active" href="nouvelle-commande.php">
                        <i class="fas fa-plus me-2"></i>Nouvelle Commande
                    </a>
                    <a class="nav-link" href="tickets.php">
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
                <!-- Formulaire de commande -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-shopping-cart me-2"></i>Créer une Nouvelle Commande
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="orderForm">
                                <!-- Sélection du service -->
                                <div class="mb-4">
                                    <label class="form-label">Choisir un Service</label>
                                    <div class="row">
                                        <?php foreach ($categories as $category): ?>
                                            <div class="col-12 mb-3">
                                                <h6 class="text-primary mb-3">
                                                    <i class="<?php echo htmlspecialchars($category['icon']); ?> me-2"></i>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </h6>
                                                <?php if (isset($services[$category['id']])): ?>
                                                    <?php foreach ($services[$category['id']] as $service): ?>
                                                        <div class="service-card" data-service-id="<?php echo $service['id']; ?>" 
                                                             data-price="<?php echo $service['price_per_1000']; ?>">
                                                            <div class="service-header">
                                                                <div class="service-name"><?php echo htmlspecialchars($service['name']); ?></div>
                                                                <div class="service-price"><?php echo number_format($service['price_per_1000'], 0, ',', ' '); ?> FCFA/1000</div>
                                                            </div>
                                                            <div class="service-description"><?php echo htmlspecialchars($service['description']); ?></div>
                                                            <div class="service-details">
                                                                <span><i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($service['delivery_time']); ?></span>
                                                                <span><i class="fas fa-chart-line me-1"></i>Min: <?php echo number_format($service['min_quantity'], 0, ',', ' '); ?></span>
                                                                <span><i class="fas fa-chart-line me-1"></i>Max: <?php echo number_format($service['max_quantity'], 0, ',', ' '); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Lien à promouvoir -->
                                <div class="mb-4">
                                    <label for="link_url" class="form-label">Lien à Promouvoir</label>
                                    <input type="url" class="form-control" id="link_url" name="link_url" 
                                           placeholder="https://..." required value="<?php echo htmlspecialchars($_POST['link_url'] ?? ''); ?>">
                                    <div class="form-text">Entrez l'URL de votre post Instagram, vidéo TikTok, YouTube ou page Facebook</div>
                                </div>
                                
                                <!-- Quantité -->
                                <div class="mb-4">
                                    <label for="quantity" class="form-label">Quantité</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="number" class="form-control quantity-input" id="quantity" name="quantity" 
                                                   min="1000" step="1000" placeholder="1000" required 
                                                   value="<?php echo htmlspecialchars($_POST['quantity'] ?? '1000'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="price-display" id="priceDisplay">
                                                0 FCFA
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-text">Quantité minimale : 1 000</div>
                                </div>
                                
                                <!-- Méthode de paiement -->
                                <div class="mb-4">
                                    <label for="payment_method" class="form-label">Méthode de Paiement</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Choisir une méthode de paiement</option>
                                        <option value="MTN Money" <?php echo ($_POST['payment_method'] ?? '') === 'MTN Money' ? 'selected' : ''; ?>>MTN Money</option>
                                        <option value="Moov Money" <?php echo ($_POST['payment_method'] ?? '') === 'Moov Money' ? 'selected' : ''; ?>>Moov Money</option>
                                    </select>
                                </div>
                                
                                <!-- Bouton de commande -->
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                        <i class="fas fa-shopping-cart me-2"></i>Créer la Commande
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Résumé de la commande -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h5 class="mb-4 text-white">
                            <i class="fas fa-receipt me-2"></i>Résumé de la Commande
                        </h5>
                        
                        <div id="orderSummary">
                            <div class="text-center text-muted">
                                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                <p>Sélectionnez un service pour voir le résumé</p>
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
        let selectedService = null;
        let selectedPrice = 0;
        
        // Sélection du service
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('click', function() {
                // Désélectionner tous les services
                document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
                
                // Sélectionner ce service
                this.classList.add('selected');
                
                selectedService = this.dataset.serviceId;
                selectedPrice = parseFloat(this.dataset.price);
                
                // Mettre à jour le résumé
                updateOrderSummary();
                
                // Activer le bouton de soumission
                document.getElementById('submitBtn').disabled = false;
            });
        });
        
        // Mise à jour de la quantité
        document.getElementById('quantity').addEventListener('input', function() {
            updateOrderSummary();
        });
        
        // Mise à jour du résumé
        function updateOrderSummary() {
            const quantity = parseInt(document.getElementById('quantity').value) || 0;
            const totalPrice = (selectedPrice * quantity) / 1000;
            
            if (selectedService && quantity > 0) {
                const summaryHtml = `
                    <div class="summary-item">
                        <span>Service sélectionné</span>
                        <span id="selectedServiceName">Chargement...</span>
                    </div>
                    <div class="summary-item">
                        <span>Quantité</span>
                        <span>${quantity.toLocaleString()}</span>
                    </div>
                    <div class="summary-item">
                        <span>Prix par 1000</span>
                        <span>${selectedPrice.toLocaleString()} FCFA</span>
                    </div>
                    <div class="summary-item">
                        <span>Total</span>
                        <span>${totalPrice.toLocaleString()} FCFA</span>
                    </div>
                `;
                
                document.getElementById('orderSummary').innerHTML = summaryHtml;
                
                // Mettre à jour l'affichage du prix
                document.getElementById('priceDisplay').textContent = totalPrice.toLocaleString() + ' FCFA';
                
                // Mettre à jour le nom du service sélectionné
                const selectedCard = document.querySelector('.service-card.selected');
                if (selectedCard) {
                    const serviceName = selectedCard.querySelector('.service-name').textContent;
                    document.getElementById('selectedServiceName').textContent = serviceName;
                }
            }
        }
        
        // Validation du formulaire
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            if (!selectedService) {
                e.preventDefault();
                alert('Veuillez sélectionner un service.');
                return false;
            }
            
            const quantity = parseInt(document.getElementById('quantity').value);
            if (quantity < 1000) {
                e.preventDefault();
                alert('La quantité minimale est de 1 000.');
                return false;
            }
            
            const paymentMethod = document.getElementById('payment_method').value;
            if (!paymentMethod) {
                e.preventDefault();
                alert('Veuillez sélectionner une méthode de paiement.');
                return false;
            }
        });
    </script>
</body>
</html>