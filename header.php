<?php
// Shared header / navbar
// Expects an optional $activePage variable: 'dashboard', 'expenses', 'income', 'budgets'
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">

        <a class="navbar-brand" href="dashboard.php">
            <img src="logo.png" alt="Logo" width="40" height="40" class="me-2">
            Twój portfel (Witaj, <?= htmlspecialchars(
                
                
                
                
                
                $_SESSION['username'] ?? ''
            ) ?>)
        </a>

        <div class="d-flex align-items-center">
            <div class="navbar-nav">
                <a class="nav-link <?= (isset($activePage) && $activePage === 'dashboard') ? 'active' : '' ?>" href="dashboard.php">Panel główny</a>
                <a class="nav-link <?= (isset($activePage) && $activePage === 'expenses') ? 'active' : '' ?>" href="expenses.php">Wydatki</a>
                <a class="nav-link <?= (isset($activePage) && $activePage === 'income') ? 'active' : '' ?>" href="income.php">Przychody</a>
                <a class="nav-link <?= (isset($activePage) && $activePage === 'budgets') ? 'active' : '' ?>" href="budgets.php">Budżety</a>
                <a class="nav-link logout-link" href="logout.php">Wyloguj</a>
            </div>

            <button id="theme-toggle" class="btn btn-outline-light btn-sm ms-3" title="Zmień tło" aria-pressed="false">Tło</button>
        </div>

    </div>
</nav>

<script>
// Theme toggle: toggles 'dark-theme' class on body and persists choice in localStorage
(function(){
    var btn = document.getElementById('theme-toggle');
    if(!btn) return;

    function applyTheme(theme){
        if(theme === 'dark') document.body.classList.add('dark-theme');
        else document.body.classList.remove('dark-theme');
        // keep button label stable ('Tło'), only update aria-pressed for accessibility
        btn.setAttribute('aria-pressed', document.body.classList.contains('dark-theme'));
    }

    var saved = localStorage.getItem('theme');
    if(!saved){
        saved = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    applyTheme(saved);

    btn.addEventListener('click', function(){
        var isDark = document.body.classList.toggle('dark-theme');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        applyTheme(isDark ? 'dark' : 'light');
    });
})();
</script>
