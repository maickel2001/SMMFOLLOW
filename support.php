<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

// Traitement de la création d'un ticket de support
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_ticket') {
    $customerName = cleanInput($_POST['customer_name']);
    $customerEmail = cleanInput($_POST['customer_email']);
    $subject = cleanInput($_POST['subject']);
    $message = cleanInput($_POST['message']);
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : null;
    
    if (empty($customerName) || empty($customerEmail) || empty($subject) || empty($message)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!isValidEmail($customerEmail)) {
        $error = 'Veuillez entrer une adresse email valide.';
    } else {
        $ticketId = createSupportTicket($customerEmail, $customerName, $subject, $message, $orderId);
        
        if ($ticketId) {
            $success = 'Votre ticket de support a été créé avec succès. Nous vous répondrons dans les plus brefs délais.';
            
            // Envoyer une notification email
            $emailSubject = "Ticket de support créé - $subject";
            $emailMessage = "
                <h2>Confirmation de votre ticket de support</h2>
                <p>Bonjour $customerName,</p>
                <p>Votre ticket de support a été créé avec succès.</p>
                <p><strong>Sujet :</strong> $subject</p>
                <p><strong>Message :</strong> $message</p>
                <p>Notre équipe support vous répondra dans les plus brefs délais.</p>
                <p>Cordialement,<br>L'équipe SMM Pro</p>
            ";
            
            sendEmailNotification($customerEmail, $emailSubject, $emailMessage);
        } else {
            $error = 'Erreur lors de la création du ticket. Veuillez réessayer.';
        }
    }
}

// Récupération des FAQ
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM faq WHERE is_active = 1 ORDER BY category, order_index");
    $faqs = $stmt->fetchAll();
    
    // Grouper les FAQ par catégorie
    $faqsByCategory = [];
    foreach ($faqs as $faq) {
        $category = $faq['category'];
        if (!isset($faqsByCategory[$category])) {
            $faqsByCategory[$category] = [];
        }
        $faqsByCategory[$category][] = $faq;
    }
    
} catch (Exception $e) {
    $error = 'Erreur lors du chargement des FAQ.';
}

