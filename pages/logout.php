<?php
// Rozpoczęcie sesji
session_start();

// Usunięcie tokenu "Zapamiętaj mnie" z bazy danych
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config/db.php';
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL, token_expires = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Logowanie błędu
        error_log('Błąd podczas usuwania tokenu: ' . $e->getMessage());
    }
}

// Usunięcie ciasteczka "Zapamiętaj mnie"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Zniszczenie sesji
$_SESSION = array();
session_destroy();

// Przekierowanie do strony logowania
header('Location: ?page=login');
exit;
?> 