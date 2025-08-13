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
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f5f5f7;
            --bg-tertiary: #fafafa;
            --bg-dark: #1d1d1f;
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --text-tertiary: #6e6e73;
            --text-light: #ffffff;
            --accent-primary: #007aff;
            --accent-secondary: #5856d6;
            --accent-success: #34c759;
            --accent-warning: #ff9500;
            --accent-danger: #ff3b30;
            --border-light: #d2d2d7;
            --border-lighter: #e5e5e7;
            --shadow-subtle: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-medium: 0 4px 16px rgba(0, 0, 0, 0.08);
            --shadow-large: 0 8px 32px rgba(0, 0, 0, 0.12);
            --radius-small: 8px;
            --radius-medium: 12px;
            --radius-large: 16px;
            --radius-xl: 24px;
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
        
        /* Navigation Minimaliste */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-lighter);
            padding: 16px 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--accent-primary) !important;
            background: rgba(0, 122, 255, 0.04);
            transform: translateY(-1px);
        }
        
        .navbar-toggler {
            border: none;
            padding: 4px 8px;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        /* Hero Section Minimaliste */
        .support-hero {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            padding: 120px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .support-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 24px;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            line-height: 1.1;
        }
        
        .support-hero .lead {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .support-hero i {
            color: var(--accent-primary);
        }
        
        /* Sections Générales */
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        
        /* FAQ Section Minimaliste */
        .faq-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            margin-bottom: 32px;
            overflow: hidden;
            box-shadow: var(--shadow-subtle);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .faq-section:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
        }
        
        .faq-header {
            background: var(--bg-secondary);
            padding: 24px;
            border-bottom: 1px solid var(--border-lighter);
        }
        
        .faq-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        .faq-header i {
            color: var(--accent-primary);
        }
        
        .faq-item {
            border-bottom: 1px solid var(--border-lighter);
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .faq-item:hover {
            background: var(--bg-secondary);
            transform: translateX(8px);
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
            transition: color 0.2s ease;
        }
        
        .faq-question:hover {
            color: var(--accent-primary);
        }
        
        .faq-answer {
            color: var(--text-secondary);
            margin-top: 16px;
            display: none;
            line-height: 1.6;
            padding: 16px;
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            border-left: 3px solid var(--accent-primary);
        }
        
        .faq-answer.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .faq-toggle {
            color: var(--accent-primary);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.875rem;
        }
        
        .faq-toggle.rotated {
            transform: rotate(180deg);
        }
        
        /* Témoignages Minimalistes */
        .testimonial-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px;
            margin-bottom: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        
        .testimonial-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .testimonial-rating {
            color: #ff9500;
            margin-bottom: 16px;
            font-size: 1.125rem;
        }
        
        .testimonial-text {
            font-style: italic;
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 1rem;
        }
        
        .testimonial-author {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .testimonial-service {
            color: var(--accent-primary);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        /* Formulaire de Support Minimaliste */
        .support-form {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 40px;
            box-shadow: var(--shadow-subtle);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .support-form:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-4px);
        }
        
        .support-form h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 24px;
            color: var(--text-primary);
        }
        
        .support-form h4 i {
            color: var(--accent-primary);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-medium);
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        .form-control:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: var(--text-tertiary);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        /* Boutons Minimalistes */
        .btn {
            border-radius: var(--radius-medium);
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            padding: 12px 24px;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056cc;
            border-color: #0056cc;
            color: white;
        }
        
        .w-100 {
            width: 100%;
        }
        
        /* Informations de Contact Minimalistes */
        .contact-info {
            background: var(--bg-secondary);
            border-radius: var(--radius-large);
            padding: 32px;
            text-align: center;
            border: 1px solid var(--border-lighter);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .contact-info:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-medium);
        }
        
        .contact-info h4 {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 24px;
            color: var(--text-primary);
        }
        
        .contact-info h5 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }
        
        .contact-info p {
            color: var(--text-secondary);
            margin-bottom: 4px;
            font-weight: 500;
        }
        
        .contact-info small {
            color: var(--text-tertiary);
            font-size: 0.875rem;
        }
        
        .contact-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 2rem;
            color: white;
        }
        
        /* Alertes Minimalistes */
        .alert {
            border: none;
            border-radius: var(--radius-medium);
            padding: 16px 20px;
            margin-bottom: 24px;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(52, 199, 89, 0.1);
            color: var(--accent-success);
            border-left: 4px solid var(--accent-success);
        }
        
        .alert-danger {
            background: rgba(255, 59, 48, 0.1);
            color: var(--accent-danger);
            border-left: 4px solid var(--accent-danger);
        }
        
        .alert i {
            margin-right: 8px;
        }
        
        /* Footer Minimaliste */
        .footer {
            background: var(--bg-dark);
            color: var(--text-light);
            padding: 40px 0;
            text-align: center;
        }
        
        .footer p {
            color: var(--text-secondary);
            margin: 0;
        }
        
        /* Animations */
        .fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
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
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Mobile First */
        @media (max-width: 576px) {
            .section {
                padding: 40px 0;
            }
            
            .support-hero {
                padding: 80px 0 40px;
            }
            
            .support-hero h1 {
                font-size: 2rem;
                line-height: 1.2;
            }
            
            .support-hero .lead {
                font-size: 1rem;
                line-height: 1.5;
            }
            
            .section-title {
                font-size: 1.75rem;
            }
            
            .support-form {
                padding: 20px;
            }
            
            .contact-info {
                padding: 20px;
            }
            
            .faq-item {
                padding: 16px;
            }
            
            .faq-header {
                padding: 20px;
            }
            
            .faq-header h3 {
                font-size: 1.125rem;
            }
            
            .testimonial-card {
                padding: 20px;
                margin-bottom: 16px;
            }
            
            .contact-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin-bottom: 12px;
            }
            
            .contact-info h4 {
                font-size: 1.25rem;
            }
            
            .contact-info h5 {
                font-size: 1rem;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .navbar-nav .nav-link {
                padding: 6px 12px;
                margin: 2px;
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .form-control {
                padding: 10px 14px;
                font-size: 0.9rem;
            }
            
            .form-label {
                font-size: 0.9rem;
            }
        }
        
        @media (min-width: 577px) and (max-width: 768px) {
            .section {
                padding: 60px 0;
            }
            
            .support-hero {
                padding: 100px 0 60px;
            }
            
            .support-hero h1 {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .support-form {
                padding: 24px;
            }
            
            .contact-info {
                padding: 24px;
            }
            
            .faq-item {
                padding: 20px;
            }
            
            .testimonial-card {
                padding: 24px;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .support-hero h1 {
                font-size: 3rem;
            }
            
            .support-form {
                padding: 32px;
            }
            
            .contact-info {
                padding: 28px;
            }
        }
        
        @media (min-width: 1025px) {
            .support-hero h1 {
                font-size: 3.5rem;
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
                        <div class="faq-section fade-in-up">
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
                                    <div class="testimonial-card fade-in-up">
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
                    <div class="support-form mb-4 fade-in-up">
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
                    <div class="contact-info fade-in-up">
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
        
        // Animation au scroll avec Intersection Observer
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
        
        // Animation des FAQ au hover
        document.querySelectorAll('.faq-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(8px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
        
        // Animation des cartes de témoignages
        document.querySelectorAll('.testimonial-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Animation du formulaire
        const form = document.querySelector('.support-form');
        if (form) {
            form.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
            });
            
            form.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        }
        
        // Animation des informations de contact
        const contactInfo = document.querySelector('.contact-info');
        if (contactInfo) {
            contactInfo.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
            });
            
            contactInfo.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        }
    </script>
</body>
</html>