// Récupération des témoignages approuvés
try {
    $stmt = $pdo->query("
        SELECT t.*, s.name as service_name 
        FROM testimonials t 
        LEFT JOIN services s ON t.service_id = s.id 
        WHERE t.is_approved = 1 
        ORDER BY t.is_featured DESC, t.created_at DESC 
        LIMIT 6
    ");
    $testimonials = $stmt->fetchAll();
} catch (Exception $e) {
    $testimonials = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support et FAQ - SMM Pro</title>
    <meta name="description" content="Besoin d'aide ? Consultez notre FAQ ou créez un ticket de support. Notre équipe est là pour vous aider.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .support-hero {
            background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 100%);
            padding: 100px 20px 60px;
            text-align: center;
        }
        
        .faq-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .faq-header {
            background: var(--darker-bg);
            padding: 20px;
            border-radius: 15px 15px 0 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .faq-item {
            border-bottom: 1px solid var(--border-color);
            padding: 20px;
        }
        
        .faq-item:last-child {
            border-bottom: none;
        }
        
        .faq-question {
            font-weight: 600;
            color: var(--text-primary);
            cursor: pointer;
            margin-bottom: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .faq-answer {
            color: var(--text-secondary);
            margin-top: 15px;
            display: none;
        }
        
        .faq-answer.show {
            display: block;
        }
        
        .faq-toggle {
            color: var(--primary-color);
            transition: transform 0.3s ease;
        }
        
        .faq-toggle.rotated {
            transform: rotate(180deg);
        }
        
        .testimonial-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .testimonial-rating {
            color: #ffaa00;
            margin-bottom: 15px;
        }
        
        .testimonial-text {
            font-style: italic;
            color: var(--text-secondary);
            margin-bottom: 15px;
        }
        
        .testimonial-author {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .testimonial-service {
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        
        .support-form {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 40px;
        }
        
        .contact-info {
            background: var(--darker-bg);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        
        .contact-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
    </style>
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
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="commander.php">Commander</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="support.php">Support</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="support-hero">
        <div class="container">
            <h1 class="display-4 mb-4">
                <i class="fas fa-headset me-3"></i>Support et Aide
            </h1>
            <p class="lead text-muted">
                Besoin d'aide ? Consultez notre FAQ ou créez un ticket de support.<br>
                Notre équipe est disponible 24/7 pour vous assister.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="section">
        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- FAQ Section -->
                <div class="col-lg-8">
                    <h2 class="section-title text-start mb-4">
                        <i class="fas fa-question-circle me-2"></i>Questions Fréquemment Posées
                    </h2>
                    
                    <?php foreach ($faqsByCategory as $category => $categoryFaqs): ?>
                        <div class="faq-section">
                            <div class="faq-header">
                                <h3 class="mb-0">
                                    <i class="fas fa-folder me-2"></i><?php echo htmlspecialchars($category); ?>
                                </h3>
                            </div>
                            
                            <?php foreach ($categoryFaqs as $faq): ?>
                                <div class="faq-item">
                                    <h4 class="faq-question" onclick="toggleFaq(this)">
                                        <?php echo htmlspecialchars($faq['question']); ?>
                                        <i class="fas fa-chevron-down faq-toggle"></i>
                                    </h4>
                                    <div class="faq-answer">
                                        <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Témoignages -->
                    <?php if (!empty($testimonials)): ?>
                        <h3 class="mb-4 mt-5">
                            <i class="fas fa-star me-2"></i>Avis de nos Clients
                        </h3>
                        
                        <div class="row">
                            <?php foreach ($testimonials as $testimonial): ?>
                                <div class="col-md-6">
                                    <div class="testimonial-card">
                                        <div class="testimonial-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $testimonial['rating'] ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        
                                        <div class="testimonial-text">
                                            "<?php echo htmlspecialchars($testimonial['comment']); ?>"
                                        </div>
                                        
                                        <div class="testimonial-author">
                                            <?php echo htmlspecialchars($testimonial['customer_name']); ?>
                                        </div>
                                        
                                        <?php if ($testimonial['service_name']): ?>
                                            <div class="testimonial-service">
                                                <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($testimonial['service_name']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Formulaire de support -->
                    <div class="support-form mb-4">
                        <h4 class="mb-4">
                            <i class="fas fa-ticket-alt me-2"></i>Créer un Ticket de Support
                        </h4>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="create_ticket">
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Nom complet *</label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="customer_email" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Sujet *</label>
                                <input type="text" class="form-control" name="subject" required>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label class="form-label">Message *</label>
                                <textarea class="form-control" name="message" rows="4" required 
                                          placeholder="Décrivez votre problème ou question..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le Ticket
                            </button>
                        </form>
                    </div>
                    
                    <!-- Informations de contact -->
                    <div class="contact-info">
                        <h4 class="mb-4">Contact Direct</h4>
                        
                        <div class="mb-4">
                            <div class="contact-icon">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <h5>WhatsApp</h5>
                            <p class="mb-0">+225 0123456789</p>
                            <small class="text-muted">Réponse immédiate</small>
                        </div>
                        
                        <div class="mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h5>Email</h5>
                            <p class="mb-0">contact@smmpro.com</p>
                            <small class="text-muted">Réponse sous 24h</small>
                        </div>
                        
                        <div>
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h5>Disponibilité</h5>
                            <p class="mb-0">24h/24 - 7j/7</p>
                            <small class="text-muted">Support permanent</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 SMM Pro. Tous droits réservés. | Support client 24/7</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        function toggleFaq(element) {
            const answer = element.nextElementSibling;
            const toggle = element.querySelector('.faq-toggle');
            
            if (answer.classList.contains('show')) {
                answer.classList.remove('show');
                toggle.classList.remove('rotated');
            } else {
                answer.classList.add('show');
                toggle.classList.add('rotated');
            }
        }
        
        // Animation au scroll pour les FAQ
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

        document.querySelectorAll('.faq-section, .testimonial-card').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>