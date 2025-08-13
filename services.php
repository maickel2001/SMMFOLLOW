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
    <title>Nos Services - BoostSocial</title>
    <meta name="description" content="Découvrez nos services SMM professionnels pour Instagram, TikTok, YouTube et Facebook. Followers, likes et vues de qualité.">
    
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

        /* Services Section */
        .services {
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

        .service-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--gray-light);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .service-icon {
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

        .service-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .service-card p {
            color: var(--gray);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .service-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .service-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        /* Social Networks Section */
        .social-networks {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--dark) 0%, var(--darker) 100%);
            color: white;
        }

        .social-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            height: 100%;
        }

        .social-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .social-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .social-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .social-stats {
            font-size: 2rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        .social-card p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.5rem;
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
            
            .services, .social-networks, .cta {
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
                <h1>Nos <span class="highlight">Services</span> SMM</h1>
                <p>Découvrez notre gamme complète de services professionnels pour booster votre présence sur les réseaux sociaux</p>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services">
        <div class="container">
            <div class="section-title">
                <h2>Ce que nous proposons</h2>
                <p>Des solutions complètes pour tous vos besoins en marketing sur les réseaux sociaux</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Followers</h3>
                        <p>Augmentez votre audience avec des followers de qualité. Engagement réel et croissance organique garantis.</p>
                        <a href="commander.php" class="service-btn">Commander</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3>Likes</h3>
                        <p>Boostez l'engagement de vos publications avec des likes authentiques. Visibilité maximale assurée.</p>
                        <a href="commander.php" class="service-btn">Commander</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Vues</h3>
                        <p>Maximisez la portée de vos vidéos et stories. Plus de vues = plus d'opportunités de conversion.</p>
                        <a href="commander.php" class="service-btn">Commander</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-comment"></i>
                        </div>
                        <h3>Commentaires</h3>
                        <p>Créez de l'engagement authentique avec des commentaires pertinents et constructifs.</p>
                        <a href="commander.php" class="service-btn">Commander</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-share"></i>
                        </div>
                        <h3>Partages</h3>
                        <p>Amplifiez votre contenu avec des partages qui augmentent votre visibilité exponentiellement.</p>
                        <a href="commander.php" class="service-btn">Commander</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>Packages Premium</h3>
                        <p>Profitez de nos packages tout-en-un pour une croissance complète et rapide de vos comptes.</p>
                        <a href="commander.php" class="service-btn">Commander</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Networks Section -->
    <section class="social-networks">
        <div class="container">
            <div class="section-title">
                <h2 style="color: white;">Nos réseaux sociaux supportés</h2>
                <p style="color: rgba(255, 255, 255, 0.8);">Boostez votre présence sur toutes les plateformes populaires</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="social-card">
                        <div class="social-icon">
                            <i class="fab fa-instagram" style="color: #E4405F;"></i>
                        </div>
                        <h3>Instagram</h3>
                        <div class="social-stats">50K+</div>
                        <p>Followers boostés</p>
                        <a href="commander.php" class="btn btn-outline-light">Commander</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="social-card">
                        <div class="social-icon">
                            <i class="fab fa-tiktok" style="color: #000000;"></i>
                        </div>
                        <h3>TikTok</h3>
                        <div class="social-stats">30K+</div>
                        <p>Vues générées</p>
                        <a href="commander.php" class="btn btn-outline-light">Commander</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="social-card">
                        <div class="social-icon">
                            <i class="fab fa-youtube" style="color: #FF0000;"></i>
                        </div>
                        <h3>YouTube</h3>
                        <div class="social-stats">25K+</div>
                        <p>Abonnés gagnés</p>
                        <a href="commander.php" class="btn btn-outline-light">Commander</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="social-card">
                        <div class="social-icon">
                            <i class="fab fa-facebook" style="color: #1877F2;"></i>
                        </div>
                        <h3>Facebook</h3>
                        <div class="social-stats">40K+</div>
                        <p>Pages boostées</p>
                        <a href="commander.php" class="btn btn-outline-light">Commander</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Prêt à booster votre présence en ligne ?</h2>
            <p>
                Rejoignez des milliers de clients satisfaits qui ont transformé leurs réseaux sociaux 
                avec nos services professionnels.
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