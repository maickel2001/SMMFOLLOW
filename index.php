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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
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
                            <ul class="dropdown-menu dropdown-menu-dark">
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
                            <a class="nav-link btn btn-primary btn-sm px-3" href="inscription.php">
                                <i class="fas fa-user-plus me-1"></i>Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">
                        Boostez votre <span class="text-primary">Présence en Ligne</span>
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
                <div class="col-lg-6">
                    <div class="hero-image">
                        <div class="social-icons">
                            <div class="icon-item">
                                <i class="fab fa-instagram"></i>
                                <span>Instagram</span>
                            </div>
                            <div class="icon-item">
                                <i class="fab fa-tiktok"></i>
                                <span>TikTok</span>
                            </div>
                            <div class="icon-item">
                                <i class="fab fa-youtube"></i>
                                <span>YouTube</span>
                            </div>
                            <div class="icon-item">
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
                        <div class="service-card">
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
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Sécurité Garantie</h4>
                        <p>Vos comptes sont protégés avec nos méthodes sécurisées et respectueuses des plateformes.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h4>Livraison Rapide</h4>
                        <p>Recevez vos followers, likes et vues dans les délais indiqués, généralement entre 1h et 72h.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Support 24/7</h4>
                        <p>Notre équipe support est disponible 24h/24 et 7j/7 pour vous assister.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Qualité Premium</h4>
                        <p>Des services de haute qualité pour des résultats durables et visibles.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Paiement Sécurisé</h4>
                        <p>Paiements sécurisés via MTN Money et Moov Money avec suivi en temps réel.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
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
                <div class="col-lg-6">
                    <div class="about-image">
                        <img src="https://via.placeholder.com/500x400/1a1a1a/00ff88?text=SMM+Pro" 
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
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h4>WhatsApp</h4>
                        <p>+225 0123456789</p>
                        <small>Réponse immédiate</small>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email</h4>
                        <p>contact@smmpro.com</p>
                        <small>Réponse sous 24h</small>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="contact-card">
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