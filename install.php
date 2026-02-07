<?php
/**
 * Script d'installation de la base de données
 * Exécutez ce fichier une seule fois pour créer la base de données et les tables
 */

// Connexion sans base de données pour la création
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'wedding_budget';

try {
    // Connexion au serveur MySQL
    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Création de la base de données
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base de données '$dbname' créée avec succès<br>";
    
    // Sélection de la base de données
    $conn->exec("USE $dbname");
    
    // Création de la table des catégories
    $sql_categories = "
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        color VARCHAR(7) DEFAULT '#3498db',
        icon VARCHAR(50) DEFAULT 'fas fa-folder',
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_categories);
    echo "✓ Table 'categories' créée avec succès<br>";
    
    //session users 
    $sql_users = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_username (username),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->exec($sql_users);
    echo "✓ Table 'users' créée avec succès<br>";

    // Création de la table des dépenses
    $sql_expenses = "
    CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        frequency INT NOT NULL DEFAULT 1,
        paid BOOLEAN DEFAULT FALSE,
        payment_date DATE NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        INDEX idx_category (category_id),
        INDEX idx_paid (paid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_expenses);
    echo "✓ Table 'expenses' créée avec succès<br>";
    
    // Insertion des catégories
    $categories = [
        ['name' => 'Connaissance', 'color' => '#3498db','icon' => 'fas fa-handshake', 'order' => 1],
        ['name' => 'Dot', 'color' => '#9b59b6','icon' => 'fas fa-gift', 'order' => 2],
        ['name' => 'Mairie', 'color' => '#e74c3c', 'icon' => 'fas fa-landmark', 'order' => 3],
        ['name' => 'Bénédiction nuptiale', 'color' => '#2ecc71','icon' => 'fas fa-church', 'order' => 4],
        ['name' => 'Logistique', 'color' => '#1abc9c', 'icon' => 'fas fa-truck', 'order' => 5],
        ['name' => 'Réception', 'color' => '#f39c12','icon' => 'fas fa-glass-cheers', 'order' => 6],
        ['name' => 'Coût indirect et imprévus','color' => '#95a5a6', 'icon' => 'fas fa-exclamation-triangle', 'order' => 7]
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, display_order) VALUES (:name, :order)");
    foreach ($categories as $cat) {
        $stmt->execute(['name' => $cat['name'], 'order' => $cat['order']]);
    }
    echo "✓ Catégories insérées avec succès<br>";
    
    // Insertion des données initiales
    $expenses_data = [
        // Catégorie 1: Prise de contact
        [1, 'Enveloppe', 2, 2000, 1, false],
        [1, 'Bouteille de jus de risins', 2, 5000, 1, false],
        [1, 'Deplacement', 1, 5000, 1, false],
        
        // Catégorie 2: La dot
        [2, 'Bible', 1, 6000, 1, false],
        [2, 'Valise', 1, 10000, 1, false],
        [2, 'Pagne vlisco 1/2', 2, 27000, 1, false],
        [2, 'Pagne côte d\'ivoire 1/2', 5, 6500, 1, false],
        [2, 'Pagne Ghana 1/2', 4, 6500, 1, false],
        [2, 'Ensemble de chaine', 3, 3000, 1, false],
        [2, 'Chaussures', 3, 3000, 1, false],
        [2, 'Sac à main', 2, 3500, 1, false],
        [2, 'Montre et Bracelet', 2, 3000, 1, false],
        [2, 'Série de bol', 3, 5500, 1, false],
        [2, 'Demi-douzaine Assiettes en verre', 2, 4800, 1, false],
        [2, 'Douzaine Assiettes en plastique', 2, 3000, 1, false],
        [2, 'Série de casseroles', 1, 7000, 1, false],
        [2, 'Marmites de 1kg, 1,5kg, 2kg et 3kg', 1, 11000, 1, false],
        [2, 'Gobelets', 1, 2000, 1, false],
        [2, 'Bols', 12, 1500, 1, false],
        [2, 'Fourchettes', 1, 1500, 1, false],
        [2, 'Cuillères', 1, 1500, 1, false],
        [2, 'Couteaux', 1, 1500, 1, false],
        [2, 'Pavie', 1, 1500, 1, false],
        [2, 'Bassines aluminium grande', 1, 10000, 1, false],
        [2, 'Bassines aluminium moyen', 2, 4000, 1, false],
        [2, 'Bassines aluminium petite', 2, 2500, 1, false],
        [2, 'Palette', 1, 500, 1, false],
        [2, 'Raclette', 1, 1000, 1, false],
        [2, 'Cuillères à sauce', 2, 800, 1, false],
        [2, 'Gaz et accessoire complet', 1, 25000, 1, false],
        [2, 'Seau de soin corporelle', 1, 10000, 1, false],
        [2, 'Enveloppe fille', 1, 100000, 1, false],
        [2, 'Enveloppe Famille', 1, 25000, 1, false],
        [2, 'Enveloppe frères et soeurs', 1, 10000, 1, false],
        [2, 'Sac de sel de cuisine', 1, 12000, 1, false],
        [2, 'Allumettes paquets', 5, 125, 1, false],
        [2, 'Liqueurs Assédekon', 2, 10000, 1, false],
        [2, 'Jus de raisins', 10, 2500, 1, false],
        [2, 'Sucrerie sobebra', 2, 5500, 1, false],
        [2, 'Collation Echoc spirtuel', 1, 45000, 1, false],
        
        // Catégorie 3: Mairie
        [3, 'Frais de dossier et chambre de la célébration à la mairie', 1, 50000, 1, false],
        [3, 'Petite réception', 1, 50000, 1, false],
        
        // Catégorie 4: Célébration à l'église
        [4, 'Robe', 1, 20000, 1, false],
        [4, 'Coustum', 1, 25000, 1, false],
        [4, 'Chaussures', 2, 25000, 1, false],
        [4, 'Bague de l\'alliance', 1, 15000, 1, false],
        [4, 'Tenues complet', 3, 15000, 1, false],
        [4, 'Tenues complet fille', 4, 15000, 1, false],
        
        // Catégorie 5: Logistique
        [5, 'Location de salle', 1, 150000, 1, false],
        [5, 'Location de véhicule', 2, 35000, 1, false],
        [5, 'Caburant', 20, 680, 1, false],
        [5, 'Prise de vue', 1, 30000, 1, false],
        [5, 'Sonorisation', 1, 20000, 1, false],
        [5, 'Conception flyers', 1, 2000, 1, false],
        
        // Catégorie 6: Réception
        [6, 'Boissons', 200, 600, 1, false],
        [6, 'Poulets', 30, 2500, 1, false],
        [6, 'Porcs', 1, 30000, 1, false],
        [6, 'Poissons', 2, 35000, 1, false],
        [6, 'Sac de riz', 1, 32000, 1, false],
        [6, 'Farine de cossette d\'igname', 20, 500, 1, false],
        [6, 'MaÏs pour akassa', 20, 200, 1, false],
        [6, 'Bois de chauffe/Charbon', 20, 200, 1, false],
        [6, 'Ensemble des ingrédients pour la cuisine', 1, 30000, 1, false],
        

        // Catégorie 6: Coût indirect
        [7, 'Ensemble des non prévus', 1, 73996, 1, false]
        
    ];
    
    $stmt = $conn->prepare("
        INSERT INTO expenses (category_id, name, quantity, unit_price, frequency, paid) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($expenses_data as $expense) {
        $stmt->execute($expense);
    }
    
    echo "✓ Données initiales insérées avec succès<br>";
    echo "<br><strong>Installation terminée !</strong><br>";
    echo "<a href='index.php' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#8b4f8d; color:white; text-decoration:none; border-radius:5px;'>Accéder à l'application</a>";
    
} catch(PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Budget Mariage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #8b4f8d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Installation de la Base de Données</h1>
        <hr>
        <div style="margin-top: 20px;">
