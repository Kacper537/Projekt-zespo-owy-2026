<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Planer Wydatków - Strona główna</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 mb-4 bg-primary text-white">
        <div class="row align-items-center gy-4">
            <div class="col-md-3 text-center">
                <img src="logo.png" alt="Logo" class="img-fluid rounded-circle border border-white shadow-sm" style="max-width:120px;">
            </div>
            <div class="col-md-9">
                <h1 class="mb-3">Planer Wydatków</h1>
                <p class="lead mb-4">Webowy system do kontrolowania finansów użytkownika. Śledź wydatki, planuj budżet i analizuj przychody w jednym miejscu.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="login.php" class="btn btn-light btn-lg text-primary">
                        Zaloguj się
                    </a>

                    <a href="register.php" class="btn btn-success btn-lg">
                        Zarejestruj się
                    </a>
                </div>
                <p class="text-white-75 mt-3 mb-0">Po zalogowaniu przejdziesz do swojego spersonalizowanego panelu finansowego.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-xl-4">
            <div class="card p-4 h-100">
                <h4>Śledzenie wydatków</h4>
                <p>Dodaj transakcje z kategorią, datą i opisem, aby zobaczyć, na co wydajesz najwięcej.</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card p-4 h-100">
                <h4>Kontrola budżetu</h4>
                <p>Ustaw limity dla kategorii takich jak jedzenie, transport czy rozrywka i śledź ich wykorzystanie.</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card p-4 h-100">
                <h4>Raporty i statystyki</h4>
                <p>Sprawdź podsumowanie wydatków, przychodów i saldo miesięczne w przejrzystej formie.</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-3">
        <div class="col-12">
            <div class="card p-4 h-100">
                <h4>Co oferuje aplikacja</h4>
                <ul>
                    <li>Rejestracja i logowanie — bezpieczne konta użytkowników</li>
                    <li>Dodawanie wydatków i przychodów z kategoriami, datą i opisem</li>
                    <li>Historia transakcji z możliwością filtrowania, edycji i usuwania</li>
                    <li>Budżety i limity dla kategorii oraz raporty wykorzystania</li>
                    <li>Dashboard z wykresami, bilansami i szybkim podglądem</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>
