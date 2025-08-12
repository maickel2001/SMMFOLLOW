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
            case 'add_category':
                $name = cleanInput($_POST['name']);
                $description = cleanInput($_POST['description']);
                $icon = cleanInput($_POST['icon']);
                
                $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $icon]);
                showAlert('Catégorie ajoutée avec succès.', 'success');
                break;
                
            case 'update_category':
                $categoryId = (int)$_POST['category_id'];
                $name = cleanInput($_POST['name']);
                $description = cleanInput($_POST['description']);
                $icon = cleanInput($_POST['icon']);
                
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?");
                $stmt->execute([$name, $description, $icon, $categoryId]);
                showAlert('Catégorie mise à jour avec succès.', 'success');
                break;
                
            case 'delete_category':
                $categoryId = (int)$_POST['category_id'];
                
                // Vérifier s'il y a des services liés
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM services WHERE category_id = ?");
                $stmt->execute([$categoryId]);
                $serviceCount = $stmt->fetch()['count'];
                
                if ($serviceCount > 0) {
                    showAlert('Impossible de supprimer cette catégorie car elle contient des services.', 'danger');
                } else {
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$categoryId]);
                    showAlert('Catégorie supprimée avec succès.', 'success');
                }
                break;
        }
    } catch (Exception $e) {
        showAlert('Erreur lors de l\'opération.', 'danger');
    }
}

// Récupération des catégories
try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("
        SELECT c.*, COUNT(s.id) as service_count
        FROM categories c
        LEFT JOIN services s ON c.id = s.category_id
        GROUP BY c.id
        ORDER BY c.name
    ");
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
    <title>Gestion des Catégories - SMM Pro</title>
    
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
        
        .category-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .category-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .category-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .category-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .category-stats {
            background: var(--darker-bg);
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .stats-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }
        
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
        
        .icon-preview {
            font-size: 2rem;
            color: var(--primary-color);
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-tags me-2"></i>Gestion des Catégories
                </h1>
                <button class="btn btn-primary" onclick="showAddCategoryModal()">
                    <i class="fas fa-plus me-2"></i>Ajouter une Catégorie
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
                    <a class="nav-link" href="services.php">
                        <i class="fas fa-cogs me-2"></i>Services
                    </a>
                    <a class="nav-link active" href="categories.php">
                        <i class="fas fa-tags me-2"></i>Catégories
                    </a>
                </nav>
            </div>
            
            <!-- Liste des catégories -->
            <div class="row">
                <?php foreach ($categories as $category): ?>
                    <div class="col-lg-6 col-xl-4">
                        <div class="category-card">
                            <div class="text-center mb-3">
                                <div class="category-icon">
                                    <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                                </div>
                            </div>
                            
                            <div class="category-header">
                                <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                            </div>
                            
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($category['description']); ?></p>
                            
                            <div class="category-stats">
                                <div class="stats-number"><?php echo $category['service_count']; ?></div>
                                <div class="stats-label">Services</div>
                            </div>
                            
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal Ajout/Modification de catégorie -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white" id="categoryModalTitle">Ajouter une Catégorie</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="categoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="categoryAction" value="add_category">
                        <input type="hidden" name="category_id" id="categoryId">
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Nom de la catégorie *</label>
                            <input type="text" class="form-control" name="name" id="categoryName" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" id="categoryDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Icône Font Awesome *</label>
                            <input type="text" class="form-control" name="icon" id="categoryIcon" 
                                   placeholder="ex: fab fa-instagram" required>
                            <small class="text-muted">Utilisez les classes Font Awesome (ex: fab fa-instagram, fas fa-rocket)</small>
                            <div class="icon-preview" id="iconPreview">
                                <i class="fas fa-question"></i>
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
        // Prévisualisation de l'icône
        document.getElementById('categoryIcon').addEventListener('input', function() {
            const iconClass = this.value;
            const preview = document.getElementById('iconPreview');
            if (iconClass) {
                preview.innerHTML = `<i class="${iconClass}"></i>`;
            } else {
                preview.innerHTML = '<i class="fas fa-question"></i>';
            }
        });
        
        function showAddCategoryModal() {
            document.getElementById('categoryModalTitle').textContent = 'Ajouter une Catégorie';
            document.getElementById('categoryAction').value = 'add_category';
            document.getElementById('categoryForm').reset();
            document.getElementById('iconPreview').innerHTML = '<i class="fas fa-question"></i>';
            new bootstrap.Modal(document.getElementById('categoryModal')).show();
        }
        
        function editCategory(category) {
            document.getElementById('categoryModalTitle').textContent = 'Modifier la Catégorie';
            document.getElementById('categoryAction').value = 'update_category';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description;
            document.getElementById('categoryIcon').value = category.icon;
            document.getElementById('iconPreview').innerHTML = `<i class="${category.icon}"></i>`;
            new bootstrap.Modal(document.getElementById('categoryModal')).show();
        }
        
        function deleteCategory(categoryId, categoryName) {
            if (confirm('Êtes-vous sûr de vouloir supprimer la catégorie "' + categoryName + '" ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="category_id" value="${categoryId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>