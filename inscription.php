<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Rediriger si déjà connecté
if (isUserLoggedIn()) {
    redirect('client/dashboard.php');
}

$success = '';
$error = '';

// Traitement de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $firstName = cleanInput($_POST['first_name']);
    $lastName = cleanInput($_POST['last_name']);
    $phone = cleanInput($_POST['phone']);
    $country = cleanInput($_POST['country']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($firstName) || empty($lastName)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!isValidEmail($email)) {
        $error = 'Veuillez entrer une adresse email valide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($username) < 3) {
        $error = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
    } else {
        // Créer l'utilisateur
        $userId = createUser($username, $email, $password, $firstName, $lastName, $phone, $country);
        
        if ($userId) {
            $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            
            // Envoyer un email de bienvenue
            $emailSubject = "Bienvenue sur SMM Pro - $firstName !";
            $emailMessage = "
                <h2>Bienvenue sur SMM Pro !</h2>
                <p>Bonjour $firstName,</p>
                <p>Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter et commencer à utiliser nos services.</p>
                <p><strong>Nom d'utilisateur :</strong> $username</p>
                <p><strong>Email :</strong> $email</p>
                <p>Merci de nous faire confiance pour vos besoins en marketing sur les réseaux sociaux !</p>
                <p>Cordialement,<br>L'équipe SMM Pro</p>
            ";
            
            sendEmailNotification($email, $emailSubject, $emailMessage);
        } else {
            $error = 'Erreur lors de l\'inscription. L\'email ou le nom d\'utilisateur existe peut-être déjà.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - SMM Pro</title>
    <meta name="description" content="Créez votre compte SMM Pro et accédez à nos services de marketing sur les réseaux sociaux.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 100%);
            padding: 40px 20px;
        }
        
        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-logo {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .auth-title {
            color: var(--text-primary);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating .form-control {
            background: var(--darker-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .form-floating .form-control:focus {
            background: var(--darker-bg);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 136, 0.25);
        }
        
        .form-floating label {
            color: var(--text-secondary);
        }
        
        .btn-auth {
            background: var(--primary-color);
            border: none;
            color: var(--dark-bg);
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-auth:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            color: var(--dark-bg);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .auth-footer a:hover {
            color: var(--secondary-color);
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }
        
        .strength-weak { color: var(--danger-color); }
        .strength-medium { color: var(--warning-color); }
        .strength-strong { color: var(--success-color); }
        
        .requirements {
            background: var(--darker-bg);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid var(--border-color);
        }
        
        .requirements h6 {
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .requirements ul {
            color: var(--text-secondary);
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .requirements li {
            margin-bottom: 5px;
        }
    </style>
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
                        <a class="nav-link" href="commander.php">Commander</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="support.php">Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="connexion.php">Connexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="auth-container">
        <div class="container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h1 class="auth-title">Créer un Compte</h1>
                    <p class="auth-subtitle">Rejoignez SMM Pro et boostez votre présence en ligne</p>
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
                
                <form method="POST" id="registrationForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       placeholder="Prénom" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                                <label for="first_name">Prénom *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       placeholder="Nom" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                                <label for="last_name">Nom *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Nom d'utilisateur" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        <label for="username">Nom d'utilisateur *</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <label for="email">Email *</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Mot de passe" required>
                        <label for="password">Mot de passe *</label>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirmer le mot de passe" required>
                        <label for="confirm_password">Confirmer le mot de passe *</label>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       placeholder="Téléphone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                <label for="phone">Téléphone</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="country" name="country" 
                                       placeholder="Pays" value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>">
                                <label for="country">Pays</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="requirements">
                        <h6><i class="fas fa-info-circle me-2"></i>Exigences du mot de passe :</h6>
                        <ul>
                            <li>Au moins 6 caractères</li>
                            <li>Lettres et chiffres recommandés</li>
                            <li>Caractères spéciaux recommandés</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="btn btn-auth">
                        <i class="fas fa-user-plus me-2"></i>Créer mon Compte
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous ici</a></p>
                    <p><small class="text-muted">En créant un compte, vous acceptez nos <a href="#">conditions d'utilisation</a></small></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Vérification de la force du mot de passe
        document.getElementById('password').addEventListener('input', function() {
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
        
        // Validation du formulaire
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const username = document.getElementById('username').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                return false;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                alert('Le nom d\'utilisateur doit contenir au moins 3 caractères.');
                return false;
            }
        });
    </script>
</body>
</html>