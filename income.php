<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$incomeToEdit = null;

/* =========================
   DODAWANIE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_income'])) {

    $amount = floatval($_POST['amount']);
    $source = trim($_POST['source']);
    $date = $_POST['date'];
    $description = trim($_POST['description'] ?? '');

    if ($amount > 0 && !empty($source) && !empty($date)) {

        $stmt = $db->prepare("
            INSERT INTO incomes (user_id, amount, source, date, description)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $amount,
            $source,
            $date,
            $description
        ]);
    }

    header("Location: income.php");
    exit;
}

/* =========================
   UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_income'])) {

    $income_id = intval($_POST['income_id']);
    $amount = floatval($_POST['amount']);
    $source = trim($_POST['source']);
    $date = $_POST['date'];
    $description = trim($_POST['description'] ?? '');

    if ($amount > 0 && !empty($source) && !empty($date)) {

        $stmt = $db->prepare("
            UPDATE incomes
            SET amount = ?, source = ?, date = ?, description = ?
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([
            $amount,
            $source,
            $date,
            $description,
            $income_id,
            $user_id
        ]);
    }

    header("Location: income.php");
    exit;
}

/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $db->prepare("
        DELETE FROM incomes
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([$id, $user_id]);

    header("Location: income.php");
    exit;
}

/* =========================
   EDIT MODE
========================= */
if (isset($_GET['edit'])) {

    $id = intval($_GET['edit']);

    $stmt = $db->prepare("
        SELECT *
        FROM incomes
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([$id, $user_id]);
    $incomeToEdit = $stmt->fetch();
}

$isEditing = !empty($incomeToEdit);

/* =========================
   LISTA (ZMIANA: Dodano filtrowanie po miesiącu z zachowaniem struktury)
========================= */
$filter_month = $_GET['month'] ?? '';

$query = "SELECT * FROM incomes WHERE user_id = ?";
$params = [$user_id];

if (!empty($filter_month)) {
    $query .= " AND date LIKE ?";
    $params[] = $filter_month . '%';
}

$query .= " ORDER BY date DESC, id DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$incomes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Przychody</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-light">

<?php $activePage = 'income'; include 'header.php'; ?>

<div class="container">

<div class="row">

    <div class="col-md-4 mb-4">

        <div class="card p-4 shadow-sm">

            <h5>
                <?= $isEditing ? 'Edytuj przychód' : 'Dodaj przychód' ?>
            </h5>

            <form method="POST">

                <?php if ($isEditing): ?>
                    <input type="hidden" name="update_income" value="1">
                    <input type="hidden" name="income_id" value="<?= $incomeToEdit['id'] ?>">
                <?php else: ?>
                    <input type="hidden" name="add_income" value="1">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Kwota (zł)</label>
                    <input type="number"
                           step="0.01"
                           name="amount"
                           class="form-control"
                           value="<?= $isEditing ? $incomeToEdit['amount'] : '' ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Źródło</label>
                    <input type="text"
                           name="source"
                           class="form-control"
                           placeholder="np. Pensja, Freelance, Sprzedaż..."
                           value="<?= $isEditing ? htmlspecialchars($incomeToEdit['source']) : '' ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Data</label>
                    <input type="date"
                           name="date"
                           class="form-control"
                           value="<?= $isEditing ? $incomeToEdit['date'] : date('Y-m-d') ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Opis</label>
                    <textarea name="description" class="form-control"><?= $isEditing ? htmlspecialchars($incomeToEdit['description']) : '' ?></textarea>
                </div>

                <button class="btn btn-success w-100">
                    <?= $isEditing ? 'Zapisz zmiany' : 'Dodaj przychód' ?>
                </button>

            </form>

        </div>

    </div>

    <div class="col-md-8">

        <div class="card p-3 shadow-sm mb-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <h6>Filtr:</h6>
                </div>
                <div class="col-auto">
                    <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($filter_month) ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-secondary btn-sm">Filtruj</button>
                </div>
                <?php if (!empty($filter_month)): ?>
                    <div class="col-auto">
                        <a href="income.php" class="btn btn-outline-danger btn-sm">Wszystkie</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="card p-4 shadow-sm">

            <h5>Historia przychodów</h5>

            <table class="table table-striped table-fixed-layout">
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Żródło</th>
                    <th>Kwota</th>
                    <th>Opis</th>
                    <th>Akcja</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($incomes as $i): ?>
                    <tr>
                        <td><?= $i['date'] ?></td>

                        <td class="col-fixed-category">
                            <div class="table-cell-container">
                                <?php if (shortenCategory($i['source']) !== htmlspecialchars($i['source'])): ?>
                                    <span id="inc_cat_short_<?= $i['id'] ?>" class="badge bg-success" style="cursor: pointer;" 
                                        onclick="document.getElementById('inc_cat_short_<?= $i['id'] ?>').style.display='none'; document.getElementById('inc_cat_full_<?= $i['id'] ?>').style.display='inline-block';" 
                                        title="Kliknij, aby zobaczyć całość">
                                        <?= shortenCategory($i['source']) ?>
                                    </span>
                                    <span id="inc_cat_full_<?= $i['id'] ?>" class="badge bg-success" style="cursor: pointer; display: none; white-space: normal; word-break: break-word;" 
                                        onclick="document.getElementById('inc_cat_full_<?= $i['id'] ?>').style.display='none'; document.getElementById('inc_cat_short_<?= $i['id'] ?>').style.display='inline-block';" 
                                        title="Kliknij, aby schować">
                                        <?= htmlspecialchars($i['source']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= htmlspecialchars($i['source']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>

                        

                        <td>
                            <strong class="text-success">
                                +<?= number_format($i['amount'], 2, ',', ' ') ?> zł
                            </strong>
                        </td>

                        <td>
                            <div class="table-cell-container">
                                <div id="inc_desc_<?= $i['id'] ?>" class="clamp-description"><?= htmlspecialchars($i['description']) ?></div>
                                <span id="inc_btn_<?= $i['id'] ?>" class="toggle-text-btn" style="display: none;" onclick="toggleText(<?= $i['id'] ?>, 'inc')">... pokaż więcej</span>
                            </div>
                        </td>

                        <td>
                            <a href="income.php?edit=<?= $i['id'] ?>" class="btn btn-warning btn-sm">Edytuj</a>
                            <a href="income.php?delete=<?= $i['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Usunąć?')">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($incomes)): ?>
                    <tr>
                        <td colspan="5" class="text-muted text-center">Brak przychodów spełniających kryteria.</td>
                    </tr>
                <?php endif; ?>

                </tbody>


            </table>
            <script>
            function toggleText(id, prefix) {
                var textEl = document.getElementById(prefix + '_desc_' + id);
                var btnEl = document.getElementById(prefix + '_btn_' + id);
                
                if (textEl.classList.contains('text-fully-expanded')) {
                    textEl.classList.remove('text-fully-expanded');
                    btnEl.innerText = '... pokaż więcej';
                } else {
                    textEl.classList.add('text-fully-expanded');
                    btnEl.innerText = 'pokaż mniej';
                }
            }

            document.addEventListener("DOMContentLoaded", function() {
                // Automatyczne pokazywanie przycisku "... pokaż więcej" tylko dla długich opisów
                <?php foreach ($incomes as $i): ?>
                (function() {
                    var textEl = document.getElementById('inc_desc_<?= $i['id'] ?>');
                    var btnEl = document.getElementById('inc_btn_<?= $i['id'] ?>');
                    if (textEl && textEl.scrollHeight > textEl.clientHeight) {
                        btnEl.style.display = 'inline-block';
                    }
                })();
                <?php endforeach; ?>
            });
            </script>


        </div>

    </div>

</div>

</div>

</body>
</html>