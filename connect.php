<?php
$host = 'localhost';
$dbname = 'quanlybangiay';
<<<<<<< HEAD
$username = 'rua';
$password = 'matkhau_cua_ban';
=======

$username = 'huy';
$password = '123456';

>>>>>>> 6704a280faea46fc8d6ce5e92366968cd8ab2905

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?> 
