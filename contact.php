<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    
    if (empty($nom) || empty($email) || empty($sujet) || empty($message_text)) {
        $message = 'Veuillez remplir tous les champs.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Veuillez entrer une adresse email valide.';
        $messageType = 'danger';
    } else {
        // Ici vous pouvez ajouter l'envoi d'email ou l'enregistrement en base
        $message = 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.';
        $messageType = 'success';
        
        // Réinitialiser les champs
        $nom = $email = $sujet = $message_text = '';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - SMM Pro</title>
    <meta name="description" content="Contactez l'équipe SMM Pro pour toute question ou demande de support. Nous sommes là pour vous aider.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --bg-dark: #0f172a;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --bg-gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --bg-gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            --text-light: #ffffff;
            --accent-primary: #3b82f6;
            --accent-secondary: #8b5cf6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --accent-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            --accent-gradient-2: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            --accent-gradient-3: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --border-light: #e2e8f0;
            --border-lighter: #f1f5f9;
            --shadow-subtle: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-large: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-glow: 0 0 20px rgba(59, 130, 246, 0.15);
            --radius-small: 8px;
            --radius-medium: 12px;
            --radius-large: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
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
            overflow-x: hidden;
        }
        
        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-light);
            padding: 16px 0;
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-primary) !important;
            text-decoration: none;
        }
        
        .navbar-brand i {
            color: var(--accent-primary);
        }
        
        .navbar-nav .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 8px 16px;
            margin: 0 4px;
            border-radius: var(--radius-medium);
            transition: all 0.2s ease;
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--accent-primary) !important;
            background: rgba(59, 130, 246, 0.05);
        }
        
        /* Hero Section Contact */
        .contact-hero {
            background: var(--bg-gradient-3);
            padding: 120px 0 80px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .contact-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><defs><radialGradient id="hero" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="150" fill="url(%23hero)"/><circle cx="1000" cy="300" r="200" fill="url(%23hero)"/><circle cx="400" cy="700" r="180" fill="url(%23hero)"/></svg>') no-repeat;
            background-size: cover;
            opacity: 0.8;
            animation: float 25s ease-in-out infinite;
        }
        
        .contact-hero-content {
            position: relative;
            z-index: 2;
        }
        
        .contact-hero h1 {
            font-size: clamp(3rem, 10vw, 4.5rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            letter-spacing: -0.03em;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .contact-hero p {
            font-size: clamp(1.2rem, 4vw, 1.5rem);
            margin-bottom: 2rem;
            opacity: 0.95;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Section Contact */
        .contact-section {
            padding: 100px 0;
            background: var(--bg-primary);
        }
        
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .contact-info {
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            padding: 50px 40px;
            border: 1px solid var(--border-light);
        }
        
        .contact-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--bg-primary);
            border-radius: var(--radius-large);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }
        
        .contact-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
            border-color: var(--accent-primary);
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .contact-details h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .contact-details p, .contact-details a {
            color: var(--text-secondary);
            text-decoration: none;
            margin-bottom: 5px;
            display: block;
        }
        
        .contact-details a:hover {
            color: var(--accent-primary);
        }
        
        /* Formulaire de Contact */
        .contact-form {
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            padding: 50px 40px;
            border: 1px solid var(--border-light);
        }
        
        .contact-form h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-medium);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-control::placeholder {
            color: var(--text-tertiary);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-submit {
            background: var(--accent-gradient);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: var(--radius-medium);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
        }
        
        /* Section FAQ Rapide */
        .faq-section {
            padding: 100px 0;
            background: var(--bg-secondary);
        }
        
        .faq-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .faq-item {
            background: var(--bg-primary);
            border-radius: var(--radius-large);
            margin-bottom: 20px;
            border: 1px solid var(--border-light);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            box-shadow: var(--shadow-medium);
            border-color: var(--accent-primary);
        }
        
        .faq-question {
            padding: 25px 30px;
            background: var(--bg-primary);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background: var(--bg-tertiary);
        }
        
        .faq-question i {
            color: var(--accent-primary);
            transition: transform 0.3s ease;
        }
        
        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            padding: 0 30px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .faq-item.active .faq-answer {
            padding: 0 30px 25px;
            max-height: 200px;
        }
        
        /* Section CTA */
        .cta-section {
            padding: 100px 0;
            background: var(--accent-gradient);
            text-align: center;
            color: white;
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .cta-title {
            font-size: clamp(2.5rem, 8vw, 3.5rem);
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .cta-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .cta-btn {
            display: inline-block;
            padding: 18px 36px;
            background: white;
            color: var(--accent-primary);
            text-decoration: none;
            border-radius: var(--radius-large);
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-large);
            color: var(--accent-primary);
        }
        
        /* Footer */
        .footer {
            background: var(--bg-dark);
            color: var(--text-light);
            padding: 60px 0 30px;
        }
        
        .footer h6 {
            color: var(--accent-primary);
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .footer a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer a:hover {
            color: var(--accent-primary);
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .contact-item, .faq-item {
            animation: fadeInUp 0.8s ease-out both;
        }
        
        .contact-item:nth-child(1) { animation-delay: 0.1s; }
        .contact-item:nth-child(2) { animation-delay: 0.2s; }
        .contact-item:nth-child(3) { animation-delay: 0.3s; }
        .contact-item:nth-child(4) { animation-delay: 0.4s; }
        
        .faq-item:nth-child(1) { animation-delay: 0.1s; }
        .faq-item:nth-child(2) { animation-delay: 0.2s; }
        .faq-item:nth-child(3) { animation-delay: 0.3s; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .contact-hero {
                padding: 100px 0 60px;
            }
            
            .contact-container {
                grid-template-columns: 1fr;
                gap: 40px;
                padding: 0 15px;
            }
            
            .contact-info, .contact-form {
                padding: 40px 25px;
            }
            
            .faq-container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-rocket me-2"></i>SMM Pro
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">À Propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isUserLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="commander.php">
                                <i class="fas fa-shopping-cart me-1"></i>Commander
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="client/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="client/commandes.php">
                                    <i class="fas fa-shopping-cart me-2"></i>Mes Commandes
                                </a></li>
                                <li><a class="dropdown-item" href="client/tickets.php">
                                    <i class="fas fa-ticket-alt me-2"></i>Support
                                </a></li>
                                <li><a class="dropdown-item" href="client/profil.php">
                                    <i class="fas fa-user-cog me-2"></i>Mon Profil
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="client/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="commander.php">
                                <i class="fas fa-shopping-cart me-1"></i>Commander
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="connexion.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary btn-sm" href="inscription.php">
                                <i class="fas fa-user-plus me-1"></i>Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section Contact -->
    <section class="contact-hero">
        <div class="contact-hero-content">
            <div class="container">
                <h1>Contactez-Nous</h1>
                <p>
                    Nous sommes là pour vous aider ! Contactez notre équipe d'experts 
                    pour toute question ou demande de support.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Contact -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-container">
                <!-- Informations de Contact -->
                <div class="contact-info">
                    <h3>Informations de Contact</h3>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Adresse</h4>
                            <p>123 Rue du Marketing Digital<br>75001 Paris, France</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Téléphone</h4>
                            <a href="tel:+33123456789">+33 1 23 45 67 89</a>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <a href="mailto:contact@smmpro.com">contact@smmpro.com</a>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Horaires</h4>
                            <p>Lundi - Vendredi : 9h - 18h<br>Samedi : 10h - 16h</p>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire de Contact -->
                <div class="contact-form">
                    <h3>Envoyez-nous un Message</h3>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nom" class="form-label">Nom Complet *</label>
                            <input type="text" id="nom" name="nom" class="form-control" 
                                   value="<?php echo htmlspecialchars($nom ?? ''); ?>" 
                                   placeholder="Votre nom complet" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                   placeholder="votre@email.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="sujet" class="form-label">Sujet *</label>
                            <input type="text" id="sujet" name="sujet" class="form-control" 
                                   value="<?php echo htmlspecialchars($sujet ?? ''); ?>" 
                                   placeholder="Sujet de votre message" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="form-label">Message *</label>
                            <textarea id="message" name="message" class="form-control" 
                                      placeholder="Votre message..." required><?php echo htmlspecialchars($message_text ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer le Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Section FAQ Rapide -->
    <section class="faq-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold text-primary mb-3">Questions Fréquentes</h2>
                <p class="lead text-muted">
                    Trouvez rapidement des réponses à vos questions
                </p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Comment puis-je suivre ma commande ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Connectez-vous à votre compte client et allez dans la section "Mes Commandes". 
                        Vous y trouverez le statut détaillé de toutes vos commandes en cours.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Quels sont vos délais de livraison ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Nos délais varient selon le service choisi : followers (24-48h), 
                        likes (1-2h), vues (2-6h). La livraison commence généralement 
                        dans les heures qui suivent votre commande.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Comment contacter le support technique ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Vous pouvez nous contacter via ce formulaire, par email à 
                        support@smmpro.com, ou créer un ticket depuis votre dashboard client. 
                        Notre équipe répond sous 24h.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section CTA -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Besoin d'Aide Immédiate ?</h2>
            <p class="cta-subtitle">
                Notre équipe d'experts est disponible pour vous accompagner 
                dans votre succès sur les réseaux sociaux.
            </p>
            <a href="commander.php" class="cta-btn">
                <i class="fas fa-rocket me-2"></i>Commander Maintenant
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h6><i class="fas fa-rocket me-2"></i>SMM Pro</h6>
                    <p>Services SMM professionnels pour booster votre présence sur les réseaux sociaux. Qualité garantie et résultats visibles.</p>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6>Services</h6>
                    <ul class="list-unstyled">
                        <li><a href="commander.php">Commander</a></li>
                        <li><a href="services.php">Nos Services</a></li>
                        <li><a href="about.php">À Propos</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6>Réseaux</h6>
                    <ul class="list-unstyled">
                        <li><a href="commander.php?category=1">Instagram</a></li>
                        <li><a href="commander.php?category=2">TikTok</a></li>
                        <li><a href="commander.php?category=3">YouTube</a></li>
                        <li><a href="commander.php?category=4">Facebook</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6>Compte</h6>
                    <ul class="list-unstyled">
                        <?php if (isUserLoggedIn()): ?>
                            <li><a href="client/dashboard.php">Dashboard</a></li>
                            <li><a href="client/profil.php">Mon Profil</a></li>
                            <li><a href="client/logout.php">Déconnexion</a></li>
                        <?php else: ?>
                            <li><a href="connexion.php">Connexion</a></li>
                            <li><a href="inscription.php">Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="support.php">FAQ</a></li>
                        <li><a href="support.php">Tickets</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 SMM Pro. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-muted me-3">Conditions d'utilisation</a>
                    <a href="#" class="text-muted">Politique de confidentialité</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Smooth scrolling pour les ancres
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // FAQ Interactive
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const faqItem = question.parentElement;
                const isActive = faqItem.classList.contains('active');
                
                // Fermer toutes les autres FAQ
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Ouvrir/fermer la FAQ cliquée
                if (!isActive) {
                    faqItem.classList.add('active');
                }
            });
        });
        
        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.contact-item, .faq-item').forEach(el => {
            observer.observe(el);
        });
        
        // Navigation active
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= (sectionTop - 200)) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>