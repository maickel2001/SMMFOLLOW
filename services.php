<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$categories = getAllCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Services SMM - SMM Pro</title>
    <meta name="description" content="Découvrez tous nos services SMM professionnels pour Instagram, TikTok, YouTube et Facebook. Followers, likes et vues de qualité.">
    
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
        
        /* Hero Section Services */
        .services-hero {
            background: var(--bg-gradient);
            padding: 120px 0 80px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .services-hero::before {
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
        
        .services-hero-content {
            position: relative;
            z-index: 2;
        }
        
        .services-hero h1 {
            font-size: clamp(3rem, 10vw, 4.5rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            letter-spacing: -0.03em;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .services-hero p {
            font-size: clamp(1.2rem, 4vw, 1.5rem);
            margin-bottom: 2rem;
            opacity: 0.95;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Section Services */
        .services-section {
            padding: 100px 0;
            background: var(--bg-primary);
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .service-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            padding: 50px 30px;
            text-align: center;
            border: 1px solid var(--border-light);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .service-card:hover::before {
            left: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: var(--accent-gradient);
            color: white;
            position: relative;
        }
        
        .service-icon::after {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid transparent;
            border-radius: 50%;
            background: var(--accent-gradient) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .service-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        .service-description {
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        
        .service-btn {
            display: inline-block;
            padding: 14px 28px;
            background: var(--accent-gradient);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-medium);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .service-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
            color: white;
        }
        
        /* Section Réseaux Sociaux */
        .social-networks-section {
            padding: 100px 0;
            background: var(--bg-secondary);
            position: relative;
        }
        
        .social-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .social-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 50px 30px;
            text-align: center;
            box-shadow: var(--shadow-medium);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-lighter);
        }
        
        .social-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.6s ease;
        }
        
        .social-card:hover::before {
            left: 100%;
        }
        
        .social-card:hover {
            transform: translateY(-12px) scale(1.03);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .social-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .social-icon.instagram {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            color: white;
        }
        
        .social-icon.tiktok {
            background: linear-gradient(45deg, #000000 0%, #25f4ee 50%, #fe2c55 100%);
            color: white;
        }
        
        .social-icon.youtube {
            background: linear-gradient(45deg, #ff0000 0%, #ff4444 100%);
            color: white;
        }
        
        .social-icon.facebook {
            background: linear-gradient(45deg, #1877f2 0%, #42a5f5 100%);
            color: white;
        }
        
        .social-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 15px;
        }
        
        .social-description {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .social-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 25px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-primary);
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
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
        
        .service-card, .social-card {
            animation: fadeInUp 0.8s ease-out both;
        }
        
        .service-card:nth-child(1) { animation-delay: 0.1s; }
        .service-card:nth-child(2) { animation-delay: 0.2s; }
        .service-card:nth-child(3) { animation-delay: 0.3s; }
        .service-card:nth-child(4) { animation-delay: 0.4s; }
        
        .social-card:nth-child(1) { animation-delay: 0.1s; }
        .social-card:nth-child(2) { animation-delay: 0.2s; }
        .social-card:nth-child(3) { animation-delay: 0.3s; }
        .social-card:nth-child(4) { animation-delay: 0.4s; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .services-hero {
                padding: 100px 0 60px;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 0 15px;
            }
            
            .social-grid {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 0 15px;
            }
            
            .service-card, .social-card {
                padding: 40px 20px;
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
                        <a class="nav-link active" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">À Propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
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
                                <li><a class="nav-item dropdown">
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

    <!-- Hero Section Services -->
    <section class="services-hero">
        <div class="services-hero-content">
            <div class="container">
                <h1>Nos Services SMM</h1>
                <p>
                    Découvrez notre gamme complète de services SMM professionnels pour booster 
                    votre présence sur tous les réseaux sociaux populaires.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Services -->
    <section class="services-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold text-primary mb-3">Services Disponibles</h2>
                <p class="lead text-muted">
                    Choisissez parmi nos services de qualité pour chaque réseau social
                </p>
            </div>
            
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="service-title">Followers</h3>
                    <p class="service-description">
                        Augmentez votre nombre de followers avec des comptes authentiques et engagés. 
                        Idéal pour booster votre crédibilité et votre visibilité.
                    </p>
                    <a href="commander.php" class="service-btn">
                        <i class="fas fa-rocket me-2"></i>Commander
                    </a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="service-title">Likes</h3>
                    <p class="service-description">
                        Boostez l'engagement de vos publications avec des likes de qualité. 
                        Améliorez votre algorithme et augmentez votre portée.
                    </p>
                    <a href="commander.php" class="service-btn">
                        <i class="fas fa-rocket me-2"></i>Commander
                    </a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="service-title">Vues</h3>
                    <p class="service-description">
                        Augmentez le nombre de vues de vos vidéos et stories. 
                        Parfait pour TikTok, YouTube et Instagram Reels.
                    </p>
                    <a href="commander.php" class="service-btn">
                        <i class="fas fa-rocket me-2"></i>Commander
                    </a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <h3 class="service-title">Commentaires</h3>
                    <p class="service-description">
                        Ajoutez des commentaires authentiques et engageants à vos publications. 
                        Stimulez les interactions et la discussion.
                    </p>
                    <a href="commander.php" class="service-btn">
                        <i class="fas fa-rocket me-2"></i>Commander
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Réseaux Sociaux -->
    <section class="social-networks-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold text-primary mb-3">Réseaux Supportés</h2>
                <p class="lead text-muted">
                    Nous supportons toutes les plateformes populaires
                </p>
            </div>
            
            <div class="social-grid">
                <div class="social-card">
                    <div class="social-icon instagram">
                        <i class="fab fa-instagram"></i>
                    </div>
                    <h3 class="social-title">Instagram</h3>
                    <p class="social-description">
                        Boostez votre compte Instagram avec des followers, likes et commentaires authentiques.
                    </p>
                    <div class="social-stats">
                        <div class="stat-item">
                            <span class="stat-number">50K+</span>
                            <span class="stat-label">Followers</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">100K+</span>
                            <span class="stat-label">Likes</span>
                        </div>
                    </div>
                    <a href="commander.php?category=1" class="service-btn">
                        <i class="fas fa-rocket me-2"></i>Commander
                    </a>
                </div>
                
                <div class="social-card">
                    <div class="social-icon tiktok">
                        <i class="fab fa-tiktok"></i>
                    </div>
                    <h3 class="social-title">TikTok</h3>
                    <p class="social-description">
                        Propulsez vos vidéos TikTok avec des vues, likes et followers pour maximiser votre portée.
                    </p>
                    <div class="social-stats">
                        <div class="stat-item">
                            <span class="stat-number">100K+</span>
                            <span class="stat-label">Vues</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">25K+</span>
                            <span class="stat-label">Likes</span>
                        </div>
                    </div>
                    <a href="commander.php?category=2" class="service-btn">
                        <i class="fas fa-rocket me-2"></i>Commander
                    </a>
                </div>
                
                <div class="social-card">
                    <div class="social-icon youtube">
                        <i class="fab fa-youtube"></i>
                    </div>
                    <h3 class="social-title">YouTube</h3>
                    <p class="social-description">
                        Développez votre chaîne YouTube avec des abonnés, vues et likes pour augmenter votre monétisation.
                    </p>
                    <div class="social-stats">
                        <div class="stat-item">
                            <span class="stat-number">10K+</span>
                            <span class="stat-label">Abonnés</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">500K+</span>
                            <span class="stat-label">Vues</span>
                        </div>
                    </div>
                    <a href="commander.php?category=3" class="service-btn">
                        <i class="fas fa-rocket me-2"></i>Commander
                    </a>
                </div>
                
                <div class="social-card">
                    <div class="social-icon facebook">
                        <i class="fab fa-facebook"></i>
                    </div>
                    <h3 class="social-title">Facebook</h3>
                    <p class="social-description">
                        Renforcez votre page Facebook avec des fans, likes et partages pour une meilleure engagement.
                    </p>
                    <div class="social-stats">
                        <div class="stat-item">
                            <span class="stat-number">20K+</span>
                            <span class="stat-label">Fans</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">75K+</span>
                            <span class="stat-label">Likes</span>
                        </div>
                    </div>
                    <a href="commander.php?category=4" class="service-btn">
                        <i class="fas fa-rocket me-2"></i>Commander
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section CTA -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Prêt à Booster Votre Présence ?</h2>
            <p class="cta-subtitle">
                Commencez dès aujourd'hui et transformez votre visibilité sur les réseaux sociaux
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

        document.querySelectorAll('.service-card, .social-card').forEach(el => {
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