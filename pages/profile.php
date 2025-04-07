<?php
// Dodaj te linie na początku pliku, aby wyłączyć buforowanie przeglądarki
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Dołączenie pliku połączenia z bazą danych
require_once __DIR__ . '/../config/db.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

// Pobranie ID użytkownika
$user_id = $_SESSION['user_id'];

// Pobranie danych użytkownika
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Użytkownik nie istnieje
        header('Location: ?page=login');
        exit;
    }
    
    // Pobranie statystyk użytkownika
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT p.id) as posts_count,
            SUM(p.likes_count) as total_likes,
            SUM(p.views_count) as total_views,
            SUM(p.comments_count) as total_comments
        FROM posts p
        WHERE p.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Pobranie kategorii zainteresowań użytkownika
    $stmt = $pdo->prepare("
        SELECT category_name 
        FROM user_interests 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $interests = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Pobranie ostatnich postów użytkownika - poprawione zapytanie
    $stmt = $pdo->prepare("
        SELECT p.*, 
               COALESCE(COUNT(DISTINCT c.id), 0) as comments_count
        FROM posts p
        LEFT JOIN comments c ON p.id = c.post_id
        WHERE p.user_id = ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$user_id]);
    $recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Zwiększenie licznika wyświetleń profilu
    $stmt = $pdo->prepare("UPDATE users SET profile_views = profile_views + 1 WHERE id = ?");
    $stmt->execute([$user_id]);
    
} catch (PDOException $e) {
    $error = "Wystąpił błąd podczas pobierania danych: " . $e->getMessage();
}

// POPRAWNE ŚCIEŻKI DO KATALOGÓW - upewnij się, że są poprawne!
$file_base_path = $_SERVER['DOCUMENT_ROOT'] . '/loveblog/';  // Ścieżka bazowa projektu
$upload_dir = 'uploads/';  // Względna ścieżka do katalogu przesyłanych plików
$absolute_upload_dir = $file_base_path . $upload_dir;  // Pełna ścieżka do katalogu

// Upewnij się, że katalog uploads istnieje i ma odpowiednie uprawnienia
if (!file_exists($absolute_upload_dir)) {
    mkdir($absolute_upload_dir, 0777, true);
} else if (!is_writable($absolute_upload_dir)) {
    chmod($absolute_upload_dir, 0777);
}

// Obsługa formularza edycji profilu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $bio = $_POST['bio'] ?? '';
    $interests = $_POST['interests'] ?? [];
    
    try {
        // Aktualizacja opisu
        $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmt->execute([$bio, $user_id]);
        
        // Usunięcie starych zainteresowań
        $stmt = $pdo->prepare("DELETE FROM user_interests WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Dodanie nowych zainteresowań
        if (!empty($interests)) {
            $stmt = $pdo->prepare("INSERT INTO user_interests (user_id, category_name) VALUES (?, ?)");
            foreach ($interests as $interest) {
                $stmt->execute([$user_id, $interest]);
            }
        }
        
        // Obsługa przesyłania zdjęcia profilowego - POPRAWIONA
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            // Sprawdź typ pliku
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['profile_image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Dozwolone są tylko pliki typu JPG, PNG i GIF.";
            } else {
                // Przygotuj nazwę pliku
                $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $new_filename;
                $absolute_file_path = $absolute_upload_dir . $new_filename;
                
                // Przenieś plik do docelowego katalogu
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $absolute_file_path)) {
                    // Aktualizacja w bazie danych - zapisujemy względną ścieżkę
                    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$file_path, $user_id]);
                    
                    // Aktualizacja sesji
                    $_SESSION['user_profile_image'] = $file_path;
                } else {
                    $error_message = "Nie udało się zapisać obrazu profilowego. Sprawdź uprawnienia katalogu.";
                }
            }
        }
        
        // Obsługa przesyłania banera - POPRAWIONA
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
            // Sprawdź typ pliku
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['banner_image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Dozwolone są tylko pliki typu JPG, PNG i GIF.";
            } else {
                // Przygotuj nazwę pliku
                $file_extension = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
                $new_filename = 'banner_' . $user_id . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $new_filename;
                $absolute_file_path = $absolute_upload_dir . $new_filename;
                
                // Przenieś plik do docelowego katalogu
                if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $absolute_file_path)) {
                    // Aktualizacja w bazie danych - zapisujemy względną ścieżkę
                    $stmt = $pdo->prepare("UPDATE users SET banner_image = ? WHERE id = ?");
                    $stmt->execute([$file_path, $user_id]);
                } else {
                    $error_message = "Nie udało się zapisać obrazu banera. Sprawdź uprawnienia katalogu.";
                }
            }
        }
        
        if (!isset($error_message)) {
            $success_message = "Profil został zaktualizowany pomyślnie!";
            
            // Przekierowanie na tę samą stronę, aby wymusić odświeżenie
            header("Location: ?page=profile&updated=1");
            exit;
        }
    } catch (PDOException $e) {
        $error_message = "Wystąpił błąd podczas aktualizacji profilu: " . $e->getMessage();
    }
}

