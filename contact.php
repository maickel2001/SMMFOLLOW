<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    
    if (empty($nom) || empty($email) || empty($sujet) || empty($message_text)) {
        $message = 'Veuillez remplir tous les champs.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Veuillez entrer une adresse email valide.';
        $messageType = 'danger';
    } else {
        // Ici vous pouvez ajouter l'envoi d'email ou l'enregistrement en base
        $message = 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.';
        $messageType = 'success';
        
        // Réinitialiser les champs
        $nom = $email = $sujet = $message_text = '';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - BoostSocial</title>
    <meta name="description" content="Contactez notre équipe BoostSocial pour toute question ou demande de support. Nous sommes là pour vous aider 24h/24 et 7j/7.">
    
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

        /* Contact Info Section */
        .contact-info {
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

        .contact-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--gray-light);
        }

        .contact-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .contact-icon {
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

        .contact-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .contact-card p {
            color: var(--gray);
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .contact-card small {
            color: var(--gray);
            font-size: 0.875rem;
        }

        /* Contact Form Section */
        .contact-form {
            padding: 6rem 0;
            background: var(--light);
        }

        .form-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-control.error {
            border-color: #ef4444;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1rem 3rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* FAQ Section */
        .faq {
            padding: 6rem 0;
            background: white;
        }

        .faq-item {
            background: white;
            border-radius: 15px;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-light);
            overflow: hidden;
        }

        .faq-question {
            background: var(--light);
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
            text-align: left;
        }

        .faq-question:hover {
            background: var(--gray-light);
        }

        .faq-question h4 {
            margin: 0;
            font-weight: 600;
            color: var(--dark);
        }

        .faq-icon {
            transition: transform 0.3s ease;
            color: var(--primary);
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 1.5rem;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-item.active .faq-answer {
            padding: 1.5rem;
            max-height: 200px;
        }

        .faq-answer p {
            color: var(--gray);
            margin: 0;
            line-height: 1.6;
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

        /* Success Message */
        .success-message {
            background: var(--secondary);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            display: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-container {
                padding: 2rem;
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
            
            .contact-info, .contact-form, .faq, .cta {
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
                <h1>Contactez <span class="highlight">BoostSocial</span></h1>
                <p>Notre équipe est là pour vous aider 24h/24 et 7j/7. N'hésitez pas à nous contacter !</p>
            </div>
        </div>
    </section>

    <!-- Contact Info Section -->
    <section class="contact-info">
        <div class="container">
            <div class="section-title">
                <h2>Nos Coordonnées</h2>
                <p>Plusieurs façons de nous joindre pour répondre à tous vos besoins</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email</h3>
                        <p>support@boostsocial.com</p>
                        <small>Réponse sous 2h</small>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Support 24/7</h3>
                        <p>Via notre chat en ligne</p>
                        <small>Disponible 24h/24</small>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Réponse Rapide</h3>
                        <p>Maximum 2 heures</p>
                        <small>En semaine</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-form">
        <div class="container">
            <div class="section-title">
                <h2>Envoyez-nous un message</h2>
                <p>Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-container">
                        <div class="success-message" id="successMessage">
                            Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.
                        </div>
                        
                        <form id="contactForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Nom complet *</label>
                                        <input type="text" id="name" name="name" class="form-control" required>
                                        <div class="error-message" id="nameError"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" id="email" name="email" class="form-control" required>
                                        <div class="error-message" id="emailError"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject" class="form-label">Sujet *</label>
                                <input type="text" id="subject" name="subject" class="form-control" required>
                                <div class="error-message" id="subjectError"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message" class="form-label">Message *</label>
                                <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
                                <div class="error-message" id="messageError"></div>
                            </div>
                            
                            <button type="submit" class="btn-submit" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq">
        <div class="container">
            <div class="section-title">
                <h2>Questions Fréquentes</h2>
                <p>Trouvez rapidement des réponses à vos questions les plus courantes</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item">
                        <button class="faq-question">
                            <h4>Combien de temps faut-il pour recevoir une réponse ?</h4>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Nous nous engageons à répondre à tous les messages dans un délai maximum de 2 heures en semaine. Pour les demandes urgentes, notre chat en ligne est disponible 24h/24.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <h4>Comment puis-je suivre l'état de ma commande ?</h4>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Vous pouvez suivre l'état de vos commandes directement depuis votre dashboard client. Nous vous enverrons également des notifications par email à chaque étape importante.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <h4>Quels sont vos horaires de support ?</h4>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Notre équipe support est disponible 24h/24 et 7j/7. Vous pouvez nous contacter à tout moment via le chat en ligne, par email ou en créant un ticket de support.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Besoin d'aide immédiate ?</h2>
            <p>
                Notre équipe support est disponible 24h/24 pour répondre à toutes vos questions 
                et vous accompagner dans vos projets.
            </p>
            <a href="support.php" class="btn-cta">
                <i class="fas fa-headset me-2"></i>Accéder au support
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

        // FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                const faqItem = this.parentElement;
                const isActive = faqItem.classList.contains('active');
                
                // Close all FAQ items
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Open clicked item if it wasn't active
                if (!isActive) {
                    faqItem.classList.add('active');
                }
            });
        });

        // Contact Form Validation
        const contactForm = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');
        const successMessage = document.getElementById('successMessage');

        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset error messages
            document.querySelectorAll('.error-message').forEach(error => {
                error.textContent = '';
            });
            
            document.querySelectorAll('.form-control').forEach(input => {
                input.classList.remove('error');
            });
            
            // Get form data
            const formData = new FormData(this);
            const name = formData.get('name').trim();
            const email = formData.get('email').trim();
            const subject = formData.get('subject').trim();
            const message = formData.get('message').trim();
            
            let isValid = true;
            
            // Validate name
            if (name.length < 2) {
                document.getElementById('nameError').textContent = 'Le nom doit contenir au moins 2 caractères';
                document.getElementById('name').classList.add('error');
                isValid = false;
            }
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').textContent = 'Veuillez entrer une adresse email valide';
                document.getElementById('email').classList.add('error');
                isValid = false;
            }
            
            // Validate subject
            if (subject.length < 5) {
                document.getElementById('subjectError').textContent = 'Le sujet doit contenir au moins 5 caractères';
                document.getElementById('subject').classList.add('error');
                isValid = false;
            }
            
            // Validate message
            if (message.length < 10) {
                document.getElementById('messageError').textContent = 'Le message doit contenir au moins 10 caractères';
                document.getElementById('message').classList.add('error');
                isValid = false;
            }
            
            if (isValid) {
                // Simulate form submission
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Envoi en cours...';
                
                setTimeout(() => {
                    successMessage.style.display = 'block';
                    contactForm.reset();
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Envoyer le message';
                    
                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        successMessage.style.display = 'none';
                    }, 5000);
                }, 2000);
            }
        });
    </script>
</body>
</html>