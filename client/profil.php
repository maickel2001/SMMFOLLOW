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

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $firstName = cleanInput($_POST['first_name'] ?? '');
        $lastName = cleanInput($_POST['last_name'] ?? '');
        $phone = cleanInput($_POST['phone'] ?? '');
        $country = cleanInput($_POST['country'] ?? '');
        
        if (empty($firstName) || empty($lastName)) {
            $error = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            try {
                if (updateUserProfile($currentUser['id'], $firstName, $lastName, $phone, $country)) {
                    $success = 'Profil mis à jour avec succès !';
                    
                    // Mettre à jour les informations de session
                    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                    
                    // Recharger les informations utilisateur
                    $currentUser = getCurrentUser();
                } else {
                    $error = 'Erreur lors de la mise à jour du profil.';
                }
            } catch (Exception $e) {
                $error = 'Erreur lors de la mise à jour du profil : ' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Veuillez remplir tous les champs du mot de passe.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Les nouveaux mots de passe ne correspondent pas.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        } else {
            try {
                if (changeUserPassword($currentUser['id'], $currentPassword, $newPassword)) {
                    $success = 'Mot de passe modifié avec succès !';
                } else {
                    $error = 'Mot de passe actuel incorrect.';
                }
            } catch (Exception $e) {
                $error = 'Erreur lors du changement de mot de passe : ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - SMM Pro</title>
    
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
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            color: var(--dark-bg);
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }
        
        .strength-weak { color: var(--danger-color); }
        .strength-medium { color: var(--warning-color); }
        .strength-strong { color: var(--success-color); }
        
        .form-section {
            margin-bottom: 40px;
        }
        
        .form-section h5 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }
    </style>
</head>
<body>
    <div class="client-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-user-cog me-2"></i>Mon Profil
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
                    <a class="nav-link" href="nouvelle-commande.php">
                        <i class="fas fa-plus me-2"></i>Nouvelle Commande
                    </a>
                    <a class="nav-link" href="tickets.php">
                        <i class="fas fa-ticket-alt me-2"></i>Support
                    </a>
                    <a class="nav-link active" href="profil.php">
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
            
            <!-- En-tête du profil -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h2>
                <p class="mb-0"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo htmlspecialchars($currentUser['username']); ?></div>
                        <div class="stat-label">Nom d'utilisateur</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $currentUser['phone'] ?: 'Non renseigné'; ?></div>
                        <div class="stat-label">Téléphone</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $currentUser['country'] ?: 'Non renseigné'; ?></div>
                        <div class="stat-label">Pays</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo date('d/m/Y', strtotime($currentUser['created_at'])); ?></div>
                        <div class="stat-label">Membre depuis</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Informations personnelles -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-user me-2"></i>Informations Personnelles
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="profileForm">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">Prénom *</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Nom *</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?php echo htmlspecialchars($currentUser['email']); ?>" disabled>
                                    <div class="form-text">L'email ne peut pas être modifié</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nom d'utilisateur</label>
                                    <input type="text" class="form-control" id="username" 
                                           value="<?php echo htmlspecialchars($currentUser['username']); ?>" disabled>
                                    <div class="form-text">Le nom d'utilisateur ne peut pas être modifié</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="country" class="form-label">Pays</label>
                                        <input type="text" class="form-control" id="country" name="country" 
                                               value="<?php echo htmlspecialchars($currentUser['country'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Mettre à Jour le Profil
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Changement de mot de passe -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-lock me-2"></i>Changer le Mot de Passe
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="passwordForm">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mot de passe actuel *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nouveau mot de passe *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="password-strength" id="passwordStrength"></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Conseils de sécurité :</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Utilisez au moins 6 caractères</li>
                                        <li>Combinez lettres, chiffres et caractères spéciaux</li>
                                        <li>Évitez les informations personnelles</li>
                                    </ul>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i>Changer le Mot de Passe
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Informations du compte -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-info-circle me-2"></i>Informations du Compte
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Statut du compte</small>
                                    <div class="fw-bold text-success">
                                        <i class="fas fa-check-circle me-1"></i>Actif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Dernière connexion</small>
                                    <div class="fw-bold">
                                        <?php echo $currentUser['last_login'] ? date('d/m/Y H:i', strtotime($currentUser['last_login'])) : 'Jamais'; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="text-center">
                                <a href="logout.php" class="btn btn-outline-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Se Déconnecter
                                </a>
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
        // Vérification de la force du mot de passe
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            let strength = 0;
            let strengthText = '';
            let strengthClass = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (strength < 2) {
                strengthText = 'Faible';
                strengthClass = 'strength-weak';
            } else if (strength < 4) {
                strengthText = 'Moyen';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Fort';
                strengthClass = 'strength-strong';
            }
            
            strengthDiv.textContent = `Force : ${strengthText}`;
            strengthDiv.className = `password-strength ${strengthClass}`;
        });
        
        // Validation du formulaire de mot de passe
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Les nouveaux mots de passe ne correspondent pas.');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Le nouveau mot de passe doit contenir au moins 6 caractères.');
                return false;
            }
        });
        
        // Validation du formulaire de profil
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            
            if (!firstName || !lastName) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
                return false;
            }
        });
    </script>
</body>
</html>