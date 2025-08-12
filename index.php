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
    <title>SMM Pro - Services de Marketing sur les Réseaux Sociaux</title>
    <meta name="description" content="Boostez votre présence sur Instagram, TikTok, YouTube et Facebook avec nos services SMM professionnels. Followers, likes et vues de qualité.">
    
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
            --bg-dark: #1d1d1f;
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --text-tertiary: #6e6e73;
            --text-light: #ffffff;
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
            overflow-x: hidden;
        }
        
        /* Navigation Minimaliste */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-lighter);
            padding: 16px 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--accent-primary) !important;
            background: rgba(0, 122, 255, 0.04);
            transform: translateY(-1px);
        }
        
        .navbar-nav .btn {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white !important;
            border-radius: var(--radius-medium);
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .navbar-nav .btn:hover {
            background: #0056cc;
            border-color: #0056cc;
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
        }
        
        .dropdown-menu {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-medium);
            box-shadow: var(--shadow-large);
            padding: 8px 0;
        }
        
        .dropdown-item {
            color: var(--text-primary);
            padding: 8px 20px;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(0, 122, 255, 0.04);
            color: var(--accent-primary);
        }
        
        .navbar-toggler {
            border: none;
            padding: 4px 8px;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        /* Sections Générales */
        .section {
            padding: 80px 0;
        }
        
        .section-header {
            margin-bottom: 60px;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        
        .section-subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Boutons Minimalistes */
        .btn {
            border-radius: var(--radius-medium);
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            padding: 12px 24px;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
            text-decoration: none;
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
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }
        
        .btn-lg {
            padding: 16px 32px;
            font-size: 1.125rem;
        }
        
        /* Animations */
        .fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
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
        
        /* Hero Section Minimaliste */
        .hero {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-content {
            animation: fadeInLeft 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 24px;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            line-height: 1.1;
        }
        
        .text-gradient {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 32px;
            line-height: 1.6;
        }
        
        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .hero-visual {
            animation: fadeInRight 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .floating-icons {
            position: relative;
            height: 400px;
        }
        
        .icon-item {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: var(--bg-primary);
            border-radius: var(--radius-large);
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--border-lighter);
            transition: all 0.3s ease;
        }
        
        .icon-item:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-large);
        }
        
        .icon-item i {
            font-size: 2rem;
            color: var(--accent-primary);
        }
        
        .icon-item span {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        .icon-item.instagram {
            top: 20px;
            left: 20px;
            animation: float 6s ease-in-out infinite;
        }
        
        .icon-item.tiktok {
            top: 100px;
            right: 40px;
            animation: float 6s ease-in-out infinite 1s;
        }
        
        .icon-item.youtube {
            bottom: 120px;
            left: 60px;
            animation: float 6s ease-in-out infinite 2s;
        }
        
        .icon-item.facebook {
            bottom: 40px;
            right: 20px;
            animation: float 6s ease-in-out infinite 3s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Services Section Minimaliste */
        .service-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2rem;
            color: white;
        }
        
        .service-card h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        
        .service-card p {
            color: var(--text-secondary);
            margin-bottom: 24px;
            line-height: 1.6;
        }
        
        /* Features Section Minimaliste */
        .bg-dark {
            background: var(--bg-dark) !important;
        }
        
        .feature-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2rem;
            color: white;
        }
        
        .feature-card h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        
        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        /* About Section Minimaliste */
        .about-stats {
            margin-top: 32px;
        }
        
        .stat-item {
            text-align: center;
            padding: 24px;
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            border: 1px solid var(--border-lighter);
        }
        
        .stat-item h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-primary);
            margin-bottom: 8px;
        }
        
        .stat-item p {
            color: var(--text-secondary);
            font-weight: 500;
            margin: 0;
        }
        
        .about-image img {
            border-radius: var(--radius-large);
            box-shadow: var(--shadow-medium);
        }
        
        .about-content .lead {
            font-size: 1.125rem;
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .about-content p {
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        /* Contact Section Minimaliste */
        .contact-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        
        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .contact-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2rem;
            color: white;
        }
        
        .contact-card h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        
        .contact-card p {
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .contact-card small {
            color: var(--text-tertiary);
            font-size: 0.875rem;
        }
        
        /* Footer Minimaliste */
        .footer {
            background: var(--bg-dark);
            color: var(--text-light);
            padding: 60px 0 30px;
        }
        
        .footer h5, .footer h6 {
            color: var(--text-light);
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .footer p {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .footer ul {
            list-style: none;
            padding: 0;
        }
        
        .footer ul li {
            margin-bottom: 12px;
        }
        
        .footer ul li a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .footer ul li a:hover {
            color: var(--accent-primary);
        }
        
        .footer hr {
            border-color: var(--border-light);
            margin: 40px 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .section {
                padding: 60px 0;
            }
            
            .hero {
                padding: 100px 0 60px;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .btn-lg {
                padding: 14px 28px;
                font-size: 1rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .floating-icons {
                height: 300px;
                margin-top: 40px;
            }
            
            .icon-item {
                padding: 16px;
            }
            
            .icon-item i {
                font-size: 1.5rem;
            }
            
            .about-stats .row {
                gap: 16px;
            }
            
            .stat-item {
                padding: 20px;
            }
            
            .stat-item h3 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-rocket me-2"></i>SMM Pro
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">À Propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
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

    <!-- Hero Section Minimaliste -->
    <section id="home" class="hero">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            Boostez votre <span class="text-gradient">Présence en Ligne</span>
                        </h1>
                        <p class="hero-subtitle">
                            Services SMM professionnels pour Instagram, TikTok, YouTube et Facebook. 
                            Obtenez des followers, likes et vues de qualité pour augmenter votre visibilité.
                        </p>
                        <div class="hero-buttons">
                            <?php if (isUserLoggedIn()): ?>
                                <a href="client/dashboard.php" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-tachometer-alt me-2"></i>Mon Dashboard
                                </a>
                            <?php else: ?>
                                <a href="inscription.php" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-user-plus me-2"></i>Commencer Maintenant
                                </a>
                            <?php endif; ?>
                            <a href="commander.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-shopping-cart me-2"></i>Voir les Services
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-visual">
                        <div class="floating-icons">
                            <div class="icon-item instagram">
                                <i class="fab fa-instagram"></i>
                                <span>Instagram</span>
                            </div>
                            <div class="icon-item tiktok">
                                <i class="fab fa-tiktok"></i>
                                <span>TikTok</span>
                            </div>
                            <div class="icon-item youtube">
                                <i class="fab fa-youtube"></i>
                                <span>YouTube</span>
                            </div>
                            <div class="icon-item facebook">
                                <i class="fab fa-facebook"></i>
                                <span>Facebook</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

        <!-- Services Section -->
    <section id="services" class="section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Nos Services SMM</h2>
                <p class="section-subtitle">
                    Choisissez parmi nos services de qualité pour booster votre présence sur les réseaux sociaux
                </p>
            </div>
            
            <div class="row">
                <?php foreach ($categories as $category): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="service-card fade-in-up">
                            <div class="service-icon">
                                <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                            </div>
                            <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                            <p><?php echo htmlspecialchars($category['description']); ?></p>
                            <a href="commander.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-primary">
                                Voir les Services
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="section bg-dark">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Pourquoi Choisir SMM Pro ?</h2>
                <p class="section-subtitle">
                    Découvrez les avantages de nos services professionnels
                </p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Sécurité Garantie</h4>
                        <p>Vos comptes sont protégés avec nos méthodes sécurisées et respectueuses des plateformes.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h4>Livraison Rapide</h4>
                        <p>Recevez vos followers, likes et vues dans les délais indiqués, généralement entre 1h et 72h.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Support 24/7</h4>
                        <p>Notre équipe support est disponible 24h/24 et 7j/7 pour vous assister.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Qualité Premium</h4>
                        <p>Des services de haute qualité pour des résultats durables et visibles.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Paiement Sécurisé</h4>
                        <p>Paiements sécurisés via MTN Money et Moov Money avec suivi en temps réel.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Communauté Active</h4>
                        <p>Rejoignez notre communauté de clients satisfaits et boostez votre visibilité.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="about" class="section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-content fade-in-up">
                        <h2 class="section-title">À Propos de SMM Pro</h2>
                        <p class="lead">
                            SMM Pro est votre partenaire de confiance pour le marketing sur les réseaux sociaux. 
                            Nous offrons des services de qualité pour aider les entreprises et particuliers à 
                            accroître leur visibilité en ligne.
                        </p>
                        <p>
                            Avec des années d'expérience dans le domaine, nous comprenons l'importance d'une 
                            présence forte sur les réseaux sociaux pour le succès de votre entreprise.
                        </p>
                        <div class="about-stats">
                            <div class="row">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <h3>1000+</h3>
                                        <p>Clients Satisfaits</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <h3>50K+</h3>
                                        <p>Commandes Traitées</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-image fade-in-up">
                        <img src="https://via.placeholder.com/500x400/f5f5f7/007aff?text=SMM+Pro" 
                             alt="SMM Pro" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact Section -->
    <section id="contact" class="section bg-dark">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Contactez-Nous</h2>
                <p class="section-subtitle">
                    Notre équipe est là pour vous aider. N'hésitez pas à nous contacter !
                </p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="contact-card fade-in-up">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h4>WhatsApp</h4>
                        <p>+225 0123456789</p>
                        <small>Réponse immédiate</small>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="contact-card fade-in-up">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email</h4>
                        <p>contact@smmpro.com</p>
                        <small>Réponse sous 24h</small>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="contact-card fade-in-up">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Disponibilité</h4>
                        <p>24h/24 - 7j/7</p>
                        <small>Support permanent</small>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="support.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-headset me-2"></i>Créer un Ticket de Support
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-rocket me-2"></i>SMM Pro</h5>
                    <p>Votre partenaire de confiance pour le marketing sur les réseaux sociaux. 
                    Services de qualité, livraison rapide et support 24/7.</p>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6>Services</h6>
                    <ul class="list-unstyled">
                        <li><a href="commander.php">Commander</a></li>
                        <li><a href="support.php">Support</a></li>
                        <li><a href="#about">À Propos</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6>Plateformes</h6>
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
                        <li><a href="#contact">Contact</a></li>
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

        document.querySelectorAll('.service-card, .feature-card, .contact-card').forEach(el => {
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