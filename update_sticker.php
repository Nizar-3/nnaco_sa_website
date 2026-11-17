<?php
require_once 'app/config/config.php';
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->prepare("UPDATE products SET image = 'stiker_lucu.jpg' WHERE name LIKE '%Stiker Lucu%' OR name LIKE '%Stiker%'");
    $stmt->execute();
    echo "Image updated successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
