<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Rediriger si déjà connecté
if (isUserLoggedIn()) {
    redirect('client/dashboard.php');
}

$error = '';

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $user = authenticateUser($email, $password);
        
        if ($user) {
            // Créer la session utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_username'] = $user['username'];
            
            // Rediriger vers le dashboard
            redirect('client/dashboard.php');
        } else {
            $error = 'Email/nom d\'utilisateur ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SMM Pro</title>
    <meta name="description" content="Connectez-vous à votre compte SMM Pro pour accéder à vos services et commandes.">
    
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
            max-width: 450px;
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
        
        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password a:hover {
            color: var(--primary-color);
        }
        
        .social-login {
            margin-top: 30px;
            text-align: center;
        }
        
        .social-login p {
            color: var(--text-secondary);
            margin-bottom: 20px;
            position: relative;
        }
        
        .social-login p::before,
        .social-login p::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30%;
            height: 1px;
            background: var(--border-color);
        }
        
        .social-login p::before {
            left: 0;
        }
        
        .social-login p::after {
            right: 0;
        }
        
        .btn-social {
            background: var(--darker-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 10px 20px;
            border-radius: 8px;
            margin: 0 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-social:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
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
                        <a class="nav-link active" href="connexion.php">Connexion</a>
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
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h1 class="auth-title">Connexion</h1>
                    <p class="auth-subtitle">Accédez à votre compte SMM Pro</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="email" name="email" 
                               placeholder="Email ou nom d'utilisateur" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <label for="email">Email ou nom d'utilisateur *</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Mot de passe" required>
                        <label for="password">Mot de passe *</label>
                    </div>
                    
                    <div class="forgot-password">
                        <a href="#" onclick="alert('Fonctionnalité à venir : réinitialisation du mot de passe')">
                            <i class="fas fa-question-circle me-1"></i>Mot de passe oublié ?
                        </a>
                    </div>
                    
                    <button type="submit" class="btn btn-auth">
                        <i class="fas fa-sign-in-alt me-2"></i>Se Connecter
                    </button>
                </form>
                
                <div class="social-login">
                    <p>Ou connectez-vous avec</p>
                    <a href="#" class="btn-social" onclick="alert('Fonctionnalité à venir')">
                        <i class="fab fa-google me-2"></i>Google
                    </a>
                    <a href="#" class="btn-social" onclick="alert('Fonctionnalité à venir')">
                        <i class="fab fa-facebook me-2"></i>Facebook
                    </a>
                </div>
                
                <div class="auth-footer">
                    <p>Vous n'avez pas de compte ? <a href="inscription.php">Inscrivez-vous ici</a></p>
                    <p><small class="text-muted">En vous connectant, vous acceptez nos <a href="#">conditions d'utilisation</a></small></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Validation du formulaire
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email) {
                e.preventDefault();
                alert('Veuillez saisir votre email ou nom d\'utilisateur.');
                return false;
            }
            
            if (!password) {
                e.preventDefault();
                alert('Veuillez saisir votre mot de passe.');
                return false;
            }
        });
        
        // Focus automatique sur le premier champ
        document.getElementById('email').focus();
    </script>
</body>
</html>