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
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 2rem 0;
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
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
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
            
            .hamburger-menu {
                top: 15px;
                right: 15px;
                width: 45px;
                height: 45px;
            }
        }

        @media (max-width: 576px) {
            .hero {
                padding: 1rem 0;
                min-height: 80vh;
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
                    <a href="services.php" class="btn-hero btn-hero-secondary">
                        <i class="fas fa-info-circle me-2"></i>Voir nos services
                    </a>
                </div>
            </div>
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