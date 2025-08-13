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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --bg-dark: #0f172a;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --bg-gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --bg-gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            --text-light: #ffffff;
            --accent-primary: #3b82f6;
            --accent-secondary: #8b5cf6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --accent-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            --accent-gradient-2: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            --accent-gradient-3: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --border-light: #e2e8f0;
            --border-lighter: #f1f5f9;
            --shadow-subtle: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-large: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-glow: 0 0 20px rgba(59, 130, 246, 0.15);
            --shadow-hero: 0 20px 40px rgba(0, 0, 0, 0.1);
            --radius-small: 8px;
            --radius-medium: 12px;
            --radius-large: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
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
        
        /* Navigation Professionnelle et Moderne */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-light);
            padding: 16px 0;
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
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
            transition: all 0.2s ease;
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--accent-primary) !important;
            background: rgba(59, 130, 246, 0.05);
        }
        
        /* Hero Section Moderne avec Image IA */
        .hero-section {
            min-height: 100vh;
            background: var(--bg-gradient);
            position: relative;
            display: flex;
            align-items: center;
            padding-top: 100px;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><defs><radialGradient id="hero" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="150" fill="url(%23hero)"/><circle cx="1000" cy="300" r="200" fill="url(%23hero)"/><circle cx="400" cy="700" r="180" fill="url(%23hero)"/></svg>') no-repeat;
            background-size: cover;
            opacity: 0.8;
            animation: float 25s ease-in-out infinite;
        }
        
        .hero-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }
        
        .hero-content {
            text-align: left;
        }
        
        .hero-image {
            position: relative;
            text-align: center;
        }
        
        .hero-image img {
            width: 100%;
            max-width: 500px;
            height: auto;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-hero);
            animation: float 6s ease-in-out infinite;
        }
        
        .hero-image::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background: var(--accent-gradient-3);
            border-radius: var(--radius-2xl);
            opacity: 0.3;
            z-index: -1;
            animation: pulse 4s ease-in-out infinite;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .hero-title {
            font-size: clamp(3rem, 10vw, 5rem);
            font-weight: 800;
            color: var(--text-light);
            margin-bottom: 1.5rem;
            line-height: 1.1;
            letter-spacing: -0.03em;
            animation: fadeInUp 1s ease-out 0.3s both;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .hero-title .highlight {
            background: var(--accent-gradient-3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: none;
        }
        
        .hero-subtitle {
            font-size: clamp(1.2rem, 4vw, 1.5rem);
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 2.5rem;
            font-weight: 400;
            line-height: 1.6;
            max-width: 500px;
            animation: fadeInUp 1s ease-out 0.6s both;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.9s both;
        }
        
        .hero-btn {
            padding: 18px 36px;
            border-radius: var(--radius-large);
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            display: inline-block;
            margin: 0 12px;
            overflow: hidden;
        }
        
        .hero-btn-primary {
            background: var(--accent-gradient-3);
            color: white;
            box-shadow: var(--shadow-medium);
        }
        
        .hero-btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-glow);
            color: white;
        }
        
        .hero-btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }
        
        .hero-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            color: white;
            transform: translateY(-3px) scale(1.05);
        }
        
        /* Section Présentation Moderne */
        .presentation-section {
            padding: 120px 0;
            background: var(--bg-primary);
            position: relative;
        }
        
        .presentation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .presentation-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            padding: 50px 30px;
            text-align: center;
            border: 1px solid var(--border-light);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .presentation-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .presentation-card:hover::before {
            left: 100%;
        }
        
        .presentation-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .presentation-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: var(--accent-gradient);
            color: white;
            position: relative;
        }
        
        .presentation-icon::after {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid transparent;
            border-radius: 50%;
            background: var(--accent-gradient) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .presentation-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        .presentation-text {
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 1.1rem;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 80px;
            position: relative;
            z-index: 2;
        }
        
        /* Section Avantages Professionnelle */
        .benefits-section {
            padding: 100px 0;
            background: var(--bg-secondary);
            position: relative;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .benefit-card {
            background: var(--bg-primary);
            border-radius: var(--radius-large);
            padding: 40px 30px;
            text-align: center;
            box-shadow: var(--shadow-subtle);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }
        
        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
            border-color: var(--accent-primary);
        }
        
        .benefit-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: var(--accent-gradient);
            color: white;
        }
        
        .benefit-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 15px;
        }
        
        .benefit-text {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        /* Section FAQ Moderne */
        .faq-section {
            padding: 120px 0;
            background: var(--bg-secondary);
            position: relative;
        }
        
        .faq-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .faq-item {
            background: var(--bg-primary);
            border-radius: var(--radius-large);
            margin-bottom: 20px;
            border: 1px solid var(--border-light);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            box-shadow: var(--shadow-medium);
            border-color: var(--accent-primary);
        }
        
        .faq-question {
            padding: 25px 30px;
            background: var(--bg-primary);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background: var(--bg-tertiary);
        }
        
        .faq-question i {
            color: var(--accent-primary);
            transition: transform 0.3s ease;
        }
        
        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            padding: 0 30px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .faq-item.active .faq-answer {
            padding: 0 30px 25px;
            max-height: 200px;
        }
        
        .section-title {
            font-size: clamp(3rem, 10vw, 4.5rem);
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
            background: var(--accent-gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .social-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }
        
        .social-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 50px 30px;
            text-align: center;
            box-shadow: var(--shadow-medium);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-lighter);
        }
        
        .social-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.6s ease;
        }
        
        .social-card:hover::before {
            left: 100%;
        }
        
        .social-card:hover {
            transform: translateY(-12px) scale(1.03);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .social-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .social-icon.instagram {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            color: white;
        }
        
        .social-icon.tiktok {
            background: linear-gradient(45deg, #000000 0%, #25f4ee 50%, #fe2c55 100%);
            color: white;
        }
        
        .social-icon.youtube {
            background: linear-gradient(45deg, #ff0000 0%, #ff4444 100%);
            color: white;
        }
        
        .social-icon.facebook {
            background: linear-gradient(45deg, #1877f2 0%, #42a5f5 100%);
            color: white;
        }
        
        .social-icon::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .social-card:hover .social-icon::after {
            opacity: 1;
        }
        
        .social-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 15px;
        }
        
        .social-description {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .social-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 25px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-primary);
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .social-btn {
            display: inline-block;
            padding: 16px 32px;
            background: var(--accent-gradient);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-medium);
            font-weight: 600;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-glow);
            color: white;
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
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
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .social-card {
            animation: fadeInUp 0.8s ease-out both;
        }
        
        .social-card:nth-child(1) { animation-delay: 0.1s; }
        .social-card:nth-child(2) { animation-delay: 0.2s; }
        .social-card:nth-child(3) { animation-delay: 0.3s; }
        .social-card:nth-child(4) { animation-delay: 0.4s; }
        
        /* Section Preuves Sociales Professionnelle */
        .social-proof-section {
            padding: 100px 0;
            background: var(--bg-primary);
            position: relative;
        }
        
        .social-proof-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .proof-card {
            text-align: center;
            padding: 40px 20px;
            background: var(--bg-secondary);
            border-radius: var(--radius-large);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }
        
        .proof-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }
        
        .proof-number {
            font-size: clamp(2.5rem, 6vw, 3.5rem);
            font-weight: 800;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            display: block;
        }
        
        .proof-label {
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Section Call-to-Action Finale */
        .cta-section {
            padding: 100px 0;
            background: var(--accent-gradient);
            position: relative;
            text-align: center;
            color: white;
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .cta-title {
            font-size: clamp(2.5rem, 8vw, 3.5rem);
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .cta-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-btn {
            padding: 18px 36px;
            border-radius: var(--radius-large);
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .cta-btn-primary {
            background: white;
            color: var(--accent-primary);
        }
        
        .cta-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-large);
            color: var(--accent-primary);
        }
        
        .cta-btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.8);
        }
        
        .cta-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            transform: translateY(-2px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }
        
        .stat-card {
            text-align: center;
            padding: 50px 30px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--radius-xl);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-lighter);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--accent-gradient);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }
        
        .stat-card:hover::before {
            opacity: 0.05;
        }
        
        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            background: var(--accent-gradient);
            color: white;
            position: relative;
        }
        
        .stat-icon::after {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid transparent;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--accent-primary), var(--accent-secondary)) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .stat-number {
            font-size: clamp(3rem, 8vw, 4rem);
            font-weight: 800;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            display: block;
            letter-spacing: -0.02em;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card {
            animation: fadeInUp 0.8s ease-out both;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
        /* Responsive Design Professionnel */
        @media (max-width: 1024px) {
            .hero-container {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }
            
            .hero-content {
                text-align: center;
            }
            
            .presentation-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
            
            .benefits-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding-top: 100px;
                min-height: 90vh;
            }
            
            .hero-title {
                font-size: clamp(2rem, 10vw, 3rem);
            }
            
            .hero-subtitle {
                font-size: clamp(1rem, 4vw, 1.3rem);
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .hero-btn {
                width: 100%;
                max-width: 300px;
                text-align: center;
            }
            
            .problem-solution-section,
            .benefits-section,
            .social-proof-section,
            .cta-section {
                padding: 60px 0;
            }
            
            .problem-solution-grid {
                gap: 30px;
                padding: 0 15px;
            }
            
            .benefits-grid {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 0 15px;
            }
            
            .social-proof-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 25px;
                padding: 0 15px;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .cta-btn {
                width: 100%;
                max-width: 300px;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .hero-section {
                padding-top: 120px;
                min-height: 85vh;
            }
            
            .hero-title {
                font-size: clamp(1.8rem, 12vw, 2.5rem);
            }
            
            .hero-subtitle {
                font-size: clamp(0.9rem, 5vw, 1.1rem);
            }
            
            .problem-solution-section,
            .benefits-section,
            .social-proof-section,
            .cta-section {
                padding: 50px 0;
            }
            
            .section-header {
                margin-bottom: 40px;
            }
            
            .section-title {
                font-size: clamp(1.5rem, 8vw, 2.5rem);
            }
        }
        
        /* Animations MAGNIFIQUES */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
        }
        
        .hero-section::before {
            animation: float 20s ease-in-out infinite;
        }
        
        .social-card {
            animation: fadeInUp 0.8s ease-out both;
        }
        
        .social-card:nth-child(1) { animation-delay: 0.1s; }
        .social-card:nth-child(2) { animation-delay: 0.2s; }
        .social-card:nth-child(3) { animation-delay: 0.3s; }
        .social-card:nth-child(4) { animation-delay: 0.4s; }
        
        .stat-card {
            animation: fadeInUp 0.8s ease-out both;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
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
        
        .navbar-nav .btn {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white !important;
            border-radius: var(--radius-medium);
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .navbar-nav .btn:hover {
            background: #0056cc;
            border-color: #0056cc;
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
        }
        
        .dropdown-menu {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-medium);
            box-shadow: var(--shadow-large);
            padding: 8px 0;
        }
        
        .dropdown-item {
            color: var(--text-primary);
            padding: 8px 20px;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(0, 122, 255, 0.04);
            color: var(--accent-primary);
        }
        
        .navbar-toggler {
            border: none;
            padding: 4px 8px;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        /* Sections Générales */
        .section {
            padding: 80px 0;
        }
        
        .section-header {
            margin-bottom: 60px;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        
        .section-subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
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
        
        .btn-outline-primary {
            border-color: var(--accent-primary);
            color: var(--accent-primary);
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }
        
        .btn-lg {
            padding: 16px 32px;
            font-size: 1.125rem;
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
        
        /* Hero Section Minimaliste */
        .hero {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-content {
            animation: fadeInLeft 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 24px;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            line-height: 1.1;
        }
        
        .text-gradient {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 32px;
            line-height: 1.6;
        }
        
        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .hero-visual {
            animation: fadeInRight 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .floating-icons {
            position: relative;
            height: 400px;
        }
        
        .icon-item {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: var(--bg-primary);
            border-radius: var(--radius-large);
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--border-lighter);
            transition: all 0.3s ease;
        }
        
        .icon-item:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-large);
        }
        
        .icon-item i {
            font-size: 2rem;
            color: var(--accent-primary);
        }
        
        .icon-item span {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        .icon-item.instagram {
            top: 20px;
            left: 20px;
            animation: float 6s ease-in-out infinite;
        }
        
        .icon-item.tiktok {
            top: 100px;
            right: 40px;
            animation: float 6s ease-in-out infinite 1s;
        }
        
        .icon-item.youtube {
            bottom: 120px;
            left: 60px;
            animation: float 6s ease-in-out infinite 2s;
        }
        
        .icon-item.facebook {
            bottom: 40px;
            right: 20px;
            animation: float 6s ease-in-out infinite 3s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Services Section Minimaliste */
        .service-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2rem;
            color: white;
        }
        
        .service-card h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        
        .service-card p {
            color: var(--text-secondary);
            margin-bottom: 24px;
            line-height: 1.6;
        }
        
        /* Features Section Minimaliste */
        .bg-dark {
            background: var(--bg-dark) !important;
        }
        
        .feature-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2rem;
            color: white;
        }
        
        .feature-card h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        
        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        /* About Section Minimaliste */
        .about-stats {
            margin-top: 32px;
        }
        
        .stat-item {
            text-align: center;
            padding: 24px;
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            border: 1px solid var(--border-lighter);
        }
        
        .stat-item h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-primary);
            margin-bottom: 8px;
        }
        
        .stat-item p {
            color: var(--text-secondary);
            font-weight: 500;
            margin: 0;
        }
        
        .about-image img {
            border-radius: var(--radius-large);
            box-shadow: var(--shadow-medium);
        }
        
        .about-content .lead {
            font-size: 1.125rem;
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .about-content p {
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        /* Contact Section Minimaliste */
        .contact-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-lighter);
            border-radius: var(--radius-large);
            padding: 32px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-subtle);
            height: 100%;
        }
        
        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-large);
            border-color: var(--accent-primary);
        }
        
        .contact-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2rem;
            color: white;
        }
        
        .contact-card h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        
        .contact-card p {
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .contact-card small {
            color: var(--text-tertiary);
            font-size: 0.875rem;
        }
        
        /* Footer Minimaliste */
        .footer {
            background: var(--bg-dark);
            color: var(--text-light);
            padding: 60px 0 30px;
        }
        
        .footer h5, .footer h6 {
            color: var(--text-light);
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .footer p {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .footer ul {
            list-style: none;
            padding: 0;
        }
        
        .footer ul li {
            margin-bottom: 12px;
        }
        
        .footer ul li a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .footer ul li a:hover {
            color: var(--accent-primary);
        }
        
        .footer hr {
            border-color: var(--border-light);
            margin: 40px 0;
        }
        
        /* Responsive Mobile First */
        @media (max-width: 576px) {
            .section {
                padding: 40px 0;
            }
            
            .hero {
                padding: 80px 0 40px;
            }
            
            .hero-title {
                font-size: 2rem;
                line-height: 1.2;
            }
            
            .hero-subtitle {
                font-size: 1rem;
                line-height: 1.5;
            }
            
            .section-title {
                font-size: 1.75rem;
            }
            
            .section-subtitle {
                font-size: 1rem;
            }
            
            .btn-lg {
                padding: 12px 24px;
                font-size: 0.9rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            
            .floating-icons {
                height: 250px;
                margin-top: 30px;
            }
            
            .icon-item {
                padding: 12px;
                font-size: 0.8rem;
            }
            
            .icon-item i {
                font-size: 1.25rem;
            }
            
            .icon-item span {
                font-size: 0.75rem;
            }
            
            .about-stats .row {
                gap: 12px;
            }
            
            .stat-item {
                padding: 16px;
            }
            
            .stat-item h3 {
                font-size: 1.75rem;
            }
            
            .stat-item p {
                font-size: 0.875rem;
            }
            
            .service-card,
            .feature-card,
            .contact-card {
                padding: 20px;
                margin-bottom: 16px;
            }
            
            .service-icon,
            .feature-icon,
            .contact-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin-bottom: 16px;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .navbar-nav .nav-link {
                padding: 6px 12px;
                margin: 2px;
                font-size: 0.9rem;
            }
        }
        
        @media (min-width: 577px) and (max-width: 768px) {
            .section {
                padding: 60px 0;
            }
            
            .hero {
                padding: 100px 0 60px;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .btn-lg {
                padding: 14px 28px;
                font-size: 1rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .floating-icons {
                height: 300px;
                margin-top: 40px;
            }
            
            .icon-item {
                padding: 16px;
            }
            
            .icon-item i {
                font-size: 1.5rem;
            }
            
            .about-stats .row {
                gap: 16px;
            }
            
            .stat-item {
                padding: 20px;
            }
            
            .stat-item h3 {
                font-size: 2rem;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .floating-icons {
                height: 350px;
            }
            
            .icon-item {
                padding: 18px;
            }
            
            .icon-item i {
                font-size: 1.75rem;
            }
        }
        
        @media (min-width: 1025px) {
            .hero-title {
                font-size: 3.5rem;
            }
            
            .floating-icons {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
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
                            <ul class="dropdown-menu">
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
                            <a class="nav-link btn btn-primary btn-sm" href="inscription.php">
                                <i class="fas fa-user-plus me-1"></i>Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section Moderne avec Image IA -->
    <section class="hero-section" id="home">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Boostez Votre <span class="highlight">Présence Sociale</span> en 24h
                </h1>
                <p class="hero-subtitle">
                    Services SMM professionnels pour Instagram, TikTok, YouTube et Facebook. 
                    Obtenez des followers, likes et vues de qualité pour propulser votre influence en ligne.
                </p>
                <div class="hero-buttons">
                    <a href="commander.php" class="hero-btn hero-btn-primary">
                        <i class="fas fa-rocket me-2"></i>Commander Maintenant
                    </a>
                    <a href="#presentation" class="hero-btn hero-btn-secondary">
                        <i class="fas fa-star me-2"></i>Découvrir nos Services
                    </a>
                </div>
            </div>
            
            <div class="hero-image">
                <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 500 400'><defs><linearGradient id='heroImg' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' stop-color='%233b82f6'/><stop offset='100%' stop-color='%238b5cf6'/></linearGradient></defs><rect width='500' height='400' fill='url(%23heroImg)' rx='20'/><circle cx='100' cy='100' r='30' fill='white' opacity='0.2'/><circle cx='400' cy='80' r='25' fill='white' opacity='0.15'/><circle cx='80' cy='300' r='35' fill='white' opacity='0.1'/><path d='M150 200 Q250 150 350 200 T450 250' stroke='white' stroke-width='3' fill='none' opacity='0.6'/><text x='250' y='220' text-anchor='middle' fill='white' font-family='Arial' font-size='24' font-weight='bold'>SMM Pro</text><text x='250' y='250' text-anchor='middle' fill='white' font-family='Arial' font-size='16'>Marketing Social</text></svg>" alt="SMM Pro - Marketing sur les Réseaux Sociaux" />
            </div>
        </div>
    </section>
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
                </div>
                <div class="col-lg-6">
                    <div class="hero-visual">
                        <div class="floating-icons">
                            <div class="icon-item instagram">
                                <i class="fab fa-instagram"></i>
                                <span>Instagram</span>
                            </div>
                            <div class="icon-item tiktok">
                                <i class="fab fa-tiktok"></i>
                                <span>TikTok</span>
                            </div>
                            <div class="icon-item youtube">
                                <i class="fab fa-youtube"></i>
                                <span>YouTube</span>
                            </div>
                            <div class="icon-item facebook">
                                <i class="fab fa-facebook"></i>
                                <span>Facebook</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Présentation Moderne -->
    <section class="presentation-section" id="presentation">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Nos Services SMM</h2>
                <p class="section-subtitle">
                    Découvrez comment nous transformons votre présence sur les réseaux sociaux
                </p>
            </div>
            
            <div class="presentation-grid">
                <div class="presentation-card">
                    <div class="presentation-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3 class="presentation-title">Croissance Rapide</h3>
                    <p class="presentation-text">
                        Boostez votre visibilité en 24h avec nos services SMM professionnels. 
                        Résultats garantis et visibles immédiatement.
                    </p>
                </div>
                
                <div class="presentation-card">
                    <div class="presentation-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="presentation-title">Sécurité Totale</h3>
                    <p class="presentation-text">
                        Vos comptes sont protégés avec nos méthodes sécurisées et respectueuses 
                        des plateformes. Aucun risque de bannissement.
                    </p>
                </div>
                
                <div class="presentation-card">
                    <div class="presentation-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="presentation-title">Engagement Garanti</h3>
                    <p class="presentation-text">
                        Augmentez vos likes, commentaires et partages avec des interactions 
                        authentiques et durables.
                    </p>
                </div>
                
                <div class="presentation-card">
                    <div class="presentation-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="presentation-title">Multi-Plateformes</h3>
                    <p class="presentation-text">
                        Instagram, TikTok, YouTube, Facebook : nous couvrons tous les réseaux 
                        sociaux populaires avec des services adaptés.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Avantages -->
    <section class="benefits-section" id="benefits">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Nos Avantages Uniques</h2>
                <p class="section-subtitle">
                    Découvrez ce qui nous rend différents de la concurrence
                </p>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="benefit-title">Sécurité Garantie</h3>
                    <p class="benefit-text">
                        Vos comptes sont protégés avec nos méthodes sécurisées et respectueuses des plateformes.
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="benefit-title">Livraison Rapide</h3>
                    <p class="benefit-text">
                        Recevez vos followers, likes et vues dans les délais indiqués, généralement entre 1h et 72h.
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="benefit-title">Support 24/7</h3>
                    <p class="benefit-text">
                        Notre équipe support est disponible 24h/24 et 7j/7 pour vous assister.
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="benefit-title">Qualité Premium</h3>
                    <p class="benefit-text">
                        Des services de haute qualité pour des résultats durables et visibles.
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="benefit-title">Paiement Sécurisé</h3>
                    <p class="benefit-text">
                        Paiements sécurisés via MTN Money et Moov Money avec suivi en temps réel.
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="benefit-title">Communauté Active</h3>
                    <p class="benefit-text">
                        Rejoignez notre communauté de clients satisfaits et boostez votre visibilité.
                    </p>
                </div>
            </div>
                </div>
    </section>
    
    <!-- Section FAQ Moderne -->
    <section class="faq-section" id="faq">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Questions Fréquentes</h2>
                <p class="section-subtitle">
                    Tout ce que vous devez savoir sur nos services SMM
                </p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Comment fonctionnent vos services SMM ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Nos services SMM utilisent des méthodes professionnelles et sécurisées pour augmenter votre visibilité sur les réseaux sociaux. Nous travaillons avec des partenaires de confiance pour garantir des résultats authentiques et durables.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Combien de temps pour voir les résultats ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        La plupart de nos services commencent à livrer dans les 1-2 heures suivant la commande. La livraison complète se fait généralement entre 24h et 72h selon le service choisi.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Vos services sont-ils sûrs pour mon compte ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Absolument ! Nous utilisons des méthodes respectueuses des plateformes et des comptes de haute qualité. Vos comptes sont protégés et il n'y a aucun risque de bannissement.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Quels réseaux sociaux supportez-vous ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Nous supportons Instagram, TikTok, YouTube et Facebook. Chaque plateforme a ses spécificités et nous adaptons nos services en conséquence pour des résultats optimaux.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Que se passe-t-il si je ne suis pas satisfait ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Nous offrons une garantie de satisfaction. Si vous n'êtes pas satisfait de nos services, nous vous remboursons ou relivrons gratuitement jusqu'à votre satisfaction.
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Section Réseaux Sociaux -->
    <section class="social-networks-section" id="services">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Réseaux Sociaux</h2>
                    <p class="section-subtitle">
                        Boostez votre présence sur les plateformes les plus populaires
                    </p>
                </div>
                
                <div class="social-grid">
                    <div class="social-card">
                        <div class="social-icon instagram">
                            <i class="fab fa-instagram"></i>
                        </div>
                        <h3 class="social-title">Instagram</h3>
                        <p class="social-description">
                            Augmentez votre visibilité sur Instagram avec des followers, likes et commentaires authentiques.
                        </p>
                        <div class="social-stats">
                            <div class="stat-item">
                                <span class="stat-number">50K+</span>
                                <span class="stat-label">Followers</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">100K+</span>
                                <span class="stat-label">Likes</span>
                            </div>
                        </div>
                        <a href="commander.php?category=1" class="social-btn">
                            <i class="fas fa-rocket me-2"></i>Commander
                        </a>
                    </div>
                    
                    <div class="social-card">
                        <div class="social-icon tiktok">
                            <i class="fab fa-tiktok"></i>
                        </div>
                        <h3 class="social-title">TikTok</h3>
                        <p class="social-description">
                            Propulsez vos vidéos TikTok avec des vues, likes et followers pour maximiser votre portée.
                        </p>
                        <div class="social-stats">
                            <div class="stat-item">
                                <span class="stat-number">100K+</span>
                                <span class="stat-label">Vues</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">25K+</span>
                                <span class="stat-label">Likes</span>
                            </div>
                        </div>
                        <a href="commander.php?category=2" class="social-btn">
                            <i class="fas fa-rocket me-2"></i>Commander
                        </a>
                    </div>
                    
                    <div class="social-card">
                        <div class="social-icon youtube">
                            <i class="fab fa-youtube"></i>
                        </div>
                        <h3 class="social-title">YouTube</h3>
                        <p class="social-description">
                            Développez votre chaîne YouTube avec des abonnés, vues et likes pour augmenter votre monétisation.
                        </p>
                        <div class="social-stats">
                            <div class="stat-item">
                                <span class="stat-number">10K+</span>
                                <span class="stat-label">Abonnés</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">500K+</span>
                                <span class="stat-label">Vues</span>
                            </div>
                        </div>
                        <a href="commander.php?category=3" class="social-btn">
                            <i class="fas fa-rocket me-2"></i>Commander
                        </a>
                    </div>
                    
                    <div class="social-card">
                        <div class="social-icon facebook">
                            <i class="fab fa-facebook"></i>
                        </div>
                        <h3 class="social-title">Facebook</h3>
                        <p class="social-description">
                            Renforcez votre page Facebook avec des fans, likes et partages pour une meilleure engagement.
                        </p>
                        <div class="social-stats">
                            <div class="stat-item">
                                <span class="stat-number">20K+</span>
                                <span class="stat-label">Fans</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">75K+</span>
                                <span class="stat-label">Likes</span>
                            </div>
                        </div>
                        <a href="commander.php?category=4" class="social-btn">
                            <i class="fas fa-rocket me-2"></i>Commander
                        </a>
                    </div>
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
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Sécurité Garantie</h4>
                        <p>Vos comptes sont protégés avec nos méthodes sécurisées et respectueuses des plateformes.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h4>Livraison Rapide</h4>
                        <p>Recevez vos followers, likes et vues dans les délais indiqués, généralement entre 1h et 72h.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Support 24/7</h4>
                        <p>Notre équipe support est disponible 24h/24 et 7j/7 pour vous assister.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Qualité Premium</h4>
                        <p>Des services de haute qualité pour des résultats durables et visibles.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Paiement Sécurisé</h4>
                        <p>Paiements sécurisés via MTN Money et Moov Money avec suivi en temps réel.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in-up">
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
    
    <!-- Section Preuves Sociales -->
    <section class="social-proof-section" id="social-proof">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Ils Nous Font Confiance</h2>
                <p class="section-subtitle">
                    Découvrez pourquoi des milliers de clients nous font confiance
                </p>
            </div>
            
            <div class="social-proof-grid">
                <div class="proof-card">
                    <span class="proof-number" data-target="15000">0</span>
                    <div class="proof-label">Clients Satisfaits</div>
                </div>
                
                <div class="proof-card">
                    <span class="proof-number" data-target="50000">0</span>
                    <div class="proof-label">Commandes Livrées</div>
                </div>
                
                <div class="proof-card">
                    <span class="proof-number" data-target="24">0</span>
                    <div class="proof-label">Heures de Support</div>
                </div>
                
                <div class="proof-card">
                    <span class="proof-number" data-target="99">0</span>
                    <div class="proof-label">% de Satisfaction</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Section Call-to-Action Finale -->
    <section class="cta-section" id="cta">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Prêt à Booster Votre Présence ?</h2>
                <p class="cta-subtitle">
                    Rejoignez des milliers de clients satisfaits et transformez votre visibilité sociale dès aujourd'hui
                </p>
                <div class="cta-buttons">
                    <a href="commander.php" class="cta-btn cta-btn-primary">
                        <i class="fas fa-rocket me-2"></i>Commander Maintenant
                    </a>
                    <a href="connexion.php" class="cta-btn cta-btn-secondary">
                        <i class="fas fa-user me-2"></i>Créer un Compte
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Section Statistiques -->
    <section class="stats-section">
        <div class="container">
                            <div class="section-header text-center">
                    <h2 class="section-title">Chiffres Clés</h2>
                    <p class="section-subtitle">
                        Pourquoi des milliers de clients nous font confiance
                    </p>
                </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="stat-number" data-target="15000">0</span>
                    <div class="stat-label">Clients Satisfaits</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <span class="stat-number" data-target="50000">0</span>
                    <div class="stat-label">Commandes Livrées</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="stat-number" data-target="24">0</span>
                    <div class="stat-label">Heures de Support</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <span class="stat-number" data-target="99">0</span>
                    <div class="stat-label">% de Satisfaction</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="about" class="section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-content fade-in-up">
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
                </div>
                <div class="col-lg-6">
                    <div class="about-image fade-in-up">
                        <img src="https://via.placeholder.com/500x400/f5f5f7/007aff?text=SMM+Pro" 
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
                    <div class="contact-card fade-in-up">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h4>WhatsApp</h4>
                        <p>+225 0123456789</p>
                        <small>Réponse immédiate</small>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="contact-card fade-in-up">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email</h4>
                        <p>contact@smmpro.com</p>
                        <small>Réponse sous 24h</small>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="contact-card fade-in-up">
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
        
        // Animation des compteurs Professionnels
        function animateCounters() {
            const counters = document.querySelectorAll('.proof-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // 2 secondes
                const step = target / (duration / 16); // 60 FPS
                let current = 0;
                
                const updateCounter = () => {
                    current += step;
                    if (current < target) {
                        counter.textContent = Math.floor(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
            });
        }
        
        // Effet de parallaxe sur le scroll
        function handleParallax() {
            const scrolled = window.pageYOffset;
            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                const speed = 0.5;
                const yPos = -(scrolled * speed);
                heroSection.style.transform = `translateY(${yPos}px)`;
            }
        }
        
        // Observer pour les compteurs
        const proofObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    proofObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        const proofSection = document.querySelector('.social-proof-section');
        if (proofSection) {
            proofObserver.observe(proofSection);
        }
        
        // Effet de parallaxe
        window.addEventListener('scroll', handleParallax);
        
        // Animation des cartes au scroll
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, { threshold: 0.2 });
        
        document.querySelectorAll('.social-card, .stat-card, .presentation-card').forEach(card => {
            cardObserver.observe(card);
        });
        
        // FAQ Interactive
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const faqItem = question.parentElement;
                const isActive = faqItem.classList.contains('active');
                
                // Fermer toutes les autres FAQ
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Ouvrir/fermer la FAQ cliquée
                if (!isActive) {
                    faqItem.classList.add('active');
                }
            });
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