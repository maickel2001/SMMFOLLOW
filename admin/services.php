<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de la connexion admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    try {
        $pdo = getDBConnection();
        
        switch ($action) {
            case 'add_service':
                $name = cleanInput($_POST['name']);
                $categoryId = (int)$_POST['category_id'];
                $description = cleanInput($_POST['description']);
                $pricePer1000 = (float)$_POST['price_per_1000'];
                $minQuantity = (int)$_POST['min_quantity'];
                $maxQuantity = (int)$_POST['max_quantity'];
                $platform = cleanInput($_POST['platform']);
                $type = cleanInput($_POST['type']);
                $deliveryTime = cleanInput($_POST['delivery_time']);
                
                $stmt = $pdo->prepare("
                    INSERT INTO services (category_id, name, description, price_per_1000, min_quantity, max_quantity, platform, type, delivery_time)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$categoryId, $name, $description, $pricePer1000, $minQuantity, $maxQuantity, $platform, $type, $deliveryTime]);
                showAlert('Service ajouté avec succès.', 'success');
                break;
                
            case 'update_service':
                $serviceId = (int)$_POST['service_id'];
                $name = cleanInput($_POST['name']);
                $categoryId = (int)$_POST['category_id'];
                $description = cleanInput($_POST['description']);
                $pricePer1000 = (float)$_POST['price_per_1000'];
                $minQuantity = (int)$_POST['min_quantity'];
                $maxQuantity = (int)$_POST['max_quantity'];
                $platform = cleanInput($_POST['platform']);
                $type = cleanInput($_POST['type']);
                $deliveryTime = cleanInput($_POST['delivery_time']);
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                
                $stmt = $pdo->prepare("
                    UPDATE services SET category_id = ?, name = ?, description = ?, price_per_1000 = ?, 
                    min_quantity = ?, max_quantity = ?, platform = ?, type = ?, delivery_time = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$categoryId, $name, $description, $pricePer1000, $minQuantity, $maxQuantity, $platform, $type, $deliveryTime, $isActive, $serviceId]);
                showAlert('Service mis à jour avec succès.', 'success');
                break;
                
            case 'delete_service':
                $serviceId = (int)$_POST['service_id'];
                
                // Vérifier s'il y a des commandes liées
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE service_id = ?");
                $stmt->execute([$serviceId]);
                $orderCount = $stmt->fetch()['count'];
                
                if ($orderCount > 0) {
                    showAlert('Impossible de supprimer ce service car il y a des commandes associées.', 'danger');
                } else {
                    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
                    $stmt->execute([$serviceId]);
                    showAlert('Service supprimé avec succès.', 'success');
                }
                break;
        }
    } catch (Exception $e) {
        showAlert('Erreur lors de l\'opération.', 'danger');
    }
}

