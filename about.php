<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À Propos - BoostSocial</title>
    <meta name="description" content="Découvrez l'histoire de BoostSocial, notre mission et notre équipe d'experts passionnés par le marketing sur les réseaux sociaux.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #10b981;
            --accent: #f59e0b;
            --dark: #0f172a;
            --darker: #020617;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: var(--light);
            overflow-x: hidden;
        }

        /* Hamburger Menu */
        .hamburger-menu {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            cursor: pointer;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            backdrop-filter: blur(20px);
        }

        .hamburger-menu:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-lg);
        }

        .hamburger-icon {
            width: 20px;
            height: 2px;
            background: var(--dark);
            position: relative;
            transition: all 0.3s ease;
        }

        .hamburger-icon::before,
        .hamburger-icon::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 2px;
            background: var(--dark);
            transition: all 0.3s ease;
        }

        .hamburger-icon::before {
            top: -6px;
        }

        .hamburger-icon::after {
            bottom: -6px;
        }

        .hamburger-menu.active .hamburger-icon {
            background: transparent;
        }

        .hamburger-menu.active .hamburger-icon::before {
            transform: rotate(45deg);
            top: 0;
        }

        .hamburger-menu.active .hamburger-icon::after {
            transform: rotate(-45deg);
            bottom: 0;
        }

        /* Menu Overlay */
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .menu-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .menu-item {
            display: block;
            margin: 1.5rem 0;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(20px);
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            color: white;
            text-decoration: none;
            box-shadow: var(--shadow-lg);
        }

        .menu-item i {
            margin-right: 1rem;
            width: 20px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, var(--primary) 100%);
            padding: 120px 0 80px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>') no-repeat;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }

        .hero h1 .highlight {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: clamp(1.1rem, 2.5vw, 1.3rem);
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        /* Story Section */
        .story {
            padding: 6rem 0;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.2rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .story-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .story-text h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
        }

        .story-text p {
            font-size: 1.1rem;
            color: var(--gray);
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .story-image {
            text-align: center;
        }

        .story-image img {
            max-width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
        }

        /* Mission & Values Section */
        .mission-values {
            padding: 6rem 0;
            background: var(--light);
        }

        .value-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--gray-light);
        }

        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .value-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .value-card p {
            color: var(--gray);
            line-height: 1.6;
        }

        /* Team Section */
        .team {
            padding: 6rem 0;
            background: white;
        }

        .team-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--gray-light);
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .team-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: white;
        }

        .team-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .team-card .position {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .team-card p {
            color: var(--gray);
            line-height: 1.6;
        }

        /* Statistics Section */
        .statistics {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--dark) 0%, var(--darker) 100%);
            color: white;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            color: var(--secondary);
            margin-bottom: 0.5rem;
            display: block;
        }

        .stat-label {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        /* CTA Section */
        .cta {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-align: center;
        }

        .cta h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
        }

        .cta p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-cta {
            background: white;
            color: var(--primary);
            padding: 1rem 3rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .story-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
            }
            
            .hamburger-menu {
                top: 15px;
                right: 15px;
                width: 45px;
                height: 45px;
            }
        }

        @media (max-width: 576px) {
            .hero {
                padding: 80px 0 60px;
            }
            
            .story, .mission-values, .team, .statistics, .cta {
                padding: 3rem 0;
            }
            
            .menu-content {
                padding: 1rem;
            }
            
            .menu-item {
                margin: 1rem 0;
                padding: 0.8rem 1.5rem;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hamburger Menu Button -->
    <div class="hamburger-menu" id="hamburgerMenu">
        <div class="hamburger-icon"></div>
    </div>

    <!-- Menu Overlay -->
    <div class="menu-overlay" id="menuOverlay">
        <div class="menu-content">
            <a href="index.php" class="menu-item">
                <i class="fas fa-home"></i>Accueil
            </a>
            <a href="services.php" class="menu-item">
                <i class="fas fa-rocket"></i>Services
            </a>
            <a href="about.php" class="menu-item">
                <i class="fas fa-info-circle"></i>À Propos
            </a>
            <a href="contact.php" class="menu-item">
                <i class="fas fa-envelope"></i>Contact
            </a>
            <a href="client/dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>À Propos de <span class="highlight">BoostSocial</span></h1>
                <p>Découvrez notre histoire, notre mission et notre équipe d'experts passionnés par le marketing sur les réseaux sociaux</p>
            </div>
        </div>
    </section>

    <!-- Story Section -->
    <section class="story">
        <div class="container">
            <div class="section-title">
                <h2>Notre Histoire</h2>
                <p>Comment tout a commencé et ce qui nous motive chaque jour</p>
            </div>
            
            <div class="story-content">
                <div class="story-text">
                    <h3>Une vision née d'une passion</h3>
                    <p>BoostSocial est né de la conviction que chaque créateur de contenu mérite d'être vu et entendu. En 2020, notre équipe de passionnés du digital a constaté que de nombreux talents restaient dans l'ombre faute de visibilité sur les réseaux sociaux.</p>
                    <p>Nous avons donc décidé de créer une plateforme qui démocratise l'accès aux services SMM de qualité, permettant à tous de booster leur présence en ligne de manière éthique et durable.</p>
                </div>
                <div class="story-image">
                    <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'><rect width='400' height='300' fill='%23f1f5f9' rx='20'/><circle cx='100' cy='100' r='30' fill='%236366f1' opacity='0.8'/><circle cx='300' cy='80' r='25' fill='%2310b981' opacity='0.8'/><circle cx='80' cy='250' r='35' fill='%23f59e0b' opacity='0.8'/><path d='M150 150 Q200 100 250 150 T350 200' stroke='%236366f1' stroke-width='3' fill='none' opacity='0.6'/><text x='200' y='180' text-anchor='middle' fill='%230f172a' font-family='Arial' font-size='18' font-weight='bold'>Notre Histoire</text></svg>" alt="Histoire de BoostSocial">
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Values Section -->
    <section class="mission-values">
        <div class="container">
            <div class="section-title">
                <h2>Notre Mission & Nos Valeurs</h2>
                <p>Les principes qui guident chacune de nos actions</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Qualité</h3>
                        <p>Nous nous engageons à fournir des services de la plus haute qualité, avec des résultats durables et authentiques pour nos clients.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3>Transparence</h3>
                        <p>Nous croyons en la transparence totale dans nos processus et nos résultats. Chaque client sait exactement ce qu'il achète.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Communauté</h3>
                        <p>Nous construisons une communauté de créateurs qui s'entraident et grandissent ensemble dans l'écosystème digital.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team">
        <div class="container">
            <div class="section-title">
                <h2>Notre Équipe</h2>
                <p>Des experts passionnés qui donnent vie à votre vision</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="team-card">
                        <div class="team-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>Alexandre Dubois</h3>
                        <div class="position">CEO & Fondateur</div>
                        <p>Expert en marketing digital avec 10+ années d'expérience dans l'industrie SMM. Passionné par l'innovation et la croissance durable.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="team-card">
                        <div class="team-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>Marie Laurent</h3>
                        <div class="position">Directrice Marketing</div>
                        <p>Spécialiste en stratégie de contenu et en croissance d'audience. Elle transforme les idées en succès concrets.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="team-card">
                        <div class="team-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>Thomas Moreau</h3>
                        <div class="position">CTO</div>
                        <p>Développeur full-stack passionné par les nouvelles technologies. Il assure la stabilité et l'innovation de notre plateforme.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="statistics">
        <div class="container">
            <div class="section-title">
                <h2 style="color: white;">Nos Chiffres Clés</h2>
                <p style="color: rgba(255, 255, 255, 0.8);">Des résultats qui parlent d'eux-mêmes</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number">50K+</span>
                        <div class="stat-label">Clients Satisfaits</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number">2M+</span>
                        <div class="stat-label">Commandes Traitées</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number">99.8%</span>
                        <div class="stat-label">Taux de Satisfaction</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number">24/7</span>
                        <div class="stat-label">Support Client</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Prêt à rejoindre notre communauté ?</h2>
            <p>
                Découvrez comment BoostSocial peut transformer votre présence sur les réseaux sociaux 
                et vous aider à atteindre vos objectifs.
            </p>
            <a href="commander.php" class="btn-cta">
                <i class="fas fa-rocket me-2"></i>Commencer maintenant
            </a>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Hamburger Menu Toggle
        const hamburgerMenu = document.getElementById('hamburgerMenu');
        const menuOverlay = document.getElementById('menuOverlay');

        hamburgerMenu.addEventListener('click', function() {
            hamburgerMenu.classList.toggle('active');
            menuOverlay.classList.toggle('active');
        });

        // Close menu when clicking on overlay
        menuOverlay.addEventListener('click', function(e) {
            if (e.target === menuOverlay) {
                hamburgerMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
            }
        });

        // Close menu when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hamburgerMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
            }
        });

        // Close menu when clicking on menu items
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                hamburgerMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
            });
        });
    </script>
</body>
</html>