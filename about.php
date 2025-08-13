<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À Propos - SMM Pro</title>
    <meta name="description" content="Découvrez l'histoire de SMM Pro, notre mission et notre équipe d'experts en marketing sur les réseaux sociaux.">
    
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
        
        /* Hero Section About */
        .about-hero {
            background: var(--bg-gradient-2);
            padding: 120px 0 80px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .about-hero::before {
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
        
        .about-hero-content {
            position: relative;
            z-index: 2;
        }
        
        .about-hero h1 {
            font-size: clamp(3rem, 10vw, 4.5rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            letter-spacing: -0.03em;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .about-hero p {
            font-size: clamp(1.2rem, 4vw, 1.5rem);
            margin-bottom: 2rem;
            opacity: 0.95;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Section Histoire */
        .story-section {
            padding: 100px 0;
            background: var(--bg-primary);
        }
        
        .story-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .story-content h2 {
            font-size: clamp(2.5rem, 6vw, 3.5rem);
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2rem;
            line-height: 1.2;
        }
        
        .story-content p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .story-image {
            position: relative;
            text-align: center;
        }
        
        .story-image img {
            width: 100%;
            max-width: 500px;
            height: auto;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-large);
        }
        
        .story-image::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background: var(--accent-gradient-3);
            border-radius: var(--radius-2xl);
            opacity: 0.3;
            z-index: -1;
            animation: pulse 4s ease-in-out infinite;
        }
        
        /* Section Mission & Valeurs */
        .mission-section {
            padding: 100px 0;
            background: var(--bg-secondary);
        }
        
        .mission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .mission-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 50px 30px;
            text-align: center;
            border: 1px solid var(--border-light);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .mission-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .mission-card:hover::before {
            left: 100%;
        }
        
        .mission-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .mission-icon {
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
        
        .mission-icon::after {
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
        
        .mission-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        .mission-text {
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 1.1rem;
        }
        
        /* Section Équipe */
        .team-section {
            padding: 100px 0;
            background: var(--bg-primary);
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .team-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            padding: 40px 30px;
            text-align: center;
            border: 1px solid var(--border-light);
            transition: all 0.4s ease;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .team-avatar {
            width: 120px;
            height: 120px;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            font-weight: 700;
        }
        
        .team-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .team-role {
            color: var(--accent-primary);
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .team-description {
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        /* Section Statistiques */
        .stats-section {
            padding: 100px 0;
            background: var(--accent-gradient);
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        
        .stat-item {
            position: relative;
        }
        
        .stat-number {
            font-size: clamp(2.5rem, 8vw, 4rem);
            font-weight: 800;
            margin-bottom: 10px;
            display: block;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Section CTA */
        .cta-section {
            padding: 100px 0;
            background: var(--bg-secondary);
            text-align: center;
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .cta-title {
            font-size: clamp(2.5rem, 8vw, 3.5rem);
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .cta-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }
        
        .cta-btn {
            display: inline-block;
            padding: 18px 36px;
            background: var(--accent-gradient);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-large);
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-glow);
            color: white;
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
        
        .mission-card, .team-card {
            animation: fadeInUp 0.8s ease-out both;
        }
        
        .mission-card:nth-child(1) { animation-delay: 0.1s; }
        .mission-card:nth-child(2) { animation-delay: 0.2s; }
        .mission-card:nth-child(3) { animation-delay: 0.3s; }
        
        .team-card:nth-child(1) { animation-delay: 0.1s; }
        .team-card:nth-child(2) { animation-delay: 0.2s; }
        .team-card:nth-child(3) { animation-delay: 0.3s; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .about-hero {
                padding: 100px 0 60px;
            }
            
            .story-container {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }
            
            .mission-grid {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 0 15px;
            }
            
            .team-grid {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 0 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
                padding: 0 15px;
            }
            
            .mission-card, .team-card {
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
                        <a class="nav-link" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">À Propos</a>
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

    <!-- Hero Section About -->
    <section class="about-hero">
        <div class="about-hero-content">
            <div class="container">
                <h1>À Propos de SMM Pro</h1>
                <p>
                    Découvrez notre histoire, notre mission et notre équipe d'experts 
                    dédiés à votre succès sur les réseaux sociaux.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Histoire -->
    <section class="story-section">
        <div class="container">
            <div class="story-container">
                <div class="story-content">
                    <h2>Notre Histoire</h2>
                    <p>
                        Fondée en 2020, SMM Pro est née de la vision de démocratiser l'accès 
                        aux services de marketing sur les réseaux sociaux. Nous avons constaté 
                        que de nombreux créateurs de contenu et entreprises avaient du mal à 
                        se faire remarquer dans un environnement numérique de plus en plus 
                        concurrentiel.
                    </p>
                    <p>
                        Notre équipe d'experts en marketing digital et en développement 
                        technologique s'est donnée pour mission de créer des solutions 
                        innovantes et accessibles pour booster la visibilité de nos clients.
                    </p>
                    <p>
                        Aujourd'hui, nous sommes fiers d'avoir aidé plus de 10 000 clients 
                        à atteindre leurs objectifs de croissance sur les réseaux sociaux, 
                        en leur fournissant des services de qualité et un support exceptionnel.
                    </p>
                </div>
                
                <div class="story-image">
                    <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 500 400'><defs><linearGradient id='storyImg' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' stop-color='%23f093fb'/><stop offset='100%' stop-color='%23f5576c'/></linearGradient></defs><rect width='500' height='400' fill='url(%23storyImg)' rx='20'/><circle cx='100' cy='100' r='30' fill='white' opacity='0.2'/><circle cx='400' cy='80' r='25' fill='white' opacity='0.15'/><circle cx='80' cy='300' r='35' fill='white' opacity='0.1'/><path d='M150 200 Q250 150 350 200 T450 250' stroke='white' stroke-width='3' fill='none' opacity='0.6'/><text x='250' y='220' text-anchor='middle' fill='white' font-family='Arial' font-size='24' font-weight='bold'>SMM Pro</text><text x='250' y='250' text-anchor='middle' fill='white' font-family='Arial' font-size='16'>Notre Histoire</text></svg>" alt="Histoire de SMM Pro" />
                </div>
            </div>
        </div>
    </section>

    <!-- Section Mission & Valeurs -->
    <section class="mission-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold text-primary mb-3">Notre Mission & Valeurs</h2>
                <p class="lead text-muted">
                    Les principes qui guident nos actions au quotidien
                </p>
            </div>
            
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 class="mission-title">Notre Mission</h3>
                    <p class="mission-text">
                        Aider les créateurs de contenu, influenceurs et entreprises à 
                        maximiser leur impact sur les réseaux sociaux en leur fournissant 
                        des services SMM de qualité, sécurisés et efficaces.
                    </p>
                </div>
                
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="mission-title">Notre Vision</h3>
                    <p class="mission-text">
                        Devenir la référence en matière de services SMM, en combinant 
                        innovation technologique, qualité de service et satisfaction client 
                        pour créer un écosystème digital florissant.
                    </p>
                </div>
                
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="mission-title">Nos Valeurs</h3>
                    <p class="mission-text">
                        Transparence, qualité, innovation et satisfaction client. Nous 
                        croyons en la création de relations durables basées sur la confiance 
                        et l'excellence opérationnelle.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Équipe -->
    <section class="team-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold text-primary mb-3">Notre Équipe</h2>
                <p class="lead text-muted">
                    Des experts passionnés à votre service
                </p>
            </div>
            
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">A</div>
                    <h4 class="team-name">Alexandre Martin</h4>
                    <p class="team-role">CEO & Fondateur</p>
                    <p class="team-description">
                        Expert en marketing digital avec plus de 10 ans d'expérience. 
                        Passionné par l'innovation et la croissance des entreprises.
                    </p>
                </div>
                
                <div class="team-card">
                    <div class="team-avatar">S</div>
                    <h4 class="team-name">Sarah Dubois</h4>
                    <p class="team-role">Directrice Marketing</p>
                    <p class="team-description">
                        Spécialiste en stratégie de contenu et en croissance sur les 
                        réseaux sociaux. Créative et orientée résultats.
                    </p>
                </div>
                
                <div class="team-card">
                    <div class="team-avatar">M</div>
                    <h4 class="team-name">Marc Leroy</h4>
                    <p class="team-role">CTO & Développeur</p>
                    <p class="team-description">
                        Développeur full-stack expérimenté, passionné par les nouvelles 
                        technologies et l'optimisation des processus.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Statistiques -->
    <section class="stats-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold text-white mb-3">Nos Chiffres Clés</h2>
                <p class="lead text-white opacity-75">
                    Des résultats qui parlent d'eux-mêmes
                </p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number">10K+</span>
                    <span class="stat-label">Clients Satisfaits</span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-number">50M+</span>
                    <span class="stat-label">Services Livrés</span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-number">99.9%</span>
                    <span class="stat-label">Taux de Satisfaction</span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Support Client</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Section CTA -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Prêt à Nous Faire Confiance ?</h2>
            <p class="cta-subtitle">
                Rejoignez des milliers de clients satisfaits et transformez votre 
                présence sur les réseaux sociaux dès aujourd'hui.
            </p>
            <a href="commander.php" class="cta-btn">
                <i class="fas fa-rocket me-2"></i>Commencer Maintenant
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

        document.querySelectorAll('.mission-card, .team-card').forEach(el => {
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