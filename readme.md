
# DnD Manager
Lekka, responsywna aplikacja webowa do zarządzania kampaniami i postaciami w systemie Dungeons & Dragons. Pozwala na:  
- rejestrację i logowanie użytkowników (rola _user_ lub _admin_),  
- tworzenie, edytowanie i usuwanie własnych kampanii i postaci,  
- zarządzanie listą znajomych (wysyłanie i akceptacja zaproszeń),  
- wyrzuty kostkami (D20 z menu dodatkowych kości),  
- oddzielny panel administratora do przeglądania wszystkich użytkowników, kampanii i postaci.  

## Wymagania systemowe
- **Apache** ≥ 2.4  
- **PHP** ≥ 8.0 z włączonymi rozszerzeniami:  
  - `pdo_mysql`  
  - `mbstring`  
  - `openssl`  
  - `ctype`  
  - `json`  
- **MySQL / MariaDB** ≥ 5.7  

## Instalacja
1. **Wypakuj** kod aplikacji do katalogu w web root (np. `C:\xampp\htdocs\Projekt_PHP\` lub `/var/www/dnd-manager/`).  
2. Ustaw prawa zapisu dla katalogu `includes/` (instalator musi stworzyć `includes/config.php`):  
   ```bash
   chmod 755 includes
   chmod 666 includes/config.php, a po instalacji przywróć do 644
## Autor

* **Błażej Leszczyński** 
* *nr  albumu: 402656*
* *login: leszczb*

## Wykorzystane zewnętrzne biblioteki
Bootstrap v5.4.3 (CSS i komponenty responsywne)

Popper.js (wbudowany w Bootstrap 5, do dropdownów)

Vanilla JavaScript