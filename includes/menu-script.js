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