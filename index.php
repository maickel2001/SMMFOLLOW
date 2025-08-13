<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoostSocial - Boostez vos réseaux sociaux</title>
    <meta name="description" content="Boostez vos followers, likes et vues sur Instagram, TikTok, YouTube et Facebook. Services professionnels et résultats garantis.">
    
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

        /* Header & Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, var(--primary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
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
            color: white;
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
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            color: white;
            border: none;
            box-shadow: var(--shadow);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        .btn-hero-secondary {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-hero-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }

        /* Features Section */
        .features {
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

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--gray-light);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .feature-icon {
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

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--gray);
            line-height: 1.6;
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

        /* Footer */
        .footer {
            background: var(--darker);
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer h5 {
            color: var(--secondary);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--secondary);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 2rem;
            padding-top: 1rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-hero {
                width: 100%;
                max-width: 300px;
                text-align: center;
            }
            
            .navbar-nav {
                text-align: center;
                margin-top: 1rem;
            }
        }

        @media (max-width: 576px) {
            .hero {
                padding: 2rem 0;
                min-height: 80vh;
            }
            
            .features, .social-networks, .cta {
                padding: 3rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-rocket me-2"></i>BoostSocial
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#social">Réseaux</a>
                    </li>
                    <li class="nav-link">
                        <a class="nav-link" href="support.php">Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="connexion.php">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="inscription.php">S'inscrire</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1>
                        Boostez vos <span class="highlight">réseaux sociaux</span> en un clic !
                    </h1>
                    <p>
                        Obtenez plus de followers, likes et vues sur Instagram, TikTok, YouTube et Facebook. 
                        Services professionnels, résultats garantis et support 24/7.
                    </p>
                    <div class="hero-buttons">
                        <a href="commander.php" class="btn-hero btn-hero-primary">
                            <i class="fas fa-rocket me-2"></i>Commander maintenant
                        </a>
                        <a href="#features" class="btn-hero btn-hero-secondary">
                            <i class="fas fa-info-circle me-2"></i>En savoir plus
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="hero-image">
                        <i class="fas fa-chart-line" style="font-size: 15rem; color: rgba(255,255,255,0.1);"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title fade-in">
                <h2>Pourquoi choisir BoostSocial ?</h2>
                <p>Découvrez nos services professionnels et nos avantages exclusifs</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 fade-in">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3>Résultats rapides</h3>
                        <p>Obtenez vos followers, likes et vues en quelques heures seulement. Pas d'attente interminable !</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 fade-in">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>100% sécurisé</h3>
                        <p>Vos comptes sont protégés. Nous utilisons des méthodes sûres et respectons les règles des plateformes.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 fade-in">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Support 24/7</h3>
                        <p>Notre équipe est disponible 24h/24 et 7j/7 pour vous accompagner et répondre à toutes vos questions.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 fade-in">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3>Statistiques détaillées</h3>
                        <p>Suivez vos performances en temps réel avec nos tableaux de bord détaillés et analyses avancées.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 fade-in">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3>Réseaux multiples</h3>
                        <p>Instagram, TikTok, YouTube, Facebook... Nous couvrons tous les réseaux sociaux populaires.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 fade-in">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <h3>Bonus gratuits</h3>
                        <p>Profitez de bonus et réductions exclusives sur vos commandes. Plus vous commandez, plus vous économisez !</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Networks Section -->
    <section class="social-networks" id="social">
        <div class="container">
            <div class="section-title fade-in">
                <h2>Nos réseaux sociaux supportés</h2>
                <p>Boostez votre présence sur toutes les plateformes populaires</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 fade-in">
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
                
                <div class="col-lg-3 col-md-6 fade-in">
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
                
                <div class="col-lg-3 col-md-6 fade-in">
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
                
                <div class="col-lg-3 col-md-6 fade-in">
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5><i class="fas fa-rocket me-2"></i>BoostSocial</h5>
                    <p class="mb-3">Votre partenaire de confiance pour booster vos réseaux sociaux. Qualité, rapidité et sécurité garanties.</p>
                    <div class="social-links">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Services</h5>
                    <ul class="list-unstyled">
                        <li><a href="commander.php">Commander</a></li>
                        <li><a href="support.php">Support</a></li>
                        <li><a href="inscription.php">S'inscrire</a></li>
                        <li><a href="connexion.php">Se connecter</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Réseaux</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">TikTok</a></li>
                        <li><a href="#">YouTube</a></li>
                        <li><a href="#">Facebook</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="support.php">Centre d'aide</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Tutoriels</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Légal</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Mentions légales</a></li>
                        <li><a href="#">CGV</a></li>
                        <li><a href="#">Confidentialité</a></li>
                        <li><a href="#">Cookies</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 BoostSocial. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scrolling for navigation links
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

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = 'none';
            }
        });

        // Fade in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Counter animation for social stats
        function animateCounters() {
            const counters = document.querySelectorAll('.social-stats');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/\D/g, ''));
                const increment = target / 100;
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.textContent = Math.ceil(current) + 'K+';
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target + 'K+';
                    }
                };
                
                updateCounter();
            });
        }

        // Trigger counter animation when social section is visible
        const socialSection = document.querySelector('.social-networks');
        const socialObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    socialObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        socialObserver.observe(socialSection);
    </script>
</body>
</html>