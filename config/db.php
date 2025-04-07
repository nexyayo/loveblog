<?php
// Konfiguracja bazy danych
$host = 'localhost';
$dbname = 'loveblog';
$username = 'root';
$password = '';

try {
    // Tworzymy nową instancję PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Ustawiamy tryb błędów PDO na wyjątki
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ustawiamy domyślny tryb pobierania na tablicę asocjacyjną
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Ustawiamy zestaw znaków na utf8mb4
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    // W produkcji powinieneś logować błędy zamiast je wyświetlać
    die("Połączenie z bazą danych nie powiodło się: " . $e->getMessage());
}
?>