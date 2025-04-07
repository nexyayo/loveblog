<?php
// Dołączenie pliku połączenia z bazą danych
require_once __DIR__ . '/../config/db.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

// Pobranie danych użytkownika
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// Parametry filtrowania i sortowania
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Przygotowanie zapytania SQL
$query = "SELECT p.*, u.name as author_name, u.profile_image as author_image 
          FROM posts p 
          JOIN users u ON p.user_id = u.id 
          WHERE 1=1";
$params = [];

// Dodanie warunków wyszukiwania
if (!empty($search)) {
    $query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Filtrowanie według kategorii
if (!empty($category)) {
    $query .= " AND p.category = ?";
    $params[] = $category;
}

// Sortowanie
switch ($sort) {
    case 'oldest':
        $query .= " ORDER BY p.created_at ASC";
        break;
    case 'most_liked':
        $query .= " ORDER BY p.likes_count DESC";
        break;
    case 'most_commented':
        $query .= " ORDER BY p.comments_count DESC";
        break;
    case 'most_viewed':
        $query .= " ORDER BY p.views_count DESC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

// Wykonanie zapytania
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Wystąpił błąd podczas pobierania postów: " . $e->getMessage();
}

// Pobranie kategorii do filtrowania
try {
    $stmt = $pdo->query("SELECT name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}

// Formatowanie daty
function formatDate($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return "przed chwilą";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . " " . ($minutes == 1 ? "minutę" : ($minutes < 5 ? "minuty" : "minut")) . " temu";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " " . ($hours == 1 ? "godzinę" : ($hours < 5 ? "godziny" : "godzin")) . " temu";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . " " . ($days == 1 ? "dzień" : "dni") . " temu";
    } else {
        return date("d.m.Y", $timestamp);
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoveBlog - Strona główna</title>
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
        
        <!-- Wyszukiwarka -->
        <form action="" method="GET" class="hidden md:flex items-center flex-grow max-w-xl mx-8">
            <input type="hidden" name="page" value="main">
            <div class="relative w-full">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Szukaj postów..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="w-full py-2 pl-10 pr-4 border border-gray-300 rounded-full focus:outline-none focus:border-primary"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <?php if (!empty($search) || !empty($category) || $sort !== 'newest'): ?>
                    <a href="?page=main" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-times text-gray-400 hover:text-primary"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Menu użytkownika -->
        <div class="flex items-center space-x-4">
            <a href="?page=create_post" class="bg-button-gradient text-white px-4 py-2 rounded-full font-medium btn-hover-effect hidden sm:block">
                <i class="fas fa-plus mr-2"></i> Nowy post
            </a>
            
            <div class="relative" id="user-menu-container">
                <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                    <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden">
                        <?php if (isset($_SESSION['user_profile_image']) && !empty($_SESSION['user_profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['user_profile_image']); ?>" alt="<?php echo htmlspecialchars($user_name); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-primary text-white text-xl font-bold">
                                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span class="hidden md:block font-medium"><?php echo htmlspecialchars($user_name); ?></span>
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
    
    <!-- Wyszukiwarka mobilna -->
    <div class="md:hidden border-t border-gray-100 px-4 py-3">
        <form action="" method="GET" class="flex items-center">
            <input type="hidden" name="page" value="main">
            <div class="relative w-full">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Szukaj postów..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="w-full py-2 pl-10 pr-4 border border-gray-300 rounded-full focus:outline-none focus:border-primary"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <?php if (!empty($search) || !empty($category) || $sort !== 'newest'): ?>
                    <a href="?page=main" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-times text-gray-400 hover:text-primary"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</header>

<!-- Baner główny -->
<div class="bg-cover bg-center py-16 md:py-24 relative" style="background-image: url('./assets/images/main-page-bg-2.jpg');">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="container mx-auto px-4 relative z-10 text-center">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">Witaj w LoveBlog!</h1>
        <p class="text-xl text-white mb-8 max-w-2xl mx-auto">Miejsce, gdzie możesz dzielić się swoimi doświadczeniami, szukać porad i poznawać nowych ludzi.</p>
        <a href="?page=create_post" class="bg-button-gradient text-white px-6 py-3 rounded-full font-medium btn-hover-effect pulse-animation inline-block">
            <i class="fas fa-plus mr-2"></i> Dodaj nowy post
        </a>
    </div>
</div>

<!-- Główna zawartość -->
<div class="container mx-auto px-4 py-8">
    <!-- Filtry i sortowanie -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <!-- Filtry kategorii -->
            <div class="flex flex-wrap gap-2 mb-4 md:mb-0">
                <a href="?page=main<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $sort !== 'newest' ? '&sort=' . urlencode($sort) : ''; ?>" 
                   class="<?php echo empty($category) ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> px-3 py-1 rounded-full text-sm font-medium transition">
                    Wszystkie
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="?page=main<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&category=<?php echo urlencode($cat); ?><?php echo $sort !== 'newest' ? '&sort=' . urlencode($sort) : ''; ?>" 
                       class="<?php echo $category === $cat ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> px-3 py-1 rounded-full text-sm font-medium transition">
                        <?php echo htmlspecialchars($cat); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Sortowanie -->
            <div class="flex items-center">
                <span class="text-gray-500 mr-2">Sortuj:</span>
                <select name="sort" id="sort" class="border-none bg-transparent focus:outline-none text-gray-700 font-medium" onchange="window.location.href='?page=main<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>&sort=' + this.value">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Najnowsze</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Najstarsze</option>
                    <option value="most_liked" <?php echo $sort === 'most_liked' ? 'selected' : ''; ?>>Najpopularniejsze</option>
                    <option value="most_commented" <?php echo $sort === 'most_commented' ? 'selected' : ''; ?>>Najczęściej komentowane</option>
                    <option value="most_viewed" <?php echo $sort === 'most_viewed' ? 'selected' : ''; ?>>Najczęściej oglądane</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Wyniki wyszukiwania -->
    <?php if (!empty($search) || !empty($category)): ?>
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                <?php if (!empty($search) && !empty($category)): ?>
                    Wyniki wyszukiwania dla "<?php echo htmlspecialchars($search); ?>" w kategorii "<?php echo htmlspecialchars($category); ?>"
                <?php elseif (!empty($search)): ?>
                    Wyniki wyszukiwania dla "<?php echo htmlspecialchars($search); ?>"
                <?php elseif (!empty($category)): ?>
                    Posty w kategorii "<?php echo htmlspecialchars($category); ?>"
                <?php endif; ?>
            </h2>
        </div>
    <?php endif; ?>
    
    <!-- Lista postów -->
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Błąd!</strong>
            <span class="block sm:inline"> <?php echo $error; ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (empty($posts)): ?>
        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
            <div class="text-gray-400 text-6xl mb-4">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Brak postów do wyświetlenia</h3>
            <p class="text-gray-600 mb-6">
                <?php if (!empty($search) || !empty($category)): ?>
                    Nie znaleziono postów spełniających kryteria wyszukiwania.
                <?php else: ?>
                    Nie ma jeszcze żadnych postów. Bądź pierwszy i dodaj nowy post!
                <?php endif; ?>
            </p>
            <a href="?page=create_post" class="bg-button-gradient text-white px-6 py-3 rounded-full font-medium btn-hover-effect inline-block">
                <i class="fas fa-plus mr-2"></i> Dodaj nowy post
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($posts as $post): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden post-card">
                    <?php if (!empty($post['image'])): ?>
                        <div class="h-48 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-full object-cover">
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-5">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden mr-3">
                                <?php if (!empty($post['author_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['author_image']); ?>" alt="<?php echo htmlspecialchars($post['author_name']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-primary text-white text-xl font-bold">
                                        <?php echo strtoupper(substr($post['author_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($post['author_name']); ?></h4>
                                <p class="text-xs text-gray-500"><?php echo formatDate($post['created_at']); ?></p>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2">
                            <a href="?page=post&id=<?php echo $post['id']; ?>" class="hover:text-primary transition">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        
                        <p class="text-gray-600 mb-4 line-clamp-3">
                            <?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) . (strlen(strip_tags($post['content'])) > 150 ? '...' : ''); ?>
                        </p>
                        
                        <div class="flex justify-between items-center">
                            <span class="category-badge text-white text-xs px-3 py-1 rounded-full">
                                <?php echo htmlspecialchars($post['category']); ?>
                            </span>
                            
                            <div class="flex items-center space-x-3 text-gray-500">
                                <div class="flex items-center">
                                    <i class="far fa-heart mr-1"></i>
                                    <span class="text-xs"><?php echo $post['likes_count']; ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="far fa-comment mr-1"></i>
                                    <span class="text-xs"><?php echo $post['comments_count']; ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="far fa-eye mr-1"></i>
                                    <span class="text-xs"><?php echo $post['views_count']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
    // Dodanie klasy line-clamp do Tailwind (jeśli nie jest dostępna)
    if (!('line-clamp-2' in document.documentElement.style)) {
        const style = document.createElement('style');
        style.textContent = `
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .line-clamp-3 {
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Obsługa menu rozwijanego
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
    });
    
    // Animacja ripple
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            @keyframes ripple {
                to {
                    transform: scale(100);
                    opacity: 0;
                }
            }
        </style>
    `);
</script>
</body>
</html>