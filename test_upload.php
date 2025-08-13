<?php
// Test des permissions d'upload
echo "<h1>🧪 Test des Permissions d'Upload</h1>";

// Test 1: Vérification du dossier uploads
echo "<h2>1. Vérification du dossier uploads/</h2>";
$uploadDir = 'uploads/';

if (file_exists($uploadDir)) {
    echo "✅ Dossier uploads/ existe<br>";
    
    if (is_dir($uploadDir)) {
        echo "✅ C'est bien un dossier<br>";
        
        if (is_readable($uploadDir)) {
            echo "✅ Dossier lisible<br>";
        } else {
            echo "❌ Dossier non lisible<br>";
        }
        
        if (is_writable($uploadDir)) {
            echo "✅ Dossier accessible en écriture<br>";
        } else {
            echo "❌ Dossier non accessible en écriture<br>";
        }
        
        echo "Permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "<br>";
    } else {
        echo "❌ Ce n'est pas un dossier<br>";
    }
} else {
    echo "❌ Dossier uploads/ n'existe pas<br>";
    
    // Tentative de création
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Dossier uploads/ créé avec succès<br>";
    } else {
        echo "❌ Impossible de créer le dossier uploads/<br>";
    }
}

// Test 2: Vérification des permissions PHP
echo "<h2>2. Configuration PHP</h2>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Activé' : 'Désactivé') . "<br>";

// Test 3: Vérification du dossier temporaire
echo "<h2>3. Dossier temporaire</h2>";
$tempDir = sys_get_temp_dir();
echo "Dossier temporaire: " . $tempDir . "<br>";

if (is_dir($tempDir)) {
    echo "✅ Dossier temporaire existe<br>";
    
    if (is_writable($tempDir)) {
        echo "✅ Dossier temporaire accessible en écriture<br>";
    } else {
        echo "❌ Dossier temporaire non accessible en écriture<br>";
    }
} else {
    echo "❌ Dossier temporaire n'existe pas<br>";
}

// Test 4: Vérification des extensions
echo "<h2>4. Extensions PHP</h2>";
$requiredExtensions = ['fileinfo', 'gd', 'exif'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extension $ext chargée<br>";
    } else {
        echo "❌ Extension $ext non chargée<br>";
    }
}

// Test 5: Test de création de fichier
echo "<h2>5. Test de création de fichier</h2>";
$testFile = $uploadDir . 'test_' . uniqid() . '.txt';
$testContent = 'Test de permission - ' . date('Y-m-d H:i:s');

if (file_put_contents($testFile, $testContent)) {
    echo "✅ Fichier de test créé: $testFile<br>";
    
    if (file_exists($testFile)) {
        echo "✅ Fichier existe après création<br>";
        
        $readContent = file_get_contents($testFile);
        if ($readContent === $testContent) {
            echo "✅ Contenu du fichier correct<br>";
        } else {
            echo "❌ Contenu du fichier incorrect<br>";
        }
        
        // Nettoyage
        if (unlink($testFile)) {
            echo "✅ Fichier de test supprimé<br>";
        } else {
            echo "❌ Impossible de supprimer le fichier de test<br>";
        }
    } else {
        echo "❌ Fichier n'existe pas après création<br>";
    }
} else {
    echo "❌ Impossible de créer le fichier de test<br>";
}

// Test 6: Vérification des logs d'erreur
echo "<h2>6. Configuration des logs</h2>";
echo "error_reporting: " . error_reporting() . "<br>";
echo "log_errors: " . (ini_get('log_errors') ? 'Activé' : 'Désactivé') . "<br>";
echo "error_log: " . ini_get('error_log') . "<br>";

// Test 7: Vérification des permissions du serveur web
echo "<h2>7. Informations du serveur</h2>";
echo "User: " . get_current_user() . "<br>";
echo "Process ID: " . getmypid() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Non disponible' . "<br>";

// Test 8: Vérification de la base de données
echo "<h2>8. Test de la base de données</h2>";
try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    echo "✅ Connexion à la base de données réussie<br>";
    
    // Test de la table orders
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Table 'orders' existe<br>";
        
        // Vérification de la colonne payment_proof
        $stmt = $pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('payment_proof', $columns)) {
            echo "✅ Colonne 'payment_proof' existe<br>";
        } else {
            echo "❌ Colonne 'payment_proof' n'existe pas<br>";
        }
    } else {
        echo "❌ Table 'orders' n'existe pas<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Date du test:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>OS:</strong> " . PHP_OS . "</p>";
?>