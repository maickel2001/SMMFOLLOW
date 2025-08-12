<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$categories = getAllCategories();
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = cleanInput($_POST['service_id']);
    $customerName = cleanInput($_POST['customer_name']);
    $customerEmail = cleanInput($_POST['customer_email']);
    $linkUrl = cleanInput($_POST['link_url']);
    $quantity = (int)$_POST['quantity'];
    $paymentMethod = cleanInput($_POST['payment_method']);
    
    // Validation
    if (empty($serviceId) || empty($customerName) || empty($customerEmail) || empty($linkUrl) || $quantity < 1000) {
        showAlert('Veuillez remplir tous les champs correctement.', 'danger');
    } else {
        // Calcul du prix total
        $totalPrice = calculateTotalPrice($serviceId, $quantity);
        
        if ($totalPrice > 0) {
            // Génération du numéro de commande
            $orderNumber = generateOrderNumber();
            
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("
                    INSERT INTO orders (order_number, service_id, customer_email, customer_name, link_url, quantity, total_price, payment_method)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$orderNumber, $serviceId, $customerEmail, $customerName, $linkUrl, $quantity, $totalPrice, $paymentMethod])) {
                    $orderId = $pdo->lastInsertId();
                    redirect("paiement.php?order_id=" . $orderId);
                } else {
                    showAlert('Erreur lors de la création de la commande.', 'danger');
                }
            } catch (Exception $e) {
                showAlert('Erreur de base de données.', 'danger');
            }
        } else {
            showAlert('Erreur lors du calcul du prix.', 'danger');
        }
    }
}

// Récupération des services de la catégorie sélectionnée
$services = [];
if ($selectedCategory) {
    $services = getServicesByCategory($selectedCategory);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commander - SMM Pro</title>
    
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
                        <a class="nav-link" href="index.php#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="commander.php">Commander</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title">Commander vos Services</h2>
            
            <?php if (isset($_SESSION['alert'])): ?>
                <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['alert']['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>
            
            <div class="row">
                <!-- Sélection des services -->
                <div class="col-lg-4 mb-4">
                    <div class="order-form">
                        <h4 class="mb-4">
                            <i class="fas fa-list me-2"></i>Choisir un Service
                        </h4>
                        
                        <!-- Sélection de catégorie -->
                        <div class="form-group">
                            <label class="form-label">Catégorie</label>
                            <select class="form-control form-select" id="categorySelect">
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($selectedCategory == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Liste des services -->
                        <div id="servicesList">
                            <?php if ($selectedCategory && !empty($services)): ?>
                                <div class="form-group">
                                    <label class="form-label">Service</label>
                                    <select class="form-control form-select" id="serviceSelect" name="service_id" required>
                                        <option value="">Sélectionner un service</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?php echo $service['id']; ?>" 
                                                    data-price="<?php echo $service['price_per_1000']; ?>"
                                                    data-min="<?php echo $service['min_quantity']; ?>"
                                                    data-max="<?php echo $service['max_quantity']; ?>">
                                                <?php echo htmlspecialchars($service['name']); ?> - 
                                                <?php echo formatPrice($service['price_per_1000']); ?>/1000
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire de commande -->
                <div class="col-lg-8">
                    <div class="order-form">
                        <h4 class="mb-4">
                            <i class="fas fa-shopping-cart me-2"></i>Détails de la Commande
                        </h4>
                        
                        <form method="POST" action="" id="orderForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Nom complet *</label>
                                        <input type="text" class="form-control" name="customer_name" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email *</label>
                                        <input type="email" class="form-control" name="customer_email" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Lien à promouvoir *</label>
                                <input type="url" class="form-control" name="link_url" 
                                       placeholder="https://instagram.com/votre-profil" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Quantité *</label>
                                        <input type="number" class="form-control" name="quantity" id="quantity" 
                                               min="1000" step="1000" required>
                                        <small class="text-muted">Minimum: 1 000</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Méthode de paiement *</label>
                                        <select class="form-control form-select" name="payment_method" required>
                                            <option value="">Choisir...</option>
                                            <option value="MTN Money">MTN Money</option>
                                            <option value="Moov Money">Moov Money</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Résumé de la commande -->
                            <div class="card" style="background: var(--dark-bg); border-color: var(--border-color);">
                                <div class="card-body">
                                    <h5 class="card-title text-center mb-3">
                                        <i class="fas fa-calculator me-2"></i>Résumé de la Commande
                                    </h5>
                                    
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Service:</strong></p>
                                            <p id="selectedService" class="text-muted">-</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Prix total:</strong></p>
                                            <p id="totalPrice" class="text-primary fw-bold">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                    <i class="fas fa-credit-card me-2"></i>Procéder au Paiement
                                </button>
                            </div>
                        </form>
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
    
    <!-- Custom JS -->
    <script>
        // Gestion de la sélection de catégorie
        document.getElementById('categorySelect').addEventListener('change', function() {
            const categoryId = this.value;
            if (categoryId) {
                window.location.href = 'commander.php?category=' + categoryId;
            }
        });
        
        // Gestion de la sélection de service et calcul du prix
        const serviceSelect = document.getElementById('serviceSelect');
        const quantityInput = document.getElementById('quantity');
        const selectedServiceSpan = document.getElementById('selectedService');
        const totalPriceSpan = document.getElementById('totalPrice');
        const submitBtn = document.getElementById('submitBtn');
        
        function updateOrderSummary() {
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            const quantity = parseInt(quantityInput.value) || 0;
            
            if (selectedOption && selectedOption.value && quantity >= 1000) {
                const pricePer1000 = parseFloat(selectedOption.dataset.price);
                const totalPrice = (pricePer1000 * quantity) / 1000;
                
                selectedServiceSpan.textContent = selectedOption.text;
                totalPriceSpan.textContent = new Intl.NumberFormat('fr-FR').format(totalPrice) + ' FCFA';
                submitBtn.disabled = false;
            } else {
                selectedServiceSpan.textContent = '-';
                totalPriceSpan.textContent = '-';
                submitBtn.disabled = true;
            }
        }
        
        if (serviceSelect) {
            serviceSelect.addEventListener('change', updateOrderSummary);
            quantityInput.addEventListener('input', updateOrderSummary);
        }
        
        // Validation du formulaire
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const serviceId = serviceSelect.value;
            const quantity = parseInt(quantityInput.value);
            
            if (!serviceId) {
                e.preventDefault();
                alert('Veuillez sélectionner un service.');
                return false;
            }
            
            if (quantity < 1000) {
                e.preventDefault();
                alert('La quantité minimum est de 1 000.');
                return false;
            }
        });
    </script>
</body>
</html>