// Récupération des services et catégories
try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("
        SELECT s.*, c.name as category_name
        FROM services s
        JOIN categories c ON s.category_id = c.id
        ORDER BY c.name, s.name
    ");
    $services = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Erreur de base de données.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Services - SMM Pro</title>
    
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
        
        .service-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .service-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .service-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .service-category {
            background: var(--primary-color);
            color: var(--dark-bg);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .service-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--success-color);
        }
        
        .service-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active { background: rgba(0, 255, 136, 0.2); color: var(--success-color); }
        .status-inactive { background: rgba(255, 68, 68, 0.2); color: var(--danger-color); }
        
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-cogs me-2"></i>Gestion des Services
                </h1>
                <button class="btn btn-primary" onclick="showAddServiceModal()">
                    <i class="fas fa-plus me-2"></i>Ajouter un Service
                </button>
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
                    <a class="nav-link active" href="services.php">
                        <i class="fas fa-cogs me-2"></i>Services
                    </a>
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags me-2"></i>Catégories
                    </a>
                </nav>
            </div>
            
            <!-- Liste des services -->
            <div class="row">
                <?php foreach ($services as $service): ?>
                    <div class="col-lg-6 col-xl-4">
                        <div class="service-card">
                            <div class="service-header">
                                <div class="service-name"><?php echo htmlspecialchars($service['name']); ?></div>
                                <span class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></span>
                            </div>
                            
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($service['description']); ?></p>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Plateforme:</small>
                                    <div><?php echo htmlspecialchars($service['platform']); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Type:</small>
                                    <div><?php echo htmlspecialchars($service['type']); ?></div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Quantité:</small>
                                    <div><?php echo number_format($service['min_quantity'], 0, ',', ' ') . ' - ' . number_format($service['max_quantity'], 0, ',', ' '); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Livraison:</small>
                                    <div><?php echo htmlspecialchars($service['delivery_time']); ?></div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="service-price"><?php echo formatPrice($service['price_per_1000']); ?>/1000</div>
                                <span class="service-status <?php echo $service['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $service['is_active'] ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </div>
                            
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal Ajout/Modification de service -->
    <div class="modal fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white" id="serviceModalTitle">Ajouter un Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="serviceForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="serviceAction" value="add_service">
                        <input type="hidden" name="service_id" id="serviceId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Nom du service *</label>
                                    <input type="text" class="form-control" name="name" id="serviceName" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Catégorie *</label>
                                    <select name="category_id" class="form-select" id="serviceCategory" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" id="serviceDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Prix pour 1000 *</label>
                                    <input type="number" class="form-control" name="price_per_1000" id="servicePrice" 
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Plateforme *</label>
                                    <select name="platform" class="form-select" id="servicePlatform" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="Instagram">Instagram</option>
                                        <option value="TikTok">TikTok</option>
                                        <option value="YouTube">YouTube</option>
                                        <option value="Facebook">Facebook</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Type de service *</label>
                                    <select name="type" class="form-select" id="serviceType" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="Followers">Followers</option>
                                        <option value="Likes">Likes</option>
                                        <option value="Views">Views</option>
                                        <option value="Comments">Comments</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Temps de livraison</label>
                                    <input type="text" class="form-control" name="delivery_time" id="serviceDelivery" 
                                           placeholder="ex: 24-48h">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Quantité minimum *</label>
                                    <input type="number" class="form-control" name="min_quantity" id="serviceMinQty" 
                                           min="1" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Quantité maximum *</label>
                                    <input type="number" class="form-control" name="max_quantity" id="serviceMaxQty" 
                                           min="1" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3" id="serviceActiveDiv" style="display: none;">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="serviceActive" value="1" checked>
                                <label class="form-check-label" for="serviceActive">Service actif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        function showAddServiceModal() {
            document.getElementById('serviceModalTitle').textContent = 'Ajouter un Service';
            document.getElementById('serviceAction').value = 'add_service';
            document.getElementById('serviceForm').reset();
            document.getElementById('serviceActiveDiv').style.display = 'none';
            new bootstrap.Modal(document.getElementById('serviceModal')).show();
        }
        
        function editService(service) {
            document.getElementById('serviceModalTitle').textContent = 'Modifier le Service';
            document.getElementById('serviceAction').value = 'update_service';
            document.getElementById('serviceId').value = service.id;
            document.getElementById('serviceName').value = service.name;
            document.getElementById('serviceCategory').value = service.category_id;
            document.getElementById('serviceDescription').value = service.description;
            document.getElementById('servicePrice').value = service.price_per_1000;
            document.getElementById('servicePlatform').value = service.platform;
            document.getElementById('serviceType').value = service.type;
            document.getElementById('serviceDelivery').value = service.delivery_time;
            document.getElementById('serviceMinQty').value = service.min_quantity;
            document.getElementById('serviceMaxQty').value = service.max_quantity;
            document.getElementById('serviceActive').checked = service.is_active == 1;
            document.getElementById('serviceActiveDiv').style.display = 'block';
            new bootstrap.Modal(document.getElementById('serviceModal')).show();
        }
        
        function deleteService(serviceId, serviceName) {
            if (confirm('Êtes-vous sûr de vouloir supprimer le service "' + serviceName + '" ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_service">
                    <input type="hidden" name="service_id" value="${serviceId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>