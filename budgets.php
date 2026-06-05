<?php
require_once 'database.php';
session_start();

// 1. Sprawdzenie logowania
if (!isset($_SESSION['user_id'])) { 
    header('Location: index.php'); 
    exit; 
}
$current_user = $_SESSION['user_id']; // używamy jednoznacznej zmiennej

// 2. OBSŁUGA USUWANIA (Jeśli kliknięto przycisk Usuń)
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM budgets WHERE id = ? AND user_id = ?");
    $stmt->execute([$id_to_delete, $current_user]);
    
    header("Location: budgets.php");
    exit;
}

if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$user_id = $_SESSION['user_id'];


// Dodawanie/Aktualizacja limitu budżetowego
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $limit = floatval($_POST['limit']);

    if (!empty($category) && $limit >= 0) {
        // Składnia INSERT OR REPLACE (unikalne dla MySQL, nadpisuje rekord jeśli kategoria już istnieje)
        $stmt = $db->prepare("INSERT INTO budgets (user_id, category, amount_limit) 
                      VALUES (?, ?, ?) 
                      ON DUPLICATE KEY UPDATE amount_limit = VALUES(amount_limit)");
        $stmt->execute([$user_id, $category, $limit]);
    }
    header("Location: budgets.php");
    exit;
}

// Pobranie aktualnych limitów użytkownika
$stmt = $db->prepare("SELECT * FROM budgets WHERE user_id = ?");
$stmt->execute([$user_id]);
$currentBudgets = $stmt->fetchAll();

$categories = ['Jedzenie', 'Transport', 'Rachunki', 'Rozrywka', 'Studia', 'Inne'];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Limity Budżetowe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
   
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php $activePage = 'budgets'; include 'header.php'; ?>

<div class="container" style="max-width: 600px;">
    <div class="card p-4 shadow-sm mb-4">
        <h5>Ustaw limit miesięczny</h5>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Kategoria</label>
                <select name="category" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>"><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Limit (zł)</label>
                <input type="number" step="0.01" min="0" name="limit" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Zapisz limit</button>
        </form>
    </div>

    <div class="card p-4 shadow-sm">
        <h5>Zdefiniowane limity</h5>
        <ul class="list-group">
            <?php foreach ($currentBudgets as $b): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <!-- 1. Nazwa -->
                    <div style="flex: 1;"><?= htmlspecialchars($b['category']) ?></div>
                    
                    <!-- 2. Kwota -->
                    <div class="me-3">
                        <span class="badge bg-primary rounded-pill"><?= number_format($b['amount_limit'], 2, ',', ' ') ?> zł</span>
                    </div>
                    
                    <!-- 3. Przycisk usuń -->
                    <div>
                        <a href="budgets.php?delete=<?= $b['id'] ?>" class="btn btn-danger btn-sm py-1 px-2" onclick="return confirm('Usunąć?')">Usuń</a>
                    </div>
                </li>
            <?php endforeach; ?>
            <?php if(empty($currentBudgets)): ?>
                <p class="text-muted text-center mt-2">Brak ustawionych limitów.</p>
            <?php endif; ?>
        </ul>

    </div>
</div>
</body>
</html>
