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
        
        .client-container {
            padding-top: 20px;
            min-height: 100vh;
            background: var(--bg-primary);
        }
        
        /* Header Principal */
        .main-header {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border-radius: var(--radius-xl);
            padding: 40px;
            margin-bottom: 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-subtle);
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
            font-size: 2.5rem;
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
        
        /* Navigation Client Minimaliste */
        .client-nav {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 24px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-subtle);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        
        .client-nav .nav-link {
            color: var(--text-secondary);
            padding: 12px 20px;
            border-radius: var(--radius-medium);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            margin: 0 4px;
        }
        
        .client-nav .nav-link:hover,
        .client-nav .nav-link.active {
            color: var(--accent-primary);
            background: rgba(0, 122, 255, 0.04);
            transform: translateY(-1px);
        }
        
        /* Cartes et Formulaires Minimalistes */
        .card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            box-shadow: var(--shadow-subtle);
            margin-bottom: 24px;
        }
        
        .card-header {
            background: var(--bg-secondary);
            border-color: var(--border-lighter);
            border-radius: var(--radius-large) var(--radius-large) 0 0;
            padding: 20px 24px;
        }
        
        .card-header h5 {
            color: var(--text-primary);
            font-weight: 600;
            margin: 0;
            font-size: 1.125rem;
        }
        
        .card-body {
            padding: 24px;
        }
        
        .form-control, .form-select {
            background: var(--bg-secondary);
            border: 1px solid var(--border-lighter);
            color: var(--text-primary);
            border-radius: var(--radius-medium);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 12px 16px;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            background: var(--bg-primary);
            border-color: var(--accent-primary);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
            outline: none;
        }
        
        .form-control:disabled {
            background: var(--bg-tertiary);
            color: var(--text-tertiary);
            cursor: not-allowed;
        }
        
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-text {
            color: var(--text-tertiary);
            font-size: 0.85rem;
            margin-top: 4px;
        }
        
        /* Boutons Minimalistes */
        .btn {
            border-radius: var(--radius-medium);
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            padding: 10px 20px;
            font-size: 0.95rem;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
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
        }
        
        .btn-outline-primary:hover {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }
        
        .btn-outline-danger {
            color: var(--accent-danger);
            border-color: var(--accent-danger);
        }
        
        .btn-outline-danger:hover {
            background: var(--accent-danger);
            border-color: var(--accent-danger);
            color: white;
        }
        
        /* En-tête du Profil Minimaliste */
        .profile-header {
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            border-radius: var(--radius-xl);
            padding: 40px;
            margin-bottom: 32px;
            color: white;
            text-align: center;
            box-shadow: var(--shadow-medium);
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.5rem;
            backdrop-filter: blur(10px);
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .profile-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }
        
        .profile-header p {
            font-size: 1.125rem;
            opacity: 0.9;
            margin-bottom: 32px;
        }
        
        /* Grille de Statistiques Minimaliste */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 32px;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-medium);
            padding: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-number {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: white;
        }
        
        .stat-label {
            font-size: 0.875rem;
            opacity: 0.8;
            color: white;
            font-weight: 500;
        }
        
        /* Force du Mot de Passe Minimaliste */
        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: var(--radius-small);
            display: inline-block;
        }
        
        .strength-weak { 
            background: rgba(255, 59, 48, 0.1);
            color: var(--accent-danger);
        }
        .strength-medium { 
            background: rgba(255, 149, 0, 0.1);
            color: var(--accent-warning);
        }
        .strength-strong { 
            background: rgba(52, 199, 89, 0.1);
            color: var(--accent-success);
        }
        
        /* Alertes Minimalistes */
        .alert {
            border-radius: var(--radius-medium);
            border: none;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(52, 199, 89, 0.1);
            color: var(--accent-success);
            border-left: 3px solid var(--accent-success);
        }
        
        .alert-danger {
            background: rgba(255, 59, 48, 0.1);
            color: var(--accent-danger);
            border-left: 3px solid var(--accent-danger);
        }
        
        .alert-info {
            background: rgba(0, 122, 255, 0.1);
            color: var(--accent-primary);
            border-left: 3px solid var(--accent-primary);
        }
        
        /* Informations du Compte Minimaliste */
        .account-info {
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            padding: 20px;
            margin-top: 16px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-lighter);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--text-tertiary);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .info-value {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .info-value.success {
            color: var(--accent-success);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header {
                padding: 32px 24px;
            }
            
            .header-title {
                font-size: 2rem;
            }
            
            .profile-header {
                padding: 32px 24px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 16px;
            }
            
            .card-body {
                padding: 20px;
            }
        }
        
        /* Scrollbar Personnalisée */
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
    <div class="client-container">
        <div class="container">
            <!-- Header Principal -->
            <div class="main-header animate-fade-in">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h1 class="header-title">Mon Profil</h1>
                    <p class="header-subtitle">Gérez vos informations personnelles et la sécurité de votre compte</p>
                </div>
            </div>
            
            <!-- Navigation Client -->
            <div class="client-nav animate-fade-in">
                <nav class="nav nav-pills justify-content-center">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="commandes.php">
                        <i class="fas fa-shopping-cart me-2"></i>Mes Commandes
                    </a>
                    <a class="nav-link" href="../commander.php">
                        <i class="fas fa-plus me-2"></i>Nouvelle Commande
                    </a>
                    <a class="nav-link" href="tickets.php">
                        <i class="fas fa-ticket-alt me-2"></i>Support
                    </a>
                    <a class="nav-link active" href="profil.php">
                        <i class="fas fa-user-cog me-2"></i>Mon Profil
                    </a>
                </nav>
                
                <!-- Informations utilisateur -->
                <div class="d-flex justify-content-end align-items-center mt-3">
                    <span class="text-muted me-3">
                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                    </a>
                </div>
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
            <div class="profile-header animate-fade-in">
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
                    <div class="card animate-fade-in">
                        <div class="card-header">
                            <h5 class="mb-0">
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
                    <div class="card animate-fade-in">
                        <div class="card-header">
                            <h5 class="mb-0">
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
                    <div class="card mt-4 animate-fade-in">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Informations du Compte
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="account-info">
                                <div class="info-row">
                                    <span class="info-label">Statut du compte</span>
                                    <span class="info-value success">
                                        <i class="fas fa-check-circle me-1"></i>Actif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Dernière connexion</span>
                                    <span class="info-value">
                                        <?php echo $currentUser['last_login'] ? date('d/m/Y H:i', strtotime($currentUser['last_login'])) : 'Jamais'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
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