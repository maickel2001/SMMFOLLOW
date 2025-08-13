<?php
// Test rapide pour vérifier les fonctionnalités
echo "<h1>🧪 Test Rapide - BoostSocial</h1>";

// Test 1: Vérification de la redirection
echo "<h2>1. Test de Redirection</h2>";
echo "<p>La redirection après création de commande est configurée dans commander.php :</p>";
echo "<code>redirect(\"paiement.php?order_id=\" . \$orderId);</code><br><br>";

// Test 2: Vérification des permissions
echo "<h2>2. Test des Permissions</h2>";
$uploadDir = 'uploads/';
if (file_exists($uploadDir)) {
    echo "✅ Dossier uploads/ existe<br>";
    if (is_writable($uploadDir)) {
        echo "✅ Dossier accessible en écriture<br>";
    } else {
        echo "❌ Dossier non accessible en écriture<br>";
    }
} else {
    echo "❌ Dossier uploads/ n'existe pas<br>";
}

// Test 3: Vérification de la base de données
echo "<h2>3. Test de la Base de Données</h2>";
try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    echo "✅ Connexion à la base de données réussie<br>";
    
    // Vérification de la table orders
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
        
        // Compter les commandes existantes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $result = $stmt->fetch();
        echo "📊 Nombre total de commandes: " . $result['total'] . "<br>";
        
        // Afficher la dernière commande
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
        $lastOrder = $stmt->fetch();
        if ($lastOrder) {
            echo "🆕 Dernière commande - ID: " . $lastOrder['id'] . ", Numéro: " . $lastOrder['order_number'] . "<br>";
            echo "<a href='paiement.php?order_id=" . $lastOrder['id'] . "' target='_blank'>🧪 Tester cette commande</a><br>";
        }
    } else {
        echo "❌ Table 'orders' n'existe pas<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "<br>";
}

// Test 4: Liens de test
echo "<h2>4. Liens de Test</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='commander.php' style='background: #007AFF; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;' target='_blank'>📝 Créer une Commande</a>";
echo "<a href='test_commande_redirect.html' style='background: #34C759; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;' target='_blank'>🧪 Test Complet</a>";
echo "<a href='test_upload_simple.html' style='background: #FF9500; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;' target='_blank'>📱 Test Upload Mobile</a>";
echo "</div>";

// Test 5: Instructions de test
echo "<h2>5. Instructions de Test</h2>";
echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; border: 1px solid #007AFF;'>";
echo "<h3>📋 Étapes de Test</h3>";
echo "<ol>";
echo "<li><strong>Créer une commande :</strong> Ouvrez commander.php et remplissez le formulaire</li>";
echo "<li><strong>Vérifier la redirection :</strong> Vous devriez être redirigé vers paiement.php?order_id=X</li>";
echo "<li><strong>Test mobile :</strong> Sur mobile, testez les boutons 'Prendre une photo' et 'Sélectionner un fichier'</li>";
echo "<li><strong>Vérifier l'upload :</strong> Sélectionnez une image et vérifiez l'aperçu</li>";
echo "</ol>";
echo "</div>";

// Test 6: Vérification des fichiers
echo "<h2>6. Vérification des Fichiers</h2>";
$requiredFiles = [
    'commander.php',
    'paiement.php',
    'config/database.php',
    'includes/functions.php',
    'uploads/'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file n'existe pas<br>";
    }
}

echo "<hr>";
echo "<p><strong>Date du test:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Serveur:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Non disponible') . "</p>";
?>