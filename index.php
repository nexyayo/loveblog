<?php
// Ustawienie ścieżki bazowej
define('BASE_PATH', __DIR__);

// Rozpoczęcie sesji
session_start();

// Określamy, którą stronę załadować - nie przekierowujemy automatycznie zalogowanych użytkowników
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Header
include_once BASE_PATH . '/includes/header.php';

// Ładujemy odpowiednią stronę
switch ($page) {
    case 'home':
        include_once BASE_PATH . '/pages/home.php';
        break;
    case 'register':
        include_once BASE_PATH . '/pages/register.php';
        break;
    case 'login':
        include_once BASE_PATH . '/pages/login.php';
        break;
    case 'main':
        include_once BASE_PATH . '/pages/main.php';
        break;
    case 'post':
        include_once BASE_PATH . '/pages/post.php';
        break;
    case 'profile':
        include_once BASE_PATH . '/pages/profile.php';
        break;
    case 'create_post':
        include_once BASE_PATH . '/pages/create_post.php';
        break;
    case 'logout':
        include_once BASE_PATH . '/pages/logout.php';
        break;
    default:
        include_once BASE_PATH . '/pages/home.php';
        break;
}

// Append Footer essa
// include_once BASE_PATH . '/includes/footer.php';
?>