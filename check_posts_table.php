<?php
require_once __DIR__ . '/config/db.php';

try {
    // Sprawdzenie czy tabela posts istnieje
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'posts'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "Tabela 'posts' istnieje. Struktura tabeli:\n";
        $stmt = $pdo->prepare("DESCRIBE posts");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($columns);
    } else {
        echo "Tabela 'posts' nie istnieje.";
    }
    
    // Sprawdzenie struktury tabeli users
    echo "\n\nStruktura tabeli 'users':\n";
    $stmt = $pdo->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);
    
} catch (PDOException $e) {
    echo "Błąd: " . $e->getMessage();
}
?> 