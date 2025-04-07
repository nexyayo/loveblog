<?php
// Dołączenie pliku połączenia z bazą danych
require_once __DIR__ . '/../config/db.php';

// Inicjalizacja zmiennych
$name = $age = $email = $password = $confirm_password = $gender = '';
$errors = [];
$success = false;
$registration_complete = false;

// Sprawdzenie, czy użytkownik jest już zalogowany
if (isset($_SESSION['user_id'])) {
    header('Location: ?page=main');
    exit;
}

// Sprawdzenie automatycznego logowania przez "Zapamiętaj mnie"
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? AND token_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Token jest ważny, zaloguj użytkownika
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Przekierowanie do strony głównej
            header('Location: ?page=main');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Błąd automatycznego logowania: ' . $e->getMessage());
    }
}

// Obsługa przesłanego formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sprawdzamy, czy przesłano formularz rejestracji czy formularz profilu
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'profile') {
        // Obsługa formularza profilu
        $user_id = $_POST['user_id'] ?? 0;
        $bio = $_POST['bio'] ?? '';
        
        // Obsługa przesyłania zdjęcia profilowego
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $profile_image = handleImageUpload('profile_image', 500, 500);
            if (is_array($profile_image)) {
                $errors['profile_image'] = $profile_image['error'];
                $profile_image = null;
            }
        }
        
        // Obsługa przesyłania banera
        $banner_image = null;
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
            $banner_image = handleImageUpload('banner_image', 1920, 500);
            if (is_array($banner_image)) {
                $errors['banner_image'] = $banner_image['error'];
                $banner_image = null;
            }
        }
        
        // Jeśli brak błędów, zaktualizuj profil
        if (empty($errors)) {
            try {
                $query = "UPDATE users SET bio = ?";
                $params = [$bio];
                
                if ($profile_image) {
                    $query .= ", profile_image = ?";
                    $params[] = $profile_image;
                }
                
                if ($banner_image) {
                    $query .= ", banner_image = ?";
                    $params[] = $banner_image;
                }
                
                $query .= " WHERE id = ?";
                $params[] = $user_id;
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                
                // Przekierowanie do strony logowania
                header('Location: ?page=login');
                exit;
            } catch (PDOException $e) {
                $errors['db'] = 'Wystąpił błąd podczas aktualizacji profilu. Spróbuj ponownie później.';
                error_log('Błąd aktualizacji profilu: ' . $e->getMessage());
            }
        }
    } else {
        // Obsługa głównego formularza rejestracji
        // Pobranie i sanityzacja danych z formularza
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $name = $_POST['name'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $privacy_policy = isset($_POST['privacy_policy']);

        // Walidacja danych
        if (empty($name)) {
            $errors['name'] = 'Imię jest wymagane.';
        }

        if (empty($age)) {
            $errors['age'] = 'Wiek jest wymagany.';
        } elseif ($age < 18) {
            $errors['age'] = 'Musisz mieć ukończone 18 lat, aby się zarejestrować.';
        }

        if (empty($email)) {
            $errors['email'] = 'Adres e-mail jest wymagany.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Podaj prawidłowy adres e-mail.';
        } else {
            // Sprawdzenie, czy email już istnieje w bazie
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $errors['email'] = 'Ten adres e-mail jest już zarejestrowany.';
            }
        }

        if (empty($password)) {
            $errors['password'] = 'Hasło jest wymagane.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Hasło musi zawierać co najmniej 8 znaków.';
        }

        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Hasła nie są identyczne.';
        }

        if (empty($gender)) {
            $errors['gender'] = 'Wybór płci jest wymagany.';
        }

        if (!$privacy_policy) {
            $errors['privacy_policy'] = 'Musisz zaakceptować Politykę Prywatności.';
        }

        // Jeśli brak błędów, zapisz dane do bazy
        if (empty($errors)) {
            try {
                // Haszowanie hasła
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Przygotowanie i wykonanie zapytania
                $stmt = $pdo->prepare("INSERT INTO users (name, age, email, password, gender, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([$name, $age, $email, $hashed_password, $gender]);
                
                // Pobranie ID nowego użytkownika
                $user_id = $pdo->lastInsertId();
                
                // Sukces
                $success = true;
                $registration_complete = true;
                
                // Czyszczenie danych po udanej rejestracji
                $name = $age = $email = $password = $confirm_password = $gender = '';
            } catch (PDOException $e) {
                $errors['db'] = 'Wystąpił błąd podczas rejestracji. Spróbuj ponownie później.';
                error_log('Błąd rejestracji: ' . $e->getMessage());
            }
        }
    }
}

