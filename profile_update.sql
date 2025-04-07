-- Dodanie kolumny interests do tabeli users (przechowywanie zainteresowań jako JSON)
ALTER TABLE users ADD COLUMN interests JSON DEFAULT NULL;

-- Tworzenie tabeli kategorii zainteresowań
CREATE TABLE IF NOT EXISTS interest_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-star', -- Domyślna ikona FontAwesome
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Wypełnienie tabeli kategorii zainteresowań przykładowymi danymi
INSERT INTO interest_categories (name, icon) VALUES 
('Podróże', 'fa-plane'),
('Muzyka', 'fa-music'),
('Film', 'fa-film'),
('Książki', 'fa-book'),
('Sport', 'fa-futbol'),
('Gotowanie', 'fa-utensils'),
('Fotografia', 'fa-camera'),
('Technologia', 'fa-laptop'),
('Moda', 'fa-tshirt'),
('Sztuka', 'fa-palette'),
('Gry', 'fa-gamepad'),
('Zwierzęta', 'fa-paw'),
('Natura', 'fa-leaf'),
('Fitness', 'fa-dumbbell'),
('Taniec', 'fa-music');

-- Modyfikacja struktury tabeli users, aby obsługiwać pliki obrazów
-- Zmiana typu kolumn profile_image i banner_image na TEXT, aby przechowywać ścieżki do plików
ALTER TABLE users MODIFY COLUMN profile_image TEXT DEFAULT NULL;
ALTER TABLE users MODIFY COLUMN banner_image TEXT DEFAULT NULL;

-- Dodanie kolumny phone do tabeli users
ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL;

-- Dodanie kolumny location do tabeli users
ALTER TABLE users ADD COLUMN location VARCHAR(255) DEFAULT NULL;

-- Dodanie kolumny website do tabeli users
ALTER TABLE users ADD COLUMN website VARCHAR(255) DEFAULT NULL;

-- Dodanie kolumny social_links jako JSON do tabeli users
ALTER TABLE users ADD COLUMN social_links JSON DEFAULT NULL; 