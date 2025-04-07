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

// Inicjalizacja zmiennych
$title = '';
$content = '';
$category = '';
$errors = [];
$success = false;

// Pobranie kategorii
try {
    $stmt = $pdo->query("SELECT name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $errors['db'] = 'Wystąpił błąd podczas pobierania kategorii: ' . $e->getMessage();
    $categories = [];
}

// Obsługa przesłanego formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobranie i sanityzacja danych z formularza
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? '';
    
    // Walidacja danych
    if (empty($title)) {
        $errors['title'] = 'Tytuł jest wymagany.';
    } elseif (strlen($title) > 255) {
        $errors['title'] = 'Tytuł nie może być dłuższy niż 255 znaków.';
    }
    
    if (empty($content)) {
        $errors['content'] = 'Treść posta jest wymagana.';
    }
    
    if (empty($category)) {
        $errors['category'] = 'Kategoria jest wymagana.';
    }
    
    // Obsługa przesyłania obrazu
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors['image'] = 'Dozwolone są tylko pliki typu JPG, PNG i GIF.';
        } elseif ($_FILES['image']['size'] > $max_file_size) {
            $errors['image'] = 'Maksymalny rozmiar pliku to 5MB.';
        } else {
            $upload_dir = 'uploads/posts/';
            
            // Sprawdź czy katalog istnieje, jeśli nie - utwórz go
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generuj unikalną nazwę pliku
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            // Przenieś plik do docelowego katalogu
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image_path = $file_path;
            } else {
                $errors['image'] = 'Wystąpił błąd podczas przesyłania pliku.';
            }
        }
    }
    
    // Jeśli brak błędów, zapisz post do bazy
    if (empty($errors)) {
        try {
            // Przygotowanie i wykonanie zapytania
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, category, image, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$user_id, $title, $content, $category, $image_path]);
            
            // Sukces
            $success = true;
            
            // Przekierowanie do strony głównej po 2 sekundach
            header("refresh:2;url=?page=main");
        } catch (PDOException $e) {
            $errors['db'] = 'Wystąpił błąd podczas dodawania posta: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj nowy post - LoveBlog</title>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dodanie edytora tekstu -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
        
        /* Stylowanie dla komunikatów błędów */
        .error-message {
            color: #e34b76;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        /* Stylowanie dla edytora tekstu */
        .ql-toolbar.ql-snow {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            border-color: #e5e7eb;
        }
        
        .ql-container.ql-snow {
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            border-color: #e5e7eb;
            min-height: 200px;
        }
        
        .ql-editor:focus {
            outline: none;
            border-color: #e34b76;
        }
        
        /* Animacja dla przycisku publikacji */
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
        
        /* Stylowanie dla obszaru przesyłania obrazu */
        .image-upload-area {
            background-color: #f8f8f8;
            border: 2px dashed #e1e1e1;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .image-upload-area:hover {
            border-color: #e34b76;
            background-color: #fafafa;
        }
        
        /* Menu rozwijane - styles */
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
<header class="bg-white shadow-sm">
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
    <div class="max-w-3xl mx-auto">
        <!-- Nagłówek strony -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Dodaj nowy post</h1>
            <p class="text-gray-600">Podziel się swoimi myślami, doświadczeniami lub zadaj pytanie społeczności.</p>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Sukces!</strong>
                <span class="block sm:inline"> Twój post został dodany pomyślnie. Za chwilę zostaniesz przekierowany na stronę główną.</span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['db'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Błąd!</strong>
                <span class="block sm:inline"> <?php echo $errors['db']; ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Formularz dodawania posta -->
        <form method="POST" action="" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-6">
            <!-- Tytuł posta -->
            <div class="mb-6">
                <label for="title" class="block text-gray-700 font-medium mb-2">Tytuł posta:</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="<?php echo htmlspecialchars($title); ?>"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                    placeholder="Wpisz tytuł posta..."
                    required
                >
                <?php if (isset($errors['title'])): ?>
                    <p class="error-message"><?php echo $errors['title']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Kategoria -->
            <div class="mb-6">
                <label for="category" class="block text-gray-700 font-medium mb-2">Kategoria:</label>
                <select 
                    id="category" 
                    name="category" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                    required
                >
                    <option value="" disabled <?php echo empty($category) ? 'selected' : ''; ?>>Wybierz kategorię</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <p class="error-message"><?php echo $errors['category']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Treść posta -->
            <div class="mb-6">
                <label for="editor" class="block text-gray-700 font-medium mb-2">Treść posta:</label>
                <div id="editor"><?php echo htmlspecialchars($content); ?></div>
                <input type="hidden" name="content" id="content">
                <?php if (isset($errors['content'])): ?>
                    <p class="error-message"><?php echo $errors['content']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Przesyłanie obrazu -->
            <div class="mb-6">
                <label for="image" class="block text-gray-700 font-medium mb-2">Dodaj zdjęcie (opcjonalnie):</label>
                <div class="image-upload-area h-40 flex items-center justify-center" id="image-preview">
                    <div class="text-center p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Kliknij, aby wybrać zdjęcie</p>
                        <p class="text-xs text-gray-400">Maksymalny rozmiar: 5MB. Dozwolone formaty: JPG, PNG, GIF</p>
                    </div>
                    <input type="file" name="image" id="image-input" class="hidden" accept="image/jpeg, image/png, image/gif">
                </div>
                <?php if (isset($errors['image'])): ?>
                    <p class="error-message"><?php echo $errors['image']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Przyciski -->
            <div class="flex justify-between">
                <a href="?page=main" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                    Anuluj
                </a>
                <button 
                    type="submit" 
                    class="bg-button-gradient text-white px-6 py-3 rounded-lg font-medium btn-hover-effect pulse-animation"
                >
                    Opublikuj post
                </button>
            </div>
        </form>
    </div>
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

<!-- Skrypty -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    // Inicjalizacja edytora tekstu
    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Napisz treść swojego posta...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link'],
                ['clean']
            ]
        }
    });
    
    // Aktualizacja ukrytego pola formularza przed wysłaniem
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('content').value = quill.root.innerHTML;
    });
    
    // Obsługa przesyłania obrazu
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    
    imagePreview.addEventListener('click', function() {
        imageInput.click();
    });
    
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imagePreview.innerHTML = '';
                imagePreview.style.backgroundImage = `url('${e.target.result}')`;
                imagePreview.style.backgroundSize = 'cover';
                imagePreview.style.backgroundPosition = 'center';
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>