// Pobranie wszystkich dostępnych kategorii
try {
    $stmt = $pdo->query("SELECT name FROM categories ORDER BY name");
    $all_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $all_categories = [];
}

// Formatowanie daty
function formatDate($date) {
    $timestamp = strtotime($date);
    return date("d.m.Y", $timestamp);
}

// POPRAWIONA funkcja do generowania URL obrazów
function getImageUrl($relativePath) {
    if (empty($relativePath)) {
        return '';
    }
    
    // Sprawdź, czy plik istnieje
    $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/loveblog/' . $relativePath;
    if (!file_exists($absolutePath)) {
        return '';
    }
    
    // Dodaj parametr czasu do URL, aby wymusić odświeżenie obrazu
    return $relativePath . '?v=' . time();
}

// Upewnij się, że kolumny profile_image i banner_image mają odpowiedni typ w bazie danych
try {
    // Sprawdź istniejący typ kolumny profile_image
    $stmt = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'profile_image'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Jeśli kolumna ma wartość NULL lub jest niewłaściwego typu, zmień ją na TEXT
    if (!$column || strpos(strtolower($column['Type']), 'text') === false) {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN profile_image TEXT DEFAULT NULL");
    }
    
    // Sprawdź istniejący typ kolumny banner_image
    $stmt = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'banner_image'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Jeśli kolumna ma wartość NULL lub jest niewłaściwego typu, zmień ją na TEXT
    if (!$column || strpos(strtolower($column['Type']), 'text') === false) {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN banner_image TEXT DEFAULT NULL");
    }
} catch (PDOException $e) {
    // Loguj błąd, ale nie przerywaj działania skryptu
    error_log("Błąd modyfikacji kolumn w bazie danych: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo htmlspecialchars($user['name']); ?> - LoveBlog</title>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Sen', sans-serif;
        }
        
        .btn-hover-effect {
            transition: all 0.3s ease;
            background-size: 200% auto;
            background-position: left center;
        }
        
        .btn-hover-effect:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(227, 75, 118, 0.3);
        }
        
        .bg-button-gradient {
            background: linear-gradient(to right, #e34b76, #e8638b);
        }
        
        .post-card {
            transition: all 0.3s ease;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .category-badge {
            background: linear-gradient(to right, #e34b76, #e8638b);
        }
        
        /* Animacja dla przycisków */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        /* Stylowanie scrollbara */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #e34b76;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #d33863;
        }
        
        /* Animacje dla menu rozwijanego */
        .menu-open {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
        }
        
        .rotate-arrow {
            transform: rotate(180deg);
        }
        
        .menu-item-text {
            position: relative;
            transition: all 0.3s ease;
        }
        
        #dropdown-menu a:hover .menu-item-text {
            transform: translateX(5px);
        }
        
        #dropdown-menu a {
            position: relative;
            overflow: hidden;
        }
        
        #dropdown-menu a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(to right, #e34b76, #e8638b);
            transition: width 0.3s ease;
        }
        
        #dropdown-menu a:hover::after {
            width: 100%;
        }
        
        /* Animacja ripple */
        @keyframes ripple {
            to {
                transform: scale(100);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Nagłówek -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="?page=main" class="flex items-center">
            <img src="./assets/images/logo-2.png" alt="LoveBlog Logo" class="h-10">
        </a>
        
        <!-- Menu użytkownika -->
        <div class="flex items-center space-x-4">
            <a href="?page=create_post" class="bg-button-gradient text-white px-4 py-2 rounded-full font-medium btn-hover-effect hidden sm:block">
                <i class="fas fa-plus mr-2"></i> Nowy post
            </a>
            
            <div class="relative" id="user-menu-container">
                <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                    <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden">
                        <?php if (!empty($_SESSION['user_profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['user_profile_image']); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-primary text-white text-xl font-bold">
                                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span class="hidden md:block font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <i id="menu-arrow" class="fas fa-chevron-down text-xs text-gray-500 transition-transform duration-300"></i>
                </button>
                
                <!-- Menu rozwijane -->
                <div id="dropdown-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 opacity-0 invisible transform translate-y-2 transition-all duration-300 ease-in-out z-50">
                    <a href="?page=profile" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition-colors duration-200 flex items-center">
                        <i class="fas fa-user mr-2 text-primary"></i> 
                        <span class="menu-item-text">Mój profil</span>
                    </a>
                    <a href="?page=settings" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition-colors duration-200 flex items-center">
                        <i class="fas fa-cog mr-2 text-primary"></i> 
                        <span class="menu-item-text">Ustawienia</span>
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="?page=logout" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition-colors duration-200 flex items-center">
                        <i class="fas fa-sign-out-alt mr-2 text-primary"></i> 
                        <span class="menu-item-text">Wyloguj się</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Główna zawartość -->
<div class="container mx-auto px-4 py-8">
    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Sukces!</strong>
            <span class="block sm:inline"> <?php echo $success_message; ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Błąd!</strong>
            <span class="block sm:inline"> <?php echo $error_message; ?></span>
        </div>
    <?php endif; ?>
    
    <!-- Baner i informacje o profilu -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
        <!-- Baner -->
        <div class="relative h-60 md:h-80 bg-gray-200">
            <?php if (!empty($user['banner_image'])): ?>
                <img src="<?php echo htmlspecialchars(getImageUrl($user['banner_image'])); ?>" alt="Baner użytkownika" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="w-full h-full bg-primary opacity-70"></div>
            <?php endif; ?>
            
            <!-- Przycisk edycji profilu -->
            <button id="edit-profile-btn" class="absolute top-4 right-4 bg-white bg-opacity-90 text-primary hover:bg-primary hover:text-white transition-colors duration-300 rounded-full p-3 shadow-md">
                <i class="fas fa-edit"></i>
            </button>
        </div>
        
        <!-- Informacje o profilu -->
        <div class="relative px-6 py-6 md:px-8 md:py-8">
            <!-- Avatar -->
            <div class="absolute -top-16 left-6 md:left-8 w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-white overflow-hidden bg-white shadow-md">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars(getImageUrl($user['profile_image'])); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-primary text-white text-4xl font-bold">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Dane użytkownika -->
            <div class="mt-12 md:mt-16 md:ml-28">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                            <?php echo htmlspecialchars($user['name']); ?>, <?php echo htmlspecialchars($user['age']); ?>
                        </h1>
                        <p class="text-gray-500 mt-1">
                            <i class="far fa-calendar-alt mr-2"></i> Dołączył(a): <?php echo formatDate($user['created_at']); ?>
                        </p>
                    </div>
                    
                    <div class="mt-4 md:mt-0 flex items-center">
                        <span class="text-gray-500 mr-2">
                            <i class="far fa-eye"></i> <?php echo $user['profile_views']; ?> wyświetleń
                        </span>
                    </div>
                </div>
                
                <!-- Opis użytkownika -->
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">O mnie</h2>
                    <p class="text-gray-600 whitespace-pre-line">
                        <?php echo !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'Ten użytkownik nie dodał jeszcze opisu.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Lewa kolumna - Statystyki i zainteresowania -->
        <div class="lg:col-span-1">
            <!-- Statystyki -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Statystyki</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="stat-item bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-primary"><?php echo $stats['posts_count'] ?? 0; ?></div>
                        <div class="text-gray-500 text-sm">Posty</div>
                    </div>
                    
                    <div class="stat-item bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-primary"><?php echo $stats['total_likes'] ?? 0; ?></div>
                        <div class="text-gray-500 text-sm">Polubienia</div>
                    </div>
                    
                    <div class="stat-item bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-primary"><?php echo $stats['total_comments'] ?? 0; ?></div>
                        <div class="text-gray-500 text-sm">Komentarze</div>
                    </div>
                    
                    <div class="stat-item bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-primary"><?php echo $stats['total_views'] ?? 0; ?></div>
                        <div class="text-gray-500 text-sm">Wyświetlenia</div>
                    </div>
                </div>
            </div>
            
            <!-- Zainteresowania -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Zainteresowania</h2>
                
                <?php if (empty($interests)): ?>
                    <p class="text-gray-500">Ten użytkownik nie dodał jeszcze zainteresowań.</p>
                <?php else: ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($interests as $index => $interest): ?>
                            <span class="interest-item category-badge text-white text-sm px-3 py-1 rounded-full" style="animation-delay: <?php echo $index * 0.1; ?>s">
                                <?php echo htmlspecialchars($interest); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Prawa kolumna - Ostatnie posty - ulepszone z klasami Tailwind -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Ostatnie posty</h2>
                    <a href="?page=user_posts&id=<?php echo $user_id; ?>" class="text-primary hover:underline transition-colors duration-300 flex items-center group">
                        <span>Zobacz wszystkie</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform duration-300"></i>
                    </a>
                </div>
                
                <?php if (empty($recent_posts)): ?>
                    <div class="text-center py-12 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="text-gray-300 text-6xl mb-4">
                            <i class="far fa-file-alt"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">Brak postów</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">Ten użytkownik nie dodał jeszcze żadnych postów. Zainspiruj innych swoją historią.</p>
                        <a href="?page=create_post" class="bg-gradient-to-r from-rose-500 to-pink-500 text-white px-8 py-3 rounded-full font-medium hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 inline-flex items-center">
                            <i class="fas fa-plus mr-2"></i> Dodaj pierwszy post
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-8">
                        <?php foreach ($recent_posts as $post): ?>
                            <div class="group bg-gray-50 hover:bg-gray-100 rounded-xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="flex flex-col md:flex-row">
                                    <?php if (!empty($post['image'])): ?>
                                        <div class="md:w-1/3 h-48 md:h-auto overflow-hidden relative">
                                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="p-6 flex-1 <?php echo empty($post['image']) ? '' : 'md:w-2/3'; ?>">
                                        <div class="flex items-center mb-3">
                                            <span class="bg-gradient-to-r from-rose-500 to-pink-500 text-white text-xs px-3 py-1 rounded-full">
                                                <?php echo htmlspecialchars($post['category']); ?>
                                            </span>
                                            <span class="text-gray-400 text-xs ml-auto italic">
                                                <i class="far fa-clock mr-1"></i> <?php echo formatDate($post['created_at']); ?>
                                            </span>
                                        </div>
                                        
                                        <h3 class="text-xl font-bold text-gray-800 mb-2 hover:text-primary transition">
                                            <a href="?page=post&id=<?php echo $post['id']; ?>">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h3>
                                        
                                        <p class="text-gray-600 mb-4 overflow-hidden line-clamp-3">
                                            <?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) . (strlen(strip_tags($post['content'])) > 150 ? '...' : ''); ?>
                                        </p>
                                        
                                        <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                                            <div class="flex items-center space-x-4 text-gray-500">
                                                <div class="flex items-center space-x-1 text-xs">
                                                    <i class="far fa-heart mr-1 text-primary"></i>
                                                    <span><?php echo number_format($post['likes_count']); ?></span>
                                                </div>
                                                <div class="flex items-center space-x-1 text-xs">
                                                    <i class="far fa-comment mr-1 text-primary"></i>
                                                    <span><?php echo number_format($post['comments_count']); ?></span>
                                                </div>
                                                <div class="flex items-center space-x-1 text-xs">
                                                    <i class="far fa-eye mr-1 text-primary"></i>
                                                    <span><?php echo number_format($post['views_count']); ?></span>
                                                </div>
                                            </div>
                                            
                                            <a href="?page=post&id=<?php echo $post['id']; ?>" class="text-primary text-sm font-medium hover:text-primary transition-colors duration-300 inline-flex items-center">
                                                <span>Czytaj więcej</span>
                                                <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-8 text-center">
                        <a href="?page=user_posts&id=<?php echo $user_id; ?>" class="bg-white border border-rose-500 text-rose-500 hover:bg-rose-500 hover:text-white px-6 py-2 rounded-full font-medium transition-colors duration-300 inline-flex items-center">
                            <span>Zobacz wszystkie posty</span>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal edycji profilu -->
<div id="edit-profile-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center opacity-0 invisible transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-lg max-w-3xl w-full mx-4 transform translate-y-8 transition-transform duration-300">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Edytuj profil</h2>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="update_profile">
                
                <!-- Baner -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Baner profilu:</label>
                    <div class="h-40 bg-gray-100 rounded-lg overflow-hidden relative" id="banner-preview">
                        <?php if (!empty($user['banner_image']) && file_exists($user['banner_image'])): ?>
                            <img src="<?php echo htmlspecialchars(getImageUrl($user['banner_image'])); ?>" alt="Baner użytkownika" class="w-full h-full object-cover absolute inset-0">
                            <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                                <span class="text-white font-medium">Kliknij, aby zmienić</span>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center p-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">Kliknij, aby wybrać baner</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="banner_image" id="banner-input" class="hidden" accept="image/jpeg, image/png, image/gif">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Zalecany rozmiar: 1920x500 pikseli. Maksymalny rozmiar pliku: 5MB.</p>
                </div>
                
                <!-- Avatar -->
                <div class="flex items-start space-x-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Zdjęcie profilowe:</label>
                        <div class="w-24 h-24 bg-gray-100 rounded-full overflow-hidden relative" id="avatar-preview">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars(getImageUrl($user['profile_image'])); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>" class="w-full h-full object-cover absolute inset-0">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-primary text-white text-4xl font-bold">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="profile_image" id="avatar-input" class="hidden" accept="image/jpeg, image/png, image/gif">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Maks. 5MB</p>
                    </div>
                    
                    <div class="flex-1">
                        <label for="bio" class="block text-gray-700 font-medium mb-2">O mnie:</label>
                        <textarea 
                            id="bio" 
                            name="bio" 
                            class="w-full h-32 border border-gray-300 rounded-lg p-3 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 outline-none transition"
                            placeholder="Napisz coś o sobie..."
                        ><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Opisz siebie, swoje zainteresowania i pasje.</p>
                    </div>
                </div>
                
                <!-- Zainteresowania -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Zainteresowania:</label>
                    <div class="max-h-48 overflow-y-auto p-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <?php foreach ($all_categories as $category): ?>
                            <label class="flex items-center space-x-2 p-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="interests[]" 
                                    value="<?php echo htmlspecialchars($category); ?>"
                                    <?php echo in_array($category, $interests) ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-primary focus:ring-primary"
                                >
                                <span class="text-sm"><?php echo htmlspecialchars($category); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Wybierz kategorie, które Cię interesują.</p>
                </div>
                
                <!-- Przyciski -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" id="cancel-edit-btn" class="px-6 py-2 border border-gray-300 rounded-full text-gray-700 font-medium hover:bg-gray-50 transition">
                        Anuluj
                    </button>
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-full font-medium hover:shadow-lg transition-all duration-300">
                        Zapisz zmiany
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Przycisk dodawania posta (mobilny, stały) -->
<div class="fixed bottom-6 right-6 md:hidden">
    <a href="?page=create_post" class="bg-button-gradient text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg btn-hover-effect">
        <i class="fas fa-plus text-xl"></i>
    </a>
</div>

<!-- Stopka -->
<footer class="bg-white border-t border-gray-200 mt-12 py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <img src="./assets/images/logo-2.png" alt="LoveBlog Logo" class="h-8">
                <p class="text-gray-600 text-sm mt-2">Miejsce, gdzie miłość spotyka się z technologią.</p>
            </div>
            
            <div class="flex space-x-6">
                <a href="#" class="text-gray-600 hover:text-primary transition">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="text-gray-600 hover:text-primary transition">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="text-gray-600 hover:text-primary transition">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="text-gray-600 hover:text-primary transition">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
        
        <div class="border-t border-gray-200 mt-6 pt-6 text-center">
            <p class="text-gray-600 text-sm">
                &copy; <?php echo date('Y'); ?> LoveBlog. Wszelkie prawa zastrzeżone.
            </p>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuButton = document.getElementById('user-menu-button');
        const menuContainer = document.getElementById('user-menu-container');
        const dropdownMenu = document.getElementById('dropdown-menu');
        const menuArrow = document.getElementById('menu-arrow');
        let isMenuOpen = false;
        let timeoutId;
        
        // Funkcja otwierająca menu
        function openMenu() {
            dropdownMenu.classList.add('menu-open');
            menuArrow.classList.add('rotate-arrow');
            isMenuOpen = true;
            clearTimeout(timeoutId);
        }
        
        // Funkcja zamykająca menu
        function closeMenu() {
            timeoutId = setTimeout(() => {
                dropdownMenu.classList.remove('menu-open');
                menuArrow.classList.remove('rotate-arrow');
                isMenuOpen = false;
            }, 300); // Opóźnienie zamknięcia menu
        }
        
        // Obsługa kliknięcia przycisku menu
        menuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isMenuOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });
        
        // Obsługa najechania na menu
        menuContainer.addEventListener('mouseenter', openMenu);
        menuContainer.addEventListener('mouseleave', closeMenu);
        
        // Zatrzymanie zamykania menu, gdy kursor jest nad menu
        dropdownMenu.addEventListener('mouseenter', function() {
            clearTimeout(timeoutId);
        });
        
        // Zamknięcie menu po kliknięciu poza nim
        document.addEventListener('click', function(e) {
            if (!menuContainer.contains(e.target)) {
                closeMenu();
            }
        });
        
        // Dodanie efektu ripple do elementów menu
        const menuItems = dropdownMenu.querySelectorAll('a');
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                const rect = item.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.style.position = 'absolute';
                ripple.style.width = '1px';
                ripple.style.height = '1px';
                ripple.style.borderRadius = '50%';
                ripple.style.transform = 'scale(0)';
                ripple.style.backgroundColor = 'rgba(227, 75, 118, 0.3)';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.animation = 'ripple 0.6s linear';
                
                item.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
        
        // Obsługa modalu edycji profilu
        const editProfileBtn = document.getElementById('edit-profile-btn');
        const editProfileModal = document.getElementById('edit-profile-modal');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const cancelEditBtn = document.getElementById('cancel-edit-btn');
        
        // Otwieranie modalu
        editProfileBtn.addEventListener('click', function() {
            editProfileModal.classList.remove('opacity-0', 'invisible');
            editProfileModal.classList.add('opacity-100', 'visible');
            document.body.style.overflow = 'hidden'; // Blokowanie przewijania strony
        });
        
        // Zamykanie modalu
        function closeModal() {
            editProfileModal.classList.remove('opacity-100', 'visible');
            editProfileModal.classList.add('opacity-0', 'invisible');
            document.body.style.overflow = ''; // Przywrócenie przewijania strony
        }
        
        closeModalBtn.addEventListener('click', closeModal);
        cancelEditBtn.addEventListener('click', closeModal);
        
        // Zamykanie modalu po kliknięciu poza nim
        editProfileModal.addEventListener('click', function(e) {
            if (e.target === editProfileModal) {
                closeModal();
            }
        });
        
        // Obsługa przesyłania zdjęć
        const bannerInput = document.getElementById('banner-input');
        const bannerPreview = document.getElementById('banner-preview');
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');
        
        // Podgląd banera
        bannerPreview.addEventListener('click', function() {
            bannerInput.click();
        });
        
        bannerInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    bannerPreview.innerHTML = `
                        <img src="${e.target.result}" alt="Podgląd banera" class="w-full h-full object-cover absolute inset-0">
                        <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                            <span class="text-white font-medium">Kliknij, aby zmienić</span>
                        </div>
                        <input type="file" name="banner_image" id="banner-input" class="hidden" accept="image/jpeg, image/png, image/gif">
                    `;
                    
                    // Ponowne dodanie nasłuchiwania zdarzeń
                    document.getElementById('banner-input').addEventListener('change', bannerInput.onchange);
                    document.getElementById('banner-preview').addEventListener('click', function() {
                        document.getElementById('banner-input').click();
                    });
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Podgląd avatara
        avatarPreview.addEventListener('click', function() {
            avatarInput.click();
        });
        
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    avatarPreview.innerHTML = `
                        <img src="${e.target.result}" alt="Podgląd avatara" class="w-full h-full object-cover absolute inset-0 rounded-full">
                        <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300 rounded-full">
                            <span class="text-white text-xs font-medium">Zmień</span>
                        </div>
                        <input type="file" name="profile_image" id="avatar-input" class="hidden" accept="image/jpeg, image/png, image/gif">
                    `;
                    
                    // Ponowne dodanie nasłuchiwania zdarzeń
                    document.getElementById('avatar-input').addEventListener('change', avatarInput.onchange);
                    document.getElementById('avatar-preview').addEventListener('click', function() {
                        document.getElementById('avatar-input').click();
                    });
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Sprawdź czy strona została odświeżona po aktualizacji
    if (window.location.search.includes('updated=1')) {
        // Wymuś odświeżenie wszystkich obrazów
        const timestamp = new Date().getTime();
        document.querySelectorAll('img').forEach(img => {
            const url = new URL(img.src, window.location.href);
            url.searchParams.set('v', timestamp);
            img.src = url.toString();
        });
    }
</script>
</body>
</html>