// Funkcja do obsługi przesyłania zdjęć
function handleImageUpload($field_name, $max_width, $max_height) {
    $upload_dir = 'uploads/';
    
    // Sprawdź czy katalog istnieje, jeśli nie - utwórz go
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_file_size = 2 * 1024 * 1024; // 2MB
    
    $file = $_FILES[$field_name];
    
    // Sprawdź typ pliku
    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Dozwolone są tylko pliki typu JPG, PNG i GIF.'];
    }
    
    // Sprawdź rozmiar pliku
    if ($file['size'] > $max_file_size) {
        return ['error' => 'Maksymalny rozmiar pliku to 2MB.'];
    }
    
    // Generuj unikalną nazwę pliku
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;
    
    // Przenieś plik do docelowego katalogu
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        
        // Zmieniamy rozmiar zdjęcia jeśli potrzeba
        list($width, $height) = getimagesize($file_path);
        
        if ($width > $max_width || $height > $max_height) {
            $image = null;
            
            if ($file['type'] == 'image/jpeg') {
                $image = imagecreatefromjpeg($file_path);
            } elseif ($file['type'] == 'image/png') {
                $image = imagecreatefrompng($file_path);
            } elseif ($file['type'] == 'image/gif') {
                $image = imagecreatefromgif($file_path);
            }
            
            if ($image) {
                // Obliczamy nowe wymiary z zachowaniem proporcji
                $ratio = min($max_width / $width, $max_height / $height);
                $new_width = round($width * $ratio);
                $new_height = round($height * $ratio);
                
                $new_image = imagecreatetruecolor($new_width, $new_height);
                
                // Zachowaj przezroczystość dla PNG
                if ($file['type'] == 'image/png') {
                    imagealphablending($new_image, false);
                    imagesavealpha($new_image, true);
                    $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                    imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
                }
                
                imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                
                // Zapisz zmniejszony obraz
                if ($file['type'] == 'image/jpeg') {
                    imagejpeg($new_image, $file_path, 90);
                } elseif ($file['type'] == 'image/png') {
                    imagepng($new_image, $file_path, 9);
                } elseif ($file['type'] == 'image/gif') {
                    imagegif($new_image, $file_path);
                }
                
                imagedestroy($image);
                imagedestroy($new_image);
            }
        }
        
        return $file_path;
    } else {
        return ['error' => 'Wystąpił błąd podczas przesyłania pliku.'];
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - LoveBlog</title>
    <link href="./assets/css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
        
        /* Stylowanie dla komunikatów błędów */
        .error-message {
            color: #e34b76;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        .bg-button-gradient {
            background: linear-gradient(to right, #e34b76, #e8638b);
        }
        
        /* Animacja gradientu */
        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .animated-gradient {
            background: linear-gradient(45deg, #e34b76, #e8638b, #B7687F, #F91A1A, #e34b76);
            background-size: 400% 400%;
            animation: gradientAnimation 5s ease infinite;
        }
        
        /* Animacja pojawienia się */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.8s ease forwards;
        }
        
        /* Animacja pojawienia się z opóźnieniem */
        .fade-in-delay {
            opacity: 0;
            animation: fadeIn 0.8s ease forwards;
            animation-delay: 0.3s;
        }
        
        /* Animacja skalowania */
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .scale-in {
            animation: scaleIn 0.5s ease forwards;
        }
        
        /* Animacja pełnego ekranu */
        .fullscreen-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            transition: opacity 0.5s ease;
        }
        
        /* Styl dla okna uzupełnienia profilu */
        .profile-completion-modal {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
            padding: 2rem;
            position: relative;
            z-index: 10000;
        }
        
        /* Styl dla obszaru banera */
        .banner-upload-area {
            background-color: #f8f8f8;
            border: 2px dashed #e1e1e1;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .banner-upload-area:hover {
            border-color: #e34b76;
            background-color: #fafafa;
        }
        
        /* Styl dla obszaru avatara */
        .avatar-upload-area {
            background-color: #e1e1e1;
            border: 2px dashed #ccc;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .avatar-upload-area:hover {
            border-color: #e34b76;
            background-color: #e8e8e8;
        }
    </style>
</head>
<body class="font-sen min-h-screen bg-white">

<?php if ($registration_complete): ?>
<!-- Animacja pełnoekranowa po rejestracji -->
<div class="fullscreen-animation animated-gradient" id="registration-animation">
    <div class="text-center scale-in">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Witamy w LoveBlog!</h1>
        <p class="text-xl text-white">Twoja rejestracja przebiegła pomyślnie.</p>
    </div>
</div>

<!-- Okno uzupełnienia profilu -->
<div class="fixed inset-0 bg-primary bg-opacity-10 flex justify-center items-center opacity-0" id="profile-completion" style="display: none;">
    <div class="profile-completion-modal scale-in">
        <h2 class="text-2xl font-bold text-primary mb-6 text-center">Uzupełnij swój profil:</h2>
        
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="form_type" value="profile">
            <input type="hidden" name="user_id" value="<?php echo $pdo->lastInsertId(); ?>">
            
            <!-- Obszar banera -->
            <div>
                <label class="block text-primary font-medium mb-2">Wybierz baner:</label>
                <div class="banner-upload-area h-40 flex items-center justify-center" id="banner-preview">
                    <div class="text-center p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Kliknij, aby wybrać baner</p>
                    </div>
                    <input type="file" name="banner_image" id="banner-input" class="hidden" accept="image/jpeg, image/png, image/gif">
                </div>
                <?php if (isset($errors['banner_image'])): ?>
                    <p class="error-message"><?php echo $errors['banner_image']; ?></p>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Avatar -->
                <div>
                    <div class="avatar-upload-area w-20 h-20 flex items-center justify-center" id="avatar-preview">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <input type="file" name="profile_image" id="avatar-input" class="hidden" accept="image/jpeg, image/png, image/gif">
                    </div>
                    <?php if (isset($errors['profile_image'])): ?>
                        <p class="error-message text-xs mt-1"><?php echo $errors['profile_image']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Imię i wiek -->
                <div class="font-medium">
                    <p class="text-xl"><?php echo $name; ?>, <?php echo $age; ?></p>
                </div>
            </div>
            
            <!-- Opis -->
            <div>
                <label for="bio" class="block text-primary font-medium mb-2">O mnie:</label>
                <textarea 
                    id="bio" 
                    name="bio" 
                    class="w-full h-32 border-2 border-gray-300 rounded-lg p-3 focus:border-primary focus:outline-none"
                    placeholder="Napisz coś o sobie..."
                ></textarea>
            </div>
            
            <!-- Przycisk zatwierdzenia -->
            <div>
                <button type="submit" class="w-full bg-button-gradient btn-hover-effect text-white py-3 rounded-full font-medium">
                    <a href="?page=login">Zatwierdź</a>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Obsługa animacji po rejestracji
    document.addEventListener('DOMContentLoaded', function() {
        const animation = document.getElementById('registration-animation');
        const profileModal = document.getElementById('profile-completion');
        
        // Pokazujemy animację przez 5 sekund
        setTimeout(function() {
            // Ukrywamy animację
            animation.style.opacity = '0';
            
            // Po fadeout animacji pokazujemy okno uzupełnienia profilu
            setTimeout(function() {
                animation.style.display = 'none';
                profileModal.style.display = 'flex';
                
                // Dodajemy krótką zwłokę przed pokazaniem modalu (efekt fadeIn)
                setTimeout(function() {
                    profileModal.style.opacity = '1';
                }, 100);
            }, 500);
        }, 5000);
        
        // Obsługa podglądu banera
        const bannerInput = document.getElementById('banner-input');
        const bannerPreview = document.getElementById('banner-preview');
        
        bannerPreview.addEventListener('click', function() {
            bannerInput.click();
        });
        
        bannerInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    bannerPreview.innerHTML = '';
                    bannerPreview.style.backgroundImage = `url('${e.target.result}')`;
                    bannerPreview.style.backgroundSize = 'cover';
                    bannerPreview.style.backgroundPosition = 'center';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Obsługa podglądu avatara
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');
        
        avatarPreview.addEventListener('click', function() {
            avatarInput.click();
        });
        
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    avatarPreview.innerHTML = '';
                    avatarPreview.style.backgroundImage = `url('${e.target.result}')`;
                    avatarPreview.style.backgroundSize = 'cover';
                    avatarPreview.style.backgroundPosition = 'center';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>

<?php else: ?>

<div class="flex flex-col md:flex-row min-h-screen">
    <!-- Lewa strona - Zdjęcie tła na większych ekranach -->
    <div class="hidden md:block md:w-1/2 bg-cover bg-center" style="background-image: url('./assets/images/register-bg.png');">
        <div class="flex flex-col justify-center items-center h-full p-12">
            <img src="./assets/images/logo.png" alt="LoveBlog Logo" class="h-12 lg:h-[75px] w-auto mb-6" style="max-width: 340px;">
            <h1 class="text-2xl md:text-3xl font-medium text-white mb-2 text-center">
                Zarejestruj się, aby korzystać z naszego systemu!
            </h1>
            <p class="text-white text-center">
                COPYRIGHT© 2024 BY LOVEBLOG. ALL RIGHTS RESERVED.
            </p>
        </div>
    </div>
    
    <!-- Prawa strona - Formularz -->
    <div class="w-full md:w-1/2 px-4 sm:px-6 lg:px-12 xl:px-24 flex flex-col justify-center py-12">
        <!-- Logo i nagłówek tylko dla widoku mobilnego -->
        <div class="md:hidden mb-8">
            <img src="./assets/images/logo.png" alt="LoveBlog Logo" class="h-10 w-auto mx-auto" style="max-width: 340px;">
            <h1 class="text-2xl font-medium text-primary mt-4 mb-2 text-center">
                Zarejestruj się, aby korzystać z naszego systemu!
            </h1>
            <p class="text-gray-600 text-center">
                COPYRIGHT© 2024 BY LOVEBLOG. ALL RIGHTS RESERVED.
            </p>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Sukces!</strong>
                <span class="block sm:inline"> Rejestracja zakończona pomyślnie. Możesz się teraz <a href="login.php" class="text-primary font-medium">zalogować</a>.</span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['db'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Błąd!</strong>
                <span class="block sm:inline"> <?php echo $errors['db']; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-5">
            <!-- Imię -->
            <div>
                <label for="name" class="block text-primary font-medium mb-1">Imię:</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="<?php echo htmlspecialchars($name); ?>"
                    class="w-full border-b border-primary focus:border-primary-dark outline-none py-2 px-1"
                    required
                >
                <?php if (isset($errors['name'])): ?>
                    <p class="error-message"><?php echo $errors['name']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Wiek -->
            <div>
                <label for="age" class="block text-primary font-medium mb-1">Wiek:</label>
                <input 
                    type="number" 
                    id="age" 
                    name="age" 
                    value="<?php echo htmlspecialchars($age); ?>"
                    class="w-full border-b border-primary focus:border-primary-dark outline-none py-2 px-1"
                    min="18"
                    required
                >
                <?php if (isset($errors['age'])): ?>
                    <p class="error-message"><?php echo $errors['age']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Adres e-mail -->
            <div>
                <label for="email" class="block text-primary font-medium mb-1">Adres e-mail:</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($email); ?>"
                    class="w-full border-b border-primary focus:border-primary-dark outline-none py-2 px-1"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <p class="error-message"><?php echo $errors['email']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Hasło -->
            <div>
                <label for="password" class="block text-primary font-medium mb-1">Hasło:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password"
                    class="w-full border-b border-primary focus:border-primary-dark outline-none py-2 px-1"
                    required
                    minlength="8"
                >
                <?php if (isset($errors['password'])): ?>
                    <p class="error-message"><?php echo $errors['password']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Powtórz hasło -->
            <div>
                <label for="confirm_password" class="block text-primary font-medium mb-1">Powtórz hasło:</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password"
                    class="w-full border-b border-primary focus:border-primary-dark outline-none py-2 px-1"
                    required
                >
                <?php if (isset($errors['confirm_password'])): ?>
                    <p class="error-message"><?php echo $errors['confirm_password']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Płeć -->
            <div>
                <label class="block text-primary font-medium mb-3">Płeć:</label>
                <div class="flex space-x-8">
                    <label class="inline-flex items-center">
                        <input 
                            type="radio" 
                            name="gender" 
                            value="male" 
                            <?php echo $gender === 'male' ? 'checked' : ''; ?> 
                            class="h-4 w-4 text-primary"
                        >
                        <span class="ml-2">Mężczyzna</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input 
                            type="radio" 
                            name="gender" 
                            value="female" 
                            <?php echo $gender === 'female' ? 'checked' : ''; ?> 
                            class="h-4 w-4 text-primary"
                        >
                        <span class="ml-2">Kobieta</span>
                    </label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <p class="error-message"><?php echo $errors['gender']; ?></p>
                <?php endif; ?>
            </div>

            <!-- Polityka Prywatności -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input 
                        type="checkbox" 
                        id="privacy_policy" 
                        name="privacy_policy" 
                        class="h-4 w-4 text-primary"
                    >
                </div>
                <label for="privacy_policy" class="ml-2 text-sm">
                    Akceptuję <a href="#" class="text-primary hover:underline">Politykę Prywatności</a>
                </label>
                <?php if (isset($errors['privacy_policy'])): ?>
                    <p class="error-message ml-6"><?php echo $errors['privacy_policy']; ?></p>
                <?php endif; ?>
            </div>

            <!-- Przycisk rejestracji -->
            <div>
                <button 
                    type="submit" 
                    class="w-full bg-button-gradient btn-hover-effect text-white py-3 rounded-full font-medium"
                >
                    Zarejestruj się!
                </button>
            </div>

            <!-- Link do logowania -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                <a href="?page=login" class=" hover:underline"> <span class="text-black">Masz już konto?</span> <span class="text-primary">Zaloguj się!</span></a>
                </p>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>

</body>
</html>