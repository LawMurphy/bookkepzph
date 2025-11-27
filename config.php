<?php
$host = "127.0.0.1";
$db   = "bookkepz_db";
$user = "bookkepz_user";
$pass = "StrongPassword123";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connected!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
