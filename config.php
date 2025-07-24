<?php
$host = 'localhost';
$db   = 'fandomix';
$user = 'root';
$pass = ''; // senha em branco no XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
