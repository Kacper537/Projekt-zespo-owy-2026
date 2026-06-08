<?php
require_once 'database.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {

            $stmt = $db->prepare(
                "INSERT INTO users (username, password)
                 VALUES (?, ?)"
            );

            $stmt->execute([$username, $hashedPassword]);

            header('Location: login.php?registered=1');
            exit;

        } catch (PDOException $e) {
            $error = "Nazwa użytkownika jest już zajęta.";
        }

    } else {
        $error = "Wypełnij wszystkie pola.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width:500px;">

    <a href="index.php" class="d-inline-block mb-3 text-decoration-none">&larr; Powrót do strony głównej</a>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm">

        <h4>Rejestracja</h4>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Login</label>
                <input type="text"
                       name="username"
                       class="form-control"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Hasło</label>
                <input type="password"
                       name="password"
                       class="form-control"
                       required>
            </div>

            <button type="submit"
                    class="btn btn-success w-100">
                Zarejestruj
            </button>

        </form>

    </div>
</div>

</body>
</html>