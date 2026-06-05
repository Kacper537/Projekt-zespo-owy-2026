<?php
try {
    // Łączymy się z MySQL w XAMPP, wskazując bazę budget_planner
    $db = new PDO('mysql:host=localhost;dbname=budget_planner;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Tabele stworzyłeś już bezpośrednio w phpMyAdmin za pomocą kodu SQL, 
    // więc tutaj nie musimy pisać kodu tworzącego tabele przez PHP.

} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}


function shortenCategory($text) {
    $prog = 10; // TUTAJ: wpisujesz nowy próg długości (np. 10, 8, 12)
    
    if (mb_strlen($text, 'UTF-8') <= $prog) {
        return htmlspecialchars($text);
    }
    
    // Pobieramy wybraną liczbę znaków
    $sub = mb_substr($text, 0, $prog, 'UTF-8');
    
    // Warunek: Jeśli ostatni znak to spacja, cofamy się o 1
    if (mb_substr($sub, -1, 1, 'UTF-8') === ' ') {
        $sub = mb_substr($sub, 0, $prog - 1, 'UTF-8');
    }
    
    return htmlspecialchars($sub) . '...';
}
?>
