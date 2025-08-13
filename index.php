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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: var(--light);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Navigation Apple-like */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--shadow);
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
        }

        .navbar-brand span {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-auth {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn-auth {
            padding: 10px 24px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-auth.secondary {
            background: transparent;
            color: var(--dark);
            border: 2px solid var(--gray-light);
        }

        .btn-auth.secondary:hover {
            background: var(--gray-light);
            transform: translateY(-2px);
        }

        .btn-auth.primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-auth.primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Hero Section Apple-like */
        .hero {
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, var(--primary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 120px 0 80px;
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
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: clamp(3rem, 8vw, 5rem);
            font-weight: 900;
            color: white;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .hero h1 .highlight {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 400;
        }

        .hero-cta {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 4rem;
        }

        .btn-hero {
            padding: 18px 36px;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-hero:hover::before {
            left: 100%;
        }

        .btn-hero.primary {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .btn-hero.primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 30px 60px rgba(16, 185, 129, 0.3);
        }

        .btn-hero.secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-hero.secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px);
        }

        /* Features Section */
        .features {
            padding: 8rem 0;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 5rem;
        }

        .section-title h2 {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.3rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }

        .feature-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--gray-light);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
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
            margin: 0 auto 2rem;
            font-size: 2rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--gray);
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .stat-item {
            padding: 2rem;
        }

        .stat-number {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, white, rgba(255, 255, 255, 0.8));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* CTA Section */
        .cta-section {
            padding: 8rem 0;
            background: var(--darker);
            color: white;
            text-align: center;
        }

        .cta-content h2 {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
        }

        .cta-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--secondary);
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: block;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: var(--secondary);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
            }

            .nav-auth {
                gap: 10px;
            }

            .btn-auth {
                padding: 8px 16px;
                font-size: 0.9rem;
            }

            .hero {
                padding: 100px 20px 60px;
            }

            .hero-cta {
                flex-direction: column;
                align-items: center;
            }

            .btn-hero {
                width: 100%;
                max-width: 300px;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: clamp(2.5rem, 6vw, 3rem);
            }

            .hero p {
                font-size: 1.1rem;
            }

            .btn-hero {
                padding: 16px 24px;
                font-size: 1rem;
            }

            .feature-card {
                padding: 2rem 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
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

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .float {
            animation: float 3s ease-in-out infinite;
        }

        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <?php
    // Vérifier si l'utilisateur est connecté
    session_start();
    $isLoggedIn = isset($_SESSION['user_id']);
    $userName = $isLoggedIn ? $_SESSION['user_name'] : '';
    ?>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-rocket me-2"></i>Boost<span>Social</span>
            </a>
            
            <div class="nav-auth">
                <?php if ($isLoggedIn): ?>
                    <span class="text-dark me-3">Bonjour, <?php echo htmlspecialchars($userName); ?> !</span>
                    <a href="client/dashboard.php" class="btn-auth primary">Mon Dashboard</a>
                <?php else: ?>
                    <a href="connexion.php" class="btn-auth secondary">Se connecter</a>
                    <a href="inscription.php" class="btn-auth primary">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="fade-in-up">
                    Boostez vos <span class="highlight">réseaux sociaux</span><br>
                    en quelques clics
                </h1>
                <p class="fade-in-up">
                    Services professionnels de followers, likes et vues pour Instagram, TikTok, YouTube et Facebook. 
                    Résultats garantis et support 24/7.
                </p>
                
                <div class="hero-cta">
                    <?php if ($isLoggedIn): ?>
                        <a href="commander.php" class="btn-hero primary fade-in-up">
                            <i class="fas fa-plus me-2"></i>Nouvelle Commande
                        </a>
                        <a href="client/dashboard.php" class="btn-hero secondary fade-in-up">
                            <i class="fas fa-chart-line me-2"></i>Voir mes Stats
                        </a>
                    <?php else: ?>
                        <a href="inscription.php" class="btn-hero primary fade-in-up">
                            <i class="fas fa-rocket me-2"></i>Commencer Maintenant
                        </a>
                        <a href="connexion.php" class="btn-hero secondary fade-in-up">
                            <i class="fas fa-sign-in-alt me-2"></i>Se Connecter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title fade-in-up">
                <h2>Pourquoi choisir BoostSocial ?</h2>
                <p>Une plateforme complète et professionnelle pour booster votre présence en ligne</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Followers Garantis</h3>
                    <p>Obtenez des followers réels et engagés pour augmenter votre visibilité et votre crédibilité sur les réseaux sociaux.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Likes & Interactions</h3>
                    <p>Boostez l'engagement de vos publications avec des likes authentiques et des commentaires pertinents.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Vues & Visibilité</h3>
                    <p>Augmentez la portée de vos vidéos et stories pour toucher un public plus large et développer votre audience.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Sécurité Totale</h3>
                    <p>Vos comptes sont protégés avec nos méthodes sécurisées et notre support technique expert disponible 24/7.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Résultats Rapides</h3>
                    <p>Livraison express de vos services avec des résultats visibles en quelques heures et une satisfaction garantie.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Support Premium</h3>
                    <p>Équipe dédiée et réactive pour vous accompagner à chaque étape et répondre à toutes vos questions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item fade-in-up">
                    <div class="stat-number" data-target="50000">0</div>
                    <div class="stat-label">Clients Satisfaits</div>
                </div>
                
                <div class="stat-item fade-in-up">
                    <div class="stat-number" data-target="1000000">0</div>
                    <div class="stat-label">Services Livrés</div>
                </div>
                
                <div class="stat-item fade-in-up">
                    <div class="stat-number" data-target="99">0</div>
                    <div class="stat-label">% de Satisfaction</div>
                </div>
                
                <div class="stat-item fade-in-up">
                    <div class="stat-number" data-target="24">0</div>
                    <div class="stat-label">Support 24/7</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content fade-in-up">
                <h2>Prêt à booster votre présence ?</h2>
                <p>Rejoignez des milliers d'entrepreneurs et d'influenceurs qui ont déjà transformé leurs réseaux sociaux avec BoostSocial.</p>
                
                <div class="cta-buttons">
                    <?php if ($isLoggedIn): ?>
                        <a href="commander.php" class="btn-hero primary">
                            <i class="fas fa-plus me-2"></i>Commander Maintenant
                        </a>
                    <?php else: ?>
                        <a href="inscription.php" class="btn-hero primary">
                            <i class="fas fa-user-plus me-2"></i>S'inscrire Gratuitement
                        </a>
                        <a href="connexion.php" class="btn-hero secondary">
                            <i class="fas fa-sign-in-alt me-2"></i>Se Connecter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>BoostSocial</h4>
                    <p>Votre partenaire de confiance pour booster votre présence sur les réseaux sociaux. Services professionnels et résultats garantis.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Services</h4>
                    <a href="services.php">Followers</a>
                    <a href="services.php">Likes</a>
                    <a href="services.php">Vues</a>
                    <a href="services.php">Commentaires</a>
                </div>
                
                <div class="footer-section">
                    <h4>Réseaux</h4>
                    <a href="services.php">Instagram</a>
                    <a href="services.php">TikTok</a>
                    <a href="services.php">YouTube</a>
                    <a href="services.php">Facebook</a>
                </div>
                
                <div class="footer-section">
                    <h4>Support</h4>
                    <a href="contact.php">Contact</a>
                    <a href="support.php">Aide</a>
                    <a href="about.php">À propos</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="client/tickets.php">Mes Tickets</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 BoostSocial. Tous droits réservés. | <a href="#" style="color: var(--secondary);">Mentions légales</a> | <a href="#" style="color: var(--secondary);">Politique de confidentialité</a></p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Intersection Observer pour les animations
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

        // Observer tous les éléments avec la classe fade-in-up
        document.querySelectorAll('.fade-in-up').forEach(el => {
            observer.observe(el);
        });

        // Animation des compteurs
        function animateCounter(element) {
            const target = parseInt(element.getAttribute('data-target'));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 16);
        }

        // Observer les compteurs
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stat-number').forEach(counter => {
            counterObserver.observe(counter);
        });

        // Smooth scroll pour les ancres
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

        // Redirection automatique si connecté
        <?php if ($isLoggedIn): ?>
        // L'utilisateur est connecté, on peut ajouter des fonctionnalités supplémentaires
        console.log('Utilisateur connecté: <?php echo htmlspecialchars($userName); ?>');
        <?php endif; ?>
    </script>
</body>
</html>