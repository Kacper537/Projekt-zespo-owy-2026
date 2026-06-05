<?php
// Shared header / navbar
// Expects an optional $activePage variable: 'dashboard', 'expenses', 'income', 'budgets'
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">

        <a class="navbar-brand" href="dashboard.php">
            <img src="logo.png" alt="Logo" width="40" height="40" class="me-2">
            Twój portfel (Witaj, <?= htmlspecialchars($_SESSION['username'] ?? '') ?>)
        </a>

        <div class="d-flex align-items-center">
            <div class="navbar-nav">
                <a class="nav-link <?= (isset($activePage) && $activePage === 'dashboard') ? 'active' : '' ?>" href="dashboard.php">Panel główny</a>
                <a class="nav-link <?= (isset($activePage) && $activePage === 'expenses') ? 'active' : '' ?>" href="expenses.php">Wydatki</a>
                <a class="nav-link <?= (isset($activePage) && $activePage === 'income') ? 'active' : '' ?>" href="income.php">Przychody</a>
                <a class="nav-link <?= (isset($activePage) && $activePage === 'budgets') ? 'active' : '' ?>" href="budgets.php">Limity</a>
                <a class="nav-link logout-link" href="logout.php">Wyloguj</a>
            </div>

            <!-- Przycisk z bezpośrednim kodem SVG w środku -->
            <button id="theme-toggle" class="btn btn-outline-light btn-sm ms-3" title="Zmień motyw" aria-pressed="false" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; padding: 0;">
                <span id="theme-icon-container">
                    <!-- Ikona słońca (domyślna) -->
                    <svg xmlns="http://w3.org" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                    </svg>
                </span>
            </button>
        </div>

    </div>
</nav>

<script>
(function(){
    var btn = document.getElementById('theme-toggle');
    var container = document.getElementById('theme-icon-container');
    if(!btn || !container) return;

    // Definicje czystego kodu SVG dla słońca i księżyca
    var sunSvg = '<svg xmlns="http://w3.org" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/></svg>';
    var moonSvg = '<svg xmlns="http://w3.org" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg>';

    function applyTheme(theme){
        if(theme === 'dark') {
            document.body.classList.add('dark-theme');
            container.innerHTML = sunSvg; // W nocy pokazujemy słońce, by rozjaśnić
        } else {
            document.body.classList.remove('dark-theme');
            container.innerHTML = moonSvg; // W dzień pokazujemy księżyc, by ściemnić
        }
        btn.setAttribute('aria-pressed', document.body.classList.contains('dark-theme'));
    }

    var saved = localStorage.getItem('theme');
    if(!saved){
        saved = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    applyTheme(saved);

    btn.addEventListener('click', function(){
        var isDark = document.body.classList.toggle('dark-theme');
        var choice = isDark ? 'dark' : 'light';
        localStorage.setItem('theme', choice);
        applyTheme(choice);
    });
})();
</script>

<?php



