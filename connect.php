<?php
$host = 'localhost';
$dbname = 'quanlybangiay';
<<<<<<< chien
$username = 'chienvn102';
$password = 'chienvn102';
=======
$username = 'root';
$password = '';
>>>>>>> main

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?> 