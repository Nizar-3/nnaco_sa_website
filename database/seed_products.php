<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = new Database();

echo "Mulai seeding kategori...\n";
$categories = ['Kalung', 'Gelang', 'Anting', 'Cincin', 'Jepit Rambut'];
foreach ($categories as $cat) {
    $db->query("INSERT INTO categories (name, description) VALUES (:name, :desc)");
    $db->bind('name', $cat);
    $db->bind('desc', 'Kategori aksesoris ' . $cat);
    $db->execute();
}
echo "Kategori berhasil di-seed.\n";

echo "Mulai seeding produk...\n";
$products = [
    ['category_id' => 1, 'name' => 'Kalung Titanium Elegan', 'price' => 150000, 'stock' => 10],
    ['category_id' => 1, 'name' => 'Kalung Liontin Hati', 'price' => 125000, 'stock' => 5],
    ['category_id' => 1, 'name' => 'Kalung Choker Velvet', 'price' => 45000, 'stock' => 0], // Stok habis
    ['category_id' => 2, 'name' => 'Gelang Tali Etnik', 'price' => 35000, 'stock' => 20],
    ['category_id' => 2, 'name' => 'Gelang Perak Murni', 'price' => 350000, 'stock' => 3],
    ['category_id' => 3, 'name' => 'Anting Mutiara Air Tawar', 'price' => 85000, 'stock' => 12],
    ['category_id' => 3, 'name' => 'Anting Tusuk Minimalis', 'price' => 25000, 'stock' => 30],
    ['category_id' => 4, 'name' => 'Cincin Pasangan (Couple)', 'price' => 195000, 'stock' => 8],
    ['category_id' => 4, 'name' => 'Cincin Batu Zirkonia', 'price' => 110000, 'stock' => 15],
    ['category_id' => 5, 'name' => 'Jepit Rambut Korea', 'price' => 15000, 'stock' => 50],
    ['category_id' => 5, 'name' => 'Jepit Rambut Pita Besar', 'price' => 22000, 'stock' => 25],
    ['category_id' => 1, 'name' => 'Kalung Emas 18K', 'price' => 2500000, 'stock' => 2],
    ['category_id' => 2, 'name' => 'Gelang Kulit Premium', 'price' => 180000, 'stock' => 7],
    ['category_id' => 3, 'name' => 'Anting Gantung Kristal', 'price' => 95000, 'stock' => 0], // Stok habis
    ['category_id' => 4, 'name' => 'Cincin Titanium Hitam', 'price' => 80000, 'stock' => 14],
];

foreach ($products as $p) {
    $db->query("INSERT INTO products (category_id, name, description, price, stock, status) VALUES (:cid, :name, :desc, :price, :stock, 'Available')");
    $db->bind('cid', $p['category_id']);
    $db->bind('name', $p['name']);
    $db->bind('desc', 'Deskripsi cantik untuk ' . $p['name'] . '. Cocok untuk hadiah dan dipakai sehari-hari.');
    $db->bind('price', $p['price']);
    $db->bind('stock', $p['stock']);
    $db->execute();
}
echo "Produk berhasil di-seed.\n";
