<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de la connexion utilisateur
if (!isUserLoggedIn()) {
    redirect('../connexion.php');
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    session_destroy();
    redirect('../connexion.php');
}

// Récupération des statistiques utilisateur avec gestion d'erreur
try {
    $userStats = getUserStats($currentUser['id']);
} catch (Exception $e) {
    $userStats = [
        'total_orders' => 0,
        'total_spent' => 0,
        'orders_by_status' => [],
        'tickets_by_status' => []
    ];
}

// Récupération des commandes récentes avec gestion d'erreur
try {
    $recentOrders = getUserOrders($currentUser['id'], 5);
} catch (Exception $e) {
    $recentOrders = [];
}

// Récupération des tickets récents avec gestion d'erreur
try {
    $recentTickets = getSupportTickets(null, 5, $currentUser['id']);
} catch (Exception $e) {
    $recentTickets = [];
}

// Récupération des notifications avec gestion d'erreur
try {
    $notifications = getUserNotifications($currentUser['id'], 5);
} catch (Exception $e) {
    $notifications = [];
}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BoostSocial</title>
    <meta name="description" content="Votre tableau de bord personnel pour gérer vos commandes SMM et suivre vos performances">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Apple Design System Colors */
            --apple-blue: #007AFF;
            --apple-blue-dark: #0056CC;
            --apple-green: #34C759;
            --apple-orange: #FF9500;
            --apple-red: #FF3B30;
            --apple-purple: #AF52DE;
            --apple-pink: #FF2D92;
            --apple-yellow: #FFCC00;
            
            /* Apple Neutral Colors */
            --apple-gray-1: #F2F2F7;
            --apple-gray-2: #E5E5EA;
            --apple-gray-3: #D1D1D6;
            --apple-gray-4: #C7C7CC;
            --apple-gray-5: #AEAEB2;
            --apple-gray-6: #8E8E93;
            --apple-gray-7: #636366;
            --apple-gray-8: #48484A;
            --apple-gray-9: #3A3A3C;
            --apple-gray-10: #2C2C2E;
            --apple-gray-11: #1C1C1E;
            --apple-gray-12: #000000;
            
            /* Apple System Colors */
            --system-background: #FFFFFF;
            --system-background-secondary: #F2F2F7;
            --system-background-tertiary: #FFFFFF;
            --system-grouped-background: #F2F2F7;
            --system-grouped-background-secondary: #FFFFFF;
            
            /* Apple Typography */
            --apple-font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            
            /* Apple Shadows & Effects */
            --apple-shadow-small: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            --apple-shadow-medium: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
            --apple-shadow-large: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
            --apple-shadow-xl: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
            
            /* Apple Border Radius */
            --apple-radius-small: 8px;
            --apple-radius-medium: 12px;
            --apple-radius-large: 16px;
            --apple-radius-xl: 20px;
            --apple-radius-2xl: 24px;
            
            /* Apple Transitions */
            --apple-transition-fast: 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --apple-transition-medium: 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --apple-transition-slow: 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            
            /* Legacy Support */
            --primary: var(--apple-blue);
            --primary-dark: var(--apple-blue-dark);
            --secondary: var(--apple-green);
            --accent: var(--apple-orange);
            --dark: var(--apple-gray-12);
            --darker: var(--apple-gray-12);
            --light: var(--apple-gray-1);
            --gray: var(--apple-gray-6);
            --gray-light: var(--apple-gray-2);
            --shadow: var(--apple-shadow-medium);
            --shadow-lg: var(--apple-shadow-large);
            --success: var(--apple-green);
            --warning: var(--apple-orange);
            --danger: var(--apple-red);
            --info: var(--apple-blue);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--apple-font-family);
            background: var(--system-background);
            min-height: 100vh;
            color: var(--apple-gray-12);
            line-height: 1.47059;
            font-weight: 400;
            letter-spacing: -0.022em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* Navigation Apple-like */
        .navbar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 0.5px solid rgba(0, 0, 0, 0.1);
            box-shadow: var(--apple-shadow-small);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0.75rem 0;
            transition: var(--apple-transition-medium);
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .navbar-toggler:focus {
            box-shadow: none;
            outline: none;
        }

        .navbar-toggler:hover {
            background: var(--gray-light);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(15, 23, 42, 0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .navbar-brand {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--apple-gray-12);
            text-decoration: none;
            letter-spacing: -0.025em;
            transition: var(--apple-transition-fast);
        }

        .navbar-brand span {
            background: linear-gradient(135deg, var(--apple-blue), var(--apple-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }
        
        .navbar-brand:hover {
            transform: scale(1.02);
        }

        .nav-link {
            color: var(--apple-gray-8);
            font-weight: 500;
            padding: 0.75rem 1.25rem;
            border-radius: var(--apple-radius-medium);
            transition: var(--apple-transition-medium);
            text-decoration: none;
            letter-spacing: -0.01em;
            position: relative;
            overflow: hidden;
        }

        .nav-link:hover {
            background: var(--apple-gray-1);
            color: var(--apple-blue);
            transform: translateY(-1px);
        }

        .nav-link.active {
            background: var(--apple-blue);
            color: white;
            font-weight: 600;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 122, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .nav-link:hover::before {
            left: 100%;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--apple-blue), var(--apple-purple));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            box-shadow: var(--apple-shadow-small);
            transition: var(--apple-transition-medium);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--apple-shadow-medium);
        }

        /* Bouton hamburger personnalisé */
        .hamburger-btn {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            width: 32px;
            height: 32px;
            background: rgba(0, 0, 0, 0.05);
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1001;
            transition: var(--apple-transition-medium);
            border-radius: var(--apple-radius-small);
            padding: 4px;
        }
        
        .hamburger-btn:hover {
            background: rgba(0, 0, 0, 0.1);
            transform: scale(1.05);
        }

        .hamburger-line {
            width: 100%;
            height: 2px;
            background: var(--apple-gray-8);
            border-radius: 1px;
            transition: var(--apple-transition-medium);
            transform-origin: center;
        }

        .hamburger-btn:hover .hamburger-line {
            background: var(--apple-blue);
        }

        /* Animation du hamburger */
        .hamburger-btn.active .hamburger-line:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }

        .hamburger-btn.active .hamburger-line:nth-child(2) {
            opacity: 0;
        }

        .hamburger-btn.active .hamburger-line:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }

        /* Menu de navigation */
        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 0 0 16px 16px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-light);
            border-top: none;
            overflow: hidden;
            transform: translateY(-100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 999;
        }

        .mobile-menu.active {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }

        .mobile-menu .navbar-nav {
            padding: 1rem;
        }

        .mobile-menu .nav-item {
            margin: 0.5rem 0;
        }

        .mobile-menu .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .mobile-menu .nav-link:hover {
            background: var(--primary);
            color: white;
            transform: translateX(10px);
        }

        .mobile-menu .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .btn-logout {
            background: transparent;
            border: 2px solid var(--danger);
            color: var(--danger);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
        }

        /* Dashboard Container */
        .dashboard-container {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--apple-gray-12) 0%, var(--apple-gray-11) 30%, var(--apple-blue) 100%);
            color: white;
            padding: 5rem 0;
            margin-bottom: 3rem;
            border-radius: var(--apple-radius-2xl);
            position: relative;
            overflow: hidden;
            box-shadow: var(--apple-shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>') no-repeat;
            opacity: 0.3;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-title {
            font-size: clamp(3rem, 6vw, 5rem);
            font-weight: 800;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ffffff, #f0f8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.03em;
            line-height: 1.1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .welcome-subtitle {
            font-size: 1.4rem;
            opacity: 0.95;
            margin-bottom: 2.5rem;
            font-weight: 400;
            letter-spacing: -0.01em;
            line-height: 1.4;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .welcome-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-welcome {
            padding: 16px 32px;
            border-radius: var(--apple-radius-large);
            font-weight: 600;
            text-decoration: none;
            transition: var(--apple-transition-medium);
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            letter-spacing: -0.01em;
            position: relative;
            overflow: hidden;
        }

        .btn-welcome.primary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .btn-welcome.primary:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-3px);
            color: white;
            box-shadow: var(--shadow-lg);
        }
        
        .btn-welcome.primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-welcome.primary:hover::before {
            left: 100%;
        }

        .btn-welcome.secondary {
            background: white;
            color: var(--primary);
            position: relative;
            overflow: hidden;
        }

        .btn-welcome.secondary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            background: var(--light);
        }
        
        .btn-welcome.secondary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-welcome.secondary:hover::before {
            left: 100%;
        }

        /* Stats Cards */
        .stats-section {
            margin-bottom: 3rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--system-background);
            padding: 2.5rem;
            border-radius: var(--apple-radius-2xl);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--apple-gray-2);
            box-shadow: var(--apple-shadow-medium);
            transition: var(--apple-transition-medium);
            position: relative;
            overflow: hidden;
            border-top: 4px solid transparent;
            background-image: linear-gradient(var(--system-background), var(--system-background)), 
                            linear-gradient(135deg, var(--apple-blue), var(--apple-purple));
            background-origin: border-box;
            background-clip: content-box, border-box;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            background: rgba(255, 255, 255, 1);
            border-color: var(--primary);
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--apple-blue), var(--apple-purple));
            border-radius: var(--apple-radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            color: white;
            box-shadow: var(--apple-shadow-medium);
            transition: var(--apple-transition-medium);
            position: relative;
            overflow: hidden;
        }
        
        .stat-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        
        .stat-card:hover .stat-icon::before {
            transform: translateX(100%);
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-lg);
        }
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--apple-gray-12);
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, var(--apple-blue), var(--apple-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .stat-label {
            color: var(--gray);
            font-weight: 500;
            font-size: 1rem;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        /* Content Sections */
        .content-section {
            background: var(--system-background);
            border-radius: var(--apple-radius-2xl);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--apple-shadow-medium);
            border: 1px solid var(--apple-gray-2);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            position: relative;
            overflow: hidden;
        }
        
        .content-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--apple-blue), var(--apple-purple), var(--apple-green));
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--apple-gray-12);
            margin: 0;
            background: linear-gradient(135deg, var(--apple-blue), var(--apple-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .section-action {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .section-action:hover {
            color: var(--primary-dark);
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            background: linear-gradient(135deg, var(--apple-blue), var(--apple-purple));
            padding: 1.25rem;
            text-align: left;
            font-weight: 600;
            color: white;
            border-radius: var(--apple-radius-small);
            box-shadow: var(--apple-shadow-small);
            font-size: 0.95rem;
            letter-spacing: -0.01em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .orders-table tr:hover {
            background: rgba(99, 102, 241, 0.05);
            transform: scale(1.01);
            box-shadow: var(--shadow);
        }
        
        .orders-table tr {
            transition: all 0.3s ease;
        }

        .status-badge {
            padding: 0.875rem 1.5rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: var(--apple-shadow-small);
            transition: var(--apple-transition-medium);
            letter-spacing: 0.05em;
            position: relative;
            overflow: hidden;
        }
        
        .status-badge:hover {
            transform: translateY(-3px);
            box-shadow: var(--apple-shadow-medium);
        }
        
        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .status-badge:hover::before {
            left: 100%;
        }

        .status-pending {
            background: rgba(255, 149, 0, 0.12);
            color: var(--apple-orange);
            border: 1px solid rgba(255, 149, 0, 0.25);
        }

        .status-processing {
            background: rgba(0, 122, 255, 0.12);
            color: var(--apple-blue);
            border: 1px solid rgba(0, 122, 255, 0.25);
        }

        .status-completed {
            background: rgba(52, 199, 89, 0.12);
            color: var(--apple-green);
            border: 1px solid rgba(52, 199, 89, 0.25);
        }

        .status-cancelled {
            background: rgba(255, 59, 48, 0.12);
            color: var(--apple-red);
            border: 1px solid rgba(255, 59, 48, 0.25);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action {
            background: var(--system-background);
            padding: 2.5rem;
            border-radius: var(--apple-radius-2xl);
            text-align: center;
            text-decoration: none;
            color: var(--apple-gray-12);
            transition: var(--apple-transition-medium);
            border: 1px solid var(--apple-gray-2);
            box-shadow: var(--apple-shadow-medium);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            position: relative;
            overflow: hidden;
            border-top: 3px solid transparent;
            background-image: linear-gradient(var(--system-background), var(--system-background)), 
                            linear-gradient(135deg, var(--apple-green), var(--apple-blue));
            background-origin: border-box;
            background-clip: content-box, border-box;
        }

        .quick-action:hover {
            transform: translateY(-8px);
            box-shadow: var(--apple-shadow-xl);
            color: var(--apple-blue);
            text-decoration: none;
            background: var(--system-background);
            border-color: var(--apple-blue);
        }
        
        .quick-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .quick-action:hover::before {
            left: 100%;
        }

        .quick-action-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--apple-green), var(--apple-blue));
            border-radius: var(--apple-radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 1.8rem;
            color: white;
            box-shadow: var(--apple-shadow-medium);
            transition: var(--apple-transition-medium);
            position: relative;
            overflow: hidden;
        }
        
        .quick-action-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        
        .quick-action:hover .quick-action-icon::before {
            transform: translateX(100%);
        }
        
        .quick-action:hover .quick-action-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-lg);
        }

        .quick-action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .quick-action-desc {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* Responsive Design Apple Premium */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem 0;
            }

            .welcome-section {
                padding: 3rem 1.5rem;
                margin: 0 1rem 2rem;
                border-radius: var(--apple-radius-xl);
            }
            
            .welcome-title {
                font-size: clamp(2.5rem, 8vw, 4rem);
            }
            
            .welcome-subtitle {
                font-size: 1.2rem;
                max-width: 100%;
            }

            .welcome-actions {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-welcome {
                width: 100%;
                text-align: center;
                padding: 18px 24px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .stat-card {
                padding: 2rem;
            }
            
            .stat-icon {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }
            
            .stat-number {
                font-size: 3rem;
            }

            .content-section {
                margin: 0 1rem 1.5rem;
                padding: 2rem;
                border-radius: var(--apple-radius-xl);
            }
            
            .section-title {
                font-size: 1.75rem;
            }

            .orders-table {
                font-size: 0.9rem;
            }

            .orders-table th,
            .orders-table td {
                padding: 1rem 0.75rem;
            }

            .quick-actions {
                grid-template-columns: 1fr;
                margin: 0 1rem 1.5rem;
                gap: 1.5rem;
            }
            
            .quick-action {
                padding: 2rem;
            }
            
            .quick-action-icon {
                width: 65px;
                height: 65px;
                font-size: 1.6rem;
            }

            /* Menu mobile plein écran */
            .mobile-menu {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                height: 100vh;
                width: 100vw;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(30px) saturate(180%);
                -webkit-backdrop-filter: blur(30px) saturate(180%);
                border-radius: 0;
                box-shadow: none;
                border: none;
                overflow-y: auto;
                transform: translateX(-100%);
                opacity: 0;
                transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                z-index: 9999;
            }

            .mobile-menu.active {
                transform: translateX(0);
                opacity: 1;
            }

            .mobile-menu .navbar-nav {
                padding: 2rem 1rem;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .mobile-menu .nav-item {
                margin: 1rem 0;
                text-align: center;
            }

            .mobile-menu .nav-link {
                padding: 1.75rem 2.5rem;
                font-size: 1.4rem;
                border-radius: var(--apple-radius-xl);
                margin: 0.75rem 0;
                justify-content: center;
                font-weight: 500;
                letter-spacing: -0.01em;
            }

            .mobile-menu .nav-link:hover {
                transform: translateX(0) scale(1.05);
                box-shadow: var(--apple-shadow-large);
                background: var(--apple-gray-1);
                color: var(--apple-blue);
            }

            /* Bouton fermer le menu */
            .close-menu-btn {
                position: absolute;
                top: 2rem;
                right: 2rem;
                width: 56px;
                height: 56px;
                background: rgba(255, 255, 255, 0.95);
                border: none;
                border-radius: 50%;
                font-size: 1.6rem;
                color: var(--apple-gray-8);
                cursor: pointer;
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: var(--apple-shadow-medium);
                transition: var(--apple-transition-medium);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
            }

            .close-menu-btn:hover {
                background: var(--apple-red);
                color: white;
                transform: scale(1.1) rotate(90deg);
                box-shadow: var(--apple-shadow-large);
            }

            .navbar-nav {
                text-align: center;
            }

            .nav-item {
                margin: 0.5rem 0;
            }

            .nav-link {
                padding: 0.75rem 1rem;
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .nav-link:hover {
                background: var(--primary);
                color: white;
                transform: translateX(5px);
            }

            .user-menu {
                flex-direction: column;
                gap: 0.5rem;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--gray-light);
            }

            .user-avatar {
                margin: 0 auto;
            }
        }

        @media (max-width: 480px) {
            .welcome-title {
                font-size: 1.8rem;
            }

            .welcome-subtitle {
                font-size: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .section-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }

        /* Animations Apple Premium */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes rotateIn {
            from {
                opacity: 0;
                transform: rotate(-10deg) scale(0.9);
            }
            to {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }
        }

        .fade-in-up {
            animation: fadeInUp 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            opacity: 0;
            transform: translateY(40px);
        }

        .slide-in-left {
            animation: slideInLeft 0.8s cubic-bezier(0.25, 0.94) forwards;
            opacity: 0;
            transform: translateX(-40px);
        }
        
        .scale-in {
            animation: scaleIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            opacity: 0;
            transform: scale(0.9);
        }
        
        .rotate-in {
            animation: rotateIn 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            opacity: 0;
            transform: rotate(-10deg) scale(0.9);
        }
        
        /* Animations avancées */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }
        
        .shine-effect {
            position: relative;
            overflow: hidden;
        }
        
        .shine-effect::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s ease;
        }
        
        .shine-effect:hover::after {
            left: 100%;
        }
        
        /* Effets de particules */
        .particle-effect {
            position: relative;
        }
        
        .particle-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(245, 158, 11, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

                /* Scrollbar Apple Premium */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--apple-gray-1);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--apple-blue), var(--apple-purple));
            border-radius: 5px;
            border: 2px solid var(--apple-gray-1);
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--apple-purple), var(--apple-blue));
        }
        
        /* Firefox Scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--apple-blue) var(--apple-gray-1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-rocket me-2"></i>Boost<span>Social</span>
            </a>
            
            <!-- Bouton hamburger personnalisé -->
            <button class="hamburger-btn" type="button" id="hamburgerBtn" aria-label="Toggle navigation">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            
            <!-- Menu de navigation -->
            <div class="mobile-menu" id="mobileMenu">
                <button class="close-menu-btn" id="closeMenuBtn" aria-label="Fermer le menu">
                    <i class="fas fa-times"></i>
                </button>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                                            <a class="nav-link" href="../commander.php">
                        <i class="fas fa-plus me-2"></i>Nouvelle Commande
                    </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="commandes.php">
                            <i class="fas fa-list me-2"></i>Mes Commandes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tickets.php">
                            <i class="fas fa-headset me-2"></i>Support
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">
                            <i class="fas fa-user me-2"></i>Profil
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="user-menu">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                </div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <section class="welcome-section fade-in-up particle-effect">
                <div class="welcome-content text-center">
                    <div class="header-icon mb-4 float-animation">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h1 class="welcome-title">Bonjour, <?php echo htmlspecialchars($currentUser['name']); ?> ! 👋</h1>
                    <p class="welcome-subtitle">Bienvenue sur votre tableau de bord personnel. Gérez vos commandes et suivez vos performances en temps réel.</p>
                    
                    <div class="welcome-actions">
                        <a href="../commander.php" class="btn-welcome primary shine-effect">
                            <i class="fas fa-plus me-2"></i>Nouvelle Commande
                        </a>
                        <a href="commandes.php" class="btn-welcome secondary shine-effect">
                            <i class="fas fa-chart-line me-2"></i>Voir Toutes mes Commandes
                        </a>
                    </div>
                </div>
            </section>

            <!-- Stats Section -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card slide-in-left particle-effect">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($userStats['total_orders']); ?></div>
                        <div class="stat-label">Commandes Totales</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+12% ce mois</span>
                        </div>
                    </div>
                    
                    <div class="stat-card slide-in-left particle-effect">
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($userStats['total_spent'], 0, ',', ' '); ?> FCFA</div>
                        <div class="stat-label">Total Dépensé</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+8% ce mois</span>
                        </div>
                    </div>
                    
                    <div class="stat-card slide-in-left particle-effect">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo isset($userStats['orders_by_status']['Terminée']) ? $userStats['orders_by_status']['Terminée'] : 0; ?></div>
                        <div class="stat-label">Commandes Terminées</div>
                        <div class="stat-change positive">
                            <span>+15% ce mois</span>
                        </div>
                    </div>
                    
                    <div class="stat-card slide-in-left particle-effect">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo isset($userStats['orders_by_status']['En cours']) ? $userStats['orders_by_status']['En cours'] : 0; ?></div>
                        <div class="stat-label">En Cours</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+5% ce mois</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Actions Rapides</h2>
                </div>
                
                <div class="quick-actions">
                    <a href="../commander.php" class="quick-action shine-effect">
                        <div class="quick-action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="quick-action-title">Nouvelle Commande</div>
                        <div class="quick-action-desc">Commander des followers, likes ou vues</div>
                    </a>
                    
                    <a href="commandes.php" class="quick-action shine-effect">
                        <div class="quick-action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="quick-action-title">Mes Commandes</div>
                        <div class="quick-action-desc">Suivre l'état de vos commandes</div>
                    </a>
                    
                    <a href="tickets.php" class="quick-action shine-effect">
                        <div class="quick-action-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="quick-action-title">Support</div>
                        <div class="quick-action-desc">Créer un ticket de support</div>
                    </a>
                    
                    <a href="profil.php" class="quick-action shine-effect">
                        <div class="quick-action-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="quick-action-title">Mon Profil</div>
                        <div class="quick-action-desc">Gérer vos informations</div>
                    </a>
                </div>
            </section>

            <!-- Recent Orders -->
            <section class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Commandes Récentes</h2>
                    <a href="commandes.php" class="section-action">Voir Toutes <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                
                <?php if (!empty($recentOrders)): ?>
                    <div class="table-responsive">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Service</th>
                                    <th>Quantité</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                        <td><?php echo number_format($order['quantity']); ?></td>
                                        <td><strong><?php echo number_format($order['total_price'], 0, ',', ' '); ?> FCFA</strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="commande-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Voir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Aucune commande pour le moment</h4>
                        <p class="text-muted">Commencez par créer votre première commande !</p>
                        <a href="../commander.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Première Commande
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Recent Tickets -->
            <?php if (!empty($recentTickets)): ?>
            <section class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Tickets de Support Récents</h2>
                    <a href="tickets.php" class="section-action">Voir Tous <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>N° Ticket</th>
                                <th>Sujet</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTickets as $ticket): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($ticket['ticket_number']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                            <?php echo htmlspecialchars($ticket['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($ticket['created_at'])); ?></td>
                                    <td>
                                        <a href="tickets.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
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

        // Animation des cartes de stats
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
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

        // Effet de hover sur les cartes
        document.querySelectorAll('.stat-card, .quick-action').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Mise à jour en temps réel des stats avec animation
        function animateNumber(element, start, end, duration = 1000) {
            const startTime = performance.now();
            const difference = end - start;
            
            function updateNumber(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                const current = Math.floor(start + (difference * progress));
                
                if (element.textContent.includes('FCFA')) {
                    element.textContent = current.toLocaleString() + ' FCFA';
                } else {
                    element.textContent = current.toLocaleString();
                }
                
                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                }
            }
            
            requestAnimationFrame(updateNumber);
        }
        
        // Mise à jour des stats toutes les 30 secondes
        setInterval(() => {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const currentValue = parseInt(stat.textContent.replace(/\D/g, ''));
                const randomChange = Math.floor(Math.random() * 5) + 1;
                const newValue = currentValue + randomChange;
                
                animateNumber(stat, currentValue, newValue, 800);
            });
        }, 30000);

        // Menu hamburger mobile
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const body = document.body;

        if (hamburgerBtn && mobileMenu) {
            hamburgerBtn.addEventListener('click', () => {
                hamburgerBtn.classList.toggle('active');
                mobileMenu.classList.toggle('active');
                body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
            });

            // Fermer le menu en cliquant sur un lien
            const mobileLinks = mobileMenu.querySelectorAll('.nav-link');
            mobileLinks.forEach(link => {
                link.addEventListener('click', () => {
                    hamburgerBtn.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                });
            });

            // Fermer le menu en cliquant sur le bouton X
            const closeMenuBtn = document.getElementById('closeMenuBtn');
            if (closeMenuBtn) {
                closeMenuBtn.addEventListener('click', () => {
                    hamburgerBtn.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                });
            }

            // Fermer le menu en cliquant à l'extérieur
            document.addEventListener('click', (e) => {
                if (!hamburgerBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                    hamburgerBtn.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                }
            });

            // Fermer le menu avec la touche Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                    hamburgerBtn.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                }
            });
        }
        
        // Fonctionnalités Apple Premium du dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Animation des cartes au chargement avec timing Apple
            const cards = document.querySelectorAll('.stat-card, .quick-action, .content-section');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.15}s`;
                card.classList.add('slide-in-left');
            });
            
            // Effet de particules sur les cartes de stats
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.classList.add('particle-effect');
            });
            
            // Effet de brillance sur les boutons
            const buttons = document.querySelectorAll('.btn-welcome, .quick-action');
            buttons.forEach(button => {
                button.classList.add('shine-effect');
            });
            
            // Animation flottante sur l'icône du header
            const headerIcon = document.querySelector('.welcome-section .header-icon');
            if (headerIcon) {
                headerIcon.classList.add('float-animation');
            }
            
            // Effet de pulse sur les badges de statut
            const statusBadges = document.querySelectorAll('.status-badge');
            statusBadges.forEach(badge => {
                badge.classList.add('pulse-animation');
            });
            
            // Notifications toast Apple Premium avec SweetAlert2
            function showNotification(title, message, type = 'info') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: 'var(--system-background)',
                    color: 'var(--apple-gray-12)',
                    customClass: {
                        popup: 'apple-toast'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                
                Toast.fire({
                    icon: type,
                    title: title,
                    text: message
                });
            }
            
            // Notification de bienvenue Apple Premium
            setTimeout(() => {
                showNotification('🎉 Bienvenue !', 'Votre dashboard Apple Premium est prêt', 'success');
            }, 2000);
            
            // Amélioration des interactions Apple Premium
            const interactiveElements = document.querySelectorAll('.stat-card, .quick-action, .content-section');
            interactiveElements.forEach(element => {
                element.addEventListener('click', function() {
                    // Effet de clic Apple
                    this.style.transform = 'scale(0.96)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                });
                
                element.addEventListener('mouseenter', function() {
                    this.style.zIndex = '10';
                    this.style.transform = 'translateY(-2px)';
                });
                
                element.addEventListener('mouseleave', function() {
                    this.style.zIndex = '1';
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Haptic feedback sur mobile (si supporté)
            if ('vibrate' in navigator) {
                const clickableElements = document.querySelectorAll('.btn-welcome, .quick-action, .stat-card');
                clickableElements.forEach(element => {
                    element.addEventListener('click', () => {
                        navigator.vibrate(10);
                    });
                });
            }
            
            // Lazy loading des images (si ajoutées plus tard)
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                });
                
                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
            
            // Performance monitoring Apple style
            const performanceObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.entryType === 'navigation') {
                        console.log('🚀 Dashboard Apple Premium chargé en:', entry.loadEventEnd - entry.loadEventStart, 'ms');
                    }
                });
            });
            
            if ('PerformanceObserver' in window) {
                performanceObserver.observe({ entryTypes: ['navigation'] });
            }
        });
    </script>
</body>
</html>