<!-- Dodaj ten skrypt tuż przed zamykającym tagiem </body> -->
<script>
// Czekaj na załadowanie strony
document.addEventListener('DOMContentLoaded', function() {
    console.log('Menu script loaded'); // Sprawdzenie czy skrypt się ładuje
    
    // Pobieranie elementów DOM
    const menuButton = document.getElementById('user-menu-button');
    const menuContainer = document.getElementById('user-menu-container');
    const dropdownMenu = document.getElementById('dropdown-menu');
    const menuArrow = document.getElementById('menu-arrow');
    
    // Sprawdzenie czy elementy zostały znalezione
    if (!menuButton || !menuContainer || !dropdownMenu || !menuArrow) {
        console.error('Menu elements not found:', {
            menuButton, menuContainer, dropdownMenu, menuArrow
        });
        return;
    }
    
    console.log('Menu elements found');
    
    let isMenuOpen = false;
    let timeoutId;
    
    // Funkcja otwierająca menu
    function openMenu() {
        console.log('Opening menu');
        dropdownMenu.classList.add('menu-open');
        menuArrow.classList.add('rotate-arrow');
        isMenuOpen = true;
        clearTimeout(timeoutId);
    }
    
    // Funkcja zamykająca menu
    function closeMenu() {
        console.log('Closing menu');
        timeoutId = setTimeout(() => {
            dropdownMenu.classList.remove('menu-open');
            menuArrow.classList.remove('rotate-arrow');
            isMenuOpen = false;
        }, 300);
    }
    
    // Obsługa kliknięcia przycisku menu
    menuButton.addEventListener('click', function(e) {
        console.log('Menu button clicked');
        e.stopPropagation();
        if (isMenuOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    });
    
    // Obsługa najechania na menu
    menuContainer.addEventListener('mouseenter', function() {
        console.log('Mouse entered menu container');
        openMenu();
    });
    
    menuContainer.addEventListener('mouseleave', function() {
        console.log('Mouse left menu container');
        closeMenu();
    });
    
    // Zatrzymanie zamykania menu, gdy kursor jest nad menu
    dropdownMenu.addEventListener('mouseenter', function() {
        console.log('Mouse entered dropdown menu');
        clearTimeout(timeoutId);
    });
    
    // Zamknięcie menu po kliknięciu poza nim
    document.addEventListener('click', function(e) {
        if (!menuContainer.contains(e.target)) {
            console.log('Clicked outside menu');
            closeMenu();
        }
    });
    
    // Dodanie efektu ripple do elementów menu
    const menuItems = dropdownMenu.querySelectorAll('a');
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            console.log('Menu item clicked');
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
    
    console.log('Menu initialization complete');
});
</script>
</body>
</html> 