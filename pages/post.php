<?php
// Dołączenie pliku połączenia z bazą danych
require_once __DIR__ . '/../config/db.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

// Pobranie ID posta
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    header('Location: ?page=main');
    exit;
}

// Pobranie danych użytkownika
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// Pobranie danych posta
try {
    // Zwiększenie licznika wyświetleń
    $stmt = $pdo->prepare("UPDATE posts SET views_count = views_count + 1 WHERE id = ?");
    $stmt->execute([$post_id]);
    
    // Pobranie danych posta
    $stmt = $pdo->prepare("
        SELECT p.*, u.name as author_name, u.profile_image as author_image 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header('Location: ?page=main');
        exit;
    }
    
    // Pobranie komentarzy
    $stmt = $pdo->prepare("
        SELECT c.*, u.name as author_name, u.profile_image as author_image 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.post_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sprawdzenie, czy użytkownik polubił post
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $user_liked = $stmt->rowCount() > 0;
    
} catch (PDOException $e) {
    $error = "Wystąpił błąd podczas pobierania danych: " . $e->getMessage();
}

// Obsługa dodawania komentarza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment_content = trim($_POST['comment']);
    
    if (!empty($comment_content)) {
        try {
            // Dodanie komentarza
            $stmt = $pdo->prepare("
                INSERT INTO comments (post_id, user_id, content, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$post_id, $user_id, $comment_content]);
            
            // Zwiększenie licznika komentarzy
            $stmt = $pdo->prepare("UPDATE posts SET comments_count = comments_count + 1 WHERE id = ?");
            $stmt->execute([$post_id]);
            
            // Odświeżenie strony
            header("Location: ?page=post&id=$post_id");
            exit;
        } catch (PDOException $e) {
            $error = "Wystąpił błąd podczas dodawania komentarza: " . $e->getMessage();
        }
    }
}

