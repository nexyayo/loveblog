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