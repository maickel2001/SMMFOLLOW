<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$orderId) {
    redirect('commander.php');
}

// Récupération des détails de la commande
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT o.*, s.name as service_name, s.platform, s.type, c.name as category_name
        FROM orders o
        JOIN services s ON o.service_id = s.id
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        redirect('commander.php');
    }
} catch (Exception $e) {
    redirect('commander.php');
}

// Traitement de l'upload de la preuve de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Afficher les informations du fichier
    error_log("POST request received for order ID: " . $orderId);
    error_log("FILES array: " . print_r($_FILES, true));
    
    if (isset($_FILES['payment_proof'])) {
        $file = $_FILES['payment_proof'];
        error_log("File details - Name: " . $file['name'] . ", Size: " . $file['size'] . ", Error: " . $file['error']);
        
        // Vérification des erreurs d'upload
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Vérification du type de fichier
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $fileType = mime_content_type($file['tmp_name']);
            error_log("Detected MIME type: " . $fileType);
            
            if (!in_array($fileType, $allowedTypes)) {
                $error = 'Type de fichier non autorisé. Seuls JPG et PNG sont acceptés.';
                error_log("Invalid file type: " . $fileType);
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
                $error = 'Fichier trop volumineux. Taille maximum: 5MB.';
                error_log("File too large: " . $file['size'] . " bytes");
            } else {
                // Tentative d'upload
                $uploadedFile = uploadImage($file);
                error_log("Upload result: " . ($uploadedFile ? $uploadedFile : 'false'));
                
                if ($uploadedFile) {
                    try {
                        // Mise à jour de la base de données
                        $stmt = $pdo->prepare("UPDATE orders SET payment_proof = ?, status = 'En attente', updated_at = NOW() WHERE id = ?");
                        if ($stmt->execute([$uploadedFile, $orderId])) {
                            $success = 'Preuve de paiement uploadée avec succès ! Votre commande sera traitée dans les plus brefs délais.';
                            $order['payment_proof'] = $uploadedFile;
                            $order['status'] = 'En attente';
                            
                            // Log du succès
                            error_log("Payment proof uploaded successfully for order " . $orderId . ": " . $uploadedFile);
                            
                            // Redirection pour éviter la soumission multiple
                            header("Location: paiement.php?order_id=" . $orderId . "&success=1");
                            exit();
                        } else {
                            $error = 'Erreur lors de la mise à jour de la commande dans la base de données.';
                            error_log("Database update failed for order " . $orderId);
                        }
                    } catch (Exception $e) {
                        $error = 'Erreur de base de données: ' . $e->getMessage();
                        error_log("Database exception for order " . $orderId . ": " . $e->getMessage());
                    }
                } else {
                    $error = 'Erreur lors de l\'upload du fichier. Vérifiez les permissions du dossier uploads/.';
                    error_log("File upload failed for order " . $orderId);
                }
            }
        } else {
            // Gestion des erreurs d'upload
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (limite PHP)',
                UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (limite formulaire)',
                UPLOAD_ERR_PARTIAL => 'Upload partiel du fichier',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier sélectionné',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture sur le disque',
                UPLOAD_ERR_EXTENSION => 'Extension non autorisée'
            ];
            
            $error = isset($uploadErrors[$file['error']]) ? $uploadErrors[$file['error']] : 'Erreur d\'upload inconnue';
            error_log("Upload error " . $file['error'] . " for order " . $orderId . ": " . $error);
        }
    } else {
        $error = 'Aucun fichier reçu.';
        error_log("No file received for order " . $orderId);
    }
}

