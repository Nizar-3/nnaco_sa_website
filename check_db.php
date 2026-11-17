<?php
$pdo = new PDO('mysql:host=localhost;dbname=nnaco_sa_db', 'root', '');
$stmt = $pdo->query('SELECT DISTINCT status FROM orders');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
