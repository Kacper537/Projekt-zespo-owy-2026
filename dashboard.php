<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

/* =========================
   WYBÓR MIESIĄCA I ROKU
========================= */
$selectedMonth = $_GET['month'] ?? date('Y-m');
$monthPattern = $selectedMonth . '%';

// Wyciągamy sam rok (pierwsze 4 znaki z np. "2026-06") do filtracji rocznej
$selectedYear = substr($selectedMonth, 0, 4);
$yearPattern = $selectedYear . '%';

/* =========================
   WYDATKI (Miesięczne i Roczne)
========================= */
// Miesięczne
$stmt = $db->prepare("
SELECT SUM(amount) as total
FROM expenses
WHERE user_id = ?
AND date LIKE ?
");
$stmt->execute([$user_id, $monthPattern]);
$totalMonth = $stmt->fetch()['total'] ?? 0;

// Roczne
$stmt = $db->prepare("
SELECT SUM(amount) as total
FROM expenses
WHERE user_id = ?
AND date LIKE ?
");
$stmt->execute([$user_id, $yearPattern]);
$totalYearExpenses = $stmt->fetch()['total'] ?? 0;

/* =========================
   PRZYCHODY (Miesięczne i Roczne)
========================= */
// Miesięczne
$stmt = $db->prepare("
SELECT SUM(amount) as total_income
FROM incomes
WHERE user_id = ?
AND date LIKE ?
");
$stmt->execute([$user_id, $monthPattern]);
$totalIncome = $stmt->fetch()['total_income'] ?? 0;

// Roczne
$stmt = $db->prepare("
SELECT SUM(amount) as total_income
FROM incomes
WHERE user_id = ?
AND date LIKE ?
");
$stmt->execute([$user_id, $yearPattern]);
$totalYearIncome = $stmt->fetch()['total_income'] ?? 0;

/* =========================
   BILANS (Miesięczny i Roczny)
========================= */
$balance = $totalIncome - $totalMonth;
$yearBalance = $totalYearIncome - $totalYearExpenses;

/* =========================
   OSTATNIE WYDATKI
========================= */
$stmt = $db->prepare("
SELECT * FROM expenses
WHERE user_id = ?
AND date LIKE ?
ORDER BY date DESC, id DESC
LIMIT 5
");

$stmt->execute([$user_id, $monthPattern]);
$recentExpenses = $stmt->fetchAll();

/* =========================
   WYDATKI - WYKRES
========================= */
$stmt = $db->prepare("
SELECT category, SUM(amount) as sum
FROM expenses
WHERE user_id = ?
AND date LIKE ?
GROUP BY category
");

$stmt->execute([$user_id, $monthPattern]);
$expenseChartData = $stmt->fetchAll();

$expenseCategories = [];
$expenseSums = [];

foreach ($expenseChartData as $row) {
    $expenseCategories[] = $row['category'];
    $expenseSums[] = $row['sum'];
}

/* =========================
   PRZYCHODY - WYKRES
========================= */
$stmt = $db->prepare("
SELECT source as category, SUM(amount) as sum
FROM incomes
WHERE user_id = ?
AND date LIKE ?
GROUP BY source
");

$stmt->execute([$user_id, $monthPattern]);
$incomeChartData = $stmt->fetchAll();

$incomeCategories = [];
$incomeSums = [];

foreach ($incomeChartData as $row) {
    $incomeCategories[] = $row['category'];
    $incomeSums[] = $row['sum'];
}

/* =========================
   BUDŻETY
========================= */
$stmt = $db->prepare("
SELECT b.category, b.amount_limit,
COALESCE(SUM(e.amount), 0) as current_spent
FROM budgets b
LEFT JOIN expenses e
ON b.category = e.category
AND e.user_id = b.user_id
AND e.date LIKE ?
WHERE b.user_id = ?
GROUP BY b.category, b.amount_limit
");

$stmt->execute([$monthPattern, $user_id]);
$budgets = $stmt->fetchAll();

/* =========================
   HISTORIA
========================= */
$stmt = $db->prepare("
SELECT 'expense' as type, amount, category as name, date, description
FROM expenses
WHERE user_id = ?
AND date LIKE ?

UNION ALL

SELECT 'income' as type, amount, source as name, date, description
FROM incomes
WHERE user_id = ?
AND date LIKE ?

ORDER BY date DESC
LIMIT 20
");

$stmt->execute([$user_id, $monthPattern, $user_id, $monthPattern]);
$history = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<?php $activePage = 'dashboard'; include 'header.php'; ?>

<div class="container mb-5">

<form method="GET" class="card p-3 mb-3 shadow-sm">
<div class="row align-items-center">

<div class="col-auto">
<strong>Miesiąc:</strong>
</div>

<div class="col-auto">
<input type="month"
name="month"
value="<?= $selectedMonth ?>"
class="form-control"
onchange="this.form.submit()">
</div>

</div>
</form>

<div class="row g-3 mb-3">

    <div class="col-lg-6 col-md-12">
         <div class="card <?= $balance >= 0 ? 'bg-success' : 'bg-danger' ?> text-white text-center h-100" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: center; shadow-sm">
            <h4 class="mb-1 opacity-75">BILANS MIESIĄCA</h4>
            <h2 class="fw-bold mb-3"><?= number_format($balance, 2, ',', ' ') ?> zł</h2>
            <div class="d-flex justify-content-around border-top pt-2 opacity-90">
                <span>Przychody: <strong><?= number_format($totalIncome, 2, ',', ' ') ?> zł</strong></span>
                <span>Wydatki: <strong><?= number_format($totalMonth, 2, ',', ' ') ?> zł</strong></span>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-12">
         <div class="card <?= $yearBalance >= 0 ? 'bg-success' : 'bg-danger' ?> text-white text-center h-100" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: center; shadow-sm">
            <h4 class="mb-1 opacity-75">BILANS ROKU (<?= htmlspecialchars($selectedYear) ?>)</h4>
            <h2 class="fw-bold mb-3"><?= number_format($yearBalance, 2, ',', ' ') ?> zł</h2>
            <div class="d-flex justify-content-around border-top pt-2 opacity-90">
                <span>Przychody: <strong><?= number_format($totalYearIncome, 2, ',', ' ') ?> zł</strong></span>
                <span>Wydatki: <strong><?= number_format($totalYearExpenses, 2, ',', ' ') ?> zł</strong></span>
            </div>
        </div>
    </div>

</div>

<div class="row g-3 mb-3">

    <div class="col-lg-7 col-md-12">
        <div class="card p-3 h-100 shadow-sm">
            <h5>Historia</h5>
            <div style="max-height:350px; overflow-y:auto;">
                <table class="table table-sm align-middle mb-0">
                    <?php if (empty($history)): ?>
                        <tr><td colspan="4" class="text-muted text-center py-3">Brak operacji w tym miesiącu.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= $h['date'] ?></td>
                        <td>
                            <?php if ($h['type'] === 'income'): ?>
                                <span class="badge bg-success">+</span>
                            <?php else: ?>
                                <span class="badge bg-danger">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span title="<?= htmlspecialchars($h['name']) ?>" style="cursor: help;">
                                <?= shortenCategory($h['name']) ?>
                            </span>
                        </td>
                        <td class="text-end fw-bold <?= $h['type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($h['amount'], 2, ',', ' ') ?> zł
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5 col-md-12">
        <div class="card p-3 h-100 shadow-sm">
            <h5>Limity</h5>
            <div style="max-height:350px; overflow-y:auto;">
                <?php if (empty($budgets)): ?>
                    <p class="text-muted text-center py-3 mb-0">Brak zdefiniowanych limitów.</p>
                <?php endif; ?>
                <?php foreach ($budgets as $b):
                $pct = $b['amount_limit'] > 0 ? ($b['current_spent'] / $b['amount_limit']) * 100 : 0;
                
                // ZMIANA: Zaktualizowane progi procentowe kolorów pasków
                if ($pct >= 100) {
                    $color = 'bg-danger text-white';   // Czerwony od 100%
                } elseif ($pct >= 70) {
                    $color = 'bg-warning text-black';  // Żółty od 70% do 99%
                } else {
                    $color = 'bg-success text-white';  // Zielony poniżej 70%
                }
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-bold"><?= htmlspecialchars($b['category']) ?></small>
                        <small class="text-muted"><?= number_format($b['current_spent'], 2, ',', ' ') ?> / <?= number_format($b['amount_limit'], 2, ',', ' ') ?> zł</small>
                    </div>
                    <div class="progress" style="height:18px;">
                        <div class="progress-bar <?= $color ?>" style="width: <?= min($pct,100) ?>%">
                            <?= round($pct) ?>%
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<div class="row g-3">

    <div class="col-md-6 col-12">
        <div class="card p-3 h-100 text-center shadow-sm">
            <h5>Wydatki (Struktura)</h5>
            <div style="height:300px; position: relative;">
                <canvas id="expenseChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-12">
        <div class="card p-3 h-100 text-center shadow-sm">
            <h5>Przychody (Struktura)</h5>
            <div style="height:300px; position: relative;">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>
    </div>

</div>

</div>

<script>
// Funkcja JS do skracania etykiet na wykresie (na wzór PHP)
function jsShorten(text) {
    if (text.length <= 15) return text;
    let sub = text.substring(0, 15);
    if (sub.endsWith(' ')) sub = sub.substring(0, 14);
    return sub + '...';
}

window.expenseLabels = <?= json_encode($expenseCategories) ?>.map(jsShorten) ?? [];
window.expenseData = <?= json_encode($expenseSums) ?> ?? [];

window.incomeLabels = <?= json_encode($incomeCategories) ?>.map(jsShorten) ?? [];
window.incomeData = <?= json_encode($incomeSums) ?> ?? [];
</script>

<script src="charts.js"></script>

</body>
</html>