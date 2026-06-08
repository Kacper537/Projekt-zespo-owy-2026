<?php
require_once 'database.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
            // Logowanie
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Błędny login lub hasło.";
            }
        }
    } else {
        $error = "Wypełnij wszystkie pola.";
    }

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Planer Wydatków - Logowanie</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <a href="index.php" class="d-inline-block mb-3 text-decoration-none">&larr; Powrót do strony głównej</a>
    <h2 class="text-center mb-4">Kontrola Finansów</h2>
    
    <?php if ($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>
    <?php if ($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>

    <div class="card p-4 shadow-sm mb-4">
        <h4>Zaloguj się</h4>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Login</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Hasło</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Zaloguj
        </button>
    </form>

    <div class="text-center mt-3">
        Nie masz konta?
        <a href="register.php">Zarejestruj się</a>
    </div>
</div>

</div>
</body>
</html>
