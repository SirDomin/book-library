# Symfony Book Management Project

## Opis

- Symfony 7.1.3
- PHP 8.3.10
- PostgreSQL
- Docker
- Full Rest API
- Deploy na moim serwerze
- Frontend: [Github Pages](https://sirdomin.github.io/book-library-frontend/)

## Funkcjonalności

1. **Zarządzanie Książkami [BookController](https://github.com/SirDomin/book-library/blob/main/src/Controller/BookController.php):**
    - Dodawanie książek [create_book](https://github.com/SirDomin/book-library/blob/main/src/Controller/BookController.php#L31)
    - Edycja książek [update_book](https://github.com/SirDomin/book-library/blob/main/src/Controller/BookController.php#L94)
    - Usuwanie książek [delete_book](https://github.com/SirDomin/book-library/blob/main/src/Controller/BookController.php#L125)
    - Wyświetlanie listy książek [get_books](https://github.com/SirDomin/book-library/blob/main/src/Controller/BookController.php#L61)
    - Upload okładki książki [FileManager](https://github.com/SirDomin/book-library/blob/main/src/Manager/FileManager.php)

2. **Strona Użytkownika [RegisterController](https://github.com/SirDomin/book-library/blob/main/src/Controller/RegisterController.php):** 
    - Logowanie przy użyciu JWT
    - Rejestracja
    - Wyświetlanie listy książek z ze szczegółowymi informacjami o książce
    - Wyszukiwanie książek po tytule lub autorze (jeden input)

3. **Listener i Eventy:**
    - [Listener](https://github.com/SirDomin/book-library/blob/main/src/EventListener/BookAddedListener.php) nasłuchujący na zdarzenia dodawania nowych książek i zapisujący informacje o dodanej książce do [logów](https://github.com/SirDomin/book-library/blob/main/book_added.log)

4. **Fixtury:**
    - Fixtury, które wprowadza do bazy ponad 1 milion rekordów [HugeBookFixtures](https://github.com/SirDomin/book-library/blob/main/src/DataFixtures/HugeBookFixtures.php)
    - Fixtury uzywane do testów

## Struktura Projektu

### Książka:

- `Tytuł` (string)
- `Autor` (string)
- `Opis` (text)
- `Rok wydania` (integer)
- `ISBN` (string)
- `Zdjęcie książki` (string)

## Instrukcja uruchomienia

1. **Wejdź na https://sirdomin.github.io/book-library-frontend/**
2. **Zaloguj sie na istniejące konto:**
   - email: test@test.test
   - hasło: test
3. **Lub zarejestruj własne**

## Uruchomienie lokalnie

1. **Zbudowanie obrazu i uruchomienie kontenerów**
   ```bash
    docker-compose up -d --build
   ```
2. **Migracje i fixtury**
   ```bash
   docker-compose exec php bash
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```
3. **Testy**
   ```bash
   docker-compose exec php bash
   php bin/phpunit
   ```