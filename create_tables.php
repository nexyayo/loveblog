<?php
require_once __DIR__ . '/config/db.php';

try {
    // Odczytanie pliku SQL
    $sql = file_get_contents(__DIR__ . '/create_posts_table.sql');
    
    // Wykonanie zapytań SQL
    $pdo->exec($sql);
    
    echo "Tabele zostały utworzone pomyślnie!";
} catch (PDOException $e) {
    echo "Błąd: " . $e->getMessage();
}
?> 