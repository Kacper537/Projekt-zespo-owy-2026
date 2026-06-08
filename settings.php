<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$activePage = 'settings';

$error = '';
$success = '';

$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (isset($_POST['change_username'])) {

    $newUsername = trim($_POST['new_username']);

    if (!empty($newUsername)) {

        try {

            $stmt = $db->prepare(
                "UPDATE users
                 SET username = ?
                 WHERE id = ?"
            );

            $stmt->execute([$newUsername, $userId]);

            $_SESSION['username'] = $newUsername;

            $success = "Nazwa użytkownika została zmieniona.";

        } catch(PDOException $e) {

            $error = "Ta nazwa użytkownika jest już zajęta.";
        }
    }
}

if (isset($_POST['change_password'])) {

    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];

    $stmt = $db->prepare(
        "SELECT password
         FROM users
         WHERE id = ?"
    );

    $stmt->execute([$userId]);

    $userData = $stmt->fetch();

    if (
        $userData &&
        password_verify(
            $currentPassword,
            $userData['password']
        )
    ) {

        $hash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        $stmt = $db->prepare(
            "UPDATE users
             SET password = ?
             WHERE id = ?"
        );

        $stmt->execute([$hash, $userId]);

        $success = "Hasło zostało zmienione.";

    } else {

        $error = "Aktualne hasło jest nieprawidłowe.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Ustawienia konta</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="style.css">
</head>

<body>

<?php include 'header.php'; ?>

<div class="container">

    <h2 class="mb-4">Ustawienia konta</h2>

    <?php if($error): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <div class="card p-4 mb-4">

        <h4>Zmień nazwę użytkownika</h4>

        <form method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Nowa nazwa użytkownika
                </label>

                <input
                    type="text"
                    name="new_username"
                    class="form-control"
                    value="<?= htmlspecialchars($user['username']) ?>"
                    required>

            </div>

            <button
                type="submit"
                name="change_username"
                class="btn btn-primary">

                Zapisz
            </button>

        </form>

    </div>

    <div class="card p-4">

        <h4>Zmień hasło</h4>

        <form method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Aktualne hasło
                </label>

                <input
                    type="password"
                    name="current_password"
                    class="form-control"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Nowe hasło
                </label>

                <input
                    type="password"
                    name="new_password"
                    class="form-control"
                    required>

            </div>

            <button
                type="submit"
                name="change_password"
                class="btn btn-warning">

                Zmień hasło
            </button>

        </form>

    </div>

</div>

</body>
</html>