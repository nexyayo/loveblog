<?php
// Usuwam kod przekierowujący zalogowanych użytkowników
?>
<!DOCTYPE html>
<html lang="pl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoveBlog</title>
    <link href="./assets/css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <style>
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

        /* Dodatkowe style, aby uzyskać dokładnie taki sam wygląd jak na obrazku */
        .opinions-swiper .swiper-slide {
            opacity: 0.8;
            transform: scale(0.9);
            transition: all 0.3s ease;
            height: auto;
            max-width: 420px;
        }

        .opinions-swiper .swiper-slide-active {
            opacity: 1;
            transform: scale(1);
            z-index: 10;
        }

        @media (min-width: 768px) {
            .opinions-swiper .swiper-slide:nth-child(odd) {
                margin-top: 40px;
            }
            
            .opinions-swiper .swiper-slide:nth-child(even) {
                margin-top: -40px;
            }
        }

        .swiper-slide-active .fa-quote-right {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 0.6;
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0.6;
            }
        }

        /* Style dla nawigacji Swipera */
        .swiper-button-next:after, .swiper-button-prev:after {
            font-size: 20px;
            font-weight: bold;
        }

        .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: white;
            opacity: 0.6;
        }

        .swiper-pagination-bullet-active {
            opacity: 1;
            background: white;
        }

        /* Animacja dla kartek */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        .swiper-slide-active .bg-white:nth-child(odd) {
            animation: float 6s ease-in-out infinite;
        }

        .swiper-slide-active .bg-white:nth-child(even) {
            animation: float 6s ease-in-out infinite 2s;
        }

        /* Dodatkowe animacje dla ikon cudzysłowia */
        @keyframes pulse {
            0% {
                opacity: 0.6;
                transform: translate(-50%, -50%) scale(1);
            }
            50% {
                opacity: 0.8;
                transform: translate(-50%, -50%) scale(1.05);
            }
            100% {
                opacity: 0.6;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        /* Animacja dla kartek przy hover */
        .bg-white:hover .fa-quote-right {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body class="font-sen">

<!-- Sekcja Hero ze zdjęciem w tle -->
<div class="relative w-full h-screen">
    <div class="absolute inset-0">
        <img src="./assets/images/homepage_bg.png" alt="Para o zachodzie słońca" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black opacity-40"></div>
    </div>
    
    <!-- Nawigacja -->
    <nav class="relative flex justify-between items-center px-4 sm:px-6 md:px-90px pt-4 md:pt-40px">
        <div class="flex items-center">
            <a href="index.php" class="text-white flex items-center">
                <img src="./assets/images/logo.png" alt="LoveBlog Logo" class="h-8 md:h-12 lg:h-[47px] w-auto max-w-[214px]">
            </a>
        </div>
        <div class="flex items-center space-x-4 md:space-x-6">
            <a href="#about-us" class="text-white hover:text-primary transition-colors duration-300 font-medium">O nas</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="?page=main" class="bg-button-gradient btn-hover-effect text-white px-4 py-2 md:w-215px md:h-50px flex items-center justify-center rounded-full text-sm font-medium">Przejdź do bloga</a>
            <?php else: ?>
                <a href="?page=login" class="bg-button-gradient btn-hover-effect text-white px-4 py-2 md:w-215px md:h-50px flex items-center justify-center rounded-full text-sm font-medium">Zaloguj się</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <!-- Treść sekcji Hero -->
    <div class="relative flex flex-col justify-center items-center h-full text-center text-white px-4 md:px-105px">
        <h1 class="text-2xl md:text-3xl lg:text-4xl max-w-4xl mb-8 font-medium">
            "Niektóre połączenia są tak wyjątkowe, że wystarczy jedno kliknięcie, by zmienić wszystko."
        </h1>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="?page=register" class="bg-button-gradient btn-hover-effect text-white px-8 py-3 md:w-260px md:h-60px flex items-center justify-center rounded-full text-lg font-medium">
                Zarejestruj się!
            </a>
        <?php else: ?>
            <a href="?page=main" class="bg-button-gradient btn-hover-effect text-white px-8 py-3 md:w-260px md:h-60px flex items-center justify-center rounded-full text-lg font-medium">
                Przejdź do bloga
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Sekcja O Nas -->
<section id="about-us" class="py-16 px-4 md:px-90px scroll-mt-16">
    <div class="container mx-auto max-w-4xl">
        <h2 class="text-3xl text-primary text-center font-medium mb-12 flex items-center justify-center">
            <span class="h-px bg-primary/30 w-8 mr-4"></span>
            O Nas
            <span class="h-px bg-primary/30 w-8 ml-4"></span>
        </h2>
        
        <div class="text-primary space-y-6 font-normal">
            <p>
                Witaj w naszej aplikacji, gdzie miłość, przyjaźń i inspirujące rozmowy spotykają się w jednym miejscu! Naszym celem jest łączenie ludzi, którzy szukają prawdziwych relacji, ciekawych dyskusji i nowych doświadczeń.
            </p>
            
            <p>
                Dzięki funkcji <span class="font-medium">swipowania</span> znajdziesz osoby, które pasują do Twoich zainteresowań i preferencji.
            </p>
            
            <p>
                W sekcji tematów <span class="font-medium">dyskusji</span> możesz dzielić się swoimi przemyśleniami, komentować posty innych i nawiązywać wartościowe rozmowy. A gdy już znajdziesz swojego matcha, nic nie stoi na przeszkodzie, by zacząć pisać i budować więzi.
            </p>
            
            <p>
                Jesteśmy tu, by pomóc Ci odkryć, że miłość i przyjaźń są bliżej, niż myślisz. Dołącz do nas i daj się ponieść tej wyjątkowej przygodzie!
            </p>
        </div>
    </div>
</section>

<!-- Sekcja opinii -->
<section class="py-16 bg-[#e34b76]">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-white text-center mb-16 flex items-center justify-center">
            <span class="inline-block w-12 h-[1px] bg-white mr-6"></span>
            Opinie
            <span class="inline-block w-12 h-[1px] bg-white ml-6"></span>
        </h2>
        
        <!-- Grid z opiniami - 1 kolumna na mobile, 3 kolumny na desktop -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <!-- Opinia 1 -->
            <div class="bg-white rounded-xl shadow-lg p-6 relative transform transition-transform duration-300 hover:-translate-y-2">
                <h3 class="text-[#e34b76] text-xl font-medium border-b border-[#e34b76] pb-2 mb-4 w-1/3">Maja</h3>
                <p class="text-[#e34b76] text-sm leading-relaxed mb-6">
                    Nie sądziłam, że kiedykolwiek coś z tego wyjdzie, ale pewnego dnia zobaczyłam w aplikacji profil pewnej oszałamiająco pięknej dziewczyny. Wydawała się taka super, że za dodatkowego dolara wysłałam jej Superlajka
                </p>
                <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 text-[#e34b76] text-6xl opacity-80">
                    <i class="fas fa-quote-right"></i>
                </div>
            </div>
            
            <!-- Opinia 2 - na desktop podniesiona do góry -->
            <div class="bg-white rounded-xl shadow-lg p-6 relative md:-mt-10 transform transition-transform duration-300 hover:-translate-y-2">
                <h3 class="text-[#e34b76] text-xl font-medium border-b border-[#e34b76] pb-2 mb-4 w-1/3">Adrian</h3>
                <p class="text-[#e34b76] text-sm leading-relaxed mb-6">
                    Na LoveBlog trafiłem przypadkiem, szukając miejsca do dzielenia się swoimi przemyśleniami. Nie spodziewałem się, że poznając czyjś umysł, można zakochać się tak mocno. Dziś, po dwóch latach, nie wyobrażam sobie życia bez tej osoby.
                </p>
                <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 text-[#e34b76] text-6xl opacity-80">
                    <i class="fas fa-quote-right"></i>
                </div>
            </div>
            
            <!-- Opinia 3 -->
            <div class="bg-white rounded-xl shadow-lg p-6 relative transform transition-transform duration-300 hover:-translate-y-2">
                <h3 class="text-[#e34b76] text-xl font-medium border-b border-[#e34b76] pb-2 mb-4 w-1/3">Kasia</h3>
                <p class="text-[#e34b76] text-sm leading-relaxed mb-6">
                    Zawsze byłam nieśmiała w kontaktach bezpośrednich. LoveBlog dał mi możliwość pokazania mojej prawdziwej osobowości poprzez teksty. Tutaj poznałam osobę, która najpierw pokochała moje myśli, a potem mnie całą.
                </p>
                <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 text-[#e34b76] text-6xl opacity-80">
                    <i class="fas fa-quote-right"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sekcja linków w stopce -->
<section class="py-12 px-4 md:px-90px border-t border-gray-200">
    <div class="container mx-auto max-w-4xl">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Adres -->
            <div>
                <h3 class="text-primary font-medium mb-4 flex items-center">
                    <img src="./assets/images/location.png" alt="Location Icon" class="h-5 w-5 mr-2">
                    ADRES
                </h3>
                <p class="text-primary text-sm">LOVEBLOG SP. ZOO</p>
                <p class="text-primary text-sm">UL. WARSZAWSKA 150/2</p>
                <p class="text-primary text-sm">05-300 MIŃSK MAZOWIECKI</p>
            </div>
            
            <!-- Kontakt -->
            <div>
                <h3 class="text-primary font-medium mb-4 flex items-center">
                    <img src="./assets/images/mail.png" alt="Mail Icon" class="h-5 w-5 mr-2">
                    KONTAKT
                </h3>
                <p class="text-primary text-sm">support@loveblog.pl</p>
                <p class="text-primary text-sm">+48 571 928 319</p>
            </div>
            
            <!-- Linki -->
            <div>
                <h3 class="text-primary font-medium mb-4 flex items-center">
                    <img src="./assets/images/icon.png" alt="Icon" class="h-5 w-5 mr-2">
                    NASZA STRONA
                </h3>
                <p><a href="#about-us" class="text-primary text-sm hover:text-primary transition-colors duration-300 cursor-pointer">O NAS</a></p>
                <p><a href="#testimonials" class="text-primary text-sm hover:tracking-[5px] hover:font-bold transition-all duration-300 cursor-pointer">OPINIE</a></p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <p><a href="?page=register" class="text-primary text-sm hover:text-primary transition-colors duration-300 cursor-pointer">ZAREJESTRUJ SIĘ</a></p>
                <?php else: ?>
                    <p><a href="?page=main" class="text-primary text-sm hover:text-primary transition-colors duration-300 cursor-pointer">PRZEJDŹ DO BLOGA</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Prawa autorskie -->
<div class="border-t border-gray-200 py-4">
    <div class="container mx-auto max-w-4xl text-center text-primary text-xs">
        COPYRIGHT© 2024 BY LOVEBLOG. ALL RIGHTS RESERVED.
    </div>
</div>

<!-- Inicjalizacja Swipera -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const swiper = new Swiper('.opinions-swiper', {
        spaceBetween: 30,
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        speed: 800,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
});
</script>

</body>
</html>