<?php
// Seeder for nnaco_sa products
require_once 'app/config/config.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$items = [
    ['name' => 'Jepit Rambut', 'keyword' => 'hairclip', 'cat' => 'Aksesoris Rambut', 'price' => 15000],
    ['name' => 'Gantungan Kunci', 'keyword' => 'keychain', 'cat' => 'Aksesoris Umum', 'price' => 10000],
    ['name' => 'Nametag', 'keyword' => 'nametag', 'cat' => 'Peralatan', 'price' => 20000],
    ['name' => 'Gelang', 'keyword' => 'bracelet', 'cat' => 'Perhiasan', 'price' => 25000],
    ['name' => 'Kacamata Gaya', 'keyword' => 'sunglasses', 'cat' => 'Fashion', 'price' => 50000],
    ['name' => 'Bando', 'keyword' => 'headband', 'cat' => 'Aksesoris Rambut', 'price' => 18000],
    ['name' => 'Pot Hias', 'keyword' => 'plant,pot', 'cat' => 'Dekorasi', 'price' => 35000],
    ['name' => 'Skincare', 'keyword' => 'skincare', 'cat' => 'Kecantikan', 'price' => 85000],
    ['name' => 'Dompet', 'keyword' => 'wallet', 'cat' => 'Fashion', 'price' => 60000],
    ['name' => 'Paperbag Aesthetic', 'keyword' => 'paperbag', 'cat' => 'Packaging', 'price' => 5000],
    ['name' => 'Alat Tulis', 'keyword' => 'stationery', 'cat' => 'Peralatan', 'price' => 12000],
    ['name' => 'Kerudung', 'keyword' => 'hijab', 'cat' => 'Fashion', 'price' => 45000],
    ['name' => 'Stiker Lucu', 'keyword' => 'stickers', 'cat' => 'Aksesoris Umum', 'price' => 5000],
    ['name' => 'Kalung', 'keyword' => 'necklace', 'cat' => 'Perhiasan', 'price' => 40000],
    ['name' => 'Masker', 'keyword' => 'facemask', 'cat' => 'Kesehatan', 'price' => 10000],
    ['name' => 'DIY Kawat Bulu', 'keyword' => 'craft', 'cat' => 'Hobi', 'price' => 15000],
    ['name' => 'Tas Makeup', 'keyword' => 'makeup,pouch', 'cat' => 'Kecantikan', 'price' => 30000],
    ['name' => 'Mainan', 'keyword' => 'toy', 'cat' => 'Hobi', 'price' => 25000],
    ['name' => 'Hiasan Ulang Tahun', 'keyword' => 'party,decor', 'cat' => 'Dekorasi', 'price' => 40000],
    ['name' => 'Tumbler', 'keyword' => 'tumbler', 'cat' => 'Peralatan', 'price' => 55000]
];

$imgDir = __DIR__ . '/public/img/products/';
if (!is_dir($imgDir)) {
    mkdir($imgDir, 0777, true);
}

// 1. Setup Categories
$categories = array_unique(array_column($items, 'cat'));
$catIds = [];
foreach ($categories as $catName) {
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$catName]);
    $cat = $stmt->fetch();
    
    if ($cat) {
        $catIds[$catName] = $cat['id'];
    } else {
        $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$catName, 'Kategori ' . $catName]);
        $catIds[$catName] = $db->lastInsertId();
    }
}

// 2. Setup Products & Download Images
foreach ($items as $item) {
    echo "Processing {$item['name']}...\n";
    
    // Download image
    $imgName = uniqid() . '.jpg';
    $imgPath = $imgDir . $imgName;
    
    $imageUrl = "https://loremflickr.com/400/400/" . urlencode($item['keyword']) . "?random=" . rand(1, 1000);
    
    $ch = curl_init($imageUrl);
    $fp = fopen($imgPath, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    
    if (file_exists($imgPath) && filesize($imgPath) > 0) {
        // Insert product
        $stmt = $db->prepare("INSERT INTO products (name, category_id, description, price, stock, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $desc = "Produk premium {$item['name']} berkualitas tinggi yang cocok untuk kebutuhan Anda sehari-hari.";
        $stmt->execute([
            $item['name'],
            $catIds[$item['cat']],
            $desc,
            $item['price'],
            rand(10, 100),
            $imgName,
            'Available'
        ]);
        echo "Successfully added {$item['name']}.\n";
    } else {
        echo "Failed to download image for {$item['name']}.\n";
    }
}

echo "\nDone seeding catalog!\n";