// Afficher le message de succès si redirection avec paramètre
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = 'Preuve de paiement uploadée avec succès ! Votre commande sera traitée dans les plus brefs délais.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - BoostSocial</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #007AFF;
            --primary-dark: #0056CC;
            --secondary: #5856D6;
            --success: #34C759;
            --warning: #FF9500;
            --danger: #FF3B30;
            --info: #5AC8FA;
            
            --bg-primary: #FFFFFF;
            --bg-secondary: #F2F2F7;
            --bg-tertiary: #FAFAFA;
            --bg-dark: #1C1C1E;
            
            --text-primary: #1C1C1E;
            --text-secondary: #8E8E93;
            --text-tertiary: #AEAEB2;
            --text-light: #FFFFFF;
            
            --border-light: #E5E5EA;
            --border-medium: #D1D1D6;
            --border-dark: #C7C7CC;
            
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15);
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Menu Hamburger */
        .hamburger-menu {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 1000;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .hamburger-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
        }
        
        .hamburger-icon:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-xl);
        }
        
        .hamburger-icon i {
            font-size: 1.2rem;
            color: var(--primary);
            transition: var(--transition);
        }
        
        .hamburger-icon.active i {
            transform: rotate(90deg);
        }
        
        /* Menu Overlay */
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .menu-content {
            text-align: center;
            color: white;
        }
        
        .menu-item {
            display: block;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 500;
            margin: 20px 0;
            padding: 15px 30px;
            border-radius: var(--radius-lg);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }
        
        .menu-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        /* Header Principal */
        .main-header {
            text-align: center;
            padding: 120px 20px 80px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
            z-index: -1;
        }
        
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .header-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 2.5rem;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .header-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .header-subtitle {
            font-size: 1.3rem;
            font-weight: 400;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Conteneur Principal */
        .main-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }
        
        /* Messages d'alerte */
        .alert {
            border: none;
            border-radius: var(--radius-lg);
            padding: 20px 25px;
            margin-bottom: 30px;
            font-weight: 500;
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-md);
        }
        
        .alert-success {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
            border: 1px solid rgba(52, 199, 89, 0.3);
        }
        
        .alert-danger {
            background: rgba(255, 59, 48, 0.1);
            color: var(--danger);
            border: 1px solid rgba(255, 59, 48, 0.3);
        }
        
        /* Cartes */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-2xl);
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .card h4 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 30px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .card h4 i {
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        /* Résumé de commande */
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .detail-item {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 25px;
            border: 1px solid var(--border-light);
            transition: var(--transition);
            text-align: center;
        }
        
        .detail-item:hover {
            background: var(--bg-tertiary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .detail-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .detail-value {
            color: var(--text-primary);
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .detail-value.order-number {
            color: var(--primary);
            font-size: 1.3rem;
        }
        
        .detail-value.price {
            color: var(--success);
            font-size: 1.4rem;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--warning);
            color: white;
            display: inline-block;
        }
        
        /* Instructions de paiement */
        .payment-method-card {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border-radius: var(--radius-xl);
            padding: 35px;
            border: 1px solid var(--border-light);
            transition: var(--transition);
        }
        
        .payment-method-card:hover {
            background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-secondary) 100%);
            box-shadow: var(--shadow-md);
        }
        
        .payment-method-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .payment-method-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: var(--shadow-md);
        }
        
        .payment-method-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }
        
        .payment-steps {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .payment-steps li {
            padding: 20px 0;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 20px;
            color: var(--text-secondary);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .payment-steps li:hover {
            color: var(--text-primary);
            transform: translateX(5px);
        }
        
        .payment-steps li:last-child {
            border-bottom: none;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-highlight {
            color: var(--primary);
            font-weight: 700;
            background: rgba(0, 122, 255, 0.1);
            padding: 2px 8px;
            border-radius: var(--radius-sm);
        }
        
        .warning-alert {
            background: rgba(255, 149, 0, 0.1);
            border: 1px solid rgba(255, 149, 0, 0.3);
            border-radius: var(--radius-lg);
            padding: 25px;
            margin-top: 30px;
            color: var(--warning);
            font-weight: 600;
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
        }
        
        /* Upload de preuve */
        .upload-area {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border: 3px dashed var(--border-medium);
            border-radius: var(--radius-xl);
            padding: 60px 40px;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .upload-area::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 122, 255, 0.1), transparent);
            transition: var(--transition);
        }
        
        .upload-area:hover::before {
            left: 100%;
        }
        
        .upload-area:hover {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(0, 122, 255, 0.05) 0%, rgba(88, 86, 214, 0.05) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .upload-area.dragover {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(0, 122, 255, 0.1) 0%, rgba(88, 86, 214, 0.1) 100%);
            transform: scale(1.02);
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 25px;
            opacity: 0.8;
            transition: var(--transition);
        }
        
        .upload-area:hover .upload-icon {
            opacity: 1;
            transform: scale(1.1);
        }
        
        .upload-text {
            color: var(--text-primary);
            font-size: 1.2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .upload-hint {
            color: var(--text-tertiary);
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .file-input {
            display: none;
        }
        
        .upload-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: var(--radius-lg);
            padding: 15px 30px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            cursor: pointer;
            margin-top: 25px;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }
        
        .upload-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }
        
        .upload-btn:hover::before {
            left: 100%;
        }
        
        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
        }
        
        .file-preview {
            margin-top: 30px;
            padding: 30px;
            background: rgba(0, 122, 255, 0.05);
            border-radius: var(--radius-xl);
            border: 1px solid rgba(0, 122, 255, 0.2);
            display: none;
            text-align: center;
        }
        
        .file-preview img {
            max-width: 100%;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        
        .file-info {
            margin-top: 15px;
            padding: 10px;
            background: rgba(0, 122, 255, 0.1);
            border-radius: var(--radius-md);
            color: var(--primary);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .upload-status {
            margin-top: 20px;
            padding: 15px;
            border-radius: var(--radius-lg);
            font-weight: 500;
            display: none;
        }
        
        .upload-status.info {
            background: rgba(90, 200, 250, 0.1);
            border: 1px solid rgba(90, 200, 250, 0.3);
            color: var(--info);
        }
        
        .upload-status.success {
            background: rgba(52, 199, 89, 0.1);
            border: 1px solid rgba(52, 199, 89, 0.3);
            color: var(--success);
        }
        
        .upload-status.error {
            background: rgba(255, 59, 48, 0.1);
            border: 1px solid rgba(255, 59, 48, 0.3);
            color: var(--danger);
        }
        
        .status-message {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        /* Actions */
        .action-buttons {
            text-align: center;
            margin-top: 50px;
            margin-bottom: 80px;
        }
        
        .btn-action {
            padding: 18px 35px;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: var(--transition);
            margin: 0 10px;
            display: inline-block;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }
        
        .btn-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }
        
        .btn-action:hover::before {
            left: 100%;
        }
        
        .btn-home {
            background: var(--bg-primary);
            border: 2px solid var(--border-medium);
            color: var(--text-secondary);
        }
        
        .btn-home:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
        }
        
        .btn-new-order {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: white;
        }
        
        .btn-new-order:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: white;
            text-decoration: none;
        }
        
        /* Animations */
        .animate-fade-in {
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .animate-fade-in:nth-child(1) { animation-delay: 0.1s; }
        .animate-fade-in:nth-child(2) { animation-delay: 0.2s; }
        .animate-fade-in:nth-child(3) { animation-delay: 0.3s; }
        .animate-fade-in:nth-child(4) { animation-delay: 0.4s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header {
                padding: 100px 20px 60px;
            }
            
            .header-title {
                font-size: 2.5rem;
            }
            
            .header-subtitle {
                font-size: 1.1rem;
            }
            
            .card {
                padding: 25px 20px;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .payment-method-card {
                padding: 25px 20px;
            }
            
            .payment-steps li {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                text-align: left;
            }
            
            .action-buttons .btn-action {
                display: block;
                margin: 10px auto;
                max-width: 280px;
            }
            
            .hamburger-menu {
                top: 20px;
                right: 20px;
            }
            
            .hamburger-icon {
                width: 45px;
                height: 45px;
            }
        }
        
        @media (max-width: 480px) {
            .header-title {
                font-size: 2rem;
            }
            
            .card {
                padding: 20px 15px;
            }
            
            .upload-area {
                padding: 40px 20px;
            }
            
            .upload-icon {
                font-size: 2.5rem;
            }
        }
        
        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
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
    <!-- Menu Hamburger -->
    <div class="hamburger-menu" id="hamburgerMenu">
        <div class="hamburger-icon">
            <i class="fas fa-bars"></i>
        </div>
    </div>
    
    <!-- Menu Overlay -->
    <div class="menu-overlay" id="menuOverlay">
        <div class="menu-content">
            <a href="index.php" class="menu-item">
                <i class="fas fa-home me-2"></i>Accueil
            </a>
            <a href="services.php" class="menu-item">
                <i class="fas fa-cogs me-2"></i>Services
            </a>
            <a href="about.php" class="menu-item">
                <i class="fas fa-info-circle me-2"></i>À propos
            </a>
            <a href="contact.php" class="menu-item">
                <i class="fas fa-envelope me-2"></i>Contact
            </a>
            <a href="client/dashboard.php" class="menu-item">
                <i class="fas fa-user me-2"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Header Principal -->
    <div class="main-header">
        <div class="header-bg"></div>
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <h1 class="header-title">Paiement Sécurisé</h1>
            <p class="header-subtitle">Finalisez votre commande en toute sécurité avec nos méthodes de paiement fiables</p>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="main-container">
        <!-- Messages d'alerte -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success animate-fade-in">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger animate-fade-in">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Résumé de la commande -->
        <div class="card animate-fade-in">
            <h4>
                <i class="fas fa-check-circle"></i>Commande Confirmée
            </h4>
            
            <div class="order-details">
                <div class="detail-item">
                    <div class="detail-label">Numéro de commande</div>
                    <div class="detail-value order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Service</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order['service_name']); ?></div>
                    <small style="color: var(--text-tertiary); font-size: 0.875rem;"><?php echo htmlspecialchars($order['category_name'] ?? ''); ?></small>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Quantité</div>
                    <div class="detail-value"><?php echo number_format($order['quantity'], 0, ',', ' '); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Prix total</div>
                    <div class="detail-value price"><?php echo formatPrice($order['total_price']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Méthode de paiement</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order['payment_method']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Statut</div>
                    <div class="detail-value">
                        <span class="status-badge">
                            <?php echo $order['payment_proof'] ? 'En attente' : 'En attente de paiement'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructions de paiement -->
        <div class="card animate-fade-in">
            <h4>
                <i class="fas fa-mobile-alt"></i>Instructions de Paiement
            </h4>
            
            <div class="payment-method-card">
                <div class="payment-method-header">
                    <div class="payment-method-icon">
                        <i class="fas fa-<?php echo $order['payment_method'] === 'MTN Money' ? 'mobile' : 'credit-card'; ?>"></i>
                    </div>
                    <h5 class="payment-method-title">
                        Paiement via <?php echo htmlspecialchars($order['payment_method']); ?>
                    </h5>
                </div>
                
                <ol class="payment-steps">
                    <?php if ($order['payment_method'] === 'MTN Money'): ?>
                        <li>
                            <div class="step-number">1</div>
                            <div class="step-content">Composez <span class="step-highlight">*126#</span> sur votre téléphone</div>
                        </li>
                        <li>
                            <div class="step-number">2</div>
                            <div class="step-content">Sélectionnez <span class="step-highlight">"Envoyer de l'argent"</span></div>
                        </li>
                        <li>
                            <div class="step-number">3</div>
                            <div class="step-content">Entrez le numéro: <span class="step-highlight">0123456789</span></div>
                        </li>
                        <li>
                            <div class="step-number">4</div>
                            <div class="step-content">Entrez le montant: <span class="step-highlight"><?php echo formatPrice($order['total_price']); ?></span></div>
                        </li>
                        <li>
                            <div class="step-number">5</div>
                            <div class="step-content">Confirmez la transaction</div>
                        </li>
                        <li>
                            <div class="step-number">6</div>
                            <div class="step-content">Prenez une capture d'écran de la confirmation</div>
                        </li>
                    <?php else: ?>
                        <li>
                            <div class="step-number">1</div>
                            <div class="step-content">Composez <span class="step-highlight">*155#</span> sur votre téléphone</div>
                        </li>
                        <li>
                            <div class="step-number">2</div>
                            <div class="step-content">Sélectionnez <span class="step-highlight">"Transfert d'argent"</span></div>
                        </li>
                        <li>
                            <div class="step-number">3</div>
                            <div class="step-content">Entrez le numéro: <span class="step-highlight">0123456789</span></div>
                        </li>
                        <li>
                            <div class="step-number">4</div>
                            <div class="step-content">Entrez le montant: <span class="step-highlight"><?php echo formatPrice($order['total_price']); ?></span></div>
                        </li>
                        <li>
                            <div class="step-number">5</div>
                            <div class="step-content">Confirmez la transaction</div>
                        </li>
                        <li>
                            <div class="step-number">6</div>
                            <div class="step-content">Prenez une capture d'écran de la confirmation</div>
                        </li>
                    <?php endif; ?>
                </ol>
                
                <div class="warning-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Conservez le reçu de transaction et prenez une capture d'écran de la confirmation de paiement.
                </div>
            </div>
        </div>
        
        <!-- Upload de la preuve de paiement -->
        <div class="card animate-fade-in">
            <h4>
                <i class="fas fa-upload"></i>Preuve de Paiement
            </h4>
            
            <?php if ($order['payment_proof']): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Preuve de paiement reçue !</strong> Votre commande sera traitée dans les plus brefs délais.
                </div>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">Cliquez ou glissez-déposez votre capture d'écran ici</div>
                        <div class="upload-hint">Formats acceptés: JPG, PNG. Taille max: 5MB</div>
                        <input type="file" class="file-input" name="payment_proof" id="paymentProof" 
                               accept=".jpg,.jpeg,.png" required>
                        <button type="button" class="upload-btn" onclick="document.getElementById('paymentProof').click()">
                            <i class="fas fa-folder-open me-2"></i>Sélectionner un fichier
                        </button>
                    </div>
                    
                    <div class="file-preview" id="filePreview">
                        <img id="previewImage" src="" alt="Aperçu">
                        <div class="file-info" id="fileInfo"></div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="upload-btn" id="submitBtn" disabled>
                            <i class="fas fa-paper-plane me-2"></i>Envoyer la Preuve
                        </button>
                        <div class="upload-status" id="uploadStatus"></div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Actions -->
        <div class="action-buttons animate-fade-in">
            <a href="index.php" class="btn-action btn-home">
                <i class="fas fa-home me-2"></i>Retour à l'accueil
            </a>
            <a href="commander.php" class="btn-action btn-new-order">
                <i class="fas fa-plus me-2"></i>Nouvelle commande
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Menu Hamburger
        const hamburgerMenu = document.getElementById('hamburgerMenu');
        const menuOverlay = document.getElementById('menuOverlay');

        hamburgerMenu.addEventListener('click', function() {
            hamburgerMenu.classList.toggle('active');
            menuOverlay.classList.toggle('active');
        });

        // Fermer le menu en cliquant sur l'overlay
        menuOverlay.addEventListener('click', function(e) {
            if (e.target === menuOverlay) {
                hamburgerMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
            }
        });

        // Fermer le menu avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hamburgerMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
            }
        });

        // Fermer le menu en cliquant sur les éléments du menu
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                hamburgerMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
            });
        });
        
        // Animation des éléments au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-fade-in').forEach(el => {
            observer.observe(el);
        });
        
        // Gestion de l'upload de fichier
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('paymentProof');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');
        const submitBtn = document.getElementById('submitBtn');
        const uploadStatus = document.getElementById('uploadStatus');
        
        if (uploadArea && fileInput) {
            // Clic sur la zone d'upload
            uploadArea.addEventListener('click', () => fileInput.click());
            
            // Drag and drop
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect(files[0]);
                }
            });
            
            // Sélection de fichier
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                }
            });
        }
        
        function handleFileSelect(file) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    filePreview.style.display = 'block';
                    uploadArea.style.display = 'none';
                    updateFileInfo(file);
                    
                    // Activer le bouton de soumission
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Envoyer la Preuve';
                    
                    // Masquer les messages d'erreur précédents
                    showUploadStatus('', '');
                };
                reader.readAsDataURL(file);
            } else {
                showUploadStatus('Veuillez sélectionner un fichier image valide (JPG, PNG).', 'error');
                previewImage.src = ''; // Clear preview
                filePreview.style.display = 'none';
                uploadArea.style.display = 'block';
                updateFileInfo('');
                
                // Désactiver le bouton de soumission
                submitBtn.disabled = true;
            }
        }

        function updateFileInfo(file) {
            const fileInfo = document.getElementById('fileInfo');
            if (file) {
                fileInfo.textContent = `Fichier sélectionné: ${file.name} (${formatBytes(file.size)})`;
            } else {
                fileInfo.textContent = '';
            }
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
        
        // Validation du formulaire
        document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const file = fileInput.files[0];
            if (!file) {
                showUploadStatus('Veuillez sélectionner un fichier.', 'error');
                return false;
            }
            
            if (!file.type.startsWith('image/')) {
                showUploadStatus('Veuillez sélectionner un fichier image valide.', 'error');
                return false;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                showUploadStatus('Le fichier est trop volumineux. Taille maximum: 5MB.', 'error');
                return false;
            }
            
            // Désactiver le bouton et afficher le statut
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Envoi en cours...';
            showUploadStatus('Envoi de la preuve de paiement...', 'info');
            
            // Soumettre le formulaire
            this.submit();
        });
        
        function showUploadStatus(message, type = 'info') {
            const statusDiv = document.getElementById('uploadStatus');
            if (statusDiv) {
                statusDiv.className = `upload-status ${type}`;
                statusDiv.innerHTML = `
                    <div class="status-message">
                        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                `;
                statusDiv.style.display = 'block';
                
                // Auto-hide après 5 secondes pour les messages de succès
                if (type === 'success') {
                    setTimeout(() => {
                        statusDiv.style.display = 'none';
                    }, 5000);
                }
            }
        }
        
        // Prévenir la soumission multiple
        let formSubmitted = false;
        document.getElementById('uploadForm')?.addEventListener('submit', function() {
            if (formSubmitted) {
                return false;
            }
            formSubmitted = true;
        });
        
        // Réinitialiser le statut si une nouvelle page est chargée
        window.addEventListener('beforeunload', function() {
            formSubmitted = false;
        });
        
        // Effet de parallaxe sur les formes flottantes
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const shapes = document.querySelectorAll('.shape');
            
            shapes.forEach((shape, index) => {
                const speed = 0.5 + (index * 0.1);
                shape.style.transform = `translateY(${scrolled * speed}px) rotate(${scrolled * 0.1}deg)`;
            });
        });
        
        // Animation des compteurs
        function animateCounters() {
            const counters = document.querySelectorAll('.detail-value');
            counters.forEach(counter => {
                const target = counter.textContent;
                if (target.includes('FCFA')) {
                    const number = parseInt(target.replace(/[^\d]/g, ''));
                    if (!isNaN(number)) {
                        animateNumber(counter, 0, number, 2000);
                    }
                }
            });
        }
        
        function animateNumber(element, start, end, duration) {
            const startTime = performance.now();
            const difference = end - start;
            
            function updateNumber(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                const current = Math.floor(start + (difference * progress));
                element.textContent = current.toLocaleString('fr-FR') + ' FCFA';
                
                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                }
            }
            
            requestAnimationFrame(updateNumber);
        }
        
        // Démarrer l'animation des compteurs quand la page est chargée
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(animateCounters, 1000);
        });
    </script>
</body>
</html>