// Obsługa polubienia/odlubienia posta
if (isset($_GET['action']) && ($_GET['action'] === 'like' || $_GET['action'] === 'unlike')) {
    try {
        if ($_GET['action'] === 'like' && !$user_liked) {
            // Dodanie polubienia
            $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$post_id, $user_id]);
            
            // Zwiększenie licznika polubień
            $stmt = $pdo->prepare("UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?");
            $stmt->execute([$post_id]);
            
            $user_liked = true;
        } elseif ($_GET['action'] === 'unlike' && $user_liked) {
            // Usunięcie polubienia
            $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post_id, $user_id]);
            
            // Zmniejszenie licznika polubień
            $stmt = $pdo->prepare("UPDATE posts SET likes_count = likes_count - 1 WHERE id = ?");
            $stmt->execute([$post_id]);
            
            $user_liked = false;
        }
        
        // Odświeżenie strony
        header("Location: ?page=post&id=$post_id");
        exit;
    } catch (PDOException $e) {
        $error = "Wystąpił błąd podczas aktualizacji polubienia: " . $e->getMessage();
    }
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
    <title><?php echo isset($post) ? htmlspecialchars($post['title']) . ' - LoveBlog' : 'Post - LoveBlog'; ?></title>
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
        
        .category-badge {
            background: linear-gradient(to right, #e34b76, #e8638b);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Nagłówek -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="?page=main" class="flex items-center">
            <img src="./assets/images/logo.png" alt="LoveBlog Logo" class="h-10">
        </a>
        
        <!-- Menu użytkownika -->
        <div class="flex items-center space-x-4">
            <a href="?page=create_post" class="bg-button-gradient text-white px-4 py-2 rounded-full font-medium btn-hover-effect hidden sm:block">
                <i class="fas fa-plus mr-2"></i> Nowy post
            </a>
            
            <div class="relative group">
                <button class="flex items-center space-x-2 focus:outline-none">
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
                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                </button>
                
                <!-- Menu rozwijane -->
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                    <a href="?page=profile" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-user mr-2 text-primary"></i> Mój profil
                    </a>
                    <a href="?page=settings" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-cog mr-2 text-primary"></i> Ustawienia
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="?page=logout" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-sign-out-alt mr-2 text-primary"></i> Wyloguj się
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Główna zawartość -->
<div class="container mx-auto px-4 py-8">
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Błąd!</strong>
            <span class="block sm:inline"> <?php echo $error; ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($post)): ?>
        <div class="max-w-4xl mx-auto">
            <!-- Nawigacja -->
            <div class="mb-6">
                <a href="?page=main" class="text-gray-600 hover:text-primary transition">
                    <i class="fas fa-arrow-left mr-2"></i> Powrót do strony głównej
                </a>
            </div>
            
            <!-- Post -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <?php if (!empty($post['image'])): ?>
                    <div class="h-64 md:h-96 overflow-hidden">
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>
                
                <div class="p-6 md:p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 rounded-full bg-gray-200 overflow-hidden mr-4">
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
                            <p class="text-sm text-gray-500"><?php echo formatDate($post['created_at']); ?></p>
                        </div>
                    </div>
                    
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <div class="flex items-center mb-6">
                        <span class="category-badge text-white text-xs px-3 py-1 rounded-full mr-4">
                            <?php echo htmlspecialchars($post['category']); ?>
                        </span>
                        
                        <div class="flex items-center space-x-4 text-gray-500">
                            <div class="flex items-center">
                                <i class="far fa-eye mr-1"></i>
                                <span class="text-sm"><?php echo $post['views_count']; ?> wyświetleń</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="prose max-w-none mb-8">
                        <?php echo $post['content']; ?>
                    </div>
                    
                    <div class="flex items-center justify-between border-t border-gray-100 pt-6">
                        <div class="flex items-center space-x-4">
                            <?php if ($user_liked): ?>
                                <a href="?page=post&id=<?php echo $post_id; ?>&action=unlike" class="flex items-center text-primary">
                                    <i class="fas fa-heart mr-2"></i>
                                    <span><?php echo $post['likes_count']; ?> polubień</span>
                                </a>
                            <?php else: ?>
                                <a href="?page=post&id=<?php echo $post_id; ?>&action=like" class="flex items-center text-gray-500 hover:text-primary transition">
                                    <i class="far fa-heart mr-2"></i>
                                    <span><?php echo $post['likes_count']; ?> polubień</span>
                                </a>
                            <?php endif; ?>
                            
                            <div class="flex items-center text-gray-500">
                                <i class="far fa-comment mr-2"></i>
                                <span><?php echo $post['comments_count']; ?> komentarzy</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <button class="text-gray-500 hover:text-primary transition">
                                <i class="fas fa-share-alt mr-2"></i>
                                <span>Udostępnij</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sekcja komentarzy -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="p-6 md:p-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Komentarze (<?php echo count($comments); ?>)</h2>
                    
                    <!-- Formularz dodawania komentarza -->
                    <form method="POST" action="" class="mb-8">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                                <?php if (isset($_SESSION['user_profile_image']) && !empty($_SESSION['user_profile_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($_SESSION['user_profile_image']); ?>" alt="<?php echo htmlspecialchars($user_name); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-primary text-white text-xl font-bold">
                                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow">
                                <textarea 
                                    name="comment" 
                                    placeholder="Napisz komentarz..." 
                                    class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-primary"
                                    rows="3"
                                    required
                                ></textarea>
                                <button type="submit" class="mt-2 bg-button-gradient text-white px-4 py-2 rounded-full font-medium btn-hover-effect">
                                    Dodaj komentarz
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Lista komentarzy -->
                    <?php if (empty($comments)): ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">Brak komentarzy. Bądź pierwszy i dodaj komentarz!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($comments as $comment): ?>
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                                        <?php if (!empty($comment['author_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($comment['author_image']); ?>" alt="<?php echo htmlspecialchars($comment['author_name']); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center bg-primary text-white text-xl font-bold">
                                                <?php echo strtoupper(substr($comment['author_name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow">
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($comment['author_name']); ?></h4>
                                                <p class="text-xs text-gray-500"><?php echo formatDate($comment['created_at']); ?></p>
                                            </div>
                                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Stopka -->
<footer class="bg-white border-t border-gray-200 mt-12 py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <img src="./assets/images/logo.png" alt="LoveBlog Logo" class="h-8">
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

</body>
</html> 