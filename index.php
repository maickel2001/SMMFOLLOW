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
    <meta name="description" content="Achetez des followers, likes et vues pour Instagram, TikTok, YouTube et Facebook. Services de qualité à prix abordables.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
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
                        <a class="nav-link" href="#accueil">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary ms-2" href="commander.php">Commander</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="accueil" class="hero">
        <div class="hero-content fade-in-up">
            <h1>Boostez vos Réseaux Sociaux</h1>
            <p>Obtenez plus de followers, likes et vues pour Instagram, TikTok, YouTube et Facebook. Services de qualité garantis avec livraison rapide.</p>
            <a href="commander.php" class="btn btn-primary">
                <i class="fas fa-shopping-cart me-2"></i>Commander Maintenant
            </a>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="section">
        <div class="container">
            <h2 class="section-title">Nos Services</h2>
            
            <div class="services-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="service-card fade-in-up">
                        <div class="service-icon">
                            <i class="<?php echo $category['icon']; ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="commander.php?category=<?php echo $category['id']; ?>" class="btn btn-primary">
                            Voir les Services
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Avantages Section -->
    <section class="section" style="background: var(--darker-bg);">
        <div class="container">
            <h2 class="section-title">Pourquoi Choisir SMM Pro ?</h2>
            
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="text-center">
                        <div class="service-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4>Livraison Rapide</h4>
                        <p>Livraison en 1-72h selon le service</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="text-center">
                        <div class="service-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Qualité Garantie</h4>
                        <p>Services de haute qualité avec garantie</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="text-center">
                        <div class="service-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Support 24/7</h4>
                        <p>Assistance clientèle disponible</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="text-center">
                        <div class="service-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Paiement Sécurisé</h4>
                        <p>Paiement via Mobile Money sécurisé</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section">
        <div class="container">
            <h2 class="section-title">Contactez-nous</h2>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="order-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="text-center">
                                    <div class="service-icon">
                                        <i class="fab fa-whatsapp"></i>
                                    </div>
                                    <h4>WhatsApp</h4>
                                    <p>+225 0123456789</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="text-center">
                                    <div class="service-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <h4>Email</h4>
                                    <p>contact@smmpro.com</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="commander.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-rocket me-2"></i>Commencer Maintenant
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 SMM Pro. Tous droits réservés. | Services de marketing sur les réseaux sociaux</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
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

        document.querySelectorAll('.service-card, .service-card').forEach(el => {
            observer.observe(el);
        });

        // Navigation smooth scroll
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
    </script>
</body>
</html>