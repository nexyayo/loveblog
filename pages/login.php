<?php
// Dołączenie pliku połączenia z bazą danych
require_once __DIR__ . '/../config/db.php';

// Inicjalizacja zmiennych
$email = '';
$password = '';
$errors = [];
$success = false;

// Obsługa przesłanego formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobranie i sanityzacja danych z formularza
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // Walidacja danych
    if (empty($email)) {
        $errors['email'] = 'Adres e-mail jest wymagany.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Podaj prawidłowy adres e-mail.';
    }

    if (empty($password)) {
        $errors['password'] = 'Hasło jest wymagane.';
    }

    // Jeśli brak błędów, sprawdź dane logowania
    if (empty($errors)) {
        try {
            // Wyszukanie użytkownika po emailu
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Poprawne dane logowania
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Obsługa "Zapamiętaj mnie"
                if ($remember_me) {
                    // Generowanie tokenu
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + 600; // 10 minut
                    
                    // Zapisanie tokenu w bazie danych
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, token_expires = ? WHERE id = ?");
                    $stmt->execute([$token, date('Y-m-d H:i:s', $expires), $user['id']]);
                    
                    // Ustawienie ciasteczka
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }
                
                // Przekierowanie do strony głównej lub dashboardu
                header('Location: ?page=main');
                exit;
            } else {
                $errors['login'] = 'Nieprawidłowy adres e-mail lub hasło.';
            }
        } catch (PDOException $e) {
            $errors['db'] = 'Wystąpił błąd podczas logowania. Spróbuj ponownie później.';
            error_log('Błąd logowania: ' . $e->getMessage());
        }
    }
}

// Sprawdzenie automatycznego logowania przez "Zapamiętaj mnie"
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
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
            
            // Przekierowanie do strony głównej lub dashboardu
            header('Location: ?page=main');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Błąd automatycznego logowania: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - LoveBlog</title>
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
    </style>
</head>
<body class="font-sen min-h-screen bg-white">

<div class="flex flex-col md:flex-row min-h-screen">
    <!-- Lewa strona - Zdjęcie tła na większych ekranach -->
    <div class="hidden md:block md:w-1/2 bg-cover bg-center" style="background-image: url('./assets/images/register-bg.png');">
        <div class="flex flex-col justify-center items-center h-full p-12">
            <img src="./assets/images/logo.png" alt="LoveBlog Logo" class="h-12 lg:h-[75px] w-auto mb-6" style="max-width: 340px;">
            <h1 class="text-2xl md:text-3xl font-medium text-white mb-2 text-center">
                Zaloguj się, aby kontynuować!
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
                Zaloguj się, aby kontynuować!
            </h1>
            <p class="text-gray-600 text-center">
                COPYRIGHT© 2024 BY LOVEBLOG. ALL RIGHTS RESERVED.
            </p>
        </div>
        
        <?php if (isset($errors['login']) || isset($errors['db'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Błąd!</strong>
                <span class="block sm:inline"> <?php echo $errors['login'] ?? $errors['db']; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-5">
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
                >
                <?php if (isset($errors['password'])): ?>
                    <p class="error-message"><?php echo $errors['password']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Zapamiętaj mnie i Zapomniałem hasła -->
            <div class="flex justify-between items-center">
                <label class="inline-flex items-center">
                    <input 
                        type="checkbox" 
                        name="remember_me" 
                        class="h-4 w-4 text-primary"
                    >
                    <span class="ml-2 text-sm">Zapamiętaj mnie</span>
                </label>
                <a href="forgot-password.php" class="text-primary hover:underline text-sm">Zapomniałem hasła</a>
            </div>

            <!-- Przycisk logowania -->
            <div>
                <button 
                    type="submit" 
                    class="w-full bg-button-gradient btn-hover-effect text-white py-3 rounded-full font-medium"
                >
                    Zaloguj się
                </button>
            </div>

            <!-- Link do rejestracji -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Nie masz jeszcze konta? <a href="?page=register" class="text-primary hover:underline">Zarejestruj się!</a>
                </p>
            </div>
        </form>
    </div>
</div>

</body>
</html>