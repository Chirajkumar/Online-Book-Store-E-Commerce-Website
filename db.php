<?php
$host = 'localhost';
$dbname = 'bookstore_db';
$username = 'root';
$username = ''; // for XAMPP, username is often empty or 'root'
$password = ''; // for XAMPP, password is often empